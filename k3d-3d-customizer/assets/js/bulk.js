/**
 * "طلب أكتر من قطعة" - نافذة منبثقة بتضيف أكتر من قيمة للسلة دفعة واحدة.
 * بتستخدم نفس محرك المعاينة 3D اللي init.js بيحمّله (عبر window.K3D_3DC)
 * من غير ما تعيد تحميله تاني.
 */
( function () {
	'use strict';

	// السكربت ده بيتحمّل كـclassic script في الفوتر من غير defer، فبيشتغل
	// فورًا وقت ما المتصفح يوصله وهو لسه بيحلل الصفحة - وقتها الـmodal
	// (اللي بيتطبع بـwp_footer priority أعلى، يعني بعد السكربت في ترتيب
	// الصفحة) ممكن يكون لسه مش موجود في الـDOM، فـgetElementById بيرجّع
	// null والزرار مايتوصلش بأي click listener. بننتظر DOMContentLoaded
	// عشان نضمن إن كل حاجة اتحطت في الصفحة قبل ما نفتش عنها.
	function init() {
		var cfg = window.K3D_3DCB_CONFIG || {};
		var overlay = document.getElementById( 'k3d-3dcb-overlay' );
		var trigger = document.getElementById( 'k3d-3dcb-trigger' );

		if ( ! overlay || ! trigger ) {
			return;
		}

		var closeBtn = document.getElementById( 'k3d-3dcb-close' );
	var ta = document.getElementById( 'k3d-3dcb-ta' );
	var addListBtn = document.getElementById( 'k3d-3dcb-addList' );
	var rowsEl = document.getElementById( 'k3d-3dcb-rows' );
	var cntEl = document.getElementById( 'k3d-3dcb-cnt' );
	var clearBtn = document.getElementById( 'k3d-3dcb-clear' );
	var colorsWrap = document.getElementById( 'k3d-3dcb-colors' );
	var canvas = document.getElementById( 'k3d-3dcb-canvas' );
	var selEl = document.getElementById( 'k3d-3dcb-sel' );
	var prevBtn = document.getElementById( 'k3d-3dcb-prev' );
	var nextBtn = document.getElementById( 'k3d-3dcb-next' );
	var totalEl = document.getElementById( 'k3d-3dcb-total' );
	var errEl = document.getElementById( 'k3d-3dcb-err' );
	var cartBtn = document.getElementById( 'k3d-3dcb-cart' );

	var items = [];
	var selectedIndex = -1;
	var previewApi = null;
	var previewRequested = false;

	function activeColorBtn() {
		return colorsWrap ? colorsWrap.querySelector( '.k3d-3dcb-color.is-active' ) : null;
	}

	function activeColorId() {
		var btn = activeColorBtn();
		return btn ? btn.dataset.colorId : '';
	}

	function activeColorHex() {
		var btn = activeColorBtn();
		return btn ? btn.style.getPropertyValue( '--sw' ).trim() : '#D9583A';
	}

	function showError( msg ) {
		if ( ! errEl ) {
			return;
		}
		errEl.textContent = msg || '';
		errEl.hidden = ! msg;
	}

	function renderTotal() {
		if ( ! totalEl ) {
			return;
		}
		var total = ( parseFloat( cfg.price ) || 0 ) * items.length;
		totalEl.textContent = ( cfg.currency || '' ) + total.toFixed( 2 );
	}

	function renderRows() {
		if ( ! rowsEl || ! cntEl ) {
			return;
		}

		rowsEl.innerHTML = '';
		cntEl.textContent = String( items.length );

		if ( ! items.length ) {
			var empty = document.createElement( 'div' );
			empty.className = 'k3d-3dcb-row-empty';
			empty.textContent = cfg.i18n && cfg.i18n.empty ? cfg.i18n.empty : '';
			rowsEl.appendChild( empty );
		} else {
			items.forEach( function ( item, index ) {
				var row = document.createElement( 'div' );
				row.className = 'k3d-3dcb-row';

				var span = document.createElement( 'span' );
				span.textContent = item.value;
				if ( cfg.isDigits ) {
					span.dir = 'ltr';
				}
				row.appendChild( span );

				var btn = document.createElement( 'button' );
				btn.type = 'button';
				btn.setAttribute( 'aria-label', 'remove' );
				btn.textContent = '✕';
				btn.addEventListener( 'click', function () {
					items.splice( index, 1 );
					if ( selectedIndex >= items.length ) {
						selectedIndex = items.length - 1;
					}
					renderAll();
				} );
				row.appendChild( btn );

				rowsEl.appendChild( row );
			} );
		}

		renderSelect();
		renderTotal();

		if ( cartBtn ) {
			cartBtn.disabled = ! items.length;
		}
	}

	function renderSelect() {
		if ( ! selEl ) {
			return;
		}

		selEl.innerHTML = '';
		items.forEach( function ( item, index ) {
			var opt = document.createElement( 'option' );
			opt.value = String( index );
			opt.textContent = item.value;
			if ( cfg.isDigits ) {
				opt.dir = 'ltr';
			}
			selEl.appendChild( opt );
		} );

		if ( selectedIndex < 0 && items.length ) {
			selectedIndex = items.length - 1;
		}

		if ( selectedIndex >= 0 && selectedIndex < items.length ) {
			selEl.value = String( selectedIndex );
		}

		updatePreview();
	}

	function renderAll() {
		renderRows();
	}

	function ensurePreview() {
		if ( previewRequested || ! canvas ) {
			return;
		}
		previewRequested = true;

		if ( ! window.K3D_3DC ) {
			return;
		}

		window.K3D_3DC.start();
		window.K3D_3DC.createPreview( canvas, cfg.design, '', activeColorHex() ).then( function ( api ) {
			previewApi = api;
			updatePreview();
		} );
	}

	function updatePreview() {
		if ( ! previewApi || selectedIndex < 0 || ! items[ selectedIndex ] ) {
			return;
		}
		previewApi.instance.update( items[ selectedIndex ].value, activeColorHex() );
	}

	function addValues( raw ) {
		var lines = raw.split( /\r?\n/ )
			.map( function ( l ) { return l.trim(); } )
			.filter( function ( l ) { return l !== ''; } );

		var room = ( cfg.maxItems || 20 ) - items.length;

		if ( room <= 0 ) {
			return;
		}

		lines.slice( 0, room ).forEach( function ( value ) {
			items.push( { value: value } );
		} );

		selectedIndex = items.length - 1;
		renderAll();
	}

	function openModal() {
		overlay.hidden = false;
		document.body.style.overflow = 'hidden';
		ensurePreview();
	}

	function closeModal() {
		overlay.hidden = true;
		document.body.style.overflow = '';
	}

	trigger.addEventListener( 'click', openModal );
	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', closeModal );
	}
	overlay.addEventListener( 'click', function ( e ) {
		if ( e.target === overlay ) {
			closeModal();
		}
	} );
	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && ! overlay.hidden ) {
			closeModal();
		}
	} );

	if ( addListBtn && ta ) {
		addListBtn.addEventListener( 'click', function () {
			addValues( ta.value );
			ta.value = '';
			ta.focus();
		} );
	}

	if ( clearBtn ) {
		clearBtn.addEventListener( 'click', function () {
			items = [];
			selectedIndex = -1;
			renderAll();
		} );
	}

	if ( colorsWrap ) {
		Array.prototype.slice.call( colorsWrap.querySelectorAll( '.k3d-3dcb-color' ) ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				colorsWrap.querySelectorAll( '.k3d-3dcb-color' ).forEach( function ( b ) { b.classList.remove( 'is-active' ); } );
				btn.classList.add( 'is-active' );
				updatePreview();
			} );
		} );
	}

	if ( selEl ) {
		selEl.addEventListener( 'change', function () {
			selectedIndex = parseInt( selEl.value, 10 ) || 0;
			updatePreview();
		} );
	}

	function step( dir ) {
		if ( ! items.length ) {
			return;
		}
		selectedIndex = ( selectedIndex + dir + items.length ) % items.length;
		selEl.value = String( selectedIndex );
		updatePreview();
	}

	if ( prevBtn ) {
		prevBtn.addEventListener( 'click', function () { step( 1 ); } );
	}
	if ( nextBtn ) {
		nextBtn.addEventListener( 'click', function () { step( -1 ); } );
	}

	if ( cartBtn ) {
		cartBtn.addEventListener( 'click', function () {
			if ( ! items.length ) {
				showError( cfg.i18n && cfg.i18n.empty ? cfg.i18n.empty : '' );
				return;
			}

			showError( '' );
			cartBtn.disabled = true;
			var originalText = cartBtn.textContent;
			cartBtn.textContent = cfg.i18n && cfg.i18n.adding ? cfg.i18n.adding : '...';

			var body = new URLSearchParams();
			body.set( 'action', 'k3d_3dc_bulk_add' );
			body.set( 'nonce', cfg.nonce || '' );
			body.set( 'product_id', cfg.productId || 0 );
			body.set( 'color', activeColorId() );
			body.set( 'items', JSON.stringify( items.map( function ( i ) { return i.value; } ) ) );

			fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
				.then( function ( res ) { return res.json(); } )
				.then( function ( data ) {
					if ( data && data.success && data.data && data.data.cartUrl ) {
						window.location.href = data.data.cartUrl;
						return;
					}
					showError( ( data && data.data && data.data.message ) || ( cfg.i18n && cfg.i18n.error ) || '' );
					cartBtn.disabled = false;
					cartBtn.textContent = originalText;
				} )
				.catch( function () {
					showError( cfg.i18n && cfg.i18n.error ? cfg.i18n.error : '' );
					cartBtn.disabled = false;
					cartBtn.textContent = originalText;
				} );
		} );
	}

		renderAll();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init, { once: true } );
	} else {
		init();
	}
} )();
