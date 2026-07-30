/**
 * تصميم "لوحة/رقم" - لوحة معدنية مجسمة (extrude حقيقي) + حلقة مفتاح،
 * والرقم منقوش عليها بخط لاتيني/أرقام (TextGeometry) - الأرقام مالهاش
 * مشكلة تشكيل حروف زي العربي، فمعاينة 3D حقيقية بالكامل هنا. الشريط
 * الأزرق بـ"IL" على الطرف تقليد لشكل لوحة السيارة الإسرائيلية الحقيقية.
 */

import { registerDesign } from '../customizer.js';

const FONT_URL = 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/fonts/helvetiker_bold.typeface.json';
const FONT_LOADER_URL = 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/loaders/FontLoader.js';
const TEXT_GEOMETRY_URL = 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/geometries/TextGeometry.js';

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

function roundedRectShape( THREE, w, h, r ) {
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
	return shape;
}

registerDesign( 'plate', function ( { THREE, scene, value, colorHex } ) {
	const group = new THREE.Group();
	scene.add( group );

	const plateShape = roundedRectShape( THREE, 3.6, 1.05, 0.14 );
	const plateGeometry = new THREE.ExtrudeGeometry( plateShape, {
		depth: 0.14,
		bevelEnabled: true,
		bevelThickness: 0.02,
		bevelSize: 0.02,
		bevelSegments: 2,
	} );
	const plateMaterial = new THREE.MeshStandardMaterial( { color: colorHex || '#F5C518', roughness: 0.4, metalness: 0.12 } );
	const plateMesh = new THREE.Mesh( plateGeometry, plateMaterial );
	plateMesh.position.z = -0.09;
	group.add( plateMesh );

	const ringMaterial = new THREE.MeshStandardMaterial( { color: 0xc7c9c8, roughness: 0.25, metalness: 0.85 } );
	const ring = new THREE.Mesh( new THREE.TorusGeometry( 0.16, 0.032, 12, 32 ), ringMaterial );
	ring.position.set( -1.95, 0.5, 0 );
	group.add( ring );

	// شريط "IL" الأزرق - نفس مكانه على أي لوحة سيارة إسرائيلية حقيقية،
	// ثابت (مش بيتغير مع النص/اللون)، فبيتبني مرة واحدة بس.
	const chipWidth = 0.55;
	const chipHeight = 0.85;
	const chipCenterX = -1.4;
	const chipShape = roundedRectShape( THREE, chipWidth, chipHeight, 0.06 );
	const chipGeometry = new THREE.ExtrudeGeometry( chipShape, {
		depth: 0.05,
		bevelEnabled: false,
	} );
	const chipMaterial = new THREE.MeshStandardMaterial( { color: '#0057B8', roughness: 0.35, metalness: 0.1 } );
	const chipMesh = new THREE.Mesh( chipGeometry, chipMaterial );
	chipMesh.position.set( chipCenterX, 0, -0.02 );
	group.add( chipMesh );

	let chipLabelMesh = null;
	let chipLabelMaterial = null;
	const textOffsetX = 0.35;

	function buildChipLabel() {
		if ( ! fontCache || chipLabelMesh ) {
			return;
		}

		loadTextGeometry().then( ( { TextGeometry } ) => {
			const geometry = new TextGeometry( 'IL', {
				font: fontCache,
				size: 0.2,
				height: 0.05,
				curveSegments: 4,
				bevelEnabled: false,
			} );
			geometry.computeBoundingBox();
			geometry.center();

			chipLabelMaterial = new THREE.MeshStandardMaterial( { color: '#F3F5F1', roughness: 0.4, metalness: 0.05 } );
			chipLabelMesh = new THREE.Mesh( geometry, chipLabelMaterial );
			chipLabelMesh.position.set( chipCenterX, 0, 0.035 );
			group.add( chipLabelMesh );
		} );
	}

	let textMesh = null;
	let textMaterial = null;

	function rebuildText( text ) {
		if ( ! fontCache ) {
			return;
		}

		loadTextGeometry().then( ( { TextGeometry } ) => {
			if ( textMesh ) {
				group.remove( textMesh );
				textMesh.geometry.dispose();
			}

			const geometry = new TextGeometry( ( text || '' ).toString() || ' ', {
				font: fontCache,
				size: 0.4,
				height: 0.12,
				curveSegments: 6,
				bevelEnabled: false,
			} );
			geometry.computeBoundingBox();
			geometry.center();

			if ( ! textMaterial ) {
				textMaterial = new THREE.MeshStandardMaterial( { color: 0x16211f, roughness: 0.4, metalness: 0.18 } );
			}

			textMesh = new THREE.Mesh( geometry, textMaterial );
			textMesh.position.set( textOffsetX, 0, 0.05 );
			group.add( textMesh );
		} );
	}

	loadFont().then( () => {
		rebuildText( value );
		buildChipLabel();
	} );

	return {
		object3D: group,
		update( newValue, newColorHex ) {
			plateMaterial.color.set( newColorHex || '#F5C518' );

			if ( fontCache ) {
				rebuildText( newValue );
			} else {
				loadFont().then( () => rebuildText( newValue ) );
			}
		},
		dispose() {
			plateGeometry.dispose();
			plateMaterial.dispose();
			ringMaterial.dispose();
			ring.geometry.dispose();
			chipGeometry.dispose();
			chipMaterial.dispose();
			if ( chipLabelMesh ) {
				chipLabelMesh.geometry.dispose();
			}
			if ( chipLabelMaterial ) {
				chipLabelMaterial.dispose();
			}
			if ( textMesh ) {
				textMesh.geometry.dispose();
			}
			if ( textMaterial ) {
				textMaterial.dispose();
			}
		},
	};
} );
