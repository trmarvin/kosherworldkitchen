# kosherworldkitchen

Totally custom WP build for my family's hobby recipe site

## Release Runbook — SFTP (Code-Only, No DB Changes)

### Scope

Deploy only custom code to production:

wp-content/themes/kwk-custom/
wp-content/plugins/kwk-\*/ (custom plugins only)
wp-content/mu-plugins/ (if you use small MU utilities)

Never deploy: WordPress core, uploads, wp-config.php, DB.

### Prereqs

Local changes committed to main.
Built assets committed (if you have a build step).
SFTP credentials for SiteGround.
(Optional) SSH/WP-CLI on prod for quick cache/permalink flushes.

Preflight (local)

Pull latest: git pull --rebase origin main
Build assets (if any): npm run build (commit build output)

Tag the release (optional):
git tag -a vYYYY.MM.DD -m "KWK release"
git push --tags

Backup on prod (once): keep a copy of current kwk-custom/ (and any kwk-\* plugins).

Maintenance (optional but recommended)
Enable maintenance during upload:
SSH/WP-CLI (if available):
wp maintenance-mode activate

Or create .maintenance in WP root with:

<?php $upgrading = time();

Remove it after deploy.

### Upload (SFTP)

Connect via SFTP to the prod server and upload only:

Theme: wp-content/themes/kwk-custom/ (overwrite changed files)
Custom plugins: wp-content/plugins/kwk-*/ (overwrite changed files)
MU plugins (if any): wp-content/mu-plugins/

Tip: upload the theme last if the update needs templates and functions to land together.

### Post-deploy (prod)
If you have SSH/WP-CLI:
> from the WordPress docroot
wp cache flush
wp rewrite flush --hard
wp maintenance-mode deactivate

(If you use a caching plugin, also purge it via its UI or plugin-specific WP-CLI command; then clear SiteGround’s cache.)

If you don’t have SSH:
wp-admin → Settings → Permalinks → Save (flushes rewrites)
Clear plugin cache + SiteGround cache via their UIs
Disable maintenance (delete .maintenance or toggle off the plugin)

### Smoke Test (prod)

Homepage, a single post/recipe, category listing
Header/footer/nav, search, pagination
Forms (contact/newsletter) send successfully
Logged-in admin area loads; no PHP notices in error log

### Rollback

If something looks off:

Re-upload the previous backup of kwk-custom/ (and custom plugins if needed), or:
git revert the offending commit locally → re-build if needed → SFTP upload again.

#### Notes:

No DB URL changes are needed because prod keeps its own DB.
Local/staging DB manipulations are for testing only; do not import them into prod.
Keep /uploads out of Git and out of deploys; content lives on prod.
