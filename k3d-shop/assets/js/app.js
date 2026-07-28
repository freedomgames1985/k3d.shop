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

	function initHomeSliders() {
		document.querySelectorAll( '[data-k3d-slider]' ).forEach( function ( slider ) {
			var track = slider.querySelector( '.home-slider-track' );
			var slides = slider.querySelectorAll( '.home-slider-item' );
			var dots = slider.querySelectorAll( '.home-slider-dots button' );
			var delay = parseInt( slider.dataset.autoplay, 10 ) || 5000;
			var index = 0;
			var timer = null;

			if ( ! track || slides.length < 2 ) {
				return;
			}

			function goTo( i ) {
				index = ( i + slides.length ) % slides.length;
				track.style.transform = 'translateX(' + ( index * 100 * ( isRtl() ? 1 : -1 ) ) + '%)';
				dots.forEach( function ( d, di ) {
					d.classList.toggle( 'is-active', di === index );
				} );
			}

			function isRtl() {
				return 'rtl' === document.documentElement.dir;
			}

			function start() {
				stop();
				timer = setInterval( function () { goTo( index + 1 ); }, delay );
			}

			function stop() {
				if ( timer ) {
					clearInterval( timer );
					timer = null;
				}
			}

			dots.forEach( function ( d ) {
				d.addEventListener( 'click', function () {
					goTo( parseInt( d.dataset.index, 10 ) || 0 );
					start();
				} );
			} );

			slider.addEventListener( 'mouseenter', stop );
			slider.addEventListener( 'mouseleave', start );

			goTo( 0 );
			start();
		} );
	}

	document.addEventListener( 'DOMContentLoaded', initHomeSliders );

	function initCompactHeaderOnScroll() {
		var header = document.querySelector( 'header.site' );

		if ( ! header ) {
			return;
		}

		var enterThreshold = 120;
		var exitThreshold = 40;
		var ticking = false;

		function update() {
			var y = window.scrollY;

			if ( y > enterThreshold ) {
				header.classList.add( 'is-compact' );
			} else if ( y < exitThreshold ) {
				header.classList.remove( 'is-compact' );
			}

			ticking = false;
		}

		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( update );
				ticking = true;
			}
		}, { passive: true } );

		update();
	}

	document.addEventListener( 'DOMContentLoaded', initCompactHeaderOnScroll );

	function initRecentOrdersNotice() {
		var el = document.getElementById( 'k3d-recent-orders' );

		if ( ! el ) {
			return;
		}

		var items;

		try {
			items = JSON.parse( el.dataset.items || '[]' );
		} catch ( e ) {
			items = [];
		}

		if ( ! items.length ) {
			return;
		}

		var index = 0;
		var card = document.createElement( 'div' );
		card.className = 'k3d-recent-orders-card';
		el.appendChild( card );

		function showNext() {
			card.innerHTML = '<span class="dot"></span><span></span>';
			card.querySelector( 'span:last-child' ).textContent = items[ index % items.length ];
			index++;

			requestAnimationFrame( function () {
				card.classList.add( 'is-visible' );
			} );

			setTimeout( function () {
				card.classList.remove( 'is-visible' );
				setTimeout( showNext, 6000 );
			}, 5000 );
		}

		setTimeout( showNext, 4000 );
	}

	document.addEventListener( 'DOMContentLoaded', initRecentOrdersNotice );
} )();
