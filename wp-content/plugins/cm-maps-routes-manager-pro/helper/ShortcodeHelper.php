<?php

namespace com\cminds\mapsroutesmanager\helper;

class ShortcodeHelper {
	
	public static function filterShortcodes($content, array $whitelist) {
		if (!empty($whitelist) AND is_array($whitelist)) {
			$pattern = get_shortcode_regex();
			if (preg_match_all( '/'. $pattern .'/s', $content, $matches ) AND !empty($matches[2]) AND is_array($matches[2])) {
				foreach ($matches[2] as $shrotcodeName) {
					if (!in_array($shrotcodeName, $whitelist)) {
						$content = str_replace('['. $shrotcodeName, '&lsqb;'. $shrotcodeName, $content);
						$content = str_replace('[/'. $shrotcodeName, '&lsqb;/'. $shrotcodeName, $content);
					}
				}
			}
		}
		return $content;
	}
	
}