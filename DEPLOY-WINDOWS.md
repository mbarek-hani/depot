# Windows Local Deployment Guide

This guide installs the application on a single Windows laptop and serves it only
to that laptop. The application is not exposed to the local network or the
internet.

The application runs on the PHP built-in server at `http://127.0.0.1`. Apache is
not used and is removed by the installer.

## What you need

- Windows 10 or Windows 11.
- Administrator access on the laptop.
- PHP 8.3 x64 **Thread Safe** installed at `C:\php`.
- The release file `depot-windows-vX.Y.Z.zip`.
- The matching `depot-windows-vX.Y.Z.zip.sha256` checksum file.

The laptop does not need Apache, Composer, Node.js, npm, Laragon, XAMPP, or
internet access after the release files are copied.

The PHP installation must include these extensions:

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

## 1. Prepare PHP

The installer expects PHP at `C:\php\php.exe` by default.

Confirm the PHP version and extensions:

```powershell
C:\php\php.exe -v
C:\php\php.exe -m
```

Do not install Apache. If Apache is already installed, the installer stops and
removes it.

If PHP is installed somewhere else, pass the path to the installer:

```powershell
.\deploy\windows\install-depot.ps1 -PhpExe "D:\php\php.exe"
```

The normal installation uses `C:\php\php.exe`.

## 2. Extract the release

Verify the checksum:

```powershell
Get-FileHash ".\depot-windows-vX.Y.Z.zip" -Algorithm SHA256
Get-Content ".\depot-windows-vX.Y.Z.zip.sha256"
```

The hash printed by `Get-FileHash` must match the hash in the checksum file.

Extract the zip to `C:\` so the application is created at `C:\depot`:

```powershell
Expand-Archive -Path ".\depot-windows-vX.Y.Z.zip" -DestinationPath "C:\"
```

Confirm these files exist:

```text
C:\depot\artisan
C:\depot\server.php
C:\depot\Install Depot.bat
C:\depot\.env.windows.example
C:\depot\vendor\autoload.php
C:\depot\public\build\manifest.json
```

`C:\depot\public\hot` must not exist.

## 3. Run the installer

Double-click:

```text
C:\depot\Install Depot.bat
```

Accept the Windows administrator prompt.

The installer:

- Stops the existing `Depot Web`, `Depot Reverb`, and `Depot Scheduler` tasks.
- Stops Apache before installing and removes it after the new app passes its health
  check, including `C:\Apache24`, its PATH entries, and old Depot firewall rules.
- Backs up the existing `C:\php\php.ini`, then writes the local production
  configuration.
- Creates or updates `.env` with `APP_URL=http://127.0.0.1`.
- Generates the application key and Reverb secret when needed.
- Creates the SQLite database and writable storage directories.
- Runs migrations and Laravel optimization.
- Grants the Windows `SYSTEM` account access to the writable directories.
- Creates the first administrator account on a new installation.
- Creates three startup tasks that run as `SYSTEM`.
- Creates a `Depot` shortcut on the public desktop.
- Checks `http://127.0.0.1/up` before removing Apache.
- Opens the application in the default browser.

The administrator prompts appear in the installer window. Enter a name and a
password with at least 8 characters, including letters and numbers.

If the installer is run again on an existing installation, it keeps the existing
database, application key, and administrator account. Use `-CreateAdmin` if you
want to add another administrator:

```powershell
.\deploy\windows\install-depot.ps1 -CreateAdmin
```

Installation logs are written to:

```text
C:\depot\storage\logs\install-YYYYMMDD-HHMMSS.log
```

## 4. Startup tasks

The installer creates these scheduled tasks:

```text
Depot Web       php -c C:\php\php.ini -S 127.0.0.1:80 -t C:\depot\public C:\depot\server.php
Depot Reverb    php -c C:\php\php.ini artisan reverb:start --host=127.0.0.1 --port=8080
Depot Scheduler php -c C:\php\php.ini artisan schedule:work
```

All three:

- Run as `SYSTEM`.
- Start when Windows starts.
- Start when available if a startup was missed.
- Restart after failure.
- Continue while the laptop is on battery power.
- Do not leave running processes behind when stopped.

The application is available after Windows starts. The client can open the
`Depot` desktop shortcut, or open:

```text
http://127.0.0.1
```

`http://localhost` should also work. If the browser resolves `localhost` to IPv6
and cannot connect, use `http://127.0.0.1`.

No Windows Firewall rule is needed because the server binds only to
`127.0.0.1`.

## 5. Verify the installation

On the laptop:

```powershell
Invoke-WebRequest "http://127.0.0.1/up" -UseBasicParsing
Get-NetTCPConnection -LocalPort 80, 8080 -State Listen
Get-ScheduledTask -TaskName "Depot Web", "Depot Reverb", "Depot Scheduler"
Get-ScheduledTaskInfo -TaskName "Depot Web", "Depot Reverb", "Depot Scheduler"
```

Then:

1. Open the `Depot` shortcut.
2. Log in with the administrator account.
3. Create a product.
4. Create an input movement.
5. Create an output movement.
6. Open the movement history.
7. Download an Arabic PDF report.
8. Confirm that notifications arrive.

Restart the laptop and confirm that:

- The `Depot Web`, `Depot Reverb`, and `Depot Scheduler` tasks are running.
- Port 80 and port 8080 are listening.
- The `Depot` shortcut opens the application.
- The existing data is still present.

## 6. Update the application

There is no automatic database backup in this deployment. Keep the previous
release folder until the update is verified.

From an Administrator PowerShell window:

```powershell
Stop-ScheduledTask -TaskName "Depot Web", "Depot Reverb", "Depot Scheduler"

Rename-Item "C:\depot" "C:\depot-previous"

Expand-Archive -Path ".\depot-windows-vX.Y.Z.zip" -DestinationPath "C:\"

Copy-Item "C:\depot-previous\.env" "C:\depot\.env"
Copy-Item "C:\depot-previous\database\database.sqlite" "C:\depot\database\database.sqlite"

robocopy "C:\depot-previous\storage\app" "C:\depot\storage\app" /E
robocopy "C:\depot-previous\public\images" "C:\depot\public\images" /E
```

Then run:

```text
C:\depot\Install Depot.bat
```

The installer preserves the database and `.env`, updates the PHP configuration
and startup tasks, and verifies the application before finishing.

If the update fails, stop the tasks, restore `C:\depot-previous` to `C:\depot`,
and run `Install Depot.bat` again from the previous release.

## 7. Troubleshooting

### The shortcut does not open the application

Check:

```powershell
Get-ScheduledTask -TaskName "Depot Web"
Get-ScheduledTaskInfo -TaskName "Depot Web"
Get-NetTCPConnection -LocalPort 80 -State Listen
```

Check the logs:

```text
C:\depot\storage\logs\laravel.log
C:\depot\storage\logs\php-error.log
C:\depot\storage\logs\install-*.log
```

Start the task if it is not running:

```powershell
Start-ScheduledTask -TaskName "Depot Web"
```

### Port 80 is already in use

Find the process using the port:

```powershell
Get-NetTCPConnection -LocalPort 80 -State Listen |
    Select-Object LocalAddress, LocalPort, OwningProcess

Get-Process -Id <PID>
```

Stop the conflicting program or service, then run the installer again.

### The installer reports a missing PHP extension

Check:

```powershell
C:\php\php.exe -c C:\php\php.ini -m
```

Confirm `pdo_sqlite`, `sqlite3`, and the other required extensions are enabled.
The previous PHP configuration is backed up next to `C:\php\php.ini`.

### The page shows a 500 error

Check the Laravel and PHP logs, then confirm the `SYSTEM` account can write to:

```text
C:\depot\database
C:\depot\storage
C:\depot\bootstrap\cache
```

Reapply permissions:

```powershell
icacls "C:\depot\database" /grant "*S-1-5-18:(OI)(CI)M" /T
icacls "C:\depot\storage" /grant "*S-1-5-18:(OI)(CI)M" /T
icacls "C:\depot\bootstrap\cache" /grant "*S-1-5-18:(OI)(CI)M" /T
```

### SQLite reports a locked or read-only database

- Keep the database on the local disk.
- Do not place it in OneDrive, Dropbox, or a network share.
- Confirm the `SYSTEM` account can write to `C:\depot\database`.
- Confirm `DB_BUSY_TIMEOUT=5000` and `DB_JOURNAL_MODE=wal` in `.env`.

### Reverb notifications do not work

Check:

```powershell
Get-ScheduledTaskInfo -TaskName "Depot Reverb"
Get-NetTCPConnection -LocalPort 8080 -State Listen
```

Confirm:

- `REVERB_HOST=127.0.0.1`, `REVERB_PORT=8080`, and
  `REVERB_SERVER_HOST=127.0.0.1` in `.env`.
- `REVERB_APP_KEY` is `depot-public-key`.
- The application was opened at `http://127.0.0.1`, not through a different host.
- The browser developer tools do not show WebSocket errors.

If Reverb is down, the page polling fallback still refreshes notifications.

### PDF generation fails

Confirm:

- `C:\depot\storage\app\mpdf` exists and is writable.
- `gd`, `mbstring`, and `dom` are enabled in `php -m`.
- `memory_limit` is at least `512M`.

## 8. Daily operation

- The application is local to this laptop only.
- The laptop must be powered on and Windows started for the application to run.
- The daily 08:00 stock alert check runs only while the laptop is on.
- There is no automatic database backup.
- Do not expose port 80 or 8080 to the network.
- If LAN access is needed later, do not use this local-only setup; install a
  proper web server or containerized deployment instead.

## 9. Remove the application

From an Administrator PowerShell window:

```powershell
Stop-ScheduledTask -TaskName "Depot Web", "Depot Reverb", "Depot Scheduler"

Unregister-ScheduledTask -TaskName "Depot Web", "Depot Reverb", "Depot Scheduler" -Confirm:$false

Remove-Item "C:\depot" -Recurse -Force
Remove-Item "C:\Users\Public\Desktop\Depot.url" -Force -ErrorAction SilentlyContinue
```

Remove the PHP changes only if PHP is no longer needed:

```powershell
Remove-Item "C:\php\php.ini" -Force
```

Keep the backed-up `C:\php\php.ini.backup-*` file if you need to restore the
previous PHP configuration.
