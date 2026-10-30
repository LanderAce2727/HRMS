# DNSC Smart HRMS is a static HTML site. No PHP server or MySQL is needed.
$homePage = Join-Path $PSScriptRoot 'index.html'
Write-Output 'Open index.html in your browser, or use VS Code Live Server.'
Write-Output $homePage
Write-Output 'Upload the contents of dist/ for static hosting.'
