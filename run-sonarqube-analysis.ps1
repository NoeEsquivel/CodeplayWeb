param(
    [string] $ScannerPath = 'C:\tools\sonar-scanner-8.0.1.6346-windows-x64\bin\sonar-scanner.bat'
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $ScannerPath)) {
    throw "No se encontró SonarScanner en: $ScannerPath"
}

if (-not (Test-Path -LiteralPath 'coverage\clover.xml')) {
    throw 'No existe coverage\clover.xml. Ejecuta primero las pruebas con cobertura.'
}

$secureToken = Read-Host 'Pega un token vigente de SonarQube y presiona Enter' -AsSecureString
$credential = [Net.NetworkCredential]::new('', $secureToken)

try {
    $env:SONAR_HOST_URL = 'http://localhost:9000'
    $env:SONAR_TOKEN = $credential.Password

    if ([string]::IsNullOrWhiteSpace($env:SONAR_TOKEN)) {
        throw 'El token quedó vacío.'
    }

    & $ScannerPath
    if ($LASTEXITCODE -ne 0) {
        throw "SonarScanner terminó con código $LASTEXITCODE."
    }
} finally {
    Remove-Item Env:SONAR_TOKEN -ErrorAction SilentlyContinue
    Remove-Item Env:SONAR_HOST_URL -ErrorAction SilentlyContinue
    $credential = $null
    $secureToken = $null
}
