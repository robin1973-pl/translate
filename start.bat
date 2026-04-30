@echo off
cd /d "%~dp0"
echo STARTOWANIE SERWERA PHP DLA TRANSLATORA...
echo.
echo Adres: http://localhost:8001
echo.
echo (Nie zamykaj tego okna podczas pracy z projektem)
echo.
php -S 127.0.0.1:8001 -t .
pause
