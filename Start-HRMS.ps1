# Start DNSC Smart HRMS from any directory. MySQL must be running.
param([int]$Port = 8085)
$projectRoot = $PSScriptRoot
$phpCommand = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpCommand) { throw 'PHP is not in PATH. Add the XAMPP php folder to PATH.' }
if (Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction SilentlyContinue) { throw "Port $Port is already in use. If HRMS is running, open http://127.0.0.1:$Port/index.html; otherwise choose another -Port." }
$phpPath = $phpCommand.Source
$routerPath = Join-Path $projectRoot 'router.php'
$logPath = Join-Path $projectRoot 'storage/server.log'
$errorPath = Join-Path $projectRoot 'storage/server-errors.log'
$process = Start-Process -FilePath $phpPath -ArgumentList @('-d','display_errors=Off','-d','log_errors=On','-d','upload_max_filesize=5M','-d','post_max_size=8M','-S',"127.0.0.1:$Port",'router.php') -WorkingDirectory $projectRoot -WindowStyle Hidden -RedirectStandardOutput $logPath -RedirectStandardError $errorPath -PassThru
Start-Sleep -Milliseconds 300
$process.Refresh()
if ($process.HasExited) { throw "The server could not start. See $errorPath." }
$process.Id | Set-Content -LiteralPath (Join-Path $projectRoot 'storage/server.pid')
Write-Output "DNSC Smart HRMS: http://127.0.0.1:$Port/index.html (process $($process.Id))"
