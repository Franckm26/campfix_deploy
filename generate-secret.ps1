# Generate a secure random secret key for backup authentication
$bytes = New-Object byte[] 32
[System.Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
$secret = [Convert]::ToBase64String($bytes)

Write-Host "================================" -ForegroundColor Green
Write-Host "Your CRON_SECRET Key" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green
Write-Host ""
Write-Host $secret -ForegroundColor Yellow
Write-Host ""
Write-Host "================================" -ForegroundColor Green
Write-Host "Copy this key and:" -ForegroundColor Cyan
Write-Host "1. Add to Vercel Environment Variables" -ForegroundColor White
Write-Host "2. Add to cron-job.org Authorization header" -ForegroundColor White
Write-Host "================================" -ForegroundColor Green
Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
