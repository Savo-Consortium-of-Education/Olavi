[Console]::OutputEncoding = [System.Text.UTF8Encoding]::new()


# ...existing code...
# Organisaatio
$org = "Savo-Consortium-of-Education"

# Kysy repo-nimet
$sourceRepoName = Read-Host "Syötä lähde-repon nimi (esim. pienyrittajan-taloushallinto)"
$targetRepoName = Read-Host "Syötä kohde-repon nimi (esim. vx)"
$startNumber = Read-Host "Syötä issueen numero, josta kopioiminen aloitetaan (tai paina Enter aloittaaksesi ensimmäisestä)" 

# Muodosta täydelliset repo-polut
$sourceRepo = "$org/$sourceRepoName"
$targetRepo = "$org/$targetRepoName"

Write-Host ""
Write-Host "Lähde: $sourceRepo" -ForegroundColor Yellow
Write-Host "Kohde: $targetRepo" -ForegroundColor Yellow
if ($startNumber) {
    Write-Host "Aloitus: Issue #$startNumber" -ForegroundColor Yellow
}
Write-Host ""

# Hae kaikki issueita lähde-reposta
Write-Host "Haetaan issueita..." -ForegroundColor Yellow
$issues = gh issue list -R $sourceRepo --state open --json number,title,body | ConvertFrom-Json

# Tarkista, löytyikö issueita
if ($issues.Count -eq 0) {
    Write-Host "Ei issueita löytynyt!" -ForegroundColor Red
    exit
}

Write-Host "Löytyi $($issues.Count) issuea. Aloitetaan kopiointi..." -ForegroundColor Green
Write-Host ""

# ...existing code...
# Kopioi jokainen issue
$i = 0
foreach ($issue in $issues) {
    # Jos aloitusnumero määritetty, ohita sitä aikaisemmat
    if ($startNumber -and $issue.number -lt [int]$startNumber) {
        continue
    }
    
    $i++
    $title = $issue.title
    $body = $issue.body
    
    $message = "[$i/$($issues.Count)] Kopioidaan: $title (#{0})" -f $issue.number
    Write-Host $message -ForegroundColor Cyan
    
    gh issue create -R $targetRepo --title "$title" --body "$body" | Out-Null
}

Write-Host ""
Write-Host "✅ Kaikki $($issues.Count) issuea kopioitu!" -ForegroundColor Green
