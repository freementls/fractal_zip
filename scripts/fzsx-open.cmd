@echo off
setlocal
if "%~1"=="" (
  echo Usage: fzsx-open.cmd path\to\file.fzsx^|file.fzsxsd
  exit /b 1
)
set "ARCHIVE=%~f1"
where php >nul 2>&1
if errorlevel 1 (
  echo php not found on PATH. Install PHP CLI or set PATH.
  exit /b 127
)
php "%ARCHIVE%"
exit /b %ERRORLEVEL%
