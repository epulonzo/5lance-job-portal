@echo off
echo ==================================================
echo Starting 5Lance Job Portal (Backend + Frontend)
echo ==================================================

:: Start backend using XAMPP PHP path
echo Starting PHP Slim Backend on http://localhost:8000...
start "5Lance Backend" cmd /k "cd backend && C:\xampp\php\php.exe -S localhost:8000 -t public"

:: Start frontend using cmd to bypass PowerShell execution restrictions
echo Starting Vue Vite Frontend...
start "5Lance Frontend" cmd /k "cd frontend && npm run dev"

echo.
echo ==================================================
echo Both servers are launching in separate windows!
echo - Backend: http://localhost:8000
echo - Frontend: Check the second console window for URL
echo ==================================================
pause
