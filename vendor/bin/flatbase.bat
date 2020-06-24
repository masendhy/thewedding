@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../flatbase/flatbase/flatbase
php "%BIN_TARGET%" %*
