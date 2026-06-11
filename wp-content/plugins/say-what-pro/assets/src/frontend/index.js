import * as md5 from 'md5';
import '@wordpress/hooks';

// Initialise
const sayWhatPro = {
	discoveryPayload: [],
	discoveryActive: window.swp_data.discovery > 0,
	// Filter callbacks for each translation call.
	// Uses handle() to do the heavy lifting.
	gettext( translation, text, domain ) {
		return sayWhatPro.handle(
			translation,
			text,
			text,
			undefined,
			undefined,
			domain
		);
	},
	gettext_with_context( translation, text, context, domain ) {
		return sayWhatPro.handle(
			translation,
			text,
			text,
			undefined,
			context,
			domain
		);
	},
	ngettext( translation, single, plural, number, domain ) {
		return sayWhatPro.handle(
			translation,
			single,
			plural,
			number,
			undefined,
			domain
		);
	},
	ngettext_with_context(
		translation,
		single,
		plural,
		number,
		context,
		domain
	) {
		return sayWhatPro.handle(
			translation,
			single,
			plural,
			number,
			context,
			domain
		);
	},
	has_translation( result, single, context, domain ) {
		const swpHasReplacement =
			sayWhatPro.handle(
				single,
				single,
				single,
				undefined,
				context,
				domain
			) !== single;
		return result || swpHasReplacement;
	},
	/**
	 * Handle a call to a translation function.
	 *
	 * Looks for a replacement and filters the return value if required. If String Discovery is active
	 * also check whether this is a known available string or not, and queues discovery if not.
	 *
	 * @param {string} translation        The translated string.
	 * @param {string} single             Text to translate if non-plural.
	 * @param {string} plural             Text to translate if plural.
	 * @param {string|undefined} number   The number used to decide on plural/non-plural.
	 * @param {string|undefined} context  Context information for the translators.
	 * @param {string} domain             Domain to retrieve the translated text.
	 */
	handle( translation, single, plural, number, context, domain ) {
		// Adjust inputs to expected format.
		if ( typeof domain === 'undefined' ) {
			domain = '';
		}
		if ( typeof context === 'undefined' ) {
			context = '';
		}

		// Assume we're using plural for now.
		let compositeKey = domain + '|' + plural + '|' + context + '|';
		if ( this.discoveryActive ) {
			this.discover( plural, domain, context, translation );
		}
		// Revert to the single form if required.
		if ( number === undefined || number === 1 ) {
			compositeKey = domain + '|' + single + '|' + context + '|';
			if ( this.discoveryActive && single !== plural ) {
				this.discover( single, domain, context, translation );
			}
		}

		/**
		 * Look for replacements, and use them if configured.
		 */

		// Look for a language-specific replacement first.
		if ( typeof window.swp_data.replacements[ compositeKey + window.swp_data.lang ] !== 'undefined' ) {
			return window.swp_data.replacements[ compositeKey + window.swp_data.lang ];
		}

		// If not found, look for an "all languages" replacement.
		if ( typeof window.swp_data.replacements[ compositeKey ] !== 'undefined' ) {
			return window.swp_data.replacements[ compositeKey ];
		}

		// No replacement. Return the value unchanged.
		return translation;
	},
	/**
	 * Check whether a string being processed is "known" and log it for discovery if not.
	 *
	 * @param {string} string       Text to translate if non-plural.
	 * @param {string} domain       Domain to retrieve the translated text.
	 * @param {string} context      Context information for the translators.
	 * @param {string} translation  The translated string.
	 */
	discover( string, domain, context, translation ) {
		// Check if we already know about it
		if ( domain === '' ) {
			domain = 'default';
		}
		if ( context === '' ) {
			context = 'sw-default-context';
		}
		const hash = md5(
			string + '|' + domain + '|' + context + '|' + translation
		).substring( 0, 16 );
		if ( ! window.swp_data.available.includes( hash ) ) {
			// Add to the internal array (swp_data.available) so we don't queue it again.
			window.swp_data.available.push( hash );
			// Note it for sending to the backend.
			this.discoveryPayload.push( [
											string,
											domain,
											context,
											translation,
										] );
		}
	},
	saveDiscoveries() {
		// If nothing to do, queue up a check in 10 seconds time.
		if ( ! sayWhatPro.discoveryPayload.length ) {
			setTimeout( sayWhatPro.saveDiscoveries, 10000 );
			return;
		}

		// Bite off a chunk and send it to be stored via AJAX.
		const payload = sayWhatPro.discoveryPayload.splice( 0, 50 );
		jQuery.ajax( {
						 method: 'POST',
						 url: window.swp_data.discovery_endpoint,
						 data: {
							 payload,
							 _wpnonce: window.swp_data.discovery_nonce,
						 },
					 } );

		// Check back in 1 second for more
		setTimeout( sayWhatPro.saveDiscoveries, 1000 );
	},
};

/**
 * Attach filters.
 */
if ( window.swp_data.discovery ) {
	// Use generic filters if string discovery is active to capture everything.
	wp.hooks.addFilter(
		'i18n.gettext',
		'say-what-pro',
		sayWhatPro.gettext,
		99
	);
	wp.hooks.addFilter(
		'i18n.ngettext',
		'say-what-pro',
		sayWhatPro.ngettext,
		99
	);
	wp.hooks.addFilter(
		'i18n.gettext_with_context',
		'say-what-pro',
		sayWhatPro.gettext_with_context,
		99
	);
	wp.hooks.addFilter(
		'i18n.ngettext_with_context',
		'say-what-pro',
		sayWhatPro.ngettext_with_context,
		99
	);
	wp.hooks.addFilter(
		'i18n.has_translation',
		'say-what-pro',
		sayWhatPro.has_translation,
		99
	);
} else {
	// Discovery is not active, so we only need to add filters for the text domains
	// we have replacements for.
	window.swp_data.domains.forEach( function ( domain ) {
		wp.hooks.addFilter(
			'i18n.gettext_' + domain,
			'say-what-pro',
			sayWhatPro.gettext,
			99
		);
		wp.hooks.addFilter(
			'i18n.ngettext_' + domain,
			'say-what-pro',
			sayWhatPro.ngettext,
			99
		);
		wp.hooks.addFilter(
			'i18n.gettext_with_context_' + domain,
			'say-what-pro',
			sayWhatPro.gettext_with_context,
			99
		);
		wp.hooks.addFilter(
			'i18n.ngettext_with_context_' + domain,
			'say-what-pro',
			sayWhatPro.ngettext_with_context,
			99
		);
		wp.hooks.addFilter(
			'i18n.has_translation_' + domain,
			'say-what-pro',
			sayWhatPro.has_translation,
			99
		);
	} );
}

/**
 * Trigger periodic saving of discovered strings.
 */
if ( window.swp_data.discovery ) {
	setTimeout( sayWhatPro.saveDiscoveries, 1000 );
}
