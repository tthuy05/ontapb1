param(
    [ValidateRange(1, 20)]
    [int] $Iterations = 3,
    [string] $PhpPath = ''
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
if ($PhpPath -eq '') {
    $phpCommand = Get-Command php -ErrorAction SilentlyContinue
    if ($null -eq $phpCommand) {
        throw 'PHP was not found. Pass -PhpPath with the project PHP executable.'
    }
    $PhpPath = $phpCommand.Source
}

if (-not (Test-Path -LiteralPath $PhpPath -PathType Leaf)) {
    throw "PHP executable was not found: $PhpPath"
}

$php = (Resolve-Path -LiteralPath $PhpPath).Path
$artisan = Join-Path $projectRoot 'artisan'

$commands = @(
    @('content:validate', '--json'),
    @('route:list', '--except-vendor')
)

$results = foreach ($command in $commands) {
    $durations = foreach ($iteration in 1..$Iterations) {
        $exitCode = 0
        $measurement = Measure-Command {
            & $php $artisan @command *> $null
            $exitCode = $LASTEXITCODE
        }
        if ($exitCode -ne 0) {
            throw "Command failed during performance measurement: $($command -join ' ') (exit $exitCode)"
        }
        [math]::Round($measurement.TotalMilliseconds, 2)
    }

    [PSCustomObject]@{
        command = ($command -join ' ')
        iterations = $Iterations
        min_ms = ($durations | Measure-Object -Minimum).Minimum
        avg_ms = [math]::Round(($durations | Measure-Object -Average).Average, 2)
        max_ms = ($durations | Measure-Object -Maximum).Maximum
    }
}

$results | ConvertTo-Json
