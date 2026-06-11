<?php

namespace com\cminds\mapsroutesmanager\addon\customfields\helper;

class RateGradeHelper {
	
	const MAX_GRADE = 5;
	
	
	static function getFrontend($field, $currentGrade = 0) {
		wp_enqueue_style('cmmrmcf-frontend');
		return RateGradeHelper::wrapRateGradeItems(
				RateGradeHelper::getRateGradeItems($field, $currentGrade),
				$field, $currentGrade);
	}
	
	
	static function getEditorsField($nameTemplate, $field, $currentGrade = 0) {
		wp_enqueue_style('cmmrmcf-frontend');
		return RateGradeHelper::wrapRateGradeItems(
				RateGradeHelper::getRateGradeItems($field, $currentGrade) .
				RateGradeHelper::getEditorsInputHidden($nameTemplate, $field, $currentGrade),
			$field, $currentGrade);
	}
	
	
	static protected function getRateGradeItems($field, $currentGrade = 0) {
		$out = '';
		for ($i=0; $i<static::MAX_GRADE; $i++){
			if ($i+1 <= $currentGrade) {
				$class = 'cmmrm-marked';
			} else {
				$class = '';
			}
			$iconClass = static::getIconClass($field);
			$out .= sprintf('<div class="cmmrm-custom-field-grade-scale-item %s" data-grade="%d"><span class="icon %s"></span></div>', $class, $i+1, $iconClass);
		}
		$out .= '<div class="cmmrm-custom-field-grade-scale-value"><span>'. intval($currentGrade) .'</span>/5</div>';
		return $out;
	}
	
	
	static function getFieldLabel($field) {
		$label = explode('|', $field['label']);
		return reset($label);
	}
	
	
	static function getIconClass($field) {
		$label = explode('|', $field['label']);
		if (count($label) > 1) {
			return $label[1];
		} else {
			return 'dashicons dashicons-star-filled';
		}
	}
	
	
	static protected function wrapRateGradeItems($content, $field, $currentGrade = 0) {
		return sprintf('<div class="cmmrm-custom-field-grade-scale" data-max-grade="%d" data-meta-key="%s" data-current-grade="%d">%s</div>',
				static::MAX_GRADE, esc_attr($field['meta_key']), esc_attr($currentGrade), $content);
	}
	
	
	
	static protected function getEditorsInputHidden($nameTemplate, $field, $currentGrade = 0) {
		$name = sprintf($nameTemplate, $field['meta_key']);
		return sprintf('<input type="hidden" name="%s" value="%s" />', esc_attr($name), esc_attr($currentGrade));
	}
	
	
}