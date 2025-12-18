#!/bin/bash

# Install Git hooks for the TYPE III AUDIO plugin
# This script copies hooks from the tracked hooks/ directory to .git/hooks/

HOOKS_DIR="$(cd "$(dirname "$0")" && pwd)"
GIT_HOOKS_DIR="$(git rev-parse --git-dir)/hooks"

echo "Installing Git hooks..."

# Install pre-commit hook
if [ -f "$HOOKS_DIR/pre-commit" ]; then
    cp "$HOOKS_DIR/pre-commit" "$GIT_HOOKS_DIR/pre-commit"
    chmod +x "$GIT_HOOKS_DIR/pre-commit"
    echo "✓ Installed pre-commit hook (enforces version bumping for asset files)"
else
    echo "✗ pre-commit hook not found in $HOOKS_DIR"
    exit 1
fi

echo ""
echo "Git hooks installed successfully!"
echo ""
echo "The pre-commit hook will now:"
echo "  - Check if you modify ANY .css or .js files in assets/"
echo "  - Ensure you've bumped T3A_80K_ASSET_REV in type-3-audio.php"
echo "  - Block the commit if version wasn't bumped"
echo ""
echo "To bypass the hook (not recommended): git commit --no-verify"
