<?php

interface SayWhatProSettingsInterface {
	public function __construct( $plugin_path );
	public function run();
	public function insert_replacement( $orig_string, $domain, $context, $replacement_string, $disabled, $lang = '');
	public function update_replacement( $replacement_id, $orig_string, $domain, $context, $replacement_string, $disabled, $lang = '');
	public function get_flattened_replacements();
	public function get_available_string_hashes();
	public function invalidate_caches();
}
