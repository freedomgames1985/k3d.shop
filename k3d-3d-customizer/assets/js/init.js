/**
 * نقطة الدخول - بتحمّل Three.js + محرك المعاينة + كل وحدات التصاميم
 * (كل وحدة بتسجل نفسها في السجل المشترك لما تتحمّل)، وبعدين بتفعّل
 * أي ودجت [data-k3d-3dc] موجود في الصفحة. أي تصميم جديد يتضاف مستقبلًا
 * بيتحط اسمه هنا بس (سطر import واحد) - الباقي بيشتغل لوحده.
 */

const cfg = window.K3D_3DC_CONFIG || {};

async function boot() {
	const widgets = document.querySelectorAll( '[data-k3d-3dc]' );

	if ( ! widgets.length || ! cfg.threeUrl ) {
		return;
	}

	let THREE;
	let OrbitControls;
	let createStage;
	let getDesignFactory;

	try {
		const [ threeModule, controlsModule, engineModule ] = await Promise.all( [
			import( /* webpackIgnore: true */ cfg.threeUrl ),
			import( /* webpackIgnore: true */ cfg.orbitControlsUrl ),
			import( /* webpackIgnore: true */ cfg.moduleBaseUrl + 'customizer.js' ),
		] );

		THREE = threeModule;
		OrbitControls = controlsModule.OrbitControls;
		createStage = engineModule.createStage;
		getDesignFactory = engineModule.getDesignFactory;

		await Promise.all( [
			import( /* webpackIgnore: true */ cfg.moduleBaseUrl + 'designs/name.js' ),
			import( /* webpackIgnore: true */ cfg.moduleBaseUrl + 'designs/plate.js' ),
		] );
	} catch ( e ) {
		// المعاينة تفاعلية إضافية مش أساسية - لو فشل التحميل (شبكة/CDN)، الفورم العادي يفضل شغال بدونها.
		widgets.forEach( ( el ) => el.classList.add( 'k3d-3dc-failed' ) );
		return;
	}

	widgets.forEach( ( el ) => initWidget( el, { THREE, OrbitControls, createStage, getDesignFactory } ) );
}

function initWidget( el, engine ) {
	const designKey = el.dataset.design;
	const designDef = ( cfg.designs || {} )[ designKey ];
	const factory = engine.getDesignFactory( designKey );

	if ( ! designDef || ! factory ) {
		el.classList.add( 'k3d-3dc-failed' );
		return;
	}

	const canvas = el.querySelector( '.k3d-3dc-canvas' );
	const loading = el.querySelector( '.k3d-3dc-loading' );
	const input = el.querySelector( '.k3d-3dc-input' );
	const colorButtons = Array.prototype.slice.call( el.querySelectorAll( '.k3d-3dc-color' ) );
	const colorField = el.querySelector( '.k3d-3dc-color-field' );

	if ( ! canvas ) {
		return;
	}

	const stage = engine.createStage( canvas, engine.THREE, engine.OrbitControls );

	let currentColorHex = ( designDef.colors[ 0 ] || {} ).hex || '#D9583A';

	const instance = factory( {
		THREE: engine.THREE,
		scene: stage.scene,
		camera: stage.camera,
		controls: stage.controls,
		value: input ? input.value : designDef.default_value,
		colorHex: currentColorHex,
	} );

	function resize() {
		stage.resize();
	}

	resize();
	window.addEventListener( 'resize', resize );

	if ( window.ResizeObserver ) {
		new ResizeObserver( resize ).observe( el );
	}

	stage.loop();

	if ( loading ) {
		loading.remove();
	}
	el.classList.add( 'is-ready' );

	let debounceTimer = null;
	if ( input ) {
		input.addEventListener( 'input', () => {
			clearTimeout( debounceTimer );
			debounceTimer = setTimeout( () => {
				instance.update( input.value, currentColorHex );
			}, 120 );
		} );
	}

	colorButtons.forEach( ( btn ) => {
		btn.addEventListener( 'click', () => {
			colorButtons.forEach( ( b ) => b.classList.remove( 'is-active' ) );
			btn.classList.add( 'is-active' );
			currentColorHex = btn.style.getPropertyValue( '--k3d-3dc-sw' ).trim() || currentColorHex;

			if ( colorField ) {
				colorField.value = btn.dataset.colorId || '';
			}

			instance.update( input ? input.value : '', currentColorHex );
		} );
	} );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot );
} else {
	boot();
}
