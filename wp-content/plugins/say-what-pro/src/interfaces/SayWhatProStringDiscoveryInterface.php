<?php

interface SayWhatProStringDiscoveryInterface {
	public function __construct( SayWhatProSettingsInterface $settings );
	public function run();
	public function enable();
	public function disable();
	public function is_active();
	public function maybe_log_available_replacement( $original, $domain, $context, $translated_string );
}
