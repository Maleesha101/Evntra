<#
PowerShell setup helper for Evntra
Usage examples:
  # Using Docker (recommended). Password used for MySQL root inside container.
  .\scripts\setup-windows.ps1 -UseDocker -MySqlRootPassword "my-secret-pw" -StartPhpServer

  # Using local MySQL CLI (mysql.exe must be on PATH). Provide root password or leave empty to be prompted.
  .\scripts\setup-windows.ps1 -MySqlRootPassword "" -StartPhpServer
#>
param(
    [switch]$UseDocker,
    [string]$MySqlRootPassword = "",
    [switch]$StartPhpServer
)

function Has-Command($name) {
    return (Get-Command $name -ErrorAction SilentlyContinue) -ne $null
}

Write-Host "Running Evntra setup helper..."

# Ensure uploads/banners exists
$uploads = Join-Path $PSScriptRoot "..\uploads\banners"
if (-not (Test-Path $uploads)) {
    New-Item -ItemType Directory -Path $uploads -Force | Out-Null
    Write-Host "Created: $uploads"
} else {
    Write-Host "Exists: $uploads"
}

# Try Composer
if (Has-Command "composer") {
    Write-Host "Composer found, installing PHP dependencies..."
    Push-Location $PSScriptRoot\..\
    composer install
    Pop-Location
} else {
    Write-Host "Composer not found; please install Composer to auto-install PHP deps. Skipping."
}

if ($UseDocker) {
    if (-not (Has-Command "docker")) {
        Write-Host "Docker not available on PATH. Install Docker Desktop or pass -UseDocker=false to use local MySQL."
    } else {
        $containerName = "evntra-mysql"
        # Check if container exists
        $exists = docker ps -a --format "{{.Names}}" | Select-String -Pattern "^$containerName$"
        if (-not $exists) {
            if ($MySqlRootPassword -eq "") { $MySqlRootPassword = "my-secret-pw" }
            Write-Host "Starting MySQL Docker container '$containerName'..."
            docker run --name $containerName -e "MYSQL_ROOT_PASSWORD=$MySqlRootPassword" -e "MYSQL_DATABASE=unicompete_hub" -p 3306:3306 -d mysql:8.0
            Start-Sleep -Seconds 6
        } else {
            Write-Host "Docker container '$containerName' already exists."
        }

        Write-Host "Importing schema into Docker MySQL..."
        Get-Content "$PSScriptRoot\..\sql\schema.sql" | docker exec -i $containerName mysql -u root -p$MySqlRootPassword unicompete_hub
        Write-Host "Schema import (Docker) complete."
    }
} else {
    if (Has-Command "mysql") {
        Write-Host "Local MySQL CLI found. Importing schema..."
        if ($MySqlRootPassword -ne "") {
            Get-Content "$PSScriptRoot\..\sql\schema.sql" | & mysql -u root -p$MySqlRootPassword unicompete_hub
            Write-Host "Schema import (local MySQL with provided password) complete."
        } else {
            Write-Host "No password provided; attempting import and allowing interactive password prompt."
            try {
                Get-Content "$PSScriptRoot\..\sql\schema.sql" | & mysql -u root -p unicompete_hub
                Write-Host "Schema import (local MySQL) complete."
            } catch {
                Write-Host "Import may have failed. Rerun with -MySqlRootPassword or ensure mysql.exe is usable from this shell."
            }
        }
    } else {
        Write-Host "mysql client not found on PATH. Install MySQL or use -UseDocker."
    }
}

# Set uploads permissions (grant current user full control)
try {
    $user = $env:USERNAME
    $aclEntry = "$($user):(OI)(CI)F"
    icacls "$uploads" /grant $aclEntry /T | Out-Null
    Write-Host "Set full control for $user on uploads/banners"
} catch {
    Write-Host "Failed to adjust ACLs for uploads. You may need to set permissions manually."
}

if ($StartPhpServer) {
    if (Has-Command "php") {
        Write-Host "Starting PHP built-in server at http://localhost:8000 ..."
        Push-Location $PSScriptRoot\..\
        Start-Process php -ArgumentList "-S localhost:8000 -t ." -NoNewWindow
        Pop-Location
    } else {
        Write-Host "PHP CLI not found. Install PHP 8.1+ and add php.exe to PATH to start the built-in server."
    }
}

Write-Host "Setup script finished. Check output above for any errors."