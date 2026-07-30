/**
 * محرك المعاينة 3D المشترك - مسرح Three.js واحد (كاميرا/إضاءة/تحكم
 * بالماوس) تستخدمه كل التصاميم، وسجل تصاميم قابل للتوسيع: أي تصميم
 * جديد بيسجل نفسه هنا بـregisterDesign(key, factory) من غير ما يلمس
 * هذا الملف أو أي تصميم تاني.
 */

const registry = new Map();

export function registerDesign( key, factory ) {
	registry.set( key, factory );
}

export function getDesignFactory( key ) {
	return registry.get( key );
}

export function createStage( canvas, THREE, OrbitControls ) {
	const renderer = new THREE.WebGLRenderer( { canvas, antialias: true, alpha: true } );
	renderer.setPixelRatio( Math.min( window.devicePixelRatio || 1, 2 ) );
	if ( 'outputColorSpace' in renderer ) {
		renderer.outputColorSpace = THREE.SRGBColorSpace;
	}

	const scene = new THREE.Scene();

	const camera = new THREE.PerspectiveCamera( 32, 1, 0.1, 100 );
	camera.position.set( 0, 0.4, 6 );

	scene.add( new THREE.HemisphereLight( 0xffffff, 0x1a1410, 1.15 ) );

	const key1 = new THREE.DirectionalLight( 0xffffff, 1.5 );
	key1.position.set( 3, 5, 4 );
	scene.add( key1 );

	const fill = new THREE.DirectionalLight( 0xffffff, 0.45 );
	fill.position.set( -4, -1, -3 );
	scene.add( fill );

	const controls = new OrbitControls( camera, renderer.domElement );
	controls.enableDamping = true;
	controls.dampingFactor = 0.08;
	controls.enablePan = false;
	controls.minDistance = 2.5;
	controls.maxDistance = 10;
	controls.autoRotate = true;
	controls.autoRotateSpeed = 1.4;
	controls.addEventListener( 'start', () => {
		controls.autoRotate = false;
	} );

	function resize() {
		const parent = canvas.parentElement;
		if ( ! parent ) {
			return;
		}
		const w = Math.max( 1, parent.clientWidth );
		const h = Math.max( 1, parent.clientHeight );
		renderer.setSize( w, h, false );
		camera.aspect = w / h;
		camera.updateProjectionMatrix();
	}

	let raf = null;
	function loop() {
		controls.update();
		renderer.render( scene, camera );
		raf = requestAnimationFrame( loop );
	}

	function dispose() {
		if ( raf ) {
			cancelAnimationFrame( raf );
		}
		controls.dispose();
		renderer.dispose();
	}

	return { renderer, scene, camera, controls, resize, loop, dispose };
}
