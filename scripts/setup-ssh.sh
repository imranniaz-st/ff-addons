#!/usr/bin/env bash
# Local SSH alias for Facefood Hostinger (shared with main facefood deploy key).
set -euo pipefail

SSH_DIR="${HOME}/.ssh"
SSH_CONFIG="${SSH_DIR}/config"
KEY_FILE="${SSH_DIR}/facefood_deploy"
MARK="Host hostinger-facefood"

mkdir -p "${SSH_DIR}"
chmod 700 "${SSH_DIR}"

if [[ ! -f "${KEY_FILE}" ]]; then
  echo "Creating ${KEY_FILE} ..."
  ssh-keygen -t ed25519 -C "github-actions-ff-addons" -f "${KEY_FILE}" -N ""
  echo ""
  echo "Add this PUBLIC key in Hostinger hPanel → SSH Access → SSH keys:"
  echo "-----"
  cat "${KEY_FILE}.pub"
  echo "-----"
fi

if ! grep -q "${MARK}" "${SSH_CONFIG}" 2>/dev/null; then
  cat >> "${SSH_CONFIG}" <<'EOF'

Host hostinger-facefood
  HostName 145.79.26.46
  Port 65002
  User u875403732
  IdentityFile ~/.ssh/facefood_deploy
  IdentitiesOnly yes
EOF
  chmod 600 "${SSH_CONFIG}"
  echo "Added SSH config alias: hostinger-facefood"
else
  echo "SSH config already has hostinger-facefood"
fi

echo ""
echo "Test:"
echo "  ssh hostinger-facefood \"echo connected\""
echo ""
echo "Manual deploy:"
echo "  ssh hostinger-facefood"
echo "  export GITHUB_PAT='your-pat'"
echo "  export DEPLOY_PATH=/home/u875403732/domains/facefood.cafe/repos/ff-addons"
echo "  bash \"\${DEPLOY_PATH}/scripts/server-git-pull.sh\""
echo "  bash \"\${DEPLOY_PATH}/scripts/deploy-hostinger.sh\""
