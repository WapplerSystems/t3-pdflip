#!/usr/bin/env bash
# Copies the build of the pdflip library into Resources/Public/Pdflip/.
#
#   Build/sync-pdflip.sh                 # clones WapplerSystems/pdflip (main) and builds it
#   Build/sync-pdflip.sh ../pdflip       # builds a local checkout
#   PDFLIP_REF=v0.2.0 Build/sync-pdflip.sh
set -euo pipefail

EXT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
TARGET="$EXT_DIR/Resources/Public/Pdflip"
SOURCE="${1:-}"

if [ -z "$SOURCE" ]; then
  SOURCE="$(mktemp -d)/pdflip"
  git clone --depth 1 --branch "${PDFLIP_REF:-main}" https://github.com/WapplerSystems/pdflip.git "$SOURCE"
fi
SOURCE="$(cd "$SOURCE" && pwd)"

(cd "$SOURCE" && pnpm install --frozen-lockfile && pnpm build)

rm -rf "$TARGET"
mkdir -p "$TARGET"
cp "$SOURCE/dist/pdflip.global.js" "$SOURCE/dist/pdflip.css" "$TARGET/"
cp -r "$SOURCE/dist/pdfjs" "$TARGET/pdfjs"
cp "$SOURCE/LICENSE" "$TARGET/LICENSE"

VERSION="$(node -p "require('$SOURCE/package.json').version")"
COMMIT="$(git -C "$SOURCE" rev-parse --short HEAD 2>/dev/null || echo unknown)"
printf 'pdflip %s (%s)\n' "$VERSION" "$COMMIT" > "$TARGET/VERSION"
echo "pdflip $VERSION ($COMMIT) → $TARGET"
