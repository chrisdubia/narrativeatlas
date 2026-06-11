<?php

interface SayWhatProAutocompleteMatcherInterface {
	public function __construct();
	public function match( $string );
}
