<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\helper;

class BuddyPressAddonRequirements {

	protected $pluginName;
	protected $pluginFile;
	protected $basePluginName;
	protected $basePluginPurchaseUrl;
	protected $addonClassName;
	protected $checkRequirementsCallback;

	/**
	 * Call immediately after plugin files loaded.
	 *
	 * @param string $pluginFile
	 * @param string $pluginName
	 */
	function __construct($pluginFile, $pluginName, $addonClassName, $checkRequirementsCallback = null, $basePluginName = null, $basePluginPurchaseUrl = null) {

		$this->pluginFile = $pluginFile;
		$this->pluginName = $pluginName;
		$this->basePluginName = $basePluginName;
		$this->basePluginPurchaseUrl = $basePluginPurchaseUrl;
		$this->addonClassName = $addonClassName;
		$this->checkRequirementsCallback = $checkRequirementsCallback;

		add_action('wp_loaded', array($this, 'addon_check'));

	}

	function pluginActivation() {
		if (!$this->addon_check()) {
			return false;
		}
	}

    function addon_check() {
		$errors = $this->getRequirementsErrors();
		if (!empty($errors)) {
			foreach ($errors as $error) {
				add_action('admin_notices', $this->createNoticeFunction($error));
			}
			unset($_GET['activate']);
			deactivate_plugins(plugin_basename($this->pluginFile));
			return false;
		}
		return true;
	}
	
	function getRequirementsErrors() {
		$errors = array();
		
		// Check the base plugin requirement
		if (is_callable($this->checkRequirementsCallback)) {
			if (!call_user_func($this->checkRequirementsCallback, $this)) {
				$basePluginName = $this->basePluginName ?: 'CreativeMinds relevant';
				$basePluginPurchaseUrl = $this->basePluginPurchaseUrl ?: 'https://www.cminds.com/wordpress-plugins/';
				$errors[] = sprintf(__('%s requires <b><a href="%s" target="_blank">%s</a></b> plugin to be installed and activated.
						Since we didn\'t find it the addon has been deactivated.'),
						$this->pluginName, $basePluginPurchaseUrl, $basePluginName);
			}
		}

		return $errors;
	}

	/**
	 * Returns a function that will show admin notice with given message.
	 * 
	 * @param string $msg
	 * @return \Closure
	 */
	function createNoticeFunction($msg) {
		return function() use ($msg) {
			echo '<div class="error fade">
				<p>'. $msg .'</p>
			</div>';
		};
	}

}