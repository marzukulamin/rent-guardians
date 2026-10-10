$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$phpPath = 'E:\Xamppp\php\php.exe'
$mysqlPath = 'E:\Xamppp\mysql\bin\mysqld.exe'
if (-not (Test-Path -LiteralPath (Join-Path $projectRoot 'dist\index.html'))) {
    Push-Location $projectRoot
    try { & npm.cmd run build; if ($LASTEXITCODE -ne 0) { throw 'Build failed.' } } finally { Pop-Location }
}
$mysqlIni = Join-Path $projectRoot 'storage\mysql\my.ini'
if (-not (Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue)) {
    if (-not (Test-Path -LiteralPath $mysqlIni)) { throw 'Initialize the isolated database first with mysql_install_db.exe.' }
    Start-Process -FilePath $mysqlPath -ArgumentList ('"--defaults-file=' + $mysqlIni + '" --bind-address=127.0.0.1') -WindowStyle Hidden
    Start-Sleep -Seconds 2
}
if (-not (Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath $phpPath -WorkingDirectory $projectRoot -ArgumentList '-d upload_max_filesize=10M -d post_max_size=12M -S 127.0.0.1:8000 -t dist server/router.php' -WindowStyle Hidden -RedirectStandardError (Join-Path $projectRoot 'storage\php-server.log') -RedirectStandardOutput (Join-Path $projectRoot 'storage\php-output.log')
}
if (-not (Get-NetTCPConnection -LocalPort 8081 -State Listen -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath $phpPath -WorkingDirectory 'E:\Xamppp\phpMyAdmin' -ArgumentList '-S 127.0.0.1:8081 -t E:\Xamppp\phpMyAdmin' -WindowStyle Hidden -RedirectStandardError (Join-Path $projectRoot 'storage\phpmyadmin.log') -RedirectStandardOutput (Join-Path $projectRoot 'storage\phpmyadmin-output.log')
}
& $phpPath (Join-Path $projectRoot 'server\run-jobs.php')
Write-Output 'Rent Guardian: http://127.0.0.1:8000'
Write-Output 'phpMyAdmin: http://127.0.0.1:8081'
Write-Output 'Credentials: storage/credentials.md'
