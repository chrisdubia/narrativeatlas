#!/usr/bin/env bash
set -euo pipefail

if ! command -v wp >/dev/null 2>&1; then
  echo "wp not found in PATH.  Run from the server where wp-cli is installed."
  exit 1
fi

echo "WP audit report"
echo "Generated: $(date)"
echo

WP_PATH="$(pwd)"
echo "Path: $WP_PATH"
echo

echo "WordPress core"
wp core version
wp core check-update || true
echo

echo "PHP"
php -v | head -n 2 || true
echo

echo "Plugins (active first)"
wp plugin list --status=active
echo
wp plugin list --status=inactive
echo

echo "Themes"
wp theme list
echo

echo "Cron"
wp cron event list --fields=hook,next_run,recurrence --format=table | head -n 40 || true
echo

echo "Database size (top tables)"
wp db query "
SELECT table_name,
       ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
ORDER BY (data_length + index_length) DESC
LIMIT 25;"
echo

prefix=$(wp config get table_prefix)
echo "Table prefix: $prefix"
echo

echo "Autoloaded options size (big cause of slow admin)"
wp db query "
SELECT ROUND(SUM(LENGTH(option_value)) / 1024 / 1024, 2) AS autoload_mb
FROM ${prefix}options
WHERE autoload = 'yes';"
echo

echo "Top autoloaded options (largest 30)"
wp db query "
SELECT option_name,
       ROUND(LENGTH(option_value)/1024, 1) AS kb
FROM ${prefix}options
WHERE autoload = 'yes'
ORDER BY LENGTH(option_value) DESC
LIMIT 30;"
echo

echo "Post revisions count"
wp db query "
SELECT COUNT(*) AS revisions
FROM ${prefix}posts
WHERE post_type = 'revision';"
echo

echo "Trash posts count"
wp db query "
SELECT COUNT(*) AS trash_posts
FROM ${prefix}posts
WHERE post_status = 'trash';"
echo

echo "Spam and trash comments"
wp db query "
SELECT
  SUM(comment_approved='spam') AS spam_comments,
  SUM(comment_approved='trash') AS trash_comments
FROM ${prefix}comments;"
echo

echo "Expired transients count"
wp db query "
SELECT COUNT(*) AS expired_transients
FROM ${prefix}options
WHERE option_name LIKE '\_transient\_timeout\_%'
AND option_value < UNIX_TIMESTAMP();"
echo

echo "Orphaned postmeta (safe indicator of bloat)"
wp db query "
SELECT COUNT(*) AS orphaned_postmeta
FROM ${prefix}postmeta pm
LEFT JOIN ${prefix}posts p ON p.ID = pm.post_id
WHERE p.ID IS NULL;"
echo

echo "BuddyPress or BuddyBoss notifications and messages table sizes (if present)"
wp db query "SHOW TABLES LIKE '${prefix}bp_%';" || true
echo

wp db query "
SELECT table_name,
       ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
AND table_name IN (
  CONCAT('${prefix}','bp_notifications'),
  CONCAT('${prefix}','bp_messages_messages'),
  CONCAT('${prefix}','bp_messages_recipients'),
  CONCAT('${prefix}','bp_messages_meta')
)
ORDER BY (data_length + index_length) DESC;" || true
echo

echo "BuddyPress notifications count (if table exists)"
wp db query "
SELECT COUNT(*) AS bp_notifications_rows
FROM ${prefix}bp_notifications;" || true
echo

echo "BuddyPress messages counts (if tables exist)"
wp db query "
SELECT COUNT(*) AS messages_rows
FROM ${prefix}bp_messages_messages;" || true
wp db query "
SELECT COUNT(*) AS recipients_rows
FROM ${prefix}bp_messages_recipients;" || true
echo

echo "Done."
