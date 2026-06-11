<?php

namespace com\cminds\mapsroutesmanager\addon\buddypress\helper;

use com\cminds\mapsroutesmanager\addon\buddypress\App;

class FormHtml {
	
	static function checkboxTree($fieldName, array $current, array $values, $parentId = 0) {
		$output = '';
		if (!empty($values[$parentId])) {
			$output .= '<ul class="'. App::prefix('-form-checkbox-tree') .'">';
			foreach ($values[$parentId] as $id => $label) {
				$output .= sprintf('<li><label><input type="checkbox" name="%s" value="%s"%s /><span>%s</span></label>%s</li>',
					$fieldName,
					esc_attr($id),
					checked(in_array($id, $current), true, false),
					esc_html($label),
					static::checkboxTree($fieldName, $current, $values, $id)
				);
			}
			$output .= '</ul>';
		}
		return $output;
	}
	
	static function selectBox($fieldName, array $options, $currentValue, $args = array()) {
		$content = '';
        $desc = '';
		foreach ($options as $value => $label) {
			$content .= sprintf('<option value="%s"%s>%s</option>',
				esc_attr($value),
				selected($value, $currentValue, false),
				esc_html($label)
			);
		}
        if ( isset($args['description']) ) {
            $desc .= sprintf('<br/><span class="description">%s</span>',
                $args['description']
            );
        }
		return sprintf('<select name="%s" id="%s">%s</select>%s',
			esc_attr($fieldName),
			esc_attr($fieldName),
			$content,
            $desc
		);
	}
	
	static function renderCheckboxGroup($name, array $options, array $currentValue) {
        $out = '';
        foreach ($options as $option) {
			$value = $option['id'];
			$creator_id = $option['creator_id'];
			$label = $option['name'];
			$slug = $option['slug'];
			$description = $option['description'];
			if(in_array($value, $currentValue)) {
				$out .= sprintf('<div class="cmmrm_field_categories_row"><input type="checkbox" name="%s" id="%s" value="%s" checked="checked" /><span>%s</span></div>',
					esc_attr($name),
					esc_attr(str_replace('[]', '', $name).'_'.$value),
					esc_attr($value),
					esc_html($label)
				);
			} else {
				$out .= sprintf('<div class="cmmrm_field_categories_row"><input type="checkbox" name="%s" id="%s" value="%s" /><span>%s</span></div>',
					esc_attr($name),
					esc_attr(str_replace('[]', '', $name).'_'.$value),
					esc_attr($value),
					esc_html($label)
				);
			}
        }
        return $out;
    }

    static function renderRadioGroup($name, array $options, $currentValue, $args = array()) {
        $out = '';
        foreach ($options as $value => $label) {
            $out .= sprintf('<label><input type="radio" name="%s" id="%s" value="%s" %s />%s</label><br/>',
                esc_attr($name),
                esc_attr($name),
                esc_attr($value),
                checked($value, $currentValue, false),
                esc_html($label)
            );
        }
        if ( !empty($out) && isset($args['description']) ) {
            $out .= sprintf('<span class="description">%s</span>',
                esc_attr($args['description'])
            );
        }
        return $out;
    }

    static function renderBooleanRadio($name, $currentValue, $notSetOption = false, $args = array()) {
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
        return static::renderRadioGroup($name, $options, $currentValue, $args);
    }

    static function renderTextArea($name, $currentValue, $args = array()) {
        $out = '';
        $out .= sprintf('<textarea rows="8" name="%s" id="%s">%s</textarea>',
            esc_attr($name),
            esc_attr($name),
            esc_attr($currentValue)
        );
        if ( !empty($out) && isset($args['description']) ) {
            $out .= sprintf('<br/><span class="description">%s</span>',
                $args['description']
            );
        }
        return $out;
    }
	
}
