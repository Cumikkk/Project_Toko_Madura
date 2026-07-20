#!/usr/bin/env bash
set -e
USER="$1"
REPO_NAME="$2"
BRANCH_NAME="$3"
DIRECTORY="$4"
LOG_FILE="$DIRECTORY/config/deployment/$BRANCH_NAME.log"

{
    echo "-------------------------------------------------------"
    echo "[$(date)] START DEPLOY $REPO_NAME as user $USER on branch $BRANCH_NAME"    

    cd "$DIRECTORY" || {
        echo "Folder tidak ditemukan: $DIRECTORY"
        exit 1
    }
    echo "[$(date)] Changed directory to $(pwd)"
    
    echo "[$(date)] git fetch origin $BRANCH_NAME"
    git fetch origin "$BRANCH_NAME"

    echo "[$(date)] git reset --hard origin/$BRANCH_NAME"
    git reset --hard "origin/$BRANCH_NAME"

    echo "[$(date)] FINISHED DEPLOY $REPO_NAME"
} >> "$LOG_FILE" 2>&1