/**
 * سلوكيات القالب البسيطة - عداد الكمية (+/-) حوالين حقل ووكومرس الحقيقي
 * (input.qty)، من غير ما نلمس منطق ووكومرس نفسه، عشان إعادة حساب السعر
 * للمنتجات المتغيّرة (Variable Products) يفضل شغال.
 */
(function () {
	'use strict';

	function enhanceQuantityInputs() {
		document.querySelectorAll( '.quantity' ).forEach( function ( wrap ) {
			if ( wrap.dataset.k3dEnhanced ) {
				return;
			}
			wrap.dataset.k3dEnhanced = '1';

			var input = wrap.querySelector( 'input.qty' );
			if ( ! input ) {
				return;
			}

			var minus = document.createElement( 'button' );
			minus.type = 'button';
			minus.className = 'qty-btn qty-btn-minus';
			minus.setAttribute( 'aria-label', 'minus' );
			minus.textContent = '−';

			var plus = document.createElement( 'button' );
			plus.type = 'button';
			plus.className = 'qty-btn qty-btn-plus';
			plus.setAttribute( 'aria-label', 'plus' );
			plus.textContent = '+';

			wrap.insertBefore( minus, input );
			wrap.appendChild( plus );

			function step( dir ) {
				var value = parseFloat( input.value ) || 0;
				var stepVal = parseFloat( input.step ) || 1;
				var min = input.min !== '' ? parseFloat( input.min ) : 1;
				var max = input.max !== '' ? parseFloat( input.max ) : Infinity;

				value = Math.min( max, Math.max( min, value + dir * stepVal ) );
				input.value = value;
				input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			}

			minus.addEventListener( 'click', function () { step( -1 ); } );
			plus.addEventListener( 'click', function () { step( 1 ); } );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', enhanceQuantityInputs );
	// السلة/الاختيارات بتحدّث الـDOM بالـAJAX - لازم نعيد التحسين بعد كل تحديث.
	if ( typeof jQuery !== 'undefined' ) {
		jQuery( document.body ).on( 'updated_wc_div updated_cart_totals found_variation', enhanceQuantityInputs );
	}

	// تبديل تسجيل الدخول / حساب جديد في صفحة "حسابي".
	function initAccountTabs() {
		var tabs = document.querySelectorAll( '.account-tab' );
		var switchLinks = document.querySelectorAll( '.account-switch-link' );

		if ( ! tabs.length ) {
			return;
		}

		function showPane( name ) {
			document.querySelectorAll( '.account-pane' ).forEach( function ( p ) {
				p.hidden = p.dataset.pane !== name;
			} );
			tabs.forEach( function ( t ) {
				t.classList.toggle( 'is-active', t.dataset.pane === name );
			} );
		}

		tabs.forEach( function ( t ) {
			t.addEventListener( 'click', function () { showPane( t.dataset.pane ); } );
		} );
		switchLinks.forEach( function ( l ) {
			l.addEventListener( 'click', function () { showPane( l.dataset.pane ); } );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', initAccountTabs );
} )();
