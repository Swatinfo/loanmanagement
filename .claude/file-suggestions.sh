#!/bin/bash
# File suggestion script for Claude Code @ autocomplete
# Returns key entry points for the shf_loan_quotation project

# Read query from stdin JSON
QUERY=$(cat | sed -n 's/.*"query"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' 2>/dev/null || echo "")

# If no query, return key entry points
if [ -z "$QUERY" ]; then
    echo "CLAUDE.md"
    echo ".docs/README.md"
    echo ".claude/database-schema.md"
    echo ".claude/routes-reference.md"
    echo ".claude/services-reference.md"
    echo "tasks/lessons.md"
    echo "tasks/todo.md"
    echo ".docs/workflow-guide.md"
    echo ".docs/workflow-developer.md"
    echo ".docs/quotations.md"
    echo ".docs/frontend.md"
    echo ".docs/settings.md"
    echo ".docs/permissions.md"
    echo ".docs/general-tasks.md"
    echo ".docs/dashboard.md"
    echo ".docs/loans.md"
    echo ".docs/dvr.md"
    echo ".docs/roles.md"
    echo ".docs/activity-log.md"
    exit 0
fi

PROJECT_DIR="${CLAUDE_PROJECT_DIR:-.}"
cd "$PROJECT_DIR" 2>/dev/null || exit 0

# Search priority locations, filter by query, limit results
{
    # Controllers
    find app/Http/Controllers -name "*.php" ! -name "__*" 2>/dev/null
    # Services
    find app/Services -name "*.php" ! -name "__*" 2>/dev/null
    # Models
    find app/Models -name "*.php" 2>/dev/null
    # Views
    find resources/views -name "*.blade.php" 2>/dev/null
    # Routes
    find routes -name "*.php" 2>/dev/null
    # Migrations
    find database/migrations -name "*.php" 2>/dev/null
    # Config
    find config -name "*.php" 2>/dev/null
    # CSS/JS
    echo "public/css/shf.css"
    echo "public/js/shf-app.js"
    echo "public/js/shf-loans.js"
    echo "public/js/offline-manager.js"
    # Middleware
    echo "app/Http/Middleware/CheckPermission.php"
    echo "app/Http/Middleware/EnsureUserIsActive.php"
    echo "bootstrap/app.php"
    # Docs
    find .docs -name "*.md" 2>/dev/null
    find .claude -name "*.md" ! -path "*plans*" 2>/dev/null
    echo "tasks/lessons.md"
    echo "tasks/todo.md"
    echo "CLAUDE.md"
    echo "Loan_Lifecycle_Roles_Actions.md"
} | grep -i "$QUERY" | head -15
