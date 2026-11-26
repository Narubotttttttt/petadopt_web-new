# Script: start_online.ps1
# Automates starting Laravel, Cloudflare Tunnel, updating Vercel proxy, and publishing to live domain.

$ErrorActionPreference = "Continue"
$CloudflaredBin = "C:\Program Files (x86)\cloudflared\cloudflared.exe"

if (-not (Test-Path $CloudflaredBin)) {
    Write-Host "Error: cloudflared.exe not found at $CloudflaredBin" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    Exit 1
}

$Host.UI.RawUI.WindowTitle = "CAWS Pet Adoption - Live Online Cloud Bridge"

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   CAWS Pet Adoption - Live Online Cloud Bridge   " -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

# 1. Start multi-threaded XAMPP Apache on port 8000 (replacing single-threaded php artisan serve)
$ApacheBin = "C:\xampp\apache\bin\httpd.exe"
$PhpApacheDll = "C:\xampp\php\php8apache2_4.dll"
$Port8000 = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue

if ($Port8000) {
    foreach ($conn in $Port8000) {
        $proc = Get-Process -Id $conn.OwningProcess -ErrorAction SilentlyContinue
        if ($proc -and $proc.ProcessName -eq "php") {
            Write-Host "Stopping slow single-threaded php artisan serve (PID $($proc.Id))..." -ForegroundColor Yellow
            Stop-Process -Id $proc.Id -Force -ErrorAction SilentlyContinue
            Start-Sleep -Seconds 1
        }
    }
    $Port8000 = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
}

if (-not $Port8000) {
    if ((Test-Path $ApacheBin) -and (Test-Path $PhpApacheDll)) {
        Write-Host "1. Starting multi-threaded Apache HTTP Server (64 workers) on port 8000..." -ForegroundColor Yellow
        $ProjectSlash = $PSScriptRoot -replace '\\', '/'
        $ApacheConfPath = Join-Path $PSScriptRoot "storage\framework\apache_caws.conf"
        $ApacheConf = @"
ServerRoot "C:/xampp/apache"
Listen 8000
ServerName localhost:8000

LoadModule authz_core_module modules/mod_authz_core.so
LoadModule access_compat_module modules/mod_access_compat.so
LoadModule alias_module modules/mod_alias.so
LoadModule dir_module modules/mod_dir.so
LoadModule env_module modules/mod_env.so
LoadModule setenvif_module modules/mod_setenvif.so
LoadModule filter_module modules/mod_filter.so
LoadModule deflate_module modules/mod_deflate.so
LoadModule headers_module modules/mod_headers.so
LoadModule log_config_module modules/mod_log_config.so
LoadModule mime_module modules/mod_mime.so
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule php_module "C:/xampp/php/php8apache2_4.dll"

PHPIniDir "C:/xampp/php"
AddHandler application/x-httpd-php .php

<IfModule mpm_winnt_module>
    ThreadsPerChild 64
    MaxConnectionsPerChild 0
</IfModule>

KeepAlive On
MaxKeepAliveRequests 100
KeepAliveTimeout 5
Timeout 120

TypesConfig "C:/xampp/apache/conf/mime.types"

<IfModule dir_module>
    DirectoryIndex index.php index.html
</IfModule>

PidFile "$ProjectSlash/storage/logs/apache_caws.pid"
ErrorLog "$ProjectSlash/storage/logs/apache_error.log"
LogLevel warn

DocumentRoot "$ProjectSlash/public"
<Directory "$ProjectSlash/public">
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

<IfModule deflate_module>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json image/svg+xml
</IfModule>
"@
        [System.IO.File]::WriteAllText($ApacheConfPath, $ApacheConf, [System.Text.UTF8Encoding]::new($false))
        Start-Process -FilePath $ApacheBin -ArgumentList "-f", "`"$ApacheConfPath`"" -WorkingDirectory $PSScriptRoot -WindowStyle Hidden
        Start-Sleep -Seconds 2
    } else {
        Write-Host "1. Starting Laravel server on port 8000..." -ForegroundColor Yellow
        Start-Process -FilePath "php" -ArgumentList "artisan", "serve", "--host=0.0.0.0", "--port=8000" -WorkingDirectory $PSScriptRoot -WindowStyle Hidden
        Start-Sleep -Seconds 2
    }
} else {
    Write-Host "1. Web server already active on port 8000." -ForegroundColor Green
}

# 2. Start cloudflared process and redirect stderr to log file
$LogFile = Join-Path $PSScriptRoot "tunnel.log"
if (Test-Path $LogFile) { Remove-Item $LogFile -Force }

Write-Host "2. Launching Cloudflare Tunnel to port 8000..." -ForegroundColor Yellow
$Process = Start-Process -FilePath $CloudflaredBin -ArgumentList "tunnel", "--protocol", "auto", "--url", "http://127.0.0.1:8000" -RedirectStandardError $LogFile -PassThru -WindowStyle Hidden

# 3. Wait and capture the assigned trycloudflare URL
$TunnelUrl = ""
$MaxWaitSeconds = 25
$Waited = 0

Write-Host "3. Waiting for public tunnel address..." -ForegroundColor Yellow
while ($Waited -lt $MaxWaitSeconds) {
    Start-Sleep -Seconds 1
    $Waited++
    if (Test-Path $LogFile) {
        $LogContent = Get-Content $LogFile -Raw -ErrorAction SilentlyContinue
        if ($LogContent -match '(https://[a-zA-Z0-9-]+\.trycloudflare\.com)') {
            $TunnelUrl = $Matches[1]
            break
        }
    }
}

if (-not $TunnelUrl) {
    Write-Host "Error: Could not retrieve Cloudflare tunnel URL within $MaxWaitSeconds seconds." -ForegroundColor Red
    Write-Host "Check tunnel.log for details." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    Exit 1
}

Write-Host "-> Tunnel active at: $TunnelUrl" -ForegroundColor Green

# 4. Update Vercel configuration (UTF-8 without BOM)
Write-Host "4. Updating Vercel reverse proxy..." -ForegroundColor Yellow
$VercelJsonPath = Join-Path $PSScriptRoot "caws-portal-entry\vercel.json"
$VercelConfig = @{
    cleanUrls = $true
    rewrites  = @(
        @{
            source      = "/(.*)"
            destination = "$TunnelUrl/`$1"
        }
    )
} | ConvertTo-Json -Depth 5

[System.IO.File]::WriteAllText($VercelJsonPath, $VercelConfig, [System.Text.UTF8Encoding]::new($false))

# 5. Deploy to Vercel production
Write-Host "5. Synchronizing Vercel production deployment..." -ForegroundColor Yellow
$PortalDir = Join-Path $PSScriptRoot "caws-portal-entry"
& npx.cmd -y vercel $PortalDir --prod --yes

Write-Host ""
Write-Host "==================================================" -ForegroundColor Green
Write-Host "  SYSTEM IS NOW LIVE ONLINE!                      " -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Public Web Portal: https://caws-petadoption.vercel.app" -ForegroundColor Cyan
Write-Host "Backend Tunnel   : $TunnelUrl" -ForegroundColor Gray
Write-Host ""
Write-Host "==================================================" -ForegroundColor DarkGray
Write-Host "Status: ONLINE and proxying requests to local PC." -ForegroundColor Green
Write-Host "Keep this terminal window open while testing." -ForegroundColor Yellow
Write-Host "To shut down when finished, simply close this window." -ForegroundColor Gray
Write-Host "==================================================" -ForegroundColor DarkGray
Write-Host ""

# Monitor tunnel log in real time so the user can see incoming requests
Get-Content -Path $LogFile -Wait
