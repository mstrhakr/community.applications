@echo off
REM Run PHPUnit with short_open_tag enabled (required for CA source files)
php -d short_open_tag=On "%~dp0..\vendor\bin\phpunit" %*
