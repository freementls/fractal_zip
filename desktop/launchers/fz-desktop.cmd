@echo off
REM fractal_zip desktop launcher (Windows)
setlocal EnableExtensions

set "ROOT=%~dp0"
if "%ROOT:~-1%"=="\" set "ROOT=%ROOT:~0,-1%"

set "VERSION=0.1.0"
if exist "%ROOT%\VERSION" set /p VERSION=<"%ROOT%\VERSION"

set "PAYLOAD=%ROOT%\payload"
set "HOST=127.0.0.1"
if defined FRACTAL_ZIP_DESKTOP_HOST set "HOST=%FRACTAL_ZIP_DESKTOP_HOST%"
set "PORT=17865"
if defined FRACTAL_ZIP_DESKTOP_PORT set "PORT=%FRACTAL_ZIP_DESKTOP_PORT%"

if not exist "%PAYLOAD%\router.php" (
  echo fractal_zip desktop: missing payload\ next to this launcher.
  echo Run desktop\scripts\package.sh from the repo, or use a release zip.
  exit /b 1
)

set "PHP_BIN="
if exist "%ROOT%\runtime\php.exe" set "PHP_BIN=%ROOT%\runtime\php.exe"
if not defined PHP_BIN if exist "%ROOT%\runtime\php\php.exe" set "PHP_BIN=%ROOT%\runtime\php\php.exe"
if not defined PHP_BIN (
  where php >nul 2>&1 && for /f "delims=" %%P in ('where php') do (
    if not defined PHP_BIN set "PHP_BIN=%%P"
  )
)
if not defined PHP_BIN (
  echo fractal_zip desktop: PHP not found.
  echo Install PHP 8.1+ on PATH, or place php.exe under runtime\.
  exit /b 1
)

if not defined FZC_WEB_MAX_UPLOAD_BYTES set "FZC_WEB_MAX_UPLOAD_BYTES=0"
if not defined FZC_WEB_MAX_EXTRACT_ARCHIVE_BYTES set "FZC_WEB_MAX_EXTRACT_ARCHIVE_BYTES=0"
if not defined FRACTAL_ZIP_PHP set "FRACTAL_ZIP_PHP=%PAYLOAD%\fractal_zip.php"
if not defined FRACTAL_ZIP_SUPPRESS_HTML set "FRACTAL_ZIP_SUPPRESS_HTML=1"

if not defined FRACTAL_ZIP_WEB_JOBS (
  set "JOBS_ROOT=%LOCALAPPDATA%\fractal_zip\web_jobs"
  if not exist "%JOBS_ROOT%" mkdir "%JOBS_ROOT%"
  set "FRACTAL_ZIP_WEB_JOBS=%JOBS_ROOT%"
)

set "URL=http://%HOST%:%PORT%/"

echo fractal_zip desktop %VERSION%
echo UI:  %URL%
echo Jobs: %FRACTAL_ZIP_WEB_JOBS%
echo Stop with Ctrl-C.
echo.

start "" "%URL%"

"%PHP_BIN%" -d upload_max_filesize=2G -d post_max_size=2G -d max_file_uploads=2000 -d max_execution_time=0 -d memory_limit=1024M -S "%HOST%:%PORT%" -t "%PAYLOAD%" "%PAYLOAD%\router.php"
exit /b %ERRORLEVEL%
