<?php
/**
 * تجهيزات الثيم الأساسية: دعم الميزات، القوائم، تحميل الأصول.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function (): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	// ووكومرس
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( [
		'primary' => __( 'القائمة الرئيسية', 'k3d-shop' ),
		'footer'  => __( 'قائمة الفوتر', 'k3d-shop' ),
	] );

	set_post_thumbnail_size( 800, 800, true );
	add_image_size( 'k3d-product-card', 600, 600, true );
} );

add_action( 'wp_enqueue_scripts', function (): void {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'k3d-fonts',
		get_template_directory_uri() . '/assets/css/fonts.css',
		[],
		$theme_version
	);

	// اسم مختلف عن "k3d-shop-style" اللي إضافة k3d-shop.php القديمة بتستخدمه
	// لنفس الاسم لملف CSS مختلف تمامًا - لو الاتنين شغالين سوا، ووردبريس
	// بيسيب أول تسجيل بس ويتجاهل أي تسجيل تاني بنفس الـhandle، فستايل
	// الثيم الحقيقي كان مش بيتحمّل خالص على صفحات الووكومرس في السيناريو ده.
	wp_enqueue_style(
		'k3d-shop-theme-style',
		get_template_directory_uri() . '/assets/css/style.css',
		[ 'k3d-fonts' ],
		$theme_version
	);

	wp_enqueue_script(
		'k3d-shop-app',
		get_template_directory_uri() . '/assets/js/app.js',
		[ 'jquery' ],
		$theme_version,
		true
	);

	wp_localize_script( 'k3d-shop-app', 'K3D_SHOP', [
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'k3d_shop_social_login' ),
		'homeUrl'  => home_url( '/' ),
		'accountUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ),
		'firebase' => k3d_firebase_web_config(),
	] );

	// Firebase SDK + منطق تسجيل الدخول بجوجل/آبل - بس في صفحة "حسابي"، وبس
	// لو الإعدادات متظبطة (تجنّب تحميل SDK من غير داعي أو أخطاء JS لو مش مهيّأ).
	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() && k3d_firebase_web_configured() ) {
		wp_enqueue_script( 'firebase-app', 'https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js', [], '10.14.1', true );
		wp_enqueue_script( 'firebase-auth', 'https://www.gstatic.com/firebasejs/10.14.1/firebase-auth-compat.js', [ 'firebase-app' ], '10.14.1', true );

		wp_enqueue_script(
			'k3d-shop-social-login',
			get_template_directory_uri() . '/assets/js/social-login.js',
			[ 'firebase-auth', 'k3d-shop-app' ],
			$theme_version,
			true
		);
	}
} );

/**
 * إعدادات Firebase الخاصة بالمتصفح (Web App config) - لازم لتشغيل "تسجيل
 * الدخول بجوجل/آبل" على الموقع. دي مختلفة عن مفاتيح FCM (Server) اللي
 * مخزّنة في إضافة k3d-shop-api لإشعارات البوش - مفيش مكان في الإضافة
 * حاليًا لتخزين إعدادات الويب دي، فمؤقتًا بتتحط هنا أو في wp-config.php
 * كـ constant. لو مش متظبطة، أزرار جوجل/آبل بتختفي بدل ما تكسر الصفحة.
 *
 * فين تلاقي القيم دي: Firebase Console > Project Settings > عام > "Your apps"
 * > تطبيق الويب (لو مش موجود، ضيفه من هناك - نفس مشروع Firebase بتاع التطبيق).
 */
function k3d_firebase_web_config(): array {
	if ( defined( 'K3D_FIREBASE_WEB_CONFIG' ) && is_array( K3D_FIREBASE_WEB_CONFIG ) ) {
		return K3D_FIREBASE_WEB_CONFIG;
	}

	return [
		'apiKey'    => '',
		'authDomain'=> '',
		'projectId' => '',
		'appId'     => '',
	];
}

function k3d_firebase_web_configured(): bool {
	$config = k3d_firebase_web_config();

	return '' !== $config['apiKey'] && '' !== $config['appId'];
}
