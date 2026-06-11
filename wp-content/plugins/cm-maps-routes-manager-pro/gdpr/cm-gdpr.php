<?php
use com\cminds\mapsroutesmanager\model\Settings;
if(!class_exists('CMMRMGdpr')) {
    class CMMRMGdpr {
        protected static $instance = NULL;
        protected $config          = NULL;

        public function __construct($config) {
			$this->config = $config;
			// admin settings
			if(!empty($this->config['filters_category']))
			{
				add_filter($this->config['filters_category'], array($this, 'addCategory'));
			}
			add_filter($this->config['filters_settings_subcategory'], array($this, 'addSubcategory'));
			add_filter($this->config['filters_settings'], array( $this, 'addSettings'));
			// frontend filter
			add_filter($this->config['filters_frontend_name'], array($this, 'addDisclaimer'));
        }

		public function addCategory($tabs) {
            $tabs['cm-gdpr'] = 'GDPR Settings';
            return $tabs;
        }

        public function addSubcategory($tabs) {
            $tabs['cm-gdpr']['section'] = 'General Settings';
            return $tabs;
        }

        public function addSettings( $settings ) {
            $settings[$this->config['prefix'].'_gdpr_disclaimer_enabled'] = array(
                'type'        => 'bool',
                'default'     => true,
                'category'    => 'cm-gdpr',
                'subcategory' => 'section',
                'title'       => 'Show disclaimer for first time users?',
                'desc'        => 'If enabled, users that don\'t agree with the terms won\'t be able to post.',
            );
			$settings[$this->config['prefix'].'_gdpr_disclaimer_text'] = array(
                'type'        => 'rich_text',
                'default'     => 'Enter here your disclaimer text',
                'category'    => 'cm-gdpr',
                'subcategory' => 'section',
                'title'       => 'Disclaimer text',
                'desc'        => 'This message will be shown for first time users. You can add HTML tags here to add rich formatting and links.',
            );
            return $settings;
        }

		public static function addDisclaimer() {
			if(Settings::getOption('cmmrm_gdpr_disclaimer_enabled')) {

				wp_register_style('cmmrm_gdpr_disclaimer_css', plugin_dir_url(__FILE__).'assets/css/cm-gdpr.css' );
				wp_enqueue_style('cmmrm_gdpr_disclaimer_css');

				wp_register_script('cmmrm_gdpr_disclaimer_js', plugin_dir_url(__FILE__).'assets/js/cm-gdpr.js');
				wp_localize_script('cmmrm_gdpr_disclaimer_js', 'cmmrm_disclaimer_opts', array(
					'content'		=> Settings::getOption('cmmrm_gdpr_disclaimer_text'),
					'rejecturl'		=> get_bloginfo('url'),
					'acceptText'	=> 'Accept',
					'rejectText'	=> 'Reject'
				));
				wp_enqueue_script('cmmrm_gdpr_disclaimer_js');
			}
		}
        
    }
}