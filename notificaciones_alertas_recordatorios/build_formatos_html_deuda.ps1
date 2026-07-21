$ErrorActionPreference = 'Stop'

$baseDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$sourceFile = Join-Path $baseDir 'formatos_html.produccion.original.php'
$targetFile = Join-Path $baseDir 'formatos_html.produccion.actualizado.php'

$templateMap = [ordered]@{
    vmensaje_deuda                 = '2 - aviso_de_deuda_dynamica.html'
    vmensaje_recordatoriopago      = '1 - recordatorio_de_pago_dynamica.html'
    vmensaje_notificacionsuspension = '4 - notificaci_n_de_suspensi_n_dynamica.html'
    vmensaje_alertasuspension      = '3 - alerta_de_suspensi_n_dynamica.html'
}

function Get-NormalizedTemplate {
    param(
        [string]$Path
    )

    $content = [System.IO.File]::ReadAllText($Path, [System.Text.Encoding]::UTF8)
    $content = $content -replace '\{\{RAZON_SOCIAL\}\}', 'vRazonSocial'
    $content = $content -replace '\{\{RUT\}\}', 'vRut'
    $content = $content -replace '\{\{FECHA_SUSPENSION\}\}', 'vFechaSuspension'
    return $content.Trim()
}

function Build-AssignmentBlock {
    param(
        [string]$VariableName,
        [string]$TemplateFileName
    )

    $templatePath = Join-Path $baseDir $TemplateFileName
    $html = Get-NormalizedTemplate -Path $templatePath

    return @"
// Plantilla actualizada desde notificaciones_alertas_recordatorios/$TemplateFileName
`$$VariableName = <<<'HTML'
$html
HTML;
"@
}

$php = [System.IO.File]::ReadAllText($sourceFile, [System.Text.Encoding]::UTF8)

$orderedKeys = @($templateMap.Keys)
for ($i = 0; $i -lt $orderedKeys.Count; $i++) {
    $key = $orderedKeys[$i]
    $nextKey = if ($i -lt ($orderedKeys.Count - 1)) { $orderedKeys[$i + 1] } else { $null }
    $replacement = Build-AssignmentBlock -VariableName $key -TemplateFileName $templateMap[$key]

    if ($nextKey) {
        $pattern = "(?s)\`$$key\s*=\s*'.*?';(?=\s*\`$$nextKey\s*=)"
    } else {
        $pattern = "(?s)\`$$key\s*=\s*'.*?';"
    }

    $php = [regex]::Replace($php, $pattern, [System.Text.RegularExpressions.MatchEvaluator]{ param($m) $replacement }, 1)
}

[System.IO.File]::WriteAllText($targetFile, $php, [System.Text.Encoding]::UTF8)
Write-Output "OK -> $targetFile"
