@echo off
setlocal EnableExtensions
chcp 65001 >nul
title Instalador del banco de preguntas - CodePlay

set "INSTALLER_DIR=%~dp0"
set "PROJECT_DIR="

if exist "%CD%\artisan" set "PROJECT_DIR=%CD%"
if not defined PROJECT_DIR if exist "%INSTALLER_DIR%artisan" set "PROJECT_DIR=%INSTALLER_DIR:~0,-1%"
if not defined PROJECT_DIR if exist "%INSTALLER_DIR%..\artisan" for %%I in ("%INSTALLER_DIR%..") do set "PROJECT_DIR=%%~fI"

if not defined PROJECT_DIR (
    echo ERROR: No se encontro el archivo artisan.
    echo.
    echo Copie esta carpeta dentro de la carpeta principal de CodePlay,
    echo o ejecute este archivo desde la carpeta principal del proyecto.
    echo.
    pause
    exit /b 1
)

set "PHP_EXE="
where php.exe >nul 2>nul && set "PHP_EXE=php.exe"
if not defined PHP_EXE if exist "%PROJECT_DIR%\php\php.exe" set "PHP_EXE=%PROJECT_DIR%\php\php.exe"
if not defined PHP_EXE if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"

if not defined PHP_EXE (
    echo ERROR: No se encontro php.exe.
    echo Agregue PHP al PATH de Windows o edite este archivo con la ruta correcta.
    echo.
    pause
    exit /b 1
)

echo CodePlay: %PROJECT_DIR%
echo Se buscara al profesor Jeremias y se instalara el banco publicado.
echo No se reemplazara ningun banco existente.
echo.

"%PHP_EXE%" "%INSTALLER_DIR%instalar_banco_cultura_digital.php" --project-dir="%PROJECT_DIR%" %*
set "RESULT=%ERRORLEVEL%"

echo.
if "%RESULT%"=="0" (
    echo El proceso termino correctamente.
) else (
    echo El proceso no hizo cambios o encontro un problema. Revise el mensaje anterior.
)
echo.
pause
exit /b %RESULT%
