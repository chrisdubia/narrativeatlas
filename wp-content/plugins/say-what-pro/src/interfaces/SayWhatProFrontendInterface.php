<?php

interface SayWhatProFrontendInterface {
	public function __construct( SayWhatProSettingsInterface $settings, SayWhatProStringDiscoveryInterface $string_discovery );
	public function run();
}
