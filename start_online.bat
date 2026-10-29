@echo off
title CAWS Pet Adoption - Online Cloud Bridge
cd /d "%~dp0"
powershell -ExecutionPolicy Bypass -File "%~dp0start_online.ps1"
pause
