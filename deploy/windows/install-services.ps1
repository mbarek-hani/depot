[CmdletBinding()]
param(
    [string]$AppPath = "C:\depot",
    [string]$PhpExe = "C:\php\php.exe",
    [string]$PhpIni = "",
    [string]$WebHost = "127.0.0.1",
    [int]$WebPort = 80,
    [string]$ReverbHost = "127.0.0.1",
    [int]$ReverbPort = 8080,
    [switch]$SkipStart
)

$ErrorActionPreference = "Stop"

function Write-Step {
    param([string]$Message)
    Write-Host "`n==> $Message" -ForegroundColor Cyan
}

function Stop-Install {
    param([string]$Message)
    throw $Message
}

function Wait-ForPortRelease {
    param(
        [int]$Port,
        [int]$TimeoutSeconds = 10
    )

    $deadline = (Get-Date).AddSeconds($TimeoutSeconds)

    while ((Get-Date) -lt $deadline) {
        $listener = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue

        if (-not $listener) {
            return
        }

        Start-Sleep -Milliseconds 500
    }
}

function Assert-PortAvailable {
    param([int]$Port)

    Wait-ForPortRelease -Port $Port

    $listener = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue |
        Select-Object -First 1

    if ($listener) {
        $processName = "unknown"

        try {
            $processName = (Get-Process -Id $listener.OwningProcess -ErrorAction Stop).ProcessName
        } catch {
        }

        Stop-Install "Port $Port is already in use by $processName (PID $($listener.OwningProcess)). Stop that program before installing the local server."
    }
}

$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
$currentPrincipal = New-Object Security.Principal.WindowsPrincipal($identity)

if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Stop-Install "Run this script from an Administrator PowerShell window."
}

if (-not (Test-Path -LiteralPath (Join-Path $AppPath "artisan"))) {
    Stop-Install "artisan was not found in $AppPath."
}

if (-not (Test-Path -LiteralPath (Join-Path $AppPath "server.php"))) {
    Stop-Install "server.php was not found in $AppPath. The release is incomplete."
}

if (-not (Test-Path -LiteralPath $PhpExe)) {
    $phpCommand = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($phpCommand) {
        $PhpExe = $phpCommand.Source
    }
}

if (-not (Test-Path -LiteralPath $PhpExe)) {
    Stop-Install "PHP was not found. Pass -PhpExe C:\php\php.exe."
}

if ([string]::IsNullOrWhiteSpace($PhpIni)) {
    $PhpIni = Join-Path (Split-Path -Parent $PhpExe) "php.ini"
}

if (-not (Test-Path -LiteralPath $PhpIni)) {
    Stop-Install "PHP configuration was not found: $PhpIni"
}

$webTaskName = "Depot Web"
$reverbTaskName = "Depot Reverb"
$schedulerTaskName = "Depot Scheduler"

Write-Step "Removing existing deployment tasks"

foreach ($taskName in @($webTaskName, $reverbTaskName, $schedulerTaskName)) {
    $existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue

    if ($existingTask) {
        Stop-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
        Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
    }
}

Write-Step "Checking local ports"

Assert-PortAvailable -Port $WebPort
Assert-PortAvailable -Port $ReverbPort

$taskPrincipal = New-ScheduledTaskPrincipal `
    -UserId "SYSTEM" `
    -LogonType ServiceAccount `
    -RunLevel Highest

$taskSettings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -RunOnlyIfNetworkAvailable:$false `
    -RestartCount 3 `
    -RestartInterval (New-TimeSpan -Minutes 1) `
    -ExecutionTimeLimit ([TimeSpan]::Zero) `
    -MultipleInstances IgnoreNew

$startupTrigger = New-ScheduledTaskTrigger -AtStartup

Write-Step "Creating the local web task"

$webArguments = "-c `"$PhpIni`" -S ${WebHost}:${WebPort} -t `"$AppPath\public`" `"$AppPath\server.php`""

$webAction = New-ScheduledTaskAction `
    -Execute $PhpExe `
    -Argument $webArguments `
    -WorkingDirectory $AppPath

Register-ScheduledTask `
    -TaskName $webTaskName `
    -Action $webAction `
    -Trigger $startupTrigger `
    -Principal $taskPrincipal `
    -Settings $taskSettings `
    -Description "Runs the depot application on the local PHP server." `
    -Force | Out-Null

Write-Step "Creating the Reverb task"

$reverbAction = New-ScheduledTaskAction `
    -Execute $PhpExe `
    -Argument "-c `"$PhpIni`" artisan reverb:start --host=$ReverbHost --port=$ReverbPort" `
    -WorkingDirectory $AppPath

Register-ScheduledTask `
    -TaskName $reverbTaskName `
    -Action $reverbAction `
    -Trigger $startupTrigger `
    -Principal $taskPrincipal `
    -Settings $taskSettings `
    -Description "Starts Laravel Reverb for the depot application." `
    -Force | Out-Null

Write-Step "Creating the scheduler task"

$schedulerAction = New-ScheduledTaskAction `
    -Execute $PhpExe `
    -Argument "-c `"$PhpIni`" artisan schedule:work" `
    -WorkingDirectory $AppPath

Register-ScheduledTask `
    -TaskName $schedulerTaskName `
    -Action $schedulerAction `
    -Trigger $startupTrigger `
    -Principal $taskPrincipal `
    -Settings $taskSettings `
    -Description "Runs the Laravel scheduler for the depot application." `
    -Force | Out-Null

if (-not $SkipStart) {
    Write-Step "Starting the tasks"

    Start-ScheduledTask -TaskName $webTaskName
    Start-ScheduledTask -TaskName $reverbTaskName
    Start-ScheduledTask -TaskName $schedulerTaskName
}

$appUrl = "http://127.0.0.1"

if ($WebPort -ne 80) {
    $appUrl = $appUrl + ":" + $WebPort
}

Write-Host "`nStartup tasks installed." -ForegroundColor Green
Write-Host "Application: $appUrl"
Write-Host "Reverb: ws://${ReverbHost}:${ReverbPort}"
Write-Host "Check status with:"
Write-Host "  Get-ScheduledTask -TaskName 'Depot Web','Depot Reverb','Depot Scheduler'"
Write-Host "  Get-ScheduledTaskInfo -TaskName 'Depot Web','Depot Reverb','Depot Scheduler'"
