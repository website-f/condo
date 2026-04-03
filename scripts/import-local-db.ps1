param(
    [string]$DumpPath = "",
    [ValidateSet('full', 'missing')]
    [string]$Mode = "full"
)

$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$phpScript = Join-Path $scriptRoot 'import-local-db.php'

function Resolve-HerdPhp {
    $phpCommand = Get-Command php -ErrorAction SilentlyContinue
    if ($phpCommand) {
        return $phpCommand.Source
    }

    $herdRoot = Join-Path $HOME '.config\herd\bin'
    if (-not (Test-Path $herdRoot)) {
        throw "PHP was not found on PATH and Herd PHP was not found under $herdRoot"
    }

    $phpExecutables = Get-ChildItem $herdRoot -Directory -Filter 'php*' |
        Sort-Object Name -Descending |
        ForEach-Object {
            $candidate = Join-Path $_.FullName 'php.exe'
            if (Test-Path $candidate) { $candidate }
        }

    if (-not $phpExecutables) {
        throw "No Herd php.exe was found under $herdRoot"
    }

    return $phpExecutables[0]
}

$phpExe = Resolve-HerdPhp
$arguments = @($phpScript, "--mode=$Mode")

if ($DumpPath) {
    $resolvedDumpPath = (Resolve-Path $DumpPath).Path
    $arguments += "--dump=$resolvedDumpPath"
}

& $phpExe @arguments
exit $LASTEXITCODE
