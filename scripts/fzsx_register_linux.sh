#!/usr/bin/env bash
# Register .fzsx / .fzsxsd / .fzsd with PHP CLI handler (one-time per user on Linux).
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
LAUNCHER="${SCRIPT_DIR}/fzsx-open"
MIME_DIR="${XDG_DATA_HOME:-$HOME/.local/share}/mime/packages"
DESKTOP_DIR="${XDG_DATA_HOME:-$HOME/.local/share}/applications"

mkdir -p "${MIME_DIR}" "${DESKTOP_DIR}"

cat > "${MIME_DIR}/fzsx.xml" <<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<mime-info xmlns="http://www.freedesktop.org/standards/shared-mime-info">
  <mime-type type="application/x-fzsx">
    <comment>FZSX self-extracting fractal_zip archive</comment>
    <glob pattern="*.fzsx"/>
    <icon name="application-x-executable"/>
  </mime-type>
</mime-info>
XML

cat > "${MIME_DIR}/fzsxsd.xml" <<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<mime-info xmlns="http://www.freedesktop.org/standards/shared-mime-info">
  <mime-type type="application/x-fzsxsd">
    <comment>FZSXSD self-destructing fractal_zip archive</comment>
    <glob pattern="*.fzsxsd"/>
    <glob pattern="*.fzsd"/>
    <icon name="application-x-executable"/>
  </mime-type>
</mime-info>
XML

cat > "${DESKTOP_DIR}/fzsx-open.desktop" <<EOF
[Desktop Entry]
Type=Application
Name=FZSX Extract
Comment=Extract fractal_zip .fzsx / .fzsxsd / .fzsd beside the archive
Exec=${LAUNCHER} %f
Terminal=true
NoDisplay=true
MimeType=application/x-fzsx;application/x-fzsxsd;
EOF

chmod +x "${LAUNCHER}" 2>/dev/null || true

if command -v update-mime-database >/dev/null 2>&1; then
	update-mime-database "${XDG_DATA_HOME:-$HOME/.local/share}/mime"
fi
if command -v update-desktop-database >/dev/null 2>&1; then
	update-desktop-database "${DESKTOP_DIR}"
fi

echo "Registered .fzsx / .fzsxsd / .fzsd → ${LAUNCHER} %f"
echo "Repo root (for FRACTAL_ZIP_PHP if needed): ${REPO_ROOT}/fractal_zip.php"
echo "Double-click any .fzsx, .fzsxsd, or .fzsd in your file manager to extract beside it."
echo ".fzsxsd / .fzsd archives remove themselves after a successful extract."
