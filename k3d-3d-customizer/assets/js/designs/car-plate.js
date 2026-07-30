/**
 * تصميم "لوحة سيارة" - نفس فكرة plate.js (extrude حقيقي + TextGeometry
 * للرقم) لكن مبني بالكامل كتقليد للوحة سيارة إسرائيلية حقيقية: إطار
 * خارجي غامق + وش أصفر/أبيض/إلخ فوقه، ثقب تعليق حقيقي منقوش جوه
 * الاتنين (مش حلقة منفصلة)، شريط "IL" أزرق، والرقم منقوش بارز.
 *
 * خانة تانية اختيارية (اسم/نص حر) بتتحط كسطر تاني تحت الرقم - بترسم
 * بتقنية الـcanvas texture (زي name.js) عشان تشكيل الحروف العربي/العبري
 * يفضل صح، لأن خط TextGeometry (Helvetiker) لاتيني/أرقام بس.
 */

import { registerDesign } from '../customizer.js';

const FONT_URL = 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/fonts/helvetiker_bold.typeface.json';
const FONT_LOADER_URL = 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/loaders/FontLoader.js';
const TEXT_GEOMETRY_URL = 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/geometries/TextGeometry.js';
const CHIP_HEX = '#0057B8';
const INK_HEX = '#14181A';

let fontCache = null;
let fontPromise = null;
let textGeometryPromise = null;

function loadFont() {
	if ( ! fontPromise ) {
		fontPromise = import( FONT_LOADER_URL ).then( ( { FontLoader } ) => new Promise( ( resolve, reject ) => {
			new FontLoader().load( FONT_URL, ( font ) => {
				fontCache = font;
				resolve( font );
			}, undefined, reject );
		} ) );
	}
	return fontPromise;
}

function loadTextGeometry() {
	if ( ! textGeometryPromise ) {
		textGeometryPromise = import( TEXT_GEOMETRY_URL );
	}
	return textGeometryPromise;
}

/** مستطيل بزوايا دائرية، مع ثقب دائري اختياري (إحداثيات الثقب محلية بالنسبة لمركز الشكل نفسه). */
function roundedRectShape( THREE, w, h, r, holeCx, holeCy, holeR ) {
	const shape = new THREE.Shape();
	const x = -w / 2;
	const y = -h / 2;
	shape.moveTo( x, y + r );
	shape.lineTo( x, y + h - r );
	shape.quadraticCurveTo( x, y + h, x + r, y + h );
	shape.lineTo( x + w - r, y + h );
	shape.quadraticCurveTo( x + w, y + h, x + w, y + h - r );
	shape.lineTo( x + w, y + r );
	shape.quadraticCurveTo( x + w, y, x + w - r, y );
	shape.lineTo( x + r, y );
	shape.quadraticCurveTo( x, y, x, y + r );

	if ( holeR ) {
		const hole = new THREE.Path();
		hole.absarc( holeCx, holeCy, holeR, 0, Math.PI * 2, false );
		shape.holes.push( hole );
	}

	return shape;
}

registerDesign( 'car_plate', function ( { THREE, scene, value, colorHex, value2 } ) {
	const group = new THREE.Group();
	scene.add( group );

	// إحداثيات الثقب في مساحة اللوحة كلها (المركز = نص اللوحة).
	const holeCx = -1.62;
	const holeCy = 0.3;
	const holeR = 0.1;

	const chipCenterX = -1.55;
	const chipCenterY = -0.06;
	const chipW = 0.62;
	const chipH = 0.9;

	const backShape = roundedRectShape( THREE, 3.9, 1.25, 0.18, holeCx, holeCy, holeR );
	const backGeometry = new THREE.ExtrudeGeometry( backShape, {
		depth: 0.16, bevelEnabled: true, bevelThickness: 0.02, bevelSize: 0.02, bevelSegments: 2,
	} );
	const backMaterial = new THREE.MeshStandardMaterial( { color: INK_HEX, roughness: 0.45, metalness: 0.12 } );
	const backMesh = new THREE.Mesh( backGeometry, backMaterial );
	backMesh.position.z = -0.16;
	group.add( backMesh );

	const faceShape = roundedRectShape( THREE, 3.68, 1.0, 0.13, holeCx, holeCy, holeR );
	const faceGeometry = new THREE.ExtrudeGeometry( faceShape, {
		depth: 0.09, bevelEnabled: true, bevelThickness: 0.015, bevelSize: 0.015, bevelSegments: 2,
	} );
	const faceMaterial = new THREE.MeshStandardMaterial( { color: colorHex || '#F5C518', roughness: 0.35, metalness: 0.1 } );
	const faceMesh = new THREE.Mesh( faceGeometry, faceMaterial );
	faceMesh.position.z = -0.09;
	group.add( faceMesh );

	const chipShape = roundedRectShape( THREE, chipW, chipH, 0.08, holeCx - chipCenterX, holeCy - chipCenterY, holeR );
	const chipGeometry = new THREE.ExtrudeGeometry( chipShape, { depth: 0.05, bevelEnabled: false } );
	const chipMaterial = new THREE.MeshStandardMaterial( { color: CHIP_HEX, roughness: 0.35, metalness: 0.1 } );
	const chipMesh = new THREE.Mesh( chipGeometry, chipMaterial );
	chipMesh.position.set( chipCenterX, chipCenterY, -0.02 );
	group.add( chipMesh );

	let ilMesh = null;
	function buildIL() {
		if ( ilMesh || ! fontCache ) {
			return;
		}
		loadTextGeometry().then( ( { TextGeometry } ) => {
			const geometry = new TextGeometry( 'IL', { font: fontCache, size: 0.22, height: 0.05, curveSegments: 4, bevelEnabled: false } );
			geometry.computeBoundingBox();
			geometry.center();
			const material = new THREE.MeshStandardMaterial( { color: '#F3F5F1', roughness: 0.4, metalness: 0.05 } );
			ilMesh = new THREE.Mesh( geometry, material );
			ilMesh.position.set( chipCenterX, chipCenterY - 0.2, 0.03 );
			group.add( ilMesh );
		} );
	}

	let numberMesh = null;
	let numberMaterial = null;
	function rebuildNumber( text, hasSecondary ) {
		if ( ! fontCache ) {
			return;
		}
		loadTextGeometry().then( ( { TextGeometry } ) => {
			if ( numberMesh ) {
				group.remove( numberMesh );
				numberMesh.geometry.dispose();
			}

			const size = hasSecondary ? 0.32 : 0.42;
			const geometry = new TextGeometry( ( text || '' ).toString() || ' ', {
				font: fontCache, size, height: 0.12, curveSegments: 6, bevelEnabled: false,
			} );
			geometry.computeBoundingBox();
			geometry.center();

			if ( ! numberMaterial ) {
				numberMaterial = new THREE.MeshStandardMaterial( { color: INK_HEX, roughness: 0.4, metalness: 0.18 } );
			}

			numberMesh = new THREE.Mesh( geometry, numberMaterial );
			numberMesh.position.set( 0.35, hasSecondary ? 0.2 : 0, 0.05 );
			group.add( numberMesh );
		} );
	}

	// السطر التاني (اسم/نص حر اختياري) - canvas texture عشان أي لغة تتشكل صح.
	const secCanvas = document.createElement( 'canvas' );
	secCanvas.width = 1024;
	secCanvas.height = 256;
	const secCtx = secCanvas.getContext( '2d' );
	const secTexture = new THREE.CanvasTexture( secCanvas );
	if ( 'colorSpace' in secTexture ) {
		secTexture.colorSpace = THREE.SRGBColorSpace;
	}
	const secMaterial = new THREE.MeshStandardMaterial( { map: secTexture, transparent: true, alphaTest: 0.3, roughness: 0.4, metalness: 0.05 } );
	const secPlane = new THREE.Mesh( new THREE.PlaneGeometry( 2.7, 0.5 ), secMaterial );
	secPlane.position.set( 0.35, -0.28, 0.02 );
	group.add( secPlane );

	function drawSecondary( text ) {
		const t = ( text || '' ).toString().trim();
		secCtx.clearRect( 0, 0, secCanvas.width, secCanvas.height );

		if ( '' === t ) {
			secTexture.needsUpdate = true;
			return;
		}

		secCtx.save();
		secCtx.translate( secCanvas.width / 2, secCanvas.height / 2 );
		secCtx.direction = 'rtl';
		secCtx.textAlign = 'center';
		secCtx.textBaseline = 'middle';

		let fontSize = 130;
		secCtx.font = `700 ${ fontSize }px "IBM Plex Sans Arabic","IBM Plex Sans Hebrew","IBM Plex Sans",sans-serif`;
		const maxWidth = secCanvas.width * 0.9;
		const measured = secCtx.measureText( t ).width;

		if ( measured > maxWidth ) {
			fontSize = Math.max( 40, Math.floor( fontSize * ( maxWidth / measured ) ) );
			secCtx.font = `700 ${ fontSize }px "IBM Plex Sans Arabic","IBM Plex Sans Hebrew","IBM Plex Sans",sans-serif`;
		}

		secCtx.fillStyle = INK_HEX;
		secCtx.fillText( t, 0, 4 );
		secCtx.restore();
		secTexture.needsUpdate = true;
	}

	function refresh( val, hex, val2 ) {
		faceMaterial.color.set( hex || '#F5C518' );
		const hasSecondary = !! ( val2 || '' ).toString().trim();

		if ( fontCache ) {
			rebuildNumber( val, hasSecondary );
		} else {
			loadFont().then( () => {
				rebuildNumber( val, hasSecondary );
				buildIL();
			} );
		}

		drawSecondary( val2 );
	}

	loadFont().then( () => {
		rebuildNumber( value, !! ( value2 || '' ).toString().trim() );
		buildIL();
	} );
	drawSecondary( value2 );

	return {
		object3D: group,
		update( newValue, newColorHex, newValue2 ) {
			refresh( newValue, newColorHex, newValue2 );
		},
		dispose() {
			backGeometry.dispose();
			backMaterial.dispose();
			faceGeometry.dispose();
			faceMaterial.dispose();
			chipGeometry.dispose();
			chipMaterial.dispose();
			secTexture.dispose();
			secMaterial.dispose();
			secPlane.geometry.dispose();

			if ( numberMesh ) {
				numberMesh.geometry.dispose();
			}
			if ( numberMaterial ) {
				numberMaterial.dispose();
			}
			if ( ilMesh ) {
				ilMesh.geometry.dispose();
				ilMesh.material.dispose();
			}
		},
	};
} );
