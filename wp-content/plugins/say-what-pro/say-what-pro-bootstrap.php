<?php

/**
 * This code is in a separate file so that non-PHP 5.2 compat code doesn't
 * cause a WSOD when the main plugin file is included.
 */

global $say_what_pro_di;

// Configure the DI container.
$say_what_pro_di = new Pimple\Container();

$say_what_pro_di['SayWhatPro'] = function ( $c ) {
	return new SayWhatPro( $c );
};

$say_what_pro_di['SayWhatProSettings'] = function ( $c ) {
	$s = new SayWhatProSettings( __DIR__ );
	$s->run();

	return $s;
};

$say_what_pro_di['SayWhatProDbManager'] = function ( $c ) {
	return new SayWhatProDbManager();
};

$say_what_pro_di['SayWhatProStringDiscovery'] = function ( $c ) {
	return new SayWhatProStringDiscovery( $c['SayWhatProSettings'] );
};

$say_what_pro_di['SayWhatProAutocompleteMatcher'] = function ( $c ) {
	return new SayWhatProAutocompleteMatcher();
};

$say_what_pro_di['SayWhatProFrontend'] = function ( $c ) {
	return new SayWhatProFrontend( $c['SayWhatProSettings'], $c['SayWhatProStringDiscovery'] );
};

$say_what_pro_di['SayWhatProListTable'] = function ( $c ) {
	require_once ABSPATH . 'wp-admin/includes/translation-install.php';

	return new SayWhatProListTable( $c['SayWhatProSettings'], $c['SayWhatProTemplateLoader'] );
};

$say_what_pro_di['SayWhatProListTableFactory'] = function ( $c ) {
	return new SayWhatProListTableFactory( $c );
};

$say_what_pro_di['SayWhatProWildcardsListTable'] = function ( $c ) {
	require_once ABSPATH . 'wp-admin/includes/translation-install.php';

	return new SayWhatProWildcardsListTable( $c['SayWhatProSettings'], $c['SayWhatProTemplateLoader'] );
};

$say_what_pro_di['SayWhatProMultiLingualAdmin'] = function ( $c ) {
	return new SayWhatProMultiLingualAdmin();
};

$say_what_pro_di['SayWhatProImporter']       = function ( $c ) {
	return new SayWhatProImporter( $c['SayWhatProSettings'] );
};
$say_what_pro_di['SayWhatProTemplateLoader'] = function ( $c ) {
	return new SayWhatProTemplateLoader();
};
$say_what_pro_di['SayWhatProAdmin']          = function ( $c ) {
	return new SayWhatProAdmin(
		$c['SayWhatProSettings'],
		$c['SayWhatProStringDiscovery'],
		$c['SayWhatProAutocompleteMatcher'],
		$c['SayWhatProListTableFactory'],
		$c['SayWhatProImporter'],
		$c['SayWhatProTemplateLoader']
	);
};


// Run the plugin.
global $say_what_pro;
$say_what_pro = $say_what_pro_di['SayWhatPro'];
$say_what_pro->run();
