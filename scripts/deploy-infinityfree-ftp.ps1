param(
    [Parameter(Mandatory = $true)]
    [string]$ReleaseDirectory,
    [string]$ConfigPath = (Join-Path (Split-Path -Parent $PSScriptRoot) '.infinityfree-ftp.json'),
    [switch]$ListOnly
)

$ErrorActionPreference = 'Stop'

function Read-FtpConfig {
    param([string]$Path)

    if (-not (Test-Path -LiteralPath $Path -PathType Leaf)) {
        throw "FTP configuration is missing. Run .\scripts\set-infinityfree-ftp-config.ps1 locally with the FTP host and username from the InfinityFree panel. The password is entered only at that local prompt."
    }

    $config = Get-Content -LiteralPath $Path -Raw | ConvertFrom-Json
    foreach ($name in @('host', 'username', 'password', 'remote_root')) {
        if ([string]::IsNullOrWhiteSpace([string]$config.$name)) {
            throw "FTP configuration field '$name' is missing."
        }
    }

    $securePassword = $config.password | ConvertTo-SecureString
    [pscustomobject]@{
        Host = [string]$config.host
        Port = [int]($config.port ?? 21)
        RemoteRoot = ([string]$config.remote_root).Trim('/').Replace('\', '/')
        UseSsl = [bool]$config.use_ssl
        Credential = [pscredential]::new([string]$config.username, $securePassword)
    }
}

function Get-RemoteUri {
    param([pscustomobject]$Config, [string]$Path)

    $segments = @($Config.RemoteRoot.Trim('/').Split('/') + $Path.Trim('/').Split('/')) |
        Where-Object { $_ -ne '' } |
        ForEach-Object { [Uri]::EscapeDataString($_) }
    $scheme = if ($Config.UseSsl) { 'ftps' } else { 'ftp' }

    return "${scheme}://$($Config.Host):$($Config.Port)/$($segments -join '/')"
}

function New-FtpRequest {
    param([pscustomobject]$Config, [string]$Method, [string]$Path)

    $request = [System.Net.FtpWebRequest]::Create((Get-RemoteUri -Config $Config -Path $Path))
    $request.Method = $Method
    $request.Credentials = $Config.Credential
    $request.UseBinary = $true
    $request.KeepAlive = $false
    $request.EnableSsl = $Config.UseSsl
    return $request
}

function Get-FtpListing {
    param([pscustomobject]$Config, [string]$Path = '')

    $request = New-FtpRequest -Config $Config -Method ([System.Net.WebRequestMethods+Ftp]::ListDirectory) -Path $Path
    $response = $request.GetResponse()
    try {
        $reader = [System.IO.StreamReader]::new($response.GetResponseStream())
        try { return @($reader.ReadToEnd() -split "\r?\n" | Where-Object { $_ -ne '' }) }
        finally { $reader.Dispose() }
    }
    finally { $response.Dispose() }
}

function Test-FtpFile {
    param([pscustomobject]$Config, [string]$Path, [long]$ExpectedLength)

    try {
        $request = New-FtpRequest -Config $Config -Method ([System.Net.WebRequestMethods+Ftp]::GetFileSize) -Path $Path
        $response = $request.GetResponse()
        try { return ([long]$response.ContentLength -eq $ExpectedLength) }
        finally { $response.Dispose() }
    }
    catch { return $false }
}

function Ensure-FtpDirectory {
    param([pscustomobject]$Config, [string]$Path)

    try {
        $request = New-FtpRequest -Config $Config -Method ([System.Net.WebRequestMethods+Ftp]::MakeDirectory) -Path $Path
        $response = $request.GetResponse()
        $response.Dispose()
    }
    catch {
        # Existing directories return 550 on most FTP servers. A later upload
        # or the protected-root listing will still fail if this is another error.
    }
}

function Upload-FtpFile {
    param([pscustomobject]$Config, [string]$LocalPath, [string]$RemotePath)

    $request = New-FtpRequest -Config $Config -Method ([System.Net.WebRequestMethods+Ftp]::UploadFile) -Path $RemotePath
    $bytes = [System.IO.File]::ReadAllBytes($LocalPath)
    $request.ContentLength = $bytes.Length
    $stream = $request.GetRequestStream()
    try { $stream.Write($bytes, 0, $bytes.Length) }
    finally { $stream.Dispose() }
    $response = $request.GetResponse()
    $response.Dispose()
}

$projectRoot = Split-Path -Parent $PSScriptRoot
$releaseRoot = (Resolve-Path -LiteralPath $ReleaseDirectory).Path
$manifestPath = Join-Path $releaseRoot 'DEPLOYMENT-MANIFEST.json'
if (-not (Test-Path -LiteralPath $manifestPath -PathType Leaf)) {
    throw 'ReleaseDirectory must contain DEPLOYMENT-MANIFEST.json.'
}

$config = Read-FtpConfig -Path $ConfigPath
$rootListing = Get-FtpListing -Config $config
$rootNames = $rootListing | ForEach-Object { ($_ -split '\s+')[-1] }
if ($rootNames -notcontains '.env' -or $rootNames -notcontains 'storage') {
    throw 'Protected target check failed: remote root must already contain both .env and storage. No upload was attempted.'
}

if ($ListOnly) {
    Write-Host 'FTP connection and protected production root verified. No files uploaded.'
    $rootNames | Sort-Object | ForEach-Object { Write-Host "- $_" }
    exit 0
}

$excludedRoots = @('.env', 'storage', 'public/storage', 'node_modules', 'tests', 'dist')
$files = Get-ChildItem -LiteralPath $releaseRoot -Recurse -Force -File | Where-Object {
    $relative = $_.FullName.Substring($releaseRoot.Length).TrimStart('\', '/') -replace '\\', '/'
    $relative -notmatch '(^|/)(\.git|\.github|node_modules|tests)(/|$)' -and
        $relative -notmatch '(^|/)\.env(?:\..*)?$' -and
        -not ($excludedRoots | Where-Object { $relative -eq $_ -or $relative.StartsWith($_ + '/') })
}

$directories = $files | ForEach-Object {
    $relative = $_.FullName.Substring($releaseRoot.Length).TrimStart('\', '/') -replace '\\', '/'
    $parts = $relative.Split('/')
    if ($parts.Count -gt 1) {
        1..($parts.Count - 1) | ForEach-Object { ($parts[0..($_ - 1)]) -join '/' }
    }
} | Sort-Object -Unique | Sort-Object { ($_ -split '/').Count }
foreach ($directory in $directories) { Ensure-FtpDirectory -Config $config -Path $directory }

$uploaded = 0
$skipped = 0
foreach ($file in $files) {
    $relative = $file.FullName.Substring($releaseRoot.Length).TrimStart('\', '/') -replace '\\', '/'
    if (Test-FtpFile -Config $config -Path $relative -ExpectedLength $file.Length) {
        $skipped++
        continue
    }
    Upload-FtpFile -Config $config -LocalPath $file.FullName -RemotePath $relative
    $uploaded++
}

if (-not (Test-FtpFile -Config $config -Path 'artisan' -ExpectedLength (Get-Item -LiteralPath (Join-Path $releaseRoot 'artisan')).Length)) {
    throw 'Post-upload verification failed for artisan.'
}
if (-not (Test-FtpFile -Config $config -Path 'DEPLOYMENT-MANIFEST.json' -ExpectedLength (Get-Item -LiteralPath $manifestPath).Length)) {
    throw 'Post-upload verification failed for DEPLOYMENT-MANIFEST.json.'
}

Write-Host "FTP deployment complete. Uploaded $uploaded files; skipped $skipped same-size files. Existing .env and storage were protected."
