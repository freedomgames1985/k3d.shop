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

	// تحميل صفحات المتجر تلقائيًا عند النزول لآخر الصفحة، بدل الانتقال
	// لصفحة تالية. بيعتمد على رابط "next" الحقيقي من ووكومرس (paginate_links)
	// عشان يفضل متوافق مع أي فلتر/ترتيب مفعّل في الرابط.
	function initShopInfiniteScroll() {
		var main = document.querySelector( '[data-k3d-infinite-scroll]' );
		var grid = main && main.querySelector( '.shop-grid' );
		var sentinel = main && main.querySelector( '.shop-infinite-sentinel' );
		var loading = main && main.querySelector( '.shop-infinite-loading' );
		var pagination = main && main.querySelector( '.pagination' );

		if ( ! main || ! grid || ! sentinel || ! pagination || ! window.IntersectionObserver ) {
			return;
		}

		var nextLink = pagination.querySelector( 'a.next' );

		if ( ! nextLink ) {
			sentinel.remove();
			return;
		}

		pagination.hidden = true;

		var busy = false;
		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					loadNext();
				}
			} );
		}, { rootMargin: '600px 0px' } );

		function stop() {
			nextLink = null;
			observer.disconnect();
			sentinel.remove();
		}

		function loadNext() {
			if ( busy || ! nextLink ) {
				return;
			}
			busy = true;
			loading.hidden = false;

			fetch( nextLink.href )
				.then( function ( res ) { return res.text(); } )
				.then( function ( html ) {
					var doc = new DOMParser().parseFromString( html, 'text/html' );
					var newGrid = doc.querySelector( '.shop-grid' );
					var newPagination = doc.querySelector( '.pagination' );

					if ( newGrid ) {
						Array.prototype.slice.call( newGrid.children ).forEach( function ( card ) {
							grid.appendChild( card );
						} );
					}

					nextLink = newPagination ? newPagination.querySelector( 'a.next' ) : null;

					if ( ! nextLink ) {
						stop();
					}
				} )
				.catch( stop )
				.then( function () {
					busy = false;
					loading.hidden = true;
				} );
		}

		observer.observe( sentinel );
	}

	document.addEventListener( 'DOMContentLoaded', initShopInfiniteScroll );

	// عد تنازلي حي لعروض التخفيض في صفحة المنتج - بيتحدث لوحده كل ثانية
	// من غير أي تحديث للصفحة، وبيختفي لوحده لو العرض خلص.
	function initSaleCountdown() {
		document.querySelectorAll( '[data-k3d-countdown]' ).forEach( function ( el ) {
			var end = parseInt( el.dataset.end, 10 );

			if ( ! end ) {
				return;
			}

			function pad( n ) {
				n = String( n );
				return n.length < 2 ? '0' + n : n;
			}

			function render() {
				var diff = end - Date.now();

				if ( diff <= 0 ) {
					clearInterval( timer );
					el.remove();
					return;
				}

				var days = Math.floor( diff / 86400000 );
				var hours = Math.floor( diff / 3600000 ) % 24;
				var minutes = Math.floor( diff / 60000 ) % 60;
				var seconds = Math.floor( diff / 1000 ) % 60;

				[ [ 'd', days ], [ 'h', hours ], [ 'm', minutes ], [ 's', seconds ] ].forEach( function ( pair ) {
					var unitEl = el.querySelector( '[data-unit="' + pair[ 0 ] + '"]' );
					if ( unitEl ) {
						unitEl.textContent = pad( pair[ 1 ] );
					}
				} );
			}

			render();
			var timer = setInterval( render, 1000 );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', initSaleCountdown );

	// تأثير ظهور تدريجي (fade + slide) للكروت والأقسام في الصفحة الرئيسية
	// لما توصلهم أثناء الاسكرول، بترتيب متتابع بسيط بين عناصر نفس المجموعة.
	function initScrollReveal() {
		if ( ! window.IntersectionObserver ) {
			return;
		}

		var targets = document.querySelectorAll(
			'.section-head, .cat-card, .prod-card, .custom-cta, .how-it-works-step'
		);

		if ( ! targets.length ) {
			return;
		}

		targets.forEach( function ( el ) { el.classList.add( 'k3d-reveal' ); } );

		var counters = new WeakMap();

		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) {
					return;
				}

				var el = entry.target;
				var parent = el.parentElement;
				var index = counters.get( parent ) || 0;
				counters.set( parent, index + 1 );

				setTimeout( function () {
					el.classList.add( 'is-visible' );
				}, Math.min( index, 6 ) * 70 );

				observer.unobserve( el );
			} );
		}, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' } );

		targets.forEach( function ( el ) { observer.observe( el ); } );
	}

	document.addEventListener( 'DOMContentLoaded', initScrollReveal );
} )();
