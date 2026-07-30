/**
 * تصميم "اسم بثلاث طبقات" - نفس فكرة name.js (canvas texture عشان تشكيل
 * الحروف العربي/العبري يفضل صح) لكن بدل طبقة واحدة، بترسم نفس النص 3
 * مرات على 3 مستويات ارتفاع مختلفة، كل طبقة بخط محيطي (stroke) أعرض من
 * اللي فوقها - فبيبان حد ملوّن حوالين الحروف زي القطع المطبوعة بألوان
 * متعددة (الطبقة السفلية الغامقة هي الأوسع، والعلوية الفاتحة هي الأضيق).
 */

import { registerDesign } from '../customizer.js';

function shade( hex, amount ) {
	const clean = ( hex || '#38B3FF' ).replace( '#', '' );
	const full = clean.length === 3 ? clean.split( '' ).map( ( c ) => c + c ).join( '' ) : clean;
	const num = parseInt( full, 16 ) || 0;

	const clamp = ( v ) => Math.max( 0, Math.min( 255, v ) );
	const r = clamp( ( num >> 16 ) + amount );
	const g = clamp( ( ( num >> 8 ) & 0xff ) + amount );
	const b = clamp( ( num & 0xff ) + amount );

	return '#' + ( ( 1 << 24 ) + ( r << 16 ) + ( g << 8 ) + b ).toString( 16 ).slice( 1 );
}

const LAYERS = [
	{ z: 0, strokeExtra: 24, shadeAmount: -75 },
	{ z: 0.045, strokeExtra: 12, shadeAmount: 0 },
	{ z: 0.09, strokeExtra: 0, shadeAmount: 70 },
];

registerDesign( 'name_layers', function ( { THREE, scene, value, colorHex } ) {
	const group = new THREE.Group();
	scene.add( group );

	const baseHex = colorHex || '#38B3FF';
	const layers = LAYERS.map( ( def ) => {
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
			transparent: true,
			alphaTest: 0.35,
			roughness: 0.35,
			metalness: 0.08,
		} );

		const plane = new THREE.Mesh( new THREE.PlaneGeometry( 4.6, 1.7 ), material );
		plane.position.z = def.z;
		group.add( plane );

		return { def, canvas, ctx, texture, material, plane };
	} );

	function drawLayer( layer, text, hex ) {
		const { canvas, ctx } = layer;
		const t = ( text || '' ).toString();
		ctx.clearRect( 0, 0, canvas.width, canvas.height );
		ctx.save();
		ctx.translate( canvas.width / 2, canvas.height / 2 );
		ctx.direction = 'rtl';
		ctx.textAlign = 'center';
		ctx.textBaseline = 'middle';

		let fontSize = 220;
		ctx.font = `700 ${ fontSize }px "IBM Plex Sans Arabic","IBM Plex Sans Hebrew","IBM Plex Sans",sans-serif`;
		const maxWidth = canvas.width * 0.78;
		const measured = ctx.measureText( t || ' ' ).width;

		if ( measured > maxWidth ) {
			fontSize = Math.max( 60, Math.floor( fontSize * ( maxWidth / measured ) ) );
			ctx.font = `700 ${ fontSize }px "IBM Plex Sans Arabic","IBM Plex Sans Hebrew","IBM Plex Sans",sans-serif`;
		}

		ctx.fillStyle = hex;
		ctx.strokeStyle = hex;

		if ( layer.def.strokeExtra > 0 ) {
			ctx.lineJoin = 'round';
			ctx.lineWidth = layer.def.strokeExtra;
			ctx.strokeText( t, 0, 4 );
		}

		ctx.fillText( t, 0, 4 );
		ctx.restore();
		layer.texture.needsUpdate = true;
	}

	function drawAll( text, hex ) {
		layers.forEach( ( layer ) => drawLayer( layer, text, shade( hex, layer.def.shadeAmount ) ) );
	}

	drawAll( value, baseHex );

	return {
		object3D: group,
		update( newValue, newColorHex ) {
			drawAll( newValue, newColorHex || baseHex );
		},
		dispose() {
			layers.forEach( ( layer ) => {
				layer.texture.dispose();
				layer.material.dispose();
				layer.plane.geometry.dispose();
			} );
		},
	};
} );
