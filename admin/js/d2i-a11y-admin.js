/* global jQuery, d2iA11yAdmin */
(function ($) {
	'use strict';

	// -----------------------------------------------------------------------
	// Widget position — show/hide custom position fields
	// -----------------------------------------------------------------------

	function d2iA11yToggleCustomPos( val ) {
		var el = document.getElementById( 'd2i-a11y-custom-pos' );
		if ( el ) {
			el.style.display = ( val === 'custom' ) ? '' : 'none';
		}
	}

	// -----------------------------------------------------------------------
	// Show Widget On — show/hide exclude/include page fields
	// -----------------------------------------------------------------------

	function d2iA11yToggleShowOn( val ) {
		var excl = document.getElementById( 'd2i-a11y-exclude-wrap' );
		var incl = document.getElementById( 'd2i-a11y-include-wrap' );
		if ( excl ) excl.style.display = ( val === 'all' )      ? '' : 'none';
		if ( incl ) incl.style.display = ( val === 'specific' ) ? '' : 'none';
	}

	// -----------------------------------------------------------------------
	// Initialise on DOM ready
	// -----------------------------------------------------------------------

	$(function () {
		// Colour picker.
		$( '.d2i-a11y-color-picker' ).wpColorPicker({
			change: function ( event, ui ) {
				$( '#d2i-a11y-preview-btn' ).css( 'background', ui.color.toString() );
			},
		});

		// Trigger-size preview.
		$( '#d2i_a11y_trigger_size' ).on( 'change', function () {
			var sizes = { small: '48px', medium: '56px', large: '64px' };
			var sz = sizes[ $( this ).val() ] || '56px';
			$( '#d2i-a11y-preview-btn' ).css({ width: sz, height: sz });
		});

		// Widget position toggle — initialise with saved value and wire change event.
		var posSelect = document.getElementById( 'd2i_a11y_position' );
		if ( posSelect ) {
			d2iA11yToggleCustomPos( posSelect.value );
			posSelect.addEventListener( 'change', function () {
				d2iA11yToggleCustomPos( this.value );
			});
		}

		// Show-on radio toggle — initialise with saved value and wire change events.
		var showOnRadios = document.querySelectorAll( '.d2i-show-on-radio' );
		if ( showOnRadios.length ) {
			// Find the currently checked one to set initial state.
			var checkedRadio = document.querySelector( '.d2i-show-on-radio:checked' );
			if ( checkedRadio ) {
				d2iA11yToggleShowOn( checkedRadio.value );
			}
			showOnRadios.forEach( function ( radio ) {
				radio.addEventListener( 'change', function () {
					d2iA11yToggleShowOn( this.value );
				});
			});
		}
	});

}(jQuery));
