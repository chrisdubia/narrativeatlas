<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

class BP_MEC_Integration extends BP_Integration {

	/**
	 * BP_MEC_Integration constructor.
	 */
	public function __construct() {
		$this->start(
			'mec',
			__('MEC', 'mec-buddyboss'),
			'mec',
			array(
				'required_plugin' => array(),
			)
		);
	}

	/**
	 * Register template path for BP.
	 *
	 * @since 1.0.0
	 * @return string template path
	 */
	public function register_template() {
		return MECBUDDYBOSSDIR . DS . 'core' . DS . 'lib' . DS . 'templates/' . trim($path, '/\\');
	}

	/**
	 * Register mec setting tab
	 *
	 * @since 1.0.0
	 */
	public function setup_admin_integration_tab() {

		require_once MECBUDDYBOSSDIR . DS . 'core' . DS . 'lib' . DS . '/admin.php';

		$base_url = '';
		if (function_exists('bb_platform_pro')) {
			$base_url = trailingslashit(bb_platform_pro()->integration_dir) . $this->id;
		}

		new BP_MEC_Admin_Integration_Tab(
			"bp-{$this->id}",
			$this->name,
			array(
				'root_path' => $base_url,
				'root_url' => $base_url,
				'required_plugin' => $this->required_plugin,
			)
		);
	}
}
