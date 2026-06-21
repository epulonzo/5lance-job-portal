# 5Lance API Endpoints Automated Test Script
$baseUrl = "http://localhost:8000"

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "Starting 5Lance API Integration Tests" -ForegroundColor Cyan
Write-Host "Base URL: $baseUrl" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

# Helper function to make requests
function Send-Request {
    param(
        [string]$Method,
        [string]$Path,
        [object]$Body = $null,
        [string]$Token = $null
    )

    $headers = @{
        "Content-Type" = "application/json"
    }

    if ($Token) {
        $headers.Add("Authorization", "Bearer $Token")
    }

    $jsonBody = $null
    if ($Body) {
        $jsonBody = $Body | ConvertTo-Json
    }

    try {
        $response = Invoke-RestMethod -Uri "$baseUrl$Path" -Method $Method -Headers $headers -Body $jsonBody
        return $response
    }
    catch {
        $streamReader = New-Object System.IO.StreamReader $_.Exception.Response.GetResponseStream()
        $errBody = $streamReader.ReadToEnd()
        Write-Host "Error Response Body: $errBody" -ForegroundColor Red
        throw $_
    }
}

# 1. Clean up existing test users if they exist
Write-Host "`n[Setup] Cleaning up test records from database..." -ForegroundColor Yellow
$cleanupScript = @"
USE lance5_db;
DELETE FROM users WHERE email IN ('client_test@5lance.com', 'freelancer_test@5lance.com');
"@
& C:\xampp\mysql\bin\mysql.exe -u root -e $cleanupScript

# 2. Register Client
Write-Host "`n1. Registering Client..." -ForegroundColor Green
$clientReg = @{
    name = "Test Client"
    email = "client_test@5lance.com"
    password = "Password123"
    role = "client"
    bio = "Looking to hire top talent."
}
$clientRegRes = Send-Request -Method Post -Path "/api/auth/register" -Body $clientReg
Write-Host "Client registered successfully! ID: $($clientRegRes.user.user_id)" -ForegroundColor DarkGreen

# 3. Register Freelancer
Write-Host "`n2. Registering Freelancer..." -ForegroundColor Green
$freelancerReg = @{
    name = "Test Freelancer"
    email = "freelancer_test@5lance.com"
    password = "Password123"
    role = "freelancer"
    bio = "Experienced developer."
    skills = "Vue.js, PHP"
}
$freelancerRegRes = Send-Request -Method Post -Path "/api/auth/register" -Body $freelancerReg
Write-Host "Freelancer registered successfully! ID: $($freelancerRegRes.user.user_id)" -ForegroundColor DarkGreen

# 4. Login Client
Write-Host "`n3. Logging in Client..." -ForegroundColor Green
$clientLogin = @{
    email = "client_test@5lance.com"
    password = "Password123"
}
$clientLoginRes = Send-Request -Method Post -Path "/api/auth/login" -Body $clientLogin
$clientToken = $clientLoginRes.token
$clientId = $clientLoginRes.user.user_id
Write-Host "Client logged in! Token retrieved." -ForegroundColor DarkGreen

# 5. Login Freelancer
Write-Host "`n4. Logging in Freelancer..." -ForegroundColor Green
$freelancerLogin = @{
    email = "freelancer_test@5lance.com"
    password = "Password123"
}
$freelancerLoginRes = Send-Request -Method Post -Path "/api/auth/login" -Body $freelancerLogin
$freelancerToken = $freelancerLoginRes.token
$freelancerId = $freelancerLoginRes.user.user_id
Write-Host "Freelancer logged in! Token retrieved." -ForegroundColor DarkGreen

# 6. Get Jobs (Public)
Write-Host "`n5. Fetching All Jobs (Public)..." -ForegroundColor Green
$jobs = Send-Request -Method Get -Path "/api/jobs"
Write-Host "Found $($jobs.Count) jobs in the system." -ForegroundColor DarkGreen

# 7. Create Job (Client)
Write-Host "`n6. Creating a New Job posting (as Client)..." -ForegroundColor Green
$newJob = @{
    title = "Develop E-Commerce Frontend"
    description = "We need an experienced freelancer to develop a responsive shop UI using Vue 3 and Pinia."
    category = "Development"
    budget = 3500.00
    deadline = "2026-08-30"
}
$newJobRes = Send-Request -Method Post -Path "/api/jobs" -Body $newJob -Token $clientToken
$jobId = $newJobRes.job_id
Write-Host "Job created successfully! Job ID: $jobId" -ForegroundColor DarkGreen

# 8. Get Single Job Details (Public)
Write-Host "`n7. Fetching Single Job Details (ID: $jobId)..." -ForegroundColor Green
$jobDetails = Send-Request -Method Get -Path "/api/jobs/$jobId"
Write-Host "Fetched job: '$($jobDetails.title)' posted by $($jobDetails.client_name)" -ForegroundColor DarkGreen

# 9. Apply for Job (Freelancer)
Write-Host "`n8. Submitting Application (as Freelancer)..." -ForegroundColor Green
$newApp = @{
    cover_letter = "I have 4 years of experience building modern e-commerce websites with Vue and Tailwind. I can start immediately."
    proposed_rate = 3300.00
}
$newAppRes = Send-Request -Method Post -Path "/api/jobs/$jobId/applications" -Body $newApp -Token $freelancerToken
$appId = $newAppRes.app_id
Write-Host "Application submitted successfully! Application ID: $appId" -ForegroundColor DarkGreen

# 10. Get Job Applications (Client)
Write-Host "`n9. Fetching Applications for Job (as Client)..." -ForegroundColor Green
$apps = Send-Request -Method Get -Path "/api/jobs/$jobId/applications" -Token $clientToken
Write-Host "Found $($apps.Count) applications. Applicant cover letter snippet: '$($apps[0].cover_letter)'" -ForegroundColor DarkGreen

# 11. Update Application Status (Client accepts)
Write-Host "`n10. Updating Application Status to 'accepted' (as Client)..." -ForegroundColor Green
$updateStatus = @{
    status = "accepted"
}
$updateStatusRes = Send-Request -Method Put -Path "/api/applications/$appId" -Body $updateStatus -Token $clientToken
Write-Host "Application status updated successfully! Current status: $($updateStatusRes.status)" -ForegroundColor DarkGreen

# 12. Get User Profile (Authenticated)
Write-Host "`n11. Getting Freelancer Profile details..." -ForegroundColor Green
$profileRes = Send-Request -Method Get -Path "/api/users/$freelancerId" -Token $freelancerToken
Write-Host "Profile retrieved! Name: $($profileRes.name), Bio: $($profileRes.bio)" -ForegroundColor DarkGreen

# 13. Update Own Profile (Freelancer owner)
Write-Host "`n12. Updating Freelancer Profile (as Freelancer Owner)..." -ForegroundColor Green
$updateProfile = @{
    name = "Test Freelancer Updated"
    bio = "Senior Vue 3 and PHP REST API developer."
    skills = "Vue 3, PHP Slim 4, MySQL, Tailwind CSS"
}
$updateProfileRes = Send-Request -Method Put -Path "/api/users/$freelancerId" -Body $updateProfile -Token $freelancerToken
Write-Host "Profile updated successfully! New bio: $($updateProfileRes.bio)" -ForegroundColor DarkGreen

# 14. Admin Deactivates Client User
Write-Host "`n13. Admin Deactivates Client User..." -ForegroundColor Green
# Login default admin from seed
$adminLogin = @{
    email = "admin@5lance.com"
    password = "Password123"
}
$adminLoginRes = Send-Request -Method Post -Path "/api/auth/login" -Body $adminLogin
$adminToken = $adminLoginRes.token

$deactivateRes = Send-Request -Method Delete -Path "/api/users/$clientId" -Token $adminToken
Write-Host "Success message from admin: $($deactivateRes.message)" -ForegroundColor DarkGreen

Write-Host "`n==================================================" -ForegroundColor Cyan
Write-Host "All 5Lance Backend Tests Passed Successfully!" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
