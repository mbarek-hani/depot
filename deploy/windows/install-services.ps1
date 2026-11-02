[CmdletBinding()]
param(
    [string]$AppPath = "C:\depot",
    [string]$PhpExe = "C:\php\php.exe",
    [string]$ReverbHost = "0.0.0.0",
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
    Write-Host "`nERROR: $Message" -ForegroundColor Red
    exit 1
}

$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
$currentPrincipal = New-Object Security.Principal.WindowsPrincipal($identity)

if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Stop-Install "Run this script from an Administrator PowerShell window."
}

if (-not (Test-Path -LiteralPath (Join-Path $AppPath "artisan"))) {
    Stop-Install "artisan was not found in $AppPath."
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

$reverbTaskName = "Depot Reverb"
$schedulerTaskName = "Depot Scheduler"

Write-Step "Removing existing deployment tasks"

foreach ($taskName in @($reverbTaskName, $schedulerTaskName)) {
    $existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue

    if ($existingTask) {
        Stop-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
        Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
    }
}

$taskPrincipal = New-ScheduledTaskPrincipal `
    -UserId "SYSTEM" `
    -LogonType ServiceAccount `
    -RunLevel Highest

$taskSettings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -RestartCount 3 `
    -RestartInterval (New-TimeSpan -Minutes 1) `
    -ExecutionTimeLimit ([TimeSpan]::Zero) `
    -MultipleInstances IgnoreNew

$startupTrigger = New-ScheduledTaskTrigger -AtStartup

Write-Step "Creating the Reverb task"

$reverbAction = New-ScheduledTaskAction `
    -Execute $PhpExe `
    -Argument "artisan reverb:start --host=$ReverbHost --port=$ReverbPort" `
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
    -Argument "artisan schedule:work" `
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

    Start-ScheduledTask -TaskName $reverbTaskName
    Start-ScheduledTask -TaskName $schedulerTaskName
}

Write-Host "`nServices installed." -ForegroundColor Green
Write-Host "Reverb listens on ${ReverbHost}:${ReverbPort}."
Write-Host "Check status with:"
Write-Host "  Get-ScheduledTask -TaskName 'Depot Reverb','Depot Scheduler'"
Write-Host "  Get-ScheduledTaskInfo -TaskName 'Depot Reverb','Depot Scheduler'"
