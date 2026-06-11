<?php

interface SayWhatProListTableFactoryInterface {
	/**
	 * SayWhatProListTableFactoryInterface constructor.
	 *
	 * @param \Pimple\Container $container
	 */
	public function __construct( \Pimple\Container $container );

	/**
	 * @return SayWhatProWildcardsListTableInterface
	 */
	public function get_wildcard_list_table();

	/**
	 * @return SayWhatProListTableInterface
	 */
	public function get_replacement_list_table();
}
