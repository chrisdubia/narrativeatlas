<?php
/**
 * Helper Functions.
 *
 * @package    BuddyPress Dynamic Group Tab Content
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Get the post type for Dynamic Group Tab content.
 *
 * @return string
 */
function bp_dynamic_group_tab_content_get_post_type() {
	return 'bpdgtc_content';
}

/**
 * Get the content type taxonomy name.
 *
 * @return string
 */
function bp_dynamic_group_tab_content_get_taxonomy() {
	return 'bpdgtc_content_type';
}
