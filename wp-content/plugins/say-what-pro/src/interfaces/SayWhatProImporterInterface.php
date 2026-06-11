<?php

interface SayWhatProImporterInterface {

	public function __construct( SayWhatProSettingsInterface $settings);
	public function import_file( $filename );
}
