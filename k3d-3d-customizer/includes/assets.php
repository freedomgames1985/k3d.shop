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

const K3D_3DC_THREE_VERSION = '0.160.0';

function k3d_3dc_assets_needed(): bool {
	$needed = is_front_page()
		|| ( function_exists( 'is_product' ) && is_product() && k3d_3dc_product_enabled( get_queried_object_id() ) )
		|| ( is_singular() && is_a( get_post(), 'WP_Post' ) && has_shortcode( get_post()->post_content, 'k3d_3dc_hero' ) );

	return (bool) apply_filters( 'k3d_3dc_assets_needed', $needed );
}

add_action( 'wp_enqueue_scripts', function (): void {
	if ( ! k3d_3dc_assets_needed() ) {
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
		'threeUrl'         => 'https://cdn.jsdelivr.net/npm/three@' . K3D_3DC_THREE_VERSION . '/build/three.module.js',
		'orbitControlsUrl' => 'https://cdn.jsdelivr.net/npm/three@' . K3D_3DC_THREE_VERSION . '/examples/jsm/controls/OrbitControls.js',
		'moduleBaseUrl'    => K3D_3DC_URL . 'assets/js/',
		'designs'          => k3d_3dc_get_designs(),
		'i18n'             => [
			'rotateHint'     => __( 'اسحب للتدوير · عجلة الماوس للتكبير', 'k3d-3d-customizer' ),
			'liveBadge'      => __( 'معاينة حية', 'k3d-3d-customizer' ),
			'loading'        => __( 'جاري تجهيز المعاينة...', 'k3d-3d-customizer' ),
			'colorLabel'     => __( 'اللون', 'k3d-3d-customizer' ),
			'previewFailed'  => __( 'تعذّر تحميل المعاينة الحية 3D. تقدر تكمل بياناتك والطلب عادي.', 'k3d-3d-customizer' ),
		],
	] );
} );

/**
 * ملف OrbitControls.js نفسه بيعمل `import ... from 'three'` (specifier
 * مجرد/bare) جوّاه - المتصفح مش بيعرف يحلّه من غير import map، فالـ
 * import() الديناميكي في init.js كان بيفشل دايمًا (بغض النظر عن توقيت
 * التحميل) وبيطلع الودجت كله مختفي. خريطة الاستيراد دي بتخلي 'three'
 * تتحل لنفس نسخة الـCDN اللي بنحمّلها فعلاً.
 */
add_action( 'wp_head', function (): void {
	if ( ! k3d_3dc_assets_needed() ) {
		return;
	}
	?>
	<script type="importmap">{"imports":{"three":"https://cdn.jsdelivr.net/npm/three@<?php echo esc_js( K3D_3DC_THREE_VERSION ); ?>/build/three.module.js"}}</script>
	<?php
}, 1 );

/** موديولات الـJS (init.js وكل حاجة بتستوردها) لازم تتحمّل كـES module عشان الـimport/export يشتغلوا. */
add_filter( 'script_loader_tag', function ( string $tag, string $handle ): string {
	if ( 'k3d-3dc-init' !== $handle || str_contains( $tag, ' type=' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' type="module" src=', $tag );
}, 10, 2 );
