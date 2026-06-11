# Rate Limiting Test Script
# Run this script to test rate limiting functionality

Write-Host "=== Rate Limiting Test Suite ===" -ForegroundColor Cyan
Write-Host ""

# Configuration
$baseUrl = "http://localhost"
$testEmail = "test@example.com"
$testPassword = "wrongpassword"

# Function to test endpoint
function Test-RateLimit {
    param(
        [string]$endpoint,
        [string]$method = "GET",
        [int]$requests = 10,
        [int]$expectedLimit,
        [hashtable]$body = @{}
    )
    
    Write-Host "Testing: $method $endpoint" -ForegroundColor Yellow
    Write-Host "Sending $requests requests (limit: $expectedLimit)..." -ForegroundColor Gray
    
    $successCount = 0
    $limitedCount = 0
    
    for ($i = 1; $i -le $requests; $i++) {
        try {
            $response = $null
            $statusCode = 0
            
            if ($method -eq "POST") {
                $jsonBody = $body | ConvertTo-Json
                $response = Invoke-WebRequest -Uri "$baseUrl$endpoint" -Method POST `
                    -ContentType "application/json" `
                    -Body $jsonBody `
                    -ErrorAction Stop
                $statusCode = $response.StatusCode
            } else {
                $response = Invoke-WebRequest -Uri "$baseUrl$endpoint" -Method GET -ErrorAction Stop
                $statusCode = $response.StatusCode
            }
            
            if ($statusCode -eq 200 -or $statusCode -eq 302) {
                $successCount++
                Write-Host "  [$i] Success (200)" -ForegroundColor Green
            }
        }
        catch {
            $statusCode = $_.Exception.Response.StatusCode.value__
            if ($statusCode -eq 429) {
                $limitedCount++
                Write-Host "  [$i] Rate Limited (429)" -ForegroundColor Red
            } else {
                Write-Host "  [$i] Error ($statusCode)" -ForegroundColor Magenta
            }
        }
        
        Start-Sleep -Milliseconds 100
    }
    
    Write-Host ""
    Write-Host "Results:" -ForegroundColor Cyan
    Write-Host "  Success: $successCount" -ForegroundColor Green
    Write-Host "  Rate Limited: $limitedCount" -ForegroundColor Red
    Write-Host "  Expected Limit: $expectedLimit" -ForegroundColor Yellow
    
    if ($limitedCount -gt 0 -and $successCount -le $expectedLimit) {
        Write-Host "  ✓ PASSED: Rate limiting working correctly" -ForegroundColor Green
    } elseif ($limitedCount -eq 0) {
        Write-Host "  ⚠ WARNING: No rate limiting detected (might need authentication)" -ForegroundColor Yellow
    } else {
        Write-Host "  ✗ FAILED: Unexpected behavior" -ForegroundColor Red
    }
    
    Write-Host ""
    Write-Host "---" -ForegroundColor Gray
    Write-Host ""
}

# Test 1: Login Rate Limit (5 per minute)
Write-Host "Test 1: Login Rate Limit" -ForegroundColor Cyan
Test-RateLimit -endpoint "/login" -method "POST" -requests 7 -expectedLimit 5 `
    -body @{email=$testEmail; password=$testPassword}

Start-Sleep -Seconds 2

# Test 2: OTP Rate Limit (3 per minute)
Write-Host "Test 2: OTP Delivery Rate Limit" -ForegroundColor Cyan
Test-RateLimit -endpoint "/otp-delivery" -method "POST" -requests 5 -expectedLimit 3 `
    -body @{delivery_method="email"}

Start-Sleep -Seconds 2

# Test 3: General Web Rate Limit (200 per minute)
Write-Host "Test 3: General Web Rate Limit" -ForegroundColor Cyan
Write-Host "Note: This test should NOT trigger rate limiting with 10 requests" -ForegroundColor Gray
Test-RateLimit -endpoint "/" -method "GET" -requests 10 -expectedLimit 200

Write-Host ""
Write-Host "=== Test Suite Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Notes:" -ForegroundColor Yellow
Write-Host "- Some tests may fail if authentication is required" -ForegroundColor Gray
Write-Host "- Check storage/logs/laravel.log for detailed rate limit logs" -ForegroundColor Gray
Write-Host "- To test authenticated endpoints, use Postman or authenticated cURL" -ForegroundColor Gray
Write-Host ""
Write-Host "To clear rate limits:" -ForegroundColor Yellow
Write-Host "  php artisan cache:clear" -ForegroundColor Gray
Write-Host ""
