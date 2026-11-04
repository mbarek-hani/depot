[CmdletBinding()]
param(
    [string]$AppPath = "",
    [string]$PhpExe = "C:\php\php.exe",
    [int]$HttpPort = 80,
    [int]$ReverbPort = 8080,
    [switch]$SkipAdmin,
    [switch]$CreateAdmin,
    [switch]$SkipBrowser
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
        [int]$TimeoutSeconds = 15
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

function Wait-ForApp {
    param(
        [string]$Url,
        [int]$TimeoutSeconds = 30
    )

    $deadline = (Get-Date).AddSeconds($TimeoutSeconds)
    $lastError = $null

    while ((Get-Date) -lt $deadline) {
        try {
            $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 5

            if ($response.StatusCode -ge 200 -and $response.StatusCode -lt 400) {
                return
            }
        } catch {
            $lastError = $_
        }

        Start-Sleep -Seconds 1
    }

    throw "The local application did not respond at $Url. $lastError"
}

$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
$currentPrincipal = New-Object Security.Principal.WindowsPrincipal($identity)

if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Stop-Install "Run this installer from an Administrator PowerShell window."
}

if ([string]::IsNullOrWhiteSpace($AppPath)) {
    $AppPath = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
}

if (-not (Test-Path -LiteralPath $AppPath)) {
    Stop-Install "Application directory was not found: $AppPath"
}

$AppPath = (Resolve-Path -LiteralPath $AppPath).Path

$storageLogs = Join-Path $AppPath "storage\logs"
New-Item -ItemType Directory -Path $storageLogs -Force | Out-Null

$transcriptPath = Join-Path $storageLogs ("install-" + (Get-Date -Format "yyyyMMdd-HHmmss") + ".log")

try {
    Start-Transcript -Path $transcriptPath -Append | Out-Null
} catch {
}

$taskNames = @("Depot Web", "Depot Reverb", "Depot Scheduler")
$apacheWasRunning = @()
$apacheRemoved = $false

try {
    Write-Step "Preparing the local installation"

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
        Stop-Install "PHP was not found. Install PHP 8.3 x64 Thread Safe and pass -PhpExe C:\php\php.exe."
    }

    $PhpDir = Split-Path -Parent $PhpExe
    $PhpIni = Join-Path $PhpDir "php.ini"
    $phpIniTemplate = Join-Path $PSScriptRoot "php.ini.production"

    if (-not (Test-Path -LiteralPath $phpIniTemplate)) {
        Stop-Install "Missing php.ini.production in $PSScriptRoot."
    }

    $extDir = Join-Path $PhpDir "ext"

    if (-not (Test-Path -LiteralPath $extDir)) {
        Stop-Install "PHP extension directory was not found: $extDir"
    }

    $envExisted = Test-Path -LiteralPath (Join-Path $AppPath ".env")

    Write-Step "Stopping existing depot tasks"

    foreach ($taskName in $taskNames) {
        $existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue

        if ($existingTask) {
            Stop-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
        }
    }

    Start-Sleep -Seconds 2

    Write-Step "Stopping Apache"

    $apacheServices = @(
        Get-Service -ErrorAction SilentlyContinue |
            Where-Object {
                $_.Name -like "Apache*" -or
                $_.Name -like "httpd*" -or
                $_.DisplayName -like "Apache*"
            }
    )

    $apacheWasRunning = @(
        $apacheServices |
            Where-Object { $_.Status -eq "Running" } |
            ForEach-Object { $_.Name }
    )

    foreach ($service in $apacheServices) {
        Stop-Service -Name $service.Name -Force -ErrorAction SilentlyContinue
    }

    Wait-ForPortRelease -Port $HttpPort -TimeoutSeconds 15

    $listener = Get-NetTCPConnection -LocalPort $HttpPort -State Listen -ErrorAction SilentlyContinue |
        Select-Object -First 1

    if ($listener) {
        $processName = "unknown"

        try {
            $processName = (Get-Process -Id $listener.OwningProcess -ErrorAction Stop).ProcessName
        } catch {
        }

        Stop-Install "Port $HttpPort is already in use by $processName (PID $($listener.OwningProcess)). Stop that program and run the installer again."
    }

    foreach ($ruleName in @("Depot HTTP", "Depot Reverb")) {
        Remove-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue
    }

    Write-Step "Configuring PHP"

    if (Test-Path -LiteralPath $PhpIni) {
        $phpIniBackup = $PhpIni + ".backup-" + (Get-Date -Format "yyyyMMdd-HHmmss")
        Copy-Item -LiteralPath $PhpIni -Destination $phpIniBackup -Force
        Write-Host "Existing php.ini backed up to $phpIniBackup"
    }

    $phpIniContent = [System.IO.File]::ReadAllText($phpIniTemplate)
    $phpIniContent = $phpIniContent -replace '(?m)^extension_dir\s*=.*$', ('extension_dir = "' + $extDir + '"')
    $phpIniContent = $phpIniContent -replace '(?m)^error_log\s*=.*$', ('error_log = "' + (Join-Path $storageLogs "php-error.log") + '"')

    $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($PhpIni, $phpIniContent, $utf8NoBom)

    Write-Step "Configuring the application"

    & (Join-Path $PSScriptRoot "setup.ps1") `
        -AppPath $AppPath `
        -PhpExe $PhpExe `
        -PhpIni $PhpIni `
        -HttpPort $HttpPort `
        -ReverbPort $ReverbPort

    if (-not $SkipAdmin -and (-not $envExisted -or $CreateAdmin)) {
        Write-Step "Creating the first administrator"

        Push-Location $AppPath

        try {
            & $PhpExe -c $PhpIni artisan admin:create

            if ($LASTEXITCODE -ne 0) {
                Stop-Install "The administrator account was not created."
            }
        } finally {
            Pop-Location
        }
    }

    Write-Step "Installing automatic startup tasks"

    & (Join-Path $PSScriptRoot "install-services.ps1") `
        -AppPath $AppPath `
        -PhpExe $PhpExe `
        -PhpIni $PhpIni `
        -WebHost "127.0.0.1" `
        -WebPort $HttpPort `
        -ReverbHost "127.0.0.1" `
        -ReverbPort $ReverbPort

    $baseUrl = "http://127.0.0.1"

    if ($HttpPort -ne 80) {
        $baseUrl = $baseUrl + ":" + $HttpPort
    }

    Write-Step "Verifying the local application"

    Wait-ForApp -Url ($baseUrl + "/up") -TimeoutSeconds 30

    $reverbListener = Get-NetTCPConnection -LocalPort $ReverbPort -State Listen -ErrorAction SilentlyContinue |
        Select-Object -First 1

    if (-not $reverbListener) {
        Write-Warning "Reverb is not listening on port $ReverbPort yet. Notifications will use the page polling fallback."
    }

    $schedulerTask = Get-ScheduledTask -TaskName "Depot Scheduler" -ErrorAction SilentlyContinue

    if ($schedulerTask -and $schedulerTask.State -ne "Running") {
        Write-Warning "The scheduler task is not running yet. The daily stock check will not run until it starts."
    }

    try {
        $localhostUrl = $baseUrl -replace "127\.0\.0\.1", "localhost"
        $null = Invoke-WebRequest -Uri ($localhostUrl + "/up") -UseBasicParsing -TimeoutSec 5
    } catch {
        Write-Warning "http://localhost did not respond. Use $baseUrl instead."
    }

    Write-Step "Creating the desktop shortcut"

    $desktop = [Environment]::GetFolderPath("CommonDesktopDirectory")

    if ([string]::IsNullOrWhiteSpace($desktop)) {
        $desktop = Join-Path $env:PUBLIC "Desktop"
    }

    New-Item -ItemType Directory -Path $desktop -Force | Out-Null

    $shortcutPath = Join-Path $desktop "Depot.url"
    $shortcutContent = "[InternetShortcut]`r`nURL=$baseUrl`r`nIconFile=$AppPath\public\favicon.ico`r`nIconIndex=0`r`n"
    Set-Content -LiteralPath $shortcutPath -Value $shortcutContent -Encoding ASCII

    if ($apacheServices.Count -gt 0 -or (Test-Path -LiteralPath "C:\Apache24")) {
        Write-Step "Removing Apache"

        try {
            $httpd = "C:\Apache24\bin\httpd.exe"

            foreach ($service in $apacheServices) {
                Stop-Service -Name $service.Name -Force -ErrorAction SilentlyContinue

                if (Test-Path -LiteralPath $httpd) {
                    & $httpd -k uninstall -n $service.Name 2>$null | Out-Null
                }

                if (Get-Service -Name $service.Name -ErrorAction SilentlyContinue) {
                    & sc.exe delete $service.Name | Out-Null
                }
            }

            Get-Process -Name httpd -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
            Start-Sleep -Seconds 1

            if (Test-Path -LiteralPath "C:\Apache24") {
                Remove-Item -LiteralPath "C:\Apache24" -Recurse -Force -ErrorAction SilentlyContinue
            }

            foreach ($pathScope in @("Machine", "User")) {
                $currentPath = [Environment]::GetEnvironmentVariable("Path", $pathScope)

                if (-not [string]::IsNullOrWhiteSpace($currentPath)) {
                    $pathParts = @($currentPath -split ";" | Where-Object { $_ -and $_ -notmatch "Apache24" })
                    [Environment]::SetEnvironmentVariable("Path", ($pathParts -join ";"), $pathScope)
                }
            }

            $apacheRemoved = -not (Test-Path -LiteralPath "C:\Apache24")

            if ($apacheRemoved) {
                Write-Host "Apache cleanup completed."
            } else {
                Write-Warning "Apache files remain in C:\Apache24. Stop any remaining Apache process and delete that folder manually."
            }
        } catch {
            Write-Warning "Apache cleanup did not complete: $($_.Exception.Message)"
        }
    }

    Write-Host "`nInstallation completed." -ForegroundColor Green
    Write-Host "Open the Depot shortcut on the desktop, or use:"
    Write-Host "  $baseUrl"
    Write-Host ""
    Write-Host "The web server, Reverb, and scheduler start automatically when Windows starts." -ForegroundColor Yellow

    if (-not $SkipBrowser) {
        try {
            Start-Process explorer.exe -ArgumentList $baseUrl
        } catch {
            Write-Warning "Could not open the browser automatically. Open $baseUrl manually."
        }
    }
} catch {
    Write-Host "`nERROR: $($_.Exception.Message)" -ForegroundColor Red

    foreach ($taskName in $taskNames) {
        Stop-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
    }

    if (-not $apacheRemoved -and $apacheWasRunning.Count -gt 0) {
        foreach ($serviceName in $apacheWasRunning) {
            try {
                Set-Service -Name $serviceName -StartupType Automatic
                Start-Service -Name $serviceName
            } catch {
            }
        }
    }

    throw
} finally {
    try {
        Stop-Transcript | Out-Null
    } catch {
    }
}
