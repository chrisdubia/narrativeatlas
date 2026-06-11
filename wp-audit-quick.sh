#!/usr/bin/env bash
set -euo pipefail

echo "WP quick audit"
echo "Generated: $(date)"
echo

prefix=$(wp config get table_prefix)
echo "Table prefix: $prefix"
echo

echo "Core / PHP"
wp core version
php -v | head -n 2 || true
echo

echo "Plugins (active)"
wp plugin list --status=active
echo

echo "Cron (first 40)"
wp cron event list --fields=hook,next_run,recurrence --format=table | head -n 40 || true
echo

echo "Top tables by size (top 25)"
wp db query "
SELECT table_name,
       ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
ORDER BY (data_length + index_length) DESC
LIMIT 25;"
echo

echo "BuddyBoss messages / notifications sizes and counts"
wp db query "
SELECT table_name,
       ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
AND table_name IN (
  CONCAT('${prefix}','bp_notifications'),
  CONCAT('${prefix}','bp_notifications_meta'),
  CONCAT('${prefix}','bp_messages_messages'),
  CONCAT('${prefix}','bp_messages_recipients'),
  CONCAT('${prefix}','bp_messages_meta'),
  CONCAT('${prefix}','bp_messages_notices')
)
ORDER BY (data_length + index_length) DESC;" || true
echo

wp db query "SELECT COUNT(*) AS bp_notifications_rows FROM ${prefix}bp_notifications;" || true
wp db query "SELECT COUNT(*) AS notifications_meta_rows FROM ${prefix}bp_notifications_meta;" || true
wp db query "SELECT COUNT(*) AS messages_rows FROM ${prefix}bp_messages_messages;" || true
wp db query "SELECT COUNT(*) AS recipients_rows FROM ${prefix}bp_messages_recipients;" || true
wp db query "SELECT COUNT(*) AS messages_meta_rows FROM ${prefix}bp_messages_meta;" || true
wp db query "SELECT COUNT(*) AS notices_rows FROM ${prefix}bp_messages_notices;" || true
echo

echo "Orphans"
wp db query "SELECT COUNT(*) AS orphaned_postmeta
FROM ${prefix}postmeta pm
LEFT JOIN ${prefix}posts p ON p.ID = pm.post_id
WHERE p.ID IS NULL;"
wp db query "SELECT COUNT(*) AS orphaned_notification_meta
FROM ${prefix}bp_notifications_meta m
LEFT JOIN ${prefix}bp_notifications n ON n.id = m.notification_id
WHERE n.id IS NULL;" || true
wp db query "SELECT COUNT(*) AS expired_transients
FROM ${prefix}options
WHERE option_name LIKE '\_transient\_timeout\_%'
AND option_value < UNIX_TIMESTAMP();"
echo

echo "Autoload size"
wp db query "
SELECT ROUND(SUM(LENGTH(option_value)) / 1024 / 1024, 2) AS autoload_mb
FROM ${prefix}options
WHERE autoload = 'yes';"
echo

echo "Done."
