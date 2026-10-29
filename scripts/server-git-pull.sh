#!/usr/bin/env bash
# Runs ON Hostinger — pull latest main for ff-addons.
set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-/home/u875403732/domains/facefood.cafe/repos/ff-addons}"
GITHUB_REPO="${GITHUB_REPO:-imranniaz-st/ff-addons}"
BRANCH="${DEPLOY_BRANCH:-main}"

cd "${DEPLOY_PATH}"

if [[ -n "${GITHUB_PAT:-}" ]]; then
  git remote set-url origin "https://x-access-token:${GITHUB_PAT}@github.com/${GITHUB_REPO}.git"
else
  git remote set-url origin "https://github.com/${GITHUB_REPO}.git"
fi

git fetch origin "${BRANCH}"
git checkout "${BRANCH}"
git reset --hard "origin/${BRANCH}"

# Never leave a token in the remote URL.
git remote set-url origin "https://github.com/${GITHUB_REPO}.git"

echo "==> HEAD: $(git rev-parse --short HEAD) ($(git log -1 --pretty=%s))"
