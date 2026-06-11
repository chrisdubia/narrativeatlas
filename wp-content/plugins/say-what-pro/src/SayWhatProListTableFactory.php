<?php

class SayWhatProListTableFactory implements SayWhatProListTableFactoryInterface {
	/**
	 * @var \Pimple\Container
	 */
	private $container;

	/**
	 * SayWhatProListTableFactory constructor.
	 *
	 * @param \Pimple\Container $container
	 */
	public function __construct( \Pimple\Container $container ) {
		$this->container = $container;
	}

	public function get_wildcard_list_table() {
		return $this->container['SayWhatProWildcardsListTable'];
	}

	public function get_replacement_list_table() {
		return $this->container['SayWhatProListTable'];
	}
}
