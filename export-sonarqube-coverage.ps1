param(
    [string]$SonarUrl = 'http://localhost:9000',
    [string]$ProjectKey = 'CodePlayWeb',
    [string]$OutputPath = 'sonarqube-coverage-details.json'
)

$ErrorActionPreference = 'Stop'

$token = $env:SONAR_TOKEN
if ([string]::IsNullOrWhiteSpace($token)) {
    $secureToken = Read-Host -Prompt 'Pega el User Token de SonarQube y presiona Enter' -AsSecureString
    $token = [Net.NetworkCredential]::new('', $secureToken).Password
    Remove-Variable secureToken
}
if ([string]::IsNullOrWhiteSpace($token)) {
    throw 'No se recibió ningún token. Ejecuta de nuevo el script y pega el token cuando aparezca el aviso.'
}

$credentialBytes = [Text.Encoding]::ASCII.GetBytes("$($token):")
$headers = @{ Authorization = 'Basic ' + [Convert]::ToBase64String($credentialBytes) }
$metrics = 'new_coverage,new_lines_to_cover,new_uncovered_lines,coverage,lines_to_cover,uncovered_lines'
$encodedProject = [Uri]::EscapeDataString($ProjectKey)

$summaryUri = "$SonarUrl/api/measures/component?component=$encodedProject&metricKeys=$metrics"
$summary = Invoke-RestMethod -Uri $summaryUri -Headers $headers -Method Get

$files = @()
$page = 1
$pageSize = 500

do {
    $treeUri = "$SonarUrl/api/measures/component_tree?component=$encodedProject&metricKeys=$metrics&qualifiers=FIL&strategy=leaves&p=$page&ps=$pageSize"
    $response = Invoke-RestMethod -Uri $treeUri -Headers $headers -Method Get
    $files += @($response.components)
    $total = [int]$response.paging.total
    $page++
} while ($files.Count -lt $total)

$analysesUri = "$SonarUrl/api/project_analyses/search?project=$encodedProject&ps=10"
$analyses = Invoke-RestMethod -Uri $analysesUri -Headers $headers -Method Get

$result = [ordered]@{
    generated_at = (Get-Date).ToString('o')
    project_key = $ProjectKey
    summary = $summary.component
    files = $files
    analyses = $analyses.analyses
}

$result | ConvertTo-Json -Depth 20 | Set-Content -LiteralPath $OutputPath -Encoding utf8
Write-Host "Informe creado: $((Resolve-Path $OutputPath).Path)"
Write-Host "Archivos incluidos: $($files.Count)"
