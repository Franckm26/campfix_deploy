@echo off
REM Simple Rate Limiting Test Script
REM This will test the login endpoint rate limit

echo.
echo ===================================
echo Rate Limiting Test
echo ===================================
echo.
echo Testing login rate limit (5 per minute)
echo This should succeed 5 times, then return 429
echo.

set URL=http://localhost/login
set /A COUNT=0

:loop
set /A COUNT+=1
echo Request %COUNT%:

curl -X POST %URL% -H "Content-Type: application/json" -d "{\"email\":\"test@test.com\",\"password\":\"wrong\"}" -w "HTTP Status: %%{http_code}\n" -o nul -s

if %COUNT% lss 7 goto loop

echo.
echo ===================================
echo Test Complete
echo Expected: First 5 succeed, last 2 get HTTP 429
echo ===================================
echo.

pause
