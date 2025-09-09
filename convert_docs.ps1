# Simple PowerShell script to convert all .docx files to markdown using markitdown

# Set directories
$sourceDir = "docs\frameworks\Tools"
$destDir = "docs\frameworks\Tools\markdown"

# Create markdown directory
if (!(Test-Path $destDir)) {
    New-Item -ItemType Directory -Path $destDir -Force
    Write-Host "Created directory: $destDir"
}

# Get all .docx files
$docxFiles = Get-ChildItem -Path $sourceDir -Filter "*.docx"

Write-Host "Found $($docxFiles.Count) .docx files to convert"

# Convert each file
foreach ($file in $docxFiles) {
    $inputPath = $file.FullName
    $baseName = [System.IO.Path]::GetFileNameWithoutExtension($file.Name)
    $outputPath = Join-Path $destDir "$baseName.md"
    
    Write-Host "Converting: $($file.Name)"
    
    # Run markitdown and redirect output to markdown file
    markitdown $inputPath > $outputPath
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  Success: $baseName.md"
    } else {
        Write-Host "  Failed: $($file.Name)"
    }
}

Write-Host "Conversion complete!"
