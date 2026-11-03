[CmdletBinding()]
param(
    [string]$AppPath = "C:\depot",
    [string]$ServerIp = "",
    [string]$PhpExe = "C:\php\php.exe",
    [switch]$Force
)

$ErrorActionPreference = "Stop"

function Write-Step {
    param([string]$Message)
    Write-Host "`n==> $Message" -ForegroundColor Cyan
}

function Stop-Setup {
    param([string]$Message)
    Write-Host "`nERROR: $Message" -ForegroundColor Red
    exit 1
}

function Set-EnvValue {
    param(
        [string]$Content,
        [string]$Key,
        [string]$Value
    )

    $pattern = "(?m)^" + [regex]::Escape($Key) + "=.*$"
    $replacement = $Key + "=" + $Value

    if ($Content -match $pattern) {
        return $Content -replace $pattern, $replacement
    }

    return $Content.TrimEnd() + "`r`n" + $replacement + "`r`n"
}

$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
$currentPrincipal = New-Object Security.Principal.WindowsPrincipal($identity)

if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Stop-Setup "Run this script from an Administrator PowerShell window."
}

Write-Step "Checking installation paths"

if (-not (Test-Path -LiteralPath $AppPath)) {
    Stop-Setup "Application directory was not found: $AppPath"
}

if (-not (Test-Path -LiteralPath $PhpExe)) {
    $phpCommand = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($phpCommand) {
        $PhpExe = $phpCommand.Source
    }
}

if (-not (Test-Path -LiteralPath $PhpExe)) {
    Stop-Setup "PHP was not found. Install PHP 8.3 and pass -PhpExe C:\php\php.exe."
}

$templatePath = Join-Path $AppPath ".env.windows.example"
$envPath = Join-Path $AppPath ".env"
$manifestPath = Join-Path $AppPath "public\build\manifest.json"
$autoloadPath = Join-Path $AppPath "vendor\autoload.php"

if (-not (Test-Path -LiteralPath $templatePath)) {
    Stop-Setup "Missing .env.windows.example in $AppPath."
}

if (-not (Test-Path -LiteralPath $manifestPath)) {
    Stop-Setup "Missing public\build\manifest.json. The release did not include built frontend assets."
}

if (-not (Test-Path -LiteralPath $autoloadPath)) {
    Stop-Setup "Missing vendor\autoload.php. The release is incomplete."
}

if ((Test-Path -LiteralPath $envPath) -and -not $Force) {
    Stop-Setup ".env already exists. Use -Force only when you intend to replace it."
}

Write-Step "Preparing environment file"

if ([string]::IsNullOrWhiteSpace($ServerIp)) {
    $defaultRoute = Get-NetRoute -AddressFamily IPv4 -DestinationPrefix "0.0.0.0/0" -ErrorAction SilentlyContinue |
        Sort-Object -Property RouteMetric |
        Select-Object -First 1

    if ($defaultRoute) {
        $ServerIp = Get-NetIPAddress -AddressFamily IPv4 -InterfaceIndex $defaultRoute.InterfaceIndex -ErrorAction SilentlyContinue |
            Where-Object { $_.IPAddress -notlike "169.254.*" } |
            Select-Object -First 1 -ExpandProperty IPAddress
    }
}

if ([string]::IsNullOrWhiteSpace($ServerIp)) {
    Stop-Setup "Could not detect the LAN IPv4 address. Run ipconfig and pass it with -ServerIp."
}

$ServerIp = $ServerIp.Trim()

if ($ServerIp -notmatch "^\d{1,3}(\.\d{1,3}){3}$") {
    Stop-Setup "ServerIp must be an IPv4 address such as 192.168.1.50, without http:// or a path."
}

$appUrl = "http://" + $ServerIp

$reverbSecret = (& $PhpExe -r "echo bin2hex(random_bytes(32));").Trim()

if ($LASTEXITCODE -ne 0 -or [string]::IsNullOrWhiteSpace($reverbSecret)) {
    Stop-Setup "Could not generate the Reverb application secret."
}

$envContent = [System.IO.File]::ReadAllText($templatePath)
$envContent = Set-EnvValue $envContent "APP_URL" $appUrl
$envContent = Set-EnvValue $envContent "APP_KEY" ""
$envContent = Set-EnvValue $envContent "REVERB_APP_SECRET" $reverbSecret

$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($envPath, $envContent, $utf8NoBom)

Write-Step "Creating writable application directories"

$directories = @(
    "bootstrap\cache",
    "database",
    "storage\app\mpdf",
    "storage\app\private",
    "storage\app\public",
    "storage\framework\cache\data",
    "storage\framework\cache\locks",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs"
)

foreach ($directory in $directories) {
    New-Item -ItemType Directory -Path (Join-Path $AppPath $directory) -Force | Out-Null
}

$databasePath = Join-Path $AppPath "database\database.sqlite"
if (-not (Test-Path -LiteralPath $databasePath)) {
    New-Item -ItemType File -Path $databasePath -Force | Out-Null
}

$hotPath = Join-Path $AppPath "public\hot"
if (Test-Path -LiteralPath $hotPath) {
    Remove-Item -LiteralPath $hotPath -Force
}

Write-Step "Generating the application key and migrating the database"

Push-Location $AppPath

try {
    & $PhpExe artisan key:generate --force
    if ($LASTEXITCODE -ne 0) {
        Stop-Setup "php artisan key:generate failed."
    }

    & $PhpExe artisan optimize:clear
    if ($LASTEXITCODE -ne 0) {
        Stop-Setup "php artisan optimize:clear failed."
    }

    & $PhpExe artisan migrate --force
    if ($LASTEXITCODE -ne 0) {
        Stop-Setup "php artisan migrate --force failed."
    }

    & $PhpExe artisan optimize
    if ($LASTEXITCODE -ne 0) {
        Stop-Setup "php artisan optimize failed."
    }
} finally {
    Pop-Location
}

Write-Step "Granting the Windows SYSTEM account access to writable directories"

$systemSid = "*S-1-5-18:(OI)(CI)M"

foreach ($directory in @("database", "storage", "bootstrap\cache")) {
    $target = Join-Path $AppPath $directory
    & icacls $target /grant $systemSid /T /C | Out-Null

    if ($LASTEXITCODE -ne 0) {
        Write-Warning "Could not update permissions for $target."
    }
}

Write-Host "`nSetup completed." -ForegroundColor Green
Write-Host "Server IP: $ServerIp"
Write-Host "Application URL: $appUrl"
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Create the first admin account:"
Write-Host "     & `"$PhpExe`" artisan admin:create"
Write-Host "  2. Install the Reverb and scheduler tasks:"
Write-Host "     powershell -ExecutionPolicy Bypass -File `"$AppPath\deploy\windows\install-services.ps1`" -AppPath `"$AppPath`" -PhpExe `"$PhpExe`""
