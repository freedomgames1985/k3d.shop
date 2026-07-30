/**
 * نقطة الدخول - بتحمّل Three.js + محرك المعاينة + كل وحدات التصاميم
 * (كل وحدة بتسجل نفسها في السجل المشترك لما تتحمّل)، وبعدين بتفعّل
 * أي ودجت [data-k3d-3dc] موجود في الصفحة. أي تصميم جديد يتضاف مستقبلًا
 * بيتحط اسمه هنا بس (سطر import واحد) - الباقي بيشتغل لوحده.
 */

const cfg = window.K3D_3DC_CONFIG || {};

// API عامة بسيطة - أي كود تاني في الصفحة (زي ودجت "طلب أكتر من قطعة")
// يقدر يستخدم نفس محرك Three.js اللي اتحمّل هنا من غير ما يعيد تحميله
// تاني، ومن غير ما يعرف تفاصيل الـimport/التسجيل بتاعت التصاميم.
let resolveEngine;
let rejectEngine;
const enginePromise = new Promise( ( resolve, reject ) => {
	resolveEngine = resolve;
	rejectEngine = reject;
} );
// اشتراك صامت عشان مطلعش "Unhandled promise rejection" في الكونسول لو
// التحميل فشل ومفيش حد بيستخدم K3D_3DC.ready فعليًا في الصفحة دي.
enginePromise.catch( () => {} );

window.K3D_3DC = {
	ready: enginePromise,
	start: () => start(),
	createPreview( canvas, designKey, value, colorHex ) {
		return enginePromise.then( ( engine ) => {
			const factory = engine.getDesignFactory( designKey );

			if ( ! factory ) {
				return null;
			}

			const stage = engine.createStage( canvas, engine.THREE, engine.OrbitControls );
			const instance = factory( {
				THREE: engine.THREE,
				scene: stage.scene,
				camera: stage.camera,
				controls: stage.controls,
				value,
				colorHex,
			} );

			stage.resize();
			stage.loop();

			return { stage, instance };
		} );
	},
};

async function boot() {
	const widgets = document.querySelectorAll( '[data-k3d-3dc]' );

	if ( ! widgets.length || ! cfg.threeUrl ) {
		rejectEngine( new Error( 'no widgets/config on this page' ) );
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
			import( /* webpackIgnore: true */ cfg.moduleBaseUrl + 'designs/name-layers.js' ),
			import( /* webpackIgnore: true */ cfg.moduleBaseUrl + 'designs/car-plate.js' ),
		] );

		// تصاميم مخصّصة مرفوعة من لوحة التحكم - كل واحد بيتحمّل لوحده، عشان
		// لو واحد فيهم فيه عطل (اترفع ملف مش سليم) ميوقفش باقي التصاميم
		// المدمجة/المخصّصة التانية.
		if ( Array.isArray( cfg.customDesignUrls ) ) {
			await Promise.all( cfg.customDesignUrls.map( ( url ) => import( /* webpackIgnore: true */ url ).catch( ( err ) => {
				console.error( '[k3d-3dc] فشل تحميل تصميم مخصّص:', url, err );
			} ) ) );
		}
	} catch ( e ) {
		// المعاينة تفاعلية إضافية مش أساسية - لو فشل التحميل (شبكة/CDN)، الفورم العادي يفضل شغال بدونها.
		// بنسجّل السبب في الكونسول عشان نقدر نشخّص أي عطل مستقبلي بدل ما يختفي بصمت.
		console.error( '[k3d-3dc] فشل تحميل محرك المعاينة 3D:', e );
		widgets.forEach( ( el ) => {
			el.classList.add( 'k3d-3dc-failed' );
			const loading = el.querySelector( '.k3d-3dc-loading' );
			if ( loading && cfg.i18n && cfg.i18n.previewFailed ) {
				loading.textContent = cfg.i18n.previewFailed;
			}
		} );
		rejectEngine( e );
		return;
	}

	widgets.forEach( ( el ) => initWidget( el, { THREE, OrbitControls, createStage, getDesignFactory } ) );
	resolveEngine( { THREE, OrbitControls, createStage, getDesignFactory } );
}

function initWidget( el, engine ) {
	const designKey = el.dataset.design;
	const designDef = ( cfg.designs || {} )[ designKey ];
	const factory = engine.getDesignFactory( designKey );

	if ( ! designDef || ! factory ) {
		el.classList.add( 'k3d-3dc-failed' );
		return;
	}

	// في صفحة المنتج الـcontrols (الاسم/الألوان) مش جوه نفس العنصر - دايمًا
	// في بلوك منفصل جنب زرار "أضف للسلة" (product-render.php بيربطهم
	// بـdata-k3d-3dc-for). في الهيرو لسه بلوك واحد زي ما كانوا.
	const controlsRoot = el.querySelector( '.k3d-3dc-controls' )
		? el
		: ( document.querySelector( '[data-k3d-3dc-for="' + el.id + '"]' ) || el );

	const canvas = el.querySelector( '.k3d-3dc-canvas' );
	const loading = el.querySelector( '.k3d-3dc-loading' );
	const input = controlsRoot.querySelector( '.k3d-3dc-input' );
	// حقل ثاني اختياري (اسم/نص إضافي) - أي تصميم بيعرّف design.secondary
	// في PHP بيظهر له تلقائيًا، من غير ما init.js يعرف اسم التصميم نفسه.
	const input2 = controlsRoot.querySelector( '.k3d-3dc-input-2' );
	const colorButtons = Array.prototype.slice.call( controlsRoot.querySelectorAll( '.k3d-3dc-color' ) );
	const colorField = controlsRoot.querySelector( '.k3d-3dc-color-field' );
	const snapshotField = controlsRoot.querySelector( '.k3d-3dc-snapshot-field' );

	if ( ! canvas ) {
		return;
	}

	const stage = engine.createStage( canvas, engine.THREE, engine.OrbitControls );
	const initialCameraPos = stage.camera.position.clone();

	let currentColorHex = ( designDef.colors[ 0 ] || {} ).hex || '#D9583A';

	const instance = factory( {
		THREE: engine.THREE,
		scene: stage.scene,
		camera: stage.camera,
		controls: stage.controls,
		value: input ? input.value : designDef.default_value,
		colorHex: currentColorHex,
		value2: input2 ? input2.value : '',
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
	function scheduleUpdate() {
		clearTimeout( debounceTimer );
		debounceTimer = setTimeout( () => {
			instance.update( input ? input.value : '', currentColorHex, input2 ? input2.value : '' );
		}, 120 );
	}
	if ( input ) {
		input.addEventListener( 'input', scheduleUpdate );
	}
	if ( input2 ) {
		input2.addEventListener( 'input', scheduleUpdate );
	}

	// صفحة المنتج: أول ما العميل يبدأ يكتب (في أي حقل من حقول التخصيص)،
	// صورة المنتج نفسها (المعرض) بتتحول للمعاينة الحية بدل ما تبقى بلوك
	// منفصل تحت - بالظبط زي أي متجر تخصيص حقيقي. قبل ما يكتب، صور
	// المنتج العادية فاضلة زي ما هي.
	const galleryRoot = el.closest( '.product-gallery' );
	if ( galleryRoot && ( input || input2 ) ) {
		const revealGallery = function () {
			const hasValue = ( input && input.value.trim() !== '' ) || ( input2 && input2.value.trim() !== '' );
			if ( ! hasValue ) {
				return;
			}
			galleryRoot.classList.add( 'k3d-3dc-gallery-active' );
			resize();
			if ( input ) {
				input.removeEventListener( 'input', revealGallery );
			}
			if ( input2 ) {
				input2.removeEventListener( 'input', revealGallery );
			}
		};
		if ( input ) {
			input.addEventListener( 'input', revealGallery );
		}
		if ( input2 ) {
			input2.addEventListener( 'input', revealGallery );
		}
	}

	// لقطة من المعاينة (الشكل بالظبط اللي العميل صممه) بتتاخد لحظة "أضف
	// للسلة" وتتبعت مع الطلب - عشان تظهر في السلة والطلب بدل الصورة
	// العامة للمنتج.
	const cartForm = controlsRoot.closest( 'form.cart' );
	if ( cartForm && snapshotField ) {
		cartForm.addEventListener( 'submit', () => {
			try {
				snapshotField.value = stage.renderer.domElement.toDataURL( 'image/jpeg', 0.82 );
			} catch ( e ) {
				snapshotField.value = '';
			}
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

			instance.update( input ? input.value : '', currentColorHex, input2 ? input2.value : '' );
		} );
	} );

	const refreshBtn = el.querySelector( '.k3d-3dc-refresh' );
	if ( refreshBtn ) {
		refreshBtn.addEventListener( 'click', () => {
			stage.camera.position.copy( initialCameraPos );
			stage.controls.target.set( 0, 0, 0 );
			stage.controls.autoRotate = true;
			stage.controls.update();
		} );
	}

	const expandBtn = el.querySelector( '.k3d-3dc-expand' );
	const stageEl = el.querySelector( '.k3d-3dc-stage' );
	if ( expandBtn && stageEl && ( stageEl.requestFullscreen || stageEl.webkitRequestFullscreen ) ) {
		expandBtn.addEventListener( 'click', () => {
			const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement;

			if ( isFullscreen ) {
				( document.exitFullscreen || document.webkitExitFullscreen ).call( document );
			} else {
				( stageEl.requestFullscreen || stageEl.webkitRequestFullscreen ).call( stageEl );
			}
		} );

		[ 'fullscreenchange', 'webkitfullscreenchange' ].forEach( ( ev ) => {
			document.addEventListener( ev, resize );
		} );
	} else if ( expandBtn ) {
		expandBtn.hidden = true;
	}
}

/**
 * تحميل مؤجّل: Three.js + المحرك تقيلين شوية (~600 كيلوبايت)، فمفيش داعي
 * نحمّلهم فورًا مع كل تحميل صفحة حتى لو الزائر مش هيلمس الودجت خالص -
 * أول تفاعل حقيقي في الصفحة (لمس/كتابة/فوكس) أو مهلة قصيرة لو مفيش
 * تفاعل، أيهما أسرع.
 *
 * الاستثناء: ودجت الـ"hero" في الصفحة الرئيسية هو المحتوى الأساسي للقسم
 * (مش محتوى مساعد زي معاينة صفحة المنتج)، فبيتحمّل فورًا مع الصفحة -
 * التأجيل هنا كان بيخلي القسم يفضل شايل "جاري تجهيز المعاينة..." لثواني
 * وكأن الودجت ظهر واختفى.
 */
function whenDomReady( fn ) {
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', fn, { once: true } );
	} else {
		fn();
	}
}

let started = false;
function start() {
	if ( started ) {
		return;
	}
	started = true;
	whenDomReady( boot );
}

whenDomReady( function () {
	if ( document.querySelector( '.k3d-3dc-mode-hero[data-k3d-3dc]' ) ) {
		start();
	}
} );

[ 'pointerdown', 'touchstart', 'focusin', 'keydown' ].forEach( ( ev ) => {
	document.addEventListener( ev, start, { once: true, passive: true, capture: true } );
} );

if ( 'requestIdleCallback' in window ) {
	requestIdleCallback( start, { timeout: 2500 } );
} else {
	setTimeout( start, 1800 );
}
