# FF Addons — SSH + CI/CD (Hostinger)

Push to `main` runs **CI (PHP lint)** then **SSH deploy** to `facefood.cafe`.

## Paths

| Item | Value |
|------|--------|
| SSH | `ssh hostinger-facefood` (`u875403732@145.79.26.46:65002`) |
| Clone | `/home/u875403732/domains/facefood.cafe/repos/ff-addons` |
| WordPress | `/home/u875403732/domains/facefood.cafe/public_html` |
| Plugin symlink | `.../wp-content/plugins/ff-addons` → clone |

## One-time local SSH

```bash
bash scripts/setup-ssh.sh
ssh hostinger-facefood "echo ok"
```

Public key must be in Hostinger **hPanel → SSH Access → SSH keys** (same `facefood_deploy` key as the main Facefood repo is fine).

## GitHub Secrets

Repo → **Settings → Secrets and variables → Actions**

| Secret | Required | Value |
|--------|----------|--------|
| `SSH_PRIVATE_KEY` | **Yes** | `cat ~/.ssh/facefood_deploy` |
| `SSH_PASSWORD` | Fallback | Hostinger SSH password |
| `DEPLOY_GIT_TOKEN` | Optional | PAT if the repo is ever made private |

Repo is public — `git pull` on the server does not need a token.

## First deploy on server

From your PC (recommended — GitHub cloud runners often cannot open Hostinger `:65002`):

```bash
ssh hostinger-facefood "mkdir -p /home/u875403732/domains/facefood.cafe/repos && \
  git clone https://github.com/imranniaz-st/ff-addons.git /home/u875403732/domains/facefood.cafe/repos/ff-addons && \
  bash /home/u875403732/domains/facefood.cafe/repos/ff-addons/scripts/deploy-hostinger.sh"
```

Then activate **FF Addons** in wp-admin if `wp` CLI is missing.

## Manual redeploy

```bash
ssh hostinger-facefood
export DEPLOY_PATH=/home/u875403732/domains/facefood.cafe/repos/ff-addons
bash "${DEPLOY_PATH}/scripts/server-git-pull.sh"
bash "${DEPLOY_PATH}/scripts/deploy-hostinger.sh"
```

## Note on GitHub Actions SSH

If Actions fails with `dial tcp …:65002: i/o timeout`, run the manual deploy above from your PC (same as Facefood backend). CI lint still runs on every push.
