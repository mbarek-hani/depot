[CmdletBinding()]
param(
    [string]$AppPath = "C:\depot",
    [string]$PhpExe = "C:\php\php.exe",
    [string]$PhpIni = "",
    [int]$HttpPort = 80,
    [int]$ReverbPort = 8080
)

$ErrorActionPreference = "Stop"

function Write-Step {
    param([string]$Message)
    Write-Host "`n==> $Message" -ForegroundColor Cyan
}

function Stop-Setup {
    param([string]$Message)
    throw $Message
}

function Get-EnvValue {
    param(
        [string]$Content,
        [string]$Key
    )

    $pattern = "(?m)^" + [regex]::Escape($Key) + "=(.*)$"
    $match = [regex]::Match($Content, $pattern)

    if ($match.Success) {
        return $match.Groups[1].Value.Trim()
    }

    return ""
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

function Remove-EnvValue {
    param(
        [string]$Content,
        [string]$Key
    )

    $pattern = "(?m)^" + [regex]::Escape($Key) + "=.*\r?\n?"
    return [regex]::Replace($Content, $pattern, "")
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
    Stop-Setup "PHP was not found. Install PHP 8.3 x64 Thread Safe and pass -PhpExe C:\php\php.exe."
}

if ([string]::IsNullOrWhiteSpace($PhpIni)) {
    $PhpIni = Join-Path (Split-Path -Parent $PhpExe) "php.ini"
}

if (-not (Test-Path -LiteralPath $PhpIni)) {
    Stop-Setup "PHP configuration was not found: $PhpIni"
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

Write-Step "Checking PHP"

$phpVersion = (& $PhpExe -c $PhpIni -r "echo PHP_VERSION;").Trim()

if ($LASTEXITCODE -ne 0 -or [string]::IsNullOrWhiteSpace($phpVersion)) {
    Stop-Setup "Could not run PHP with $PhpIni."
}

if ([version]$phpVersion -lt [version]"8.3.0") {
    Stop-Setup "PHP 8.3 or newer is required. Found $phpVersion."
}

$threadSafe = (& $PhpExe -c $PhpIni -r "echo PHP_ZTS ? '1' : '0';").Trim()

if ($threadSafe -ne "1") {
    Write-Warning "The installed PHP build is not Thread Safe. The local server will still run, but keep the Thread Safe build requested for this client."
}

$requiredExtensions = @(
    "curl",
    "dom",
    "fileinfo",
    "gd",
    "intl",
    "mbstring",
    "openssl",
    "pdo_sqlite",
    "sqlite3",
    "xml",
    "xmlwriter",
    "zip"
)

$loadedExtensions = & $PhpExe -c $PhpIni -m

foreach ($extension in $requiredExtensions) {
    if ($loadedExtensions -notcontains $extension) {
        Stop-Setup "Missing PHP extension: $extension. Update $PhpIni and try again."
    }
}

& $PhpExe -c $PhpIni -r "exit(in_array('sqlite', PDO::getAvailableDrivers(), true) ? 0 : 1);"

if ($LASTEXITCODE -ne 0) {
    Stop-Setup "PDO SQLite is not available through $PhpIni."
}

Write-Step "Preparing environment file"

if (Test-Path -LiteralPath $envPath) {
    $backupPath = $envPath + ".backup-" + (Get-Date -Format "yyyyMMdd-HHmmss")
    Copy-Item -LiteralPath $envPath -Destination $backupPath -Force
    Write-Host "Existing .env backed up to $backupPath"
    $envContent = [System.IO.File]::ReadAllText($envPath)
} else {
    $envContent = [System.IO.File]::ReadAllText($templatePath)
}

$appKey = Get-EnvValue $envContent "APP_KEY"
$reverbSecret = Get-EnvValue $envContent "REVERB_APP_SECRET"

if ([string]::IsNullOrWhiteSpace($reverbSecret) -or $reverbSecret -eq "CHANGE_ME_ON_INSTALL") {
    $reverbSecret = (& $PhpExe -c $PhpIni -r "echo bin2hex(random_bytes(32));").Trim()

    if ($LASTEXITCODE -ne 0 -or [string]::IsNullOrWhiteSpace($reverbSecret)) {
        Stop-Setup "Could not generate the Reverb application secret."
    }
}

$appUrl = "http://127.0.0.1"

if ($HttpPort -ne 80) {
    $appUrl = $appUrl + ":" + $HttpPort
}

$envContent = Set-EnvValue $envContent "APP_ENV" "production"
$envContent = Set-EnvValue $envContent "APP_DEBUG" "false"
$envContent = Set-EnvValue $envContent "APP_URL" $appUrl
$envContent = Set-EnvValue $envContent "DB_CONNECTION" "sqlite"
$envContent = Set-EnvValue $envContent "SESSION_DOMAIN" "null"
$envContent = Set-EnvValue $envContent "SESSION_SECURE_COOKIE" "false"
$envContent = Set-EnvValue $envContent "BROADCAST_CONNECTION" "reverb"
$envContent = Set-EnvValue $envContent "REVERB_APP_ID" "depot"
$envContent = Set-EnvValue $envContent "REVERB_APP_KEY" "depot-public-key"
$envContent = Set-EnvValue $envContent "REVERB_HOST" "127.0.0.1"
$envContent = Set-EnvValue $envContent "REVERB_PORT" $ReverbPort
$envContent = Set-EnvValue $envContent "REVERB_SCHEME" "http"
$envContent = Set-EnvValue $envContent "REVERB_SERVER_HOST" "127.0.0.1"
$envContent = Set-EnvValue $envContent "REVERB_SERVER_PORT" $ReverbPort
$envContent = Set-EnvValue $envContent "REVERB_APP_SECRET" $reverbSecret
$envContent = Remove-EnvValue $envContent "PHP_CLI_SERVER_WORKERS"

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
    if ([string]::IsNullOrWhiteSpace($appKey)) {
        & $PhpExe -c $PhpIni artisan key:generate --force
        if ($LASTEXITCODE -ne 0) {
            Stop-Setup "php artisan key:generate failed."
        }
    }

    & $PhpExe -c $PhpIni artisan optimize:clear
    if ($LASTEXITCODE -ne 0) {
        Stop-Setup "php artisan optimize:clear failed."
    }

    & $PhpExe -c $PhpIni artisan migrate --force
    if ($LASTEXITCODE -ne 0) {
        Stop-Setup "php artisan migrate --force failed."
    }

    & $PhpExe -c $PhpIni artisan optimize
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
Write-Host "Application URL: $appUrl"
Write-Host "Reverb address: ws://127.0.0.1:$ReverbPort"
Write-Host ""
Write-Host "The installer creates the admin account and startup tasks automatically." -ForegroundColor Yellow
