# Set proxy for this session only (user copied from IT wiki)
$env:HTTP_PROXY  = "http://proxy.corp.local:8080"
$env:HTTPS_PROXY = "http://proxy.corp.local:8080"
$env:NO_PROXY    = "localhost,127.0.0.1,*.internal"
Get-ChildItem -Path $HOME\Documents -Recurse -Filter *.csv | ForEach-Object { $_.Length }
