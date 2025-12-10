#!/bin/bash
#
# Copy TYPE III AUDIO plugin from fork to WordPress local site
# Usage: ./copy-to-wp.sh
#
# NOTE: You may want to be on a new feature branch in the WordPress repo
#       before running this script, as it will change your working directory
#       and modify files in the WordPress installation.
#

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ============================================================================
# Configuration - Edit these paths if your repo locations are different
# ============================================================================

# Path to the plugin fork repository (this repo)
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_DIR="$SCRIPT_DIR"

# Path to WordPress plugins directory
# Default: ../80000-hours-2025/app/public/wp-content/plugins
WP_PLUGINS_DIR="$(cd "$SCRIPT_DIR/../80000-hours-2025/app/public/wp-content/plugins" 2>/dev/null && pwd || echo "")"

# Plugin name (folder name in wp-content/plugins)
PLUGIN_NAME="type-3-player"

# ============================================================================

echo -e "${BLUE}════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  Copy TYPE III AUDIO Plugin to WordPress Local Site${NC}"
echo -e "${BLUE}════════════════════════════════════════════════════════${NC}\n"

echo -e "${YELLOW}⚠️  NOTE: You may want to be on a new feature branch in the${NC}"
echo -e "${YELLOW}   WordPress repo before running this script.${NC}"
echo -e ""

echo -e "${YELLOW}This script assumes:${NC}"
echo -e "  • Plugin fork repo: ${BLUE}$REPO_DIR${NC}"
echo -e "  • WordPress site:   ${BLUE}../80000-hours-2025/app/public/wp-content/plugins${NC}"
echo -e ""

# Check if repo exists
if [ ! -d "$REPO_DIR" ]; then
    echo -e "${RED}Error: Repository directory not found: $REPO_DIR${NC}"
    exit 1
fi

# Check if WP plugins directory exists
if [ -z "$WP_PLUGINS_DIR" ] || [ ! -d "$WP_PLUGINS_DIR" ]; then
    echo -e "${RED}✗ Error: WordPress plugins directory not found${NC}"
    echo -e "  Expected: ../80000-hours-2025/app/public/wp-content/plugins"
    echo -e "  Relative to: $REPO_DIR"
    exit 1
fi

# Remove old plugin
echo -e "${YELLOW}→${NC} Removing old plugin from WordPress..."
rm -rf "$WP_PLUGINS_DIR/$PLUGIN_NAME"

# Copy plugin from repo
echo -e "${YELLOW}→${NC} Copying plugin files..."
cp -r "$REPO_DIR" "$WP_PLUGINS_DIR/$PLUGIN_NAME"

# Clean up development files
echo -e "${YELLOW}→${NC} Cleaning up development files..."
cd "$WP_PLUGINS_DIR/$PLUGIN_NAME"
rm -rf .git .gitignore .claude AGENTS.md build deploy copy-to-wp.sh

# Rename main plugin file if needed
if [ -f "type-3-audio.php" ]; then
    echo -e "${YELLOW}→${NC} Renaming main plugin file..."
    mv type-3-audio.php type-3-player.php
fi

# Verify copy
if [ -f "$WP_PLUGINS_DIR/$PLUGIN_NAME/type-3-player.php" ]; then
    echo -e "\n${GREEN}✓ Plugin copied successfully!${NC}"
    echo -e "  Location: $WP_PLUGINS_DIR/$PLUGIN_NAME/"
    echo -e "  Main file: type-3-player.php"
else
    echo -e "\n${RED}✗ Copy failed - main plugin file not found${NC}"
    exit 1
fi

echo -e "\n${BLUE}════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  Next Steps${NC}"
echo -e "${BLUE}════════════════════════════════════════════════════════${NC}"
echo -e ""
echo -e "${YELLOW}1.${NC} Test the plugin in WordPress locally"
echo -e "   • Refresh your local site"
echo -e "   • Reactivate the plugin if needed"
echo -e ""
echo -e "${YELLOW}2.${NC} Commit changes to the WordPress repo"
echo -e ""
echo -e "${YELLOW}3.${NC} Deploy WordPress repo to production"
echo -e ""
