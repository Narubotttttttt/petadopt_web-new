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

# 1. Check & start Laravel server if not running
$Port8000 = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
if (-not $Port8000) {
    Write-Host "1. Starting Laravel server on port 8000..." -ForegroundColor Yellow
    Start-Process -FilePath "php" -ArgumentList "artisan", "serve", "--host=0.0.0.0", "--port=8000" -WorkingDirectory $PSScriptRoot -WindowStyle Hidden
    Start-Sleep -Seconds 2
} else {
    Write-Host "1. Laravel server already active on port 8000." -ForegroundColor Green
}

# 2. Start cloudflared process and redirect stderr to log file
$LogFile = Join-Path $PSScriptRoot "tunnel.log"
if (Test-Path $LogFile) { Remove-Item $LogFile -Force }

Write-Host "2. Launching Cloudflare Tunnel to port 8000..." -ForegroundColor Yellow
$Process = Start-Process -FilePath $CloudflaredBin -ArgumentList "tunnel", "--protocol", "http2", "--url", "http://127.0.0.1:8000" -RedirectStandardError $LogFile -PassThru -WindowStyle Hidden

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
