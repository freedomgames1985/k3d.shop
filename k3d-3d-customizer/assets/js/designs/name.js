/**
 * تصميم "اسم مجسم" - النص بيترسم على canvas 2D (المتصفح نفسه بيتولى
 * تشكيل الحروف العربية صح، بعكس محاولة عمل extrude لكل حرف لوحده جوه
 * Three.js اللي بتكسر شكل الحروف المتصلة)، وبعدين الرسمة دي بتتحط
 * كـtexture + bump map على سطح 3D عشان تحس إن النص بارز فوق سطح مضيء،
 * مع ظل بسيط تحته.
 */

import { registerDesign } from '../customizer.js';

registerDesign( 'name', function ( { THREE, scene, value, colorHex } ) {
	const group = new THREE.Group();
	scene.add( group );

	const canvas = document.createElement( 'canvas' );
	canvas.width = 1024;
	canvas.height = 384;
	const ctx = canvas.getContext( '2d' );
	const texture = new THREE.CanvasTexture( canvas );
	if ( 'colorSpace' in texture ) {
		texture.colorSpace = THREE.SRGBColorSpace;
	}

	const material = new THREE.MeshStandardMaterial( {
		map: texture,
		bumpMap: texture,
		bumpScale: 0.05,
		transparent: true,
		roughness: 0.32,
		metalness: 0.1,
	} );

	const plane = new THREE.Mesh( new THREE.PlaneGeometry( 4.6, 1.7 ), material );
	group.add( plane );

	const shadowCanvas = document.createElement( 'canvas' );
	shadowCanvas.width = 256;
	shadowCanvas.height = 128;
	const sctx = shadowCanvas.getContext( '2d' );
	const grad = sctx.createRadialGradient( 128, 64, 4, 128, 64, 122 );
	grad.addColorStop( 0, 'rgba(0,0,0,0.32)' );
	grad.addColorStop( 1, 'rgba(0,0,0,0)' );
	sctx.fillStyle = grad;
	sctx.fillRect( 0, 0, 256, 128 );
	const shadowTexture = new THREE.CanvasTexture( shadowCanvas );
	const shadowMaterial = new THREE.MeshBasicMaterial( { map: shadowTexture, transparent: true, depthWrite: false } );
	const shadowMesh = new THREE.Mesh( new THREE.PlaneGeometry( 4.2, 1.6 ), shadowMaterial );
	shadowMesh.rotation.x = -Math.PI / 2;
	shadowMesh.position.y = -1.05;
	group.add( shadowMesh );

	function draw( text, hex ) {
		const t = ( text || '' ).toString();
		ctx.clearRect( 0, 0, canvas.width, canvas.height );
		ctx.save();
		ctx.translate( canvas.width / 2, canvas.height / 2 );
		ctx.direction = 'rtl';
		ctx.textAlign = 'center';
		ctx.textBaseline = 'middle';

		let fontSize = 220;
		ctx.font = `700 ${ fontSize }px "IBM Plex Sans Arabic","IBM Plex Sans Hebrew","IBM Plex Sans",sans-serif`;
		const maxWidth = canvas.width * 0.86;
		const measured = ctx.measureText( t || ' ' ).width;

		if ( measured > maxWidth ) {
			fontSize = Math.max( 60, Math.floor( fontSize * ( maxWidth / measured ) ) );
			ctx.font = `700 ${ fontSize }px "IBM Plex Sans Arabic","IBM Plex Sans Hebrew","IBM Plex Sans",sans-serif`;
		}

		ctx.fillStyle = hex || '#D9583A';
		ctx.shadowColor = 'rgba(0,0,0,0.35)';
		ctx.shadowBlur = 14;
		ctx.shadowOffsetY = 10;
		ctx.fillText( t, 0, 6 );
		ctx.restore();
		texture.needsUpdate = true;
	}

	draw( value, colorHex );

	return {
		object3D: group,
		update( newValue, newColorHex ) {
			draw( newValue, newColorHex );
		},
		dispose() {
			texture.dispose();
			shadowTexture.dispose();
			material.dispose();
			shadowMaterial.dispose();
			plane.geometry.dispose();
			shadowMesh.geometry.dispose();
		},
	};
} );
