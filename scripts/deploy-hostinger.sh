#!/usr/bin/env bash
# Runs ON Hostinger after git pull — symlink FF Addons into WordPress plugins.
set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-/home/u875403732/domains/facefood.cafe/repos/ff-addons}"
WP_ROOT="${WP_ROOT:-/home/u875403732/domains/facefood.cafe/public_html}"
WP_PLUGIN_LINK="${WP_PLUGIN_LINK:-${WP_ROOT}/wp-content/plugins/ff-addons}"

echo "==> Deploy path: ${DEPLOY_PATH}"
echo "==> WP plugin link: ${WP_PLUGIN_LINK}"

if [[ ! -f "${DEPLOY_PATH}/ff-addons.php" ]]; then
  echo "ERROR: ff-addons.php not found at ${DEPLOY_PATH}" >&2
  exit 1
fi

mkdir -p "$(dirname "${WP_PLUGIN_LINK}")"

if [[ -e "${WP_PLUGIN_LINK}" && ! -L "${WP_PLUGIN_LINK}" ]]; then
  TS="$(date +%Y%m%d-%H%M%S)"
  echo "WARN: ${WP_PLUGIN_LINK} exists and is not a symlink — backing up"
  mv "${WP_PLUGIN_LINK}" "${WP_PLUGIN_LINK}.bak-${TS}"
fi

ln -sfn "${DEPLOY_PATH}" "${WP_PLUGIN_LINK}"
echo "Symlink OK: ${WP_PLUGIN_LINK} -> $(readlink "${WP_PLUGIN_LINK}")"

if command -v wp >/dev/null 2>&1 && [[ -d "${WP_ROOT}" ]]; then
  cd "${WP_ROOT}"
  if wp plugin is-active ff-addons >/dev/null 2>&1; then
    echo "==> Plugin already active"
  else
    wp plugin activate ff-addons
    echo "==> Plugin activated"
  fi
else
  echo "WARN: wp-cli not available — activate FF Addons in wp-admin if needed"
fi

echo "==> Deploy complete"
