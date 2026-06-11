import './admin.scss';

jQuery( function ( $ ) {
	function swpFillSuggestion( ui ) {
		if ( ui.item.domain !== 'default' ) {
			$( '.say_what_domain' ).val( ui.item.domain );
		} else {
			$( '.say_what_domain' ).val( '' );
		}
		if ( ui.item.context !== 'sw-default-context' ) {
			$( '.say_what_context' ).val( ui.item.context );
		} else {
			$( '.say_what_context' ).val( '' );
		}
		if (
			typeof ui.item.translated_string !== 'undefined' &&
			ui.item.translated_string.length > 0
		) {
			$( '.say_what_translated_string' ).html(
				ui.item.translated_string
			);
		} else {
			$( '.say_what_translated_string' ).html( '' );
		}
		$( '.say_what_orig_string' ).val( ui.item.orig_string );
		$( '.say_what_replacement_string' ).focus();
	}

	$( '.say_what_orig_string' ).autocomplete( {
		minLength: 1,
		source: window.say_what.autocomplete_url,
		select: ( event, ui ) => {
			if ( ui.item.orig_string === 'SWP_NO_MATCHES' ) {
				return false;
			} else if ( ui.item.orig_string === 'SWP_NO_SUGGESTIONS' ) {
				window.location = window.say_what.string_discovery_url;
				return false;
			}
			swpFillSuggestion( ui );
			return false;
		},
	} );
	jQuery.ui.autocomplete.prototype._resizeMenu = function () {
		const ul = this.menu.element;
		ul.outerWidth( this.element.outerWidth() );
	};
} );

jQuery( document ).on(
	'click',
	'.swp-admin-notice .notice-dismiss',
	function () {
		const notice = jQuery( this ).parents( '.notice' ).eq( 0 );
		const noticeKey = notice.data( 'swp-notice' );
		jQuery.post( window.say_what.dismiss_notice_url, {
			notice: noticeKey,
		} );
	}
);
