<?php

interface SayWhatProWildcardsListTableInterface {
	public function __construct( SayWhatProSettingsInterface $settings );
	public function prepare_items();
	public function display();
}
