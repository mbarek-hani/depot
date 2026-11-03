# Windows LAN Deployment Guide

This guide installs the application on a Windows desktop for access from other
computers on the same local network. The server is not exposed to the internet.

## What you need

- Windows 10 or Windows 11 desktop.
- Administrator access.
- A reserved LAN address for the server.
- The release file `depot-windows-vX.Y.Z.zip`.
- The matching `depot-windows-vX.Y.Z.zip.sha256` checksum file.
- Apache Lounge 2.4 x64 for Windows.
- PHP 8.3 x64 Thread Safe for Windows.
- Microsoft Visual C++ Redistributable 2015-2022 x64.

The client machine does not need Composer, Node.js, npm, Laragon, XAMPP, or
internet access after the files are copied.

## 1. Choose the server IP address

The application is accessed by the Windows desktop's LAN IPv4 address. No
hostname, DNS entry, or hosts-file change is required.

Find the address on the server:

```powershell
Get-NetIPAddress -AddressFamily IPv4 |
    Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.254.*" } |
    Select-Object InterfaceAlias, IPAddress
```

Use the address of the active Ethernet or Wi-Fi adapter. For example:

```text
192.168.1.50
```

Clients will open:

```text
http://192.168.1.50
```

Create a DHCP reservation for this address in the router so the server keeps the
same IP. If the address changes later, clients must use the new address.

### Test the address from another LAN computer

```powershell
ping 192.168.1.50
Test-NetConnection -ComputerName 192.168.1.50 -Port 80
```

Use this IP address later in `APP_URL`, the Apache configuration, and the setup
script. If the address changes later, update `APP_URL` in `.env`, update
`ServerName` in the Apache virtual host, then run:

```powershell
cd C:\depot
php artisan optimize:clear
php artisan optimize
```

## 2. Prepare the server

Disable sleep and hibernation:

```powershell
powercfg /change standby-timeout-ac 0
powercfg /change hibernate-timeout-ac 0
```

Install the Microsoft Visual C++ Redistributable 2015-2022 x64.

## 3. Install Apache

1. Download the Apache Lounge 2.4 VS17 Win64 build.
2. Extract it to `C:\Apache24`.
3. Confirm this file exists:

```text
C:\Apache24\bin\httpd.exe
```

Do not start Apache yet.

## 4. Install PHP

1. Download PHP 8.3 x64 **Thread Safe** for Windows.
2. Extract it to `C:\php`.
3. Confirm this file exists:

```text
C:\php\php8apache2_4.dll
```

Add PHP to the system `PATH`:

```powershell
[Environment]::SetEnvironmentVariable(
    "Path",
    [Environment]::GetEnvironmentVariable("Path", "Machine") + ";C:\php",
    "Machine"
)
```

Open a new Administrator PowerShell window after changing `PATH`.

## 5. Configure PHP

Copy the recommended PHP configuration from the release:

```powershell
Copy-Item "C:\depot\deploy\windows\php.ini.production" "C:\php\php.ini"
```

If PHP is installed somewhere other than `C:\php`, update `extension_dir` in
`C:\php\php.ini` to match.

Check PHP:

```powershell
php -v
php -m
```

`php -m` must include:

```text
curl
dom
fileinfo
gd
intl
mbstring
openssl
pdo_sqlite
sqlite3
xml
xmlwriter
zip
```

## 6. Extract the application

Verify the release checksum first:

```powershell
Get-FileHash ".\depot-windows-vX.Y.Z.zip" -Algorithm SHA256
Get-Content ".\depot-windows-vX.Y.Z.zip.sha256"
```

The hash printed by `Get-FileHash` must match the hash in the checksum file.

Extract the zip to `C:\` so that the application is created at `C:\depot`:

```powershell
Expand-Archive -Path ".\depot-windows-vX.Y.Z.zip" -DestinationPath "C:\"
```

Confirm these files exist:

```text
C:\depot\artisan
C:\depot\.env.windows.example
C:\depot\vendor\autoload.php
C:\depot\public\build\manifest.json
C:\depot\public\.htaccess
```

`C:\depot\public\hot` must not exist. The setup script removes it if present.

## 7. Configure Apache

Edit `C:\Apache24\conf\httpd.conf`.

Confirm or add these lines:

```apache
Define SRVROOT "C:/Apache24"
ServerRoot "${SRVROOT}"
Listen 80

LoadModule rewrite_module modules/mod_rewrite.so
LoadModule php_module "C:/php/php8apache2_4.dll"
PHPIniDir "C:/php"

AddHandler application/x-httpd-php .php
DirectoryIndex index.php index.html
```

Remove the leading `#` from the `mod_rewrite` line if it is commented out.

Copy the virtual host file:

```powershell
Copy-Item "C:\depot\deploy\windows\apache-depot.conf" "C:\Apache24\conf\extra\depot.conf"
```

Edit `C:\Apache24\conf\extra\depot.conf`:

- Replace `SERVER-IP` with the reserved LAN IPv4 address, without `http://`.
- Keep `DocumentRoot` as `C:/depot/public`.
- Keep `AllowOverride All`.

Add this line near the other `Include` lines in `httpd.conf`:

```apache
Include conf/extra/depot.conf
```

Test the Apache configuration:

```powershell
C:\Apache24\bin\httpd.exe -t
```

Install Apache as a Windows service:

```powershell
C:\Apache24\bin\httpd.exe -k install -n "Apache2.4"
Set-Service -Name "Apache2.4" -StartupType Automatic
Start-Service -Name "Apache2.4"
```

If the service is already installed, skip the `-k install` command and restart
it instead:

```powershell
Restart-Service -Name "Apache2.4"
```

Check that port 80 is listening:

```powershell
Get-NetTCPConnection -LocalPort 80 -State Listen
```

## 8. Configure the application

Run the setup script from an Administrator PowerShell window:

```powershell
powershell -ExecutionPolicy Bypass -File "C:\depot\deploy\windows\setup.ps1" `
    -AppPath "C:\depot" `
    -ServerIp "192.168.1.50" `
    -PhpExe "C:\php\php.exe"
```

The script:

- Creates `.env` from `.env.windows.example`.
- Sets `APP_URL`.
- Generates a unique Reverb secret.
- Generates `APP_KEY`.
- Creates the SQLite database.
- Runs migrations.
- Creates writable storage directories.
- Removes `public/hot`.
- Runs `php artisan optimize`.
- Grants the Windows SYSTEM account access to writable directories.

If a `.env` file already exists, the script stops. Use `-Force` only when you
intend to replace it.

## 9. Create the first administrator

From `C:\depot`:

```powershell
cd C:\depot
php artisan admin:create
```

Enter the administrator name and a password with at least 8 characters,
including letters and numbers.

Do not use the demo seeder on the client machine.

## 10. Install Reverb and the scheduler

Run:

```powershell
powershell -ExecutionPolicy Bypass -File "C:\depot\deploy\windows\install-services.ps1" `
    -AppPath "C:\depot" `
    -PhpExe "C:\php\php.exe"
```

This creates two startup scheduled tasks:

- `Depot Reverb`: `php artisan reverb:start --host=0.0.0.0 --port=8080`
- `Depot Scheduler`: `php artisan schedule:work`

Both tasks run as `SYSTEM`, start at boot, and restart after failure.

Check them:

```powershell
Get-ScheduledTask -TaskName "Depot Reverb", "Depot Scheduler"
Get-ScheduledTaskInfo -TaskName "Depot Reverb", "Depot Scheduler"
```

Check that Reverb is listening:

```powershell
Get-NetTCPConnection -LocalPort 8080 -State Listen
```

The application must keep working if Reverb is temporarily down, but instant
notifications will not arrive until Reverb is running again.

## 11. Open the firewall

Allow only the local network:

```powershell
New-NetFirewallRule `
    -DisplayName "Depot HTTP" `
    -Direction Inbound `
    -Protocol TCP `
    -LocalPort 80 `
    -Action Allow `
    -RemoteAddress LocalSubnet

New-NetFirewallRule `
    -DisplayName "Depot Reverb" `
    -Direction Inbound `
    -Protocol TCP `
    -LocalPort 8080 `
    -Action Allow `
    -RemoteAddress LocalSubnet
```

If the LAN uses a specific subnet, replace `LocalSubnet` with the subnet, for
example `192.168.1.0/24`.

Do not forward these ports from the internet.

## 12. Verify the installation

On the server:

```powershell
Invoke-WebRequest "http://192.168.1.50/up" -UseBasicParsing
```

The health endpoint should return a successful response.

On another LAN computer:

1. Open `http://192.168.1.50`.
2. Log in with the administrator account.
3. Create a product.
4. Create an input movement.
5. Create an output movement.
6. Open the movement history.
7. Download an Arabic PDF report.
8. Confirm that the report opens correctly.
9. Open the application in two browsers and confirm that notifications arrive
   without refreshing.

Reboot the server and confirm that:

- Apache starts automatically.
- `Depot Reverb` starts automatically.
- `Depot Scheduler` starts automatically.
- The application still opens from another computer.

## 13. Update the application

There is no automatic database backup in this deployment. Keep the previous
release folder until the update is verified.

Stop the application:

```powershell
Stop-ScheduledTask -TaskName "Depot Reverb", "Depot Scheduler"
Stop-Service -Name "Apache2.4"
```

Rename the current release. If `C:\depot-previous` already exists, rename or
remove it first:

```powershell
Rename-Item "C:\depot" "C:\depot-previous"
```

Extract the new release:

```powershell
Expand-Archive -Path ".\depot-windows-vX.Y.Z.zip" -DestinationPath "C:\"
```

Restore the files that must be preserved:

```powershell
Copy-Item "C:\depot-previous\.env" "C:\depot\.env"
Copy-Item "C:\depot-previous\database\database.sqlite" "C:\depot\database\database.sqlite"

robocopy "C:\depot-previous\storage\app" "C:\depot\storage\app" /E
robocopy "C:\depot-previous\public\images" "C:\depot\public\images" /E

icacls "C:\depot\database" /grant "*S-1-5-18:(OI)(CI)M" /T
icacls "C:\depot\storage" /grant "*S-1-5-18:(OI)(CI)M" /T
icacls "C:\depot\bootstrap\cache" /grant "*S-1-5-18:(OI)(CI)M" /T
```

Update the database and caches:

```powershell
cd C:\depot
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

Start the application:

```powershell
Start-Service -Name "Apache2.4"
Start-ScheduledTask -TaskName "Depot Reverb", "Depot Scheduler"
```

Verify the application before deleting `C:\depot-previous`.

If the update fails, stop the services, rename the failed folder, rename
`C:\depot-previous` back to `C:\depot`, and start the services again. Rollback is
code-only because this deployment does not create automatic database backups.

## 14. Troubleshooting

### The browser shows a 500 error

Check:

```text
C:\depot\storage\logs\laravel.log
C:\depot\storage\logs\php-error.log
C:\Apache24\logs\depot-error.log
```

Also check file permissions for:

```text
C:\depot\database
C:\depot\storage
C:\depot\bootstrap\cache
```

Reapply permissions if needed:

```powershell
icacls "C:\depot\database" /grant "*S-1-5-18:(OI)(CI)M" /T
icacls "C:\depot\storage" /grant "*S-1-5-18:(OI)(CI)M" /T
icacls "C:\depot\bootstrap\cache" /grant "*S-1-5-18:(OI)(CI)M" /T
```

### Apache does not start

Run:

```powershell
C:\Apache24\bin\httpd.exe -t
```

Confirm that:

- PHP is the Thread Safe x64 build.
- `C:\php\php8apache2_4.dll` exists.
- `LoadModule php_module` points to the correct DLL.
- Port 80 is not used by another program.
- The Visual C++ Redistributable is installed.

### PHP reports a missing SQLite driver

Confirm `pdo_sqlite` and `sqlite3` are enabled:

```powershell
php -m
```

Check `extension_dir` in `C:\php\php.ini`.

### SQLite reports a locked or read-only database

- Keep the database on the local disk.
- Do not place it in OneDrive, Dropbox, or a network share.
- Confirm the Apache service account can write to `C:\depot\database`.
- Confirm `DB_BUSY_TIMEOUT=5000` and `DB_JOURNAL_MODE=wal` in `.env`.

### Reverb notifications do not work

Check:

```powershell
Get-ScheduledTaskInfo -TaskName "Depot Reverb"
Get-NetTCPConnection -LocalPort 8080 -State Listen
```

Confirm that:

- Port 8080 is allowed through Windows Firewall.
- `REVERB_APP_KEY` in `.env` is `depot-public-key`.
- `REVERB_HOST=127.0.0.1`, `REVERB_PORT=8080`, `REVERB_SCHEME=http`.
- The page was opened using the same IP address used in `APP_URL`.
- The browser developer tools do not show WebSocket errors.

### Clients cannot reach the server

Confirm that the client and server are on the same LAN, the server IP has not
changed, and Windows Firewall allows TCP port 80 from `LocalSubnet`. Use
`ipconfig` on the server to check the current address.

### PDF generation fails

Confirm:

- `C:\depot\storage\app\mpdf` exists and is writable.
- `gd`, `mbstring`, and `dom` are enabled in `php -m`.
- `memory_limit` is at least `512M`.
- `max_execution_time` is at least `120`.

## 15. Daily operation

- Leave the server powered on.
- Do not let the machine sleep.
- Access the application through the reserved LAN IP address.
- Keep the SQLite database on the local disk.
- This deployment uses HTTP inside the LAN only.
- No scheduled database backup is configured.
- If the application is ever exposed to the internet, add HTTPS and review the
  security configuration first.
