param(
    [string]$OutputDirectory = (Join-Path (Get-Location) 'dist\infinityfree')
)

$ErrorActionPreference = 'Stop'

function Resolve-Executable([string]$Name, [string[]]$Candidates) {
    $command = Get-Command $Name -ErrorAction SilentlyContinue
    if ($command) { return $command.Source }

    foreach ($candidate in $Candidates) {
        if (Test-Path -LiteralPath $candidate) { return $candidate }
    }

    throw "Required executable '$Name' was not found. Install it locally or add it to PATH."
}

$projectRoot = (Get-Location).Path
$php = Resolve-Executable 'php' @(
    'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe',
    'C:\Users\nguye\.cache\codex-runtimes\ontapb1\php-8.4\php.exe'
)
$composer = Resolve-Executable 'composer' @(
    'C:\laragon\bin\composer\composer.phar',
    'C:\laragon\bin\composer\composer.bat',
    'C:\Users\nguye\.cache\codex-runtimes\ontapb1\composer\composer.phar'
)
$node = Resolve-Executable 'node' @(
    'C:\Users\nguye\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\bin\node.exe'
)
$pnpm = Resolve-Executable 'pnpm' @()

Write-Host 'Installing production Composer dependencies locally...'
if ($composer.ToLowerInvariant().EndsWith('.phar')) {
    & $php $composer install --no-dev --classmap-authoritative --prefer-dist --no-interaction --no-progress
} else {
    & $composer install --no-dev --classmap-authoritative --prefer-dist --no-interaction --no-progress
}
if ($LASTEXITCODE -ne 0) { throw 'Composer install failed.' }

Write-Host 'Building frontend assets locally...'
$env:Path = (Split-Path -Parent $node) + ';' + $env:Path
& $pnpm install --frozen-lockfile
if ($LASTEXITCODE -ne 0) { throw 'pnpm install failed.' }
& $pnpm run build
if ($LASTEXITCODE -ne 0) { throw 'Frontend build failed.' }

$releaseName = 'release-' + (Get-Date -Format 'yyyyMMdd-HHmmss')
$releaseRoot = Join-Path $OutputDirectory $releaseName
New-Item -ItemType Directory -Path $releaseRoot -Force | Out-Null

$directories = @('app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'vendor')
foreach ($directory in $directories) {
    Copy-Item -LiteralPath (Join-Path $projectRoot $directory) -Destination $releaseRoot -Recurse -Force
}

# Production already owns its writable runtime storage. Omitting it from the
# release makes both ZIP extraction and FTP deployment preserve that state.
$databaseStateFiles = Get-ChildItem -LiteralPath (Join-Path $releaseRoot 'database') -Recurse -Force -File |
    Where-Object { $_.Extension -in @('.db', '.log', '.sqlite', '.sqlite3') }
foreach ($file in $databaseStateFiles) {
    Remove-Item -LiteralPath $file.FullName -Force
}

# Composer can leave stale development bin shims after an interrupted install;
# never include them in the upload artifact.
$developmentArtifacts = @(
    'vendor\bin\phpunit',
    'vendor\bin\phpunit.bat',
    'vendor\bin\pint',
    'vendor\bin\pint.bat',
    'vendor\bin\patch-type-declarations',
    'vendor\bin\patch-type-declarations.bat',
    'vendor\phpunit',
    'vendor\mockery',
    'vendor\nunomaduro\collision',
    'vendor\filp\whoops',
    'vendor\laravel\pint',
    'vendor\nikic\php-parser',
    'vendor\hamcrest\hamcrest-php'
)
foreach ($relativePath in $developmentArtifacts) {
    $path = Join-Path $releaseRoot $relativePath
    if (Test-Path -LiteralPath $path) {
        Remove-Item -LiteralPath $path -Recurse -Force
    }
}

# Source-based Composer installs contain repository metadata that is not needed
# at runtime and must never be uploaded to production.
$localMetadataDirectories = Get-ChildItem -LiteralPath $releaseRoot -Recurse -Force -Directory |
    Where-Object { $_.Name -in @('.git', '.github') } |
    Sort-Object -Property FullName -Descending
foreach ($directory in $localMetadataDirectories) {
    Remove-Item -LiteralPath $directory.FullName -Recurse -Force
}

$vendorDevelopmentDirectories = Get-ChildItem -LiteralPath (Join-Path $releaseRoot 'vendor') -Recurse -Force -Directory |
    Where-Object { $_.Name -in @('test', 'tests') } |
    Sort-Object -Property FullName -Descending
foreach ($directory in $vendorDevelopmentDirectories) {
    Remove-Item -LiteralPath $directory.FullName -Recurse -Force
}

$localStateFiles = Get-ChildItem -LiteralPath $releaseRoot -Recurse -Force -File |
    Where-Object { $_.Extension -in @('.db', '.log', '.sqlite', '.sqlite3') }
foreach ($file in $localStateFiles) {
    Remove-Item -LiteralPath $file.FullName -Force
}

Copy-Item -LiteralPath (Join-Path $projectRoot 'artisan') -Destination $releaseRoot -Force
Copy-Item -LiteralPath (Join-Path $projectRoot 'composer.json') -Destination $releaseRoot -Force
Copy-Item -LiteralPath (Join-Path $projectRoot 'composer.lock') -Destination $releaseRoot -Force
Copy-Item -LiteralPath (Join-Path $projectRoot 'deploy\infinityfree\htdocs.htaccess') -Destination (Join-Path $releaseRoot '.htaccess') -Force

$forbiddenFiles = Get-ChildItem -LiteralPath $releaseRoot -Recurse -Force -File | Where-Object {
    $_.Name -match '^\.env(?:\..*)?$' -or
    $_.Extension -in @('.db', '.log', '.sqlite', '.sqlite3') -or
    $_.FullName -match '[\\/]vendor[\\/]phpunit[\\/]' -or
    $_.FullName -match '[\\/]vendor[\\/]mockery[\\/]'
}
$forbiddenDirectories = Get-ChildItem -LiteralPath $releaseRoot -Recurse -Force -Directory | Where-Object {
    $_.Name -in @('.git', '.github', 'node_modules') -or
    ($_.FullName -match '[\\/]vendor[\\/]' -and $_.Name -in @('test', 'tests'))
}
if ($forbiddenFiles -or $forbiddenDirectories) {
    $names = @($forbiddenFiles.FullName) + @($forbiddenDirectories.FullName)
    throw "Release contains forbidden local-only content:`n$($names -join "`n")"
}

$manifest = [ordered]@{
    provider = 'InfinityFree free hosting'
    generated_at_utc = (Get-Date).ToUniversalTime().ToString('o')
    php_target = '8.3'
    laravel_target = '13'
    document_root = 'htdocs'
    environment_file = 'Create manually in htdocs; never upload local .env'
    database_bootstrap = 'database/infinityfree/sprint-0-schema.sql for a new database only'
    database_update = 'Sprint 6: import database/infinityfree/sprint-6-update.sql once through phpMyAdmin'
    excluded = @('.env', 'node_modules', 'tests', 'logs', 'local database files', 'source-control metadata', 'dev Composer packages')
}
$manifest | ConvertTo-Json -Depth 4 | Set-Content -LiteralPath (Join-Path $releaseRoot 'DEPLOYMENT-MANIFEST.json') -Encoding utf8

Write-Host "Release prepared at $releaseRoot"
