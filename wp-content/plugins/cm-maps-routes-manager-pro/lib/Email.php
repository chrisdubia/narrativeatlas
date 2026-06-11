<?php

namespace com\cminds\mapsroutesmanager\lib;
use com\cminds\mapsroutesmanager\model\Settings;

class Email {
	
	static function send($receivers, $subject, $body, array $vars = array(), array $headers = array()) {
		
		$hasReceivers = false;
		if (!is_array($receivers)) {
			$mailTo = $receivers;
			$hasReceivers = true;
		} else {
			$mailTo = null;
			foreach ($receivers as $email) {
				$email = trim($email);
				if (is_email($email)) {
					$headers[] = ' Bcc: '. $email;
					$hasReceivers = true;
				}
			}
		}
		
		if ($hasReceivers) {
			if (is_null($mailTo)) $mailTo = Settings::getOption(Settings::OPTION_EMAIL_TO_HEADER_WHEN_USING_BCC);
			$headers = apply_filters('cmmrm_email_headers', $headers, $mailTo, $subject, $body, $vars);
			$result = wp_mail($mailTo, strtr($subject, $vars), strtr($body, $vars), $headers);
// 			error_log(print_r(compact('mailTo', 'subject', 'vars', 'headers', 'body', 'result'), true));
			return $result;
		} else {
			return false;
		}
		
	}
	
	
}
