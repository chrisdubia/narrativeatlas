<?php

class SayWhatProAutocompleteMatcher implements SayWhatProAutocompleteMatcherInterface {

	/**
	 * SayWhatProAutocompleteMatcher constructor.
	 */
	public function __construct() {
	}

	public function match( $string ) {
		global $wpdb, $table_prefix;
		$sql         = "SELECT orig_string, domain, context, translated_string
                          FROM {$table_prefix}say_what_available_strings
                         WHERE orig_string_lc LIKE %s
                            OR translated_string_lc LIKE %s
                      ORDER BY LENGTH(orig_string),
                               orig_string,
                               domain,
                               context,
                               translated_string";
		$results     = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				'%' . mb_strtolower( $string ) . '%',
				'%' . mb_strtolower( $string ) . '%'
			)
		);
		$suggestions = array();
		foreach ( $results as $result ) {
			$suggestion = (array) $result;
			$label      = '"' . $suggestion['orig_string'] . '"';
			if ( ! empty( $suggestion['translated_string'] ) && $suggestion['translated_string'] !== $suggestion['orig_string'] ) {
				$label = '"' . $suggestion['translated_string'] . '" (Translated. Original string: ' . $label . ')';
				// Translators: %s is the translated version of the string.
				$suggestion['translated_string'] = sprintf(
					esc_html__( 'In your language:%1$s&quot;%2$s&quot;%3$s', 'say_what' ),
					'<br><span>',
					esc_html( $suggestion['translated_string'] ),
					'</span>'
				);
			} else {
				unset( $suggestion['translated_string'] );
			}
			if ( 'default' !== $suggestion['domain'] ) {
				$label .= sprintf(
					' from %s',
					$suggestion['domain']
				);
			}
			if ( 'sw-default-context' !== $suggestion['context'] ) {
				$label .= ' (Context: ' . $suggestion['context'] . ')';
			}
			$suggestion['label'] = $label;
			$suggestions[]       = $suggestion;
		}
		// Special responses if there are no matches.
		if ( count( $suggestions ) === 0 ) {
			$sql     = "SELECT COUNT(*) FROM ${table_prefix}say_what_available_strings";
			$results = $wpdb->get_var( $sql );
			if ( $results > 0 ) {
				$suggestions[] = [
					'orig_string' => 'SWP_NO_MATCHES',
					'domain'      => '',
					'context'     => '',
					'label'       => esc_html__( 'No matches found', 'say_what' ),
				];
			} else {
				$suggestions[] = [
					'orig_string' => 'SWP_NO_SUGGESTIONS',
					'domain'      => '',
					'context'     => '',
					'label'       => esc_html__( 'No suggestions available. Try String discovery', 'say_what' ),
				];
			}
		}

		return $suggestions;
	}
}
