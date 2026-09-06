param(
    [Parameter(Mandatory = $true)]
    [string]$HostName,
    [Parameter(Mandatory = $true)]
    [string]$Username,
    [int]$Port = 21,
    [string]$RemoteRoot = '/htdocs',
    [switch]$UseSsl
)

$ErrorActionPreference = 'Stop'
$configPath = Join-Path (Split-Path -Parent $PSScriptRoot) '.infinityfree-ftp.json'
$password = Read-Host 'InfinityFree FTP password (stored encrypted for this Windows user)' -AsSecureString
$encryptedPassword = $password | ConvertFrom-SecureString

[ordered]@{
    host = $HostName
    username = $Username
    port = $Port
    remote_root = $RemoteRoot
    use_ssl = [bool] $UseSsl
    password = $encryptedPassword
} | ConvertTo-Json | Set-Content -LiteralPath $configPath -Encoding utf8

Write-Host "Saved encrypted FTP configuration to $configPath"
