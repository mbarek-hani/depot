@echo off
setlocal EnableExtensions

net session >nul 2>&1
if not "%errorlevel%"=="0" (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

cd /d "%~dp0"

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0deploy\windows\install-depot.ps1" -AppPath "%~dp0."

if not "%errorlevel%"=="0" (
    echo.
    echo Installation failed. Review the message above and the install log in storage\logs.
    pause
)

endlocal
