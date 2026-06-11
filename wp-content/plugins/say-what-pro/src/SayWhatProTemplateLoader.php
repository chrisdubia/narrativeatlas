<?php

/**
 * Renders templates with a given set of variables, and returns the content.
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class SayWhatProTemplateLoader extends Gamajo_Template_Loader {

	/**
	 * Prefix for filter names.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $filter_prefix = 'say_what_pro';

	/**
	 * Directory name where custom templates for this plugin should be found in the theme.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $theme_template_directory = 'say-what-pro';

	/**
	 * Reference to the root directory path of this plugin.
	 *
	 * Can either be a defined constant, or a relative reference from where the subclass lives.
	 *
	 * @since 1.0.0
	 *
	 * @type string
	 */
	protected $plugin_directory = ''; // Set in constructor

	/**
	 * Constructor. Stores needed config.
	 */
	public function __construct() {
		$this->plugin_directory = dirname( dirname( __FILE__ ) );
	}

	/**
	 * Get the contents of a template with variables substituted.
	 *
	 * @param  string $slug      The template slug (First part of filename)
	 * @param  string $name      The template name (Second half of filename)
	 * @param  array  $variables Variables to be replaced into the template.
	 *
	 * @return string             The rendered output.
	 */
	public function get_with_vars( $slug = '', $name = null, $variables = [] ) {
		ob_start();
		$this->get_template_part( $slug, $name );
		$content = ob_get_clean();
		foreach ( $variables as $key => $value ) {
			$content = str_replace( '{' . $key . '}', $value, $content );
		}
		return $content;
	}

	/**
	 * Output the contents of a template with variables substituted.
	 *
	 * @param  string $slug      The template slug (First part of filename)
	 * @param  string $name      The template name (Second half of filename)
	 * @param  array  $variables Variables to be replaced into the template.
	 *
	 * @uses   get_template_with_variables()
	 *
	 * @return string             The rendered output.
	 */
	public function output_with_vars( $slug = '', $name = null, $variables = [] ) {
		echo $this->get_with_vars( $slug, $name, $variables );
	}

	/**
	 * Output the contents of a template without any variables.
	 *
	 * @param $slug
	 * @param $name
	 */
	public function output( $slug, $name ) {
		$this->output_with_vars( $slug, $name, [] );
	}

	/**
	 * @param $slug
	 * @param $name
	 *
	 * @return string
	 */
	public function get( $slug, $name ) {
		return $this->get_with_vars( $slug, $name, [] );
	}
}
