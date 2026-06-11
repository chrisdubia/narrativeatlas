<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\helper;

class HtmlHelper {
	
	static function renderRadioGroup($name, array $options, $currentValue) {
		$out = '';
		foreach ($options as $value => $label) {
			$out .= sprintf('<label><input type="radio" name="%s" value="%s"%s /> %s</label>',
				esc_attr($name),
				esc_attr($value),
				checked($value, $currentValue, false),
				esc_html($label)
			);
		}
		return $out;
	}
	
	static function renderBooleanRadio($name, $currentValue, $notSetOption = false) {
		$options = array(
			1 => 'Yes',
			0 => 'No',
		);
		if ($notSetOption) {
			$options = array('NULL' => 'Do not set') + $options;
			$currentValue = (is_null($currentValue) ? 'NULL' : intval($currentValue));
		} else {
			$currentValue = intval($currentValue);
		}
		return static::renderRadioGroup($name, $options, $currentValue);
	}
	
	static function renderSelect($name, array $options, $currentValue) {
		$out = sprintf('<select name="%s">', esc_attr($name));
		foreach ($options as $value => $label) {
			$out .= sprintf('<option value="%s"%s>%s</option>',
				esc_attr($value),
				selected($value, $currentValue, false),
				esc_html($label)
			);
		}
		$out .= '</select>';
		return $out;
	}
	
	static function renderBooleanSelect($name, $currentValue, $notSetOption = false) {
		$options = array(
			1 => 'Yes',
			0 => 'No',
		);
		if ($notSetOption) {
			$options = array('NULL' => 'Do not set') + $options;
			$currentValue = (is_null($currentValue) ? 'NULL' : intval($currentValue));
		} else {
			$currentValue = intval($currentValue);
		}
		return static::renderSelect($name, $options, $currentValue);
	}
	
}