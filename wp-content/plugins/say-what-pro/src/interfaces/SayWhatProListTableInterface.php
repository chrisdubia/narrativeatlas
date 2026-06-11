<?php

interface SayWhatProListTableInterface {
	public function __construct(
		SayWhatProSettingsInterface $settings,
		SayWhatProTemplateLoader $template_loader
	);
	public function prepare_items();
	public function display();
}
