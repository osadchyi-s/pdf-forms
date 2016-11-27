@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../jeremykendall/php-domain-parser/bin/parse
php "%BIN_TARGET%" %*
