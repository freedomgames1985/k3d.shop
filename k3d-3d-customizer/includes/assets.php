<?php
/**
 * تحميل أصول المعاينة 3D - Three.js (كـES module من jsdelivr، زي أي
 * موقع حديث بيستخدمه من غير build step) + محرك العرض بتاعنا + وحدات
 * التصاميم. بيتحملوا بس في الصفحات اللي محتاجاها فعلاً: صفحة منتج
 * مفعّل عليه المعاينة، أو الصفحة الرئيسية (الهيرو التفاعلي).
 *
 * لازم يتحدد أثناء wp_enqueue_scripts نفسه (مش بعدها) عشان ملف الـCSS
 * يتطبع في <head> صح - ده سبب إن الفحص هنا مش في مكان الرندر نفسه.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function (): void {
	$needed = is_front_page()
		|| ( function_exists( 'is_product' ) && is_product() && k3d_3dc_product_enabled( get_queried_object_id() ) )
		|| ( is_singular() && is_a( get_post(), 'WP_Post' ) && has_shortcode( get_post()->post_content, 'k3d_3dc_hero' ) );

	if ( ! apply_filters( 'k3d_3dc_assets_needed', $needed ) ) {
		return;
	}

	wp_enqueue_style(
		'k3d-3dc-style',
		K3D_3DC_URL . 'assets/css/customizer.css',
		[],
		K3D_3DC_VERSION
	);

	wp_enqueue_script(
		'k3d-3dc-init',
		K3D_3DC_URL . 'assets/js/init.js',
		[],
		K3D_3DC_VERSION,
		true
	);

	wp_localize_script( 'k3d-3dc-init', 'K3D_3DC_CONFIG', [
		'threeUrl'         => 'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js',
		'orbitControlsUrl' => 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/controls/OrbitControls.js',
		'moduleBaseUrl'    => K3D_3DC_URL . 'assets/js/',
		'designs'          => k3d_3dc_get_designs(),
		'i18n'             => [
			'rotateHint' => __( 'اسحب للتدوير · عجلة الماوس للتكبير', 'k3d-3d-customizer' ),
			'liveBadge'  => __( 'معاينة حية', 'k3d-3d-customizer' ),
			'loading'    => __( 'جاري تجهيز المعاينة...', 'k3d-3d-customizer' ),
			'colorLabel' => __( 'اللون', 'k3d-3d-customizer' ),
		],
	] );
} );

/** موديولات الـJS (init.js وكل حاجة بتستوردها) لازم تتحمّل كـES module عشان الـimport/export يشتغلوا. */
add_filter( 'script_loader_tag', function ( string $tag, string $handle ): string {
	if ( 'k3d-3dc-init' !== $handle || str_contains( $tag, ' type=' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' type="module" src=', $tag );
}, 10, 2 );
