<?php
/**
 * دعم تعدد اللغات (RTL/LTR + اختيار عائلة الخط حسب السكريبت). القالب بيحل
 * اللغة الحالية بنفس منطق StorefrontTranslation::resolved_lang() في
 * k3d-shop-api (؟lang= ثم كوكيز k3d_lang)، لأن الدالة الأصلية private ومش
 * متاحة من برّه الإضافة - لكن بيستخدم نفس اسم الكوكيز الحقيقي
 * (StorefrontTranslation::COOKIE_NAME) عشان يفضلوا متزامنين مع بعض دايمًا.
 *
 * ده الفرق الأساسي عن حل الـCSS المؤقت اللي كان في بلجن k3d-shop: هنا
 * القالب بيتحكم في header.php نفسه من الأول (dir على <html> مباشرة)، مش
 * بيحاول يغلب ثيم تالت بـ !important.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * نفس الـ13 لغة اللي StorefrontTranslation::LANGUAGE_DIRECTORY بتدعمها في
 * الإضافة، بنفس الترتيب اللي resolved_lang()/render_language_switcher()
 * بيرجعوه (en, ar, he الأول، بعدين الباقي).
 */
function k3d_language_directory(): array {
	return [
		'en' => [ 'name' => 'English', 'flag' => '🇬🇧', 'dir' => 'ltr', 'script' => 'latin' ],
		'ar' => [ 'name' => 'العربية', 'flag' => '🇸🇦', 'dir' => 'rtl', 'script' => 'arabic' ],
		'he' => [ 'name' => 'עברית', 'flag' => '🇮🇱', 'dir' => 'rtl', 'script' => 'hebrew' ],
		'es' => [ 'name' => 'Español', 'flag' => '🇪🇸', 'dir' => 'ltr', 'script' => 'latin' ],
		'de' => [ 'name' => 'Deutsch', 'flag' => '🇩🇪', 'dir' => 'ltr', 'script' => 'latin' ],
		'it' => [ 'name' => 'Italiano', 'flag' => '🇮🇹', 'dir' => 'ltr', 'script' => 'latin' ],
		'fr' => [ 'name' => 'Français', 'flag' => '🇫🇷', 'dir' => 'ltr', 'script' => 'latin' ],
		'ru' => [ 'name' => 'Русский', 'flag' => '🇷🇺', 'dir' => 'ltr', 'script' => 'latin' ],
		'uk' => [ 'name' => 'Українська', 'flag' => '🇺🇦', 'dir' => 'ltr', 'script' => 'latin' ],
		'pl' => [ 'name' => 'Polski', 'flag' => '🇵🇱', 'dir' => 'ltr', 'script' => 'latin' ],
		'hu' => [ 'name' => 'Magyar', 'flag' => '🇭🇺', 'dir' => 'ltr', 'script' => 'latin' ],
		'nl' => [ 'name' => 'Nederlands', 'flag' => '🇳🇱', 'dir' => 'ltr', 'script' => 'latin' ],
		'hi' => [ 'name' => 'हिन्दी', 'flag' => '🇮🇳', 'dir' => 'ltr', 'script' => 'devanagari' ],
	];
}

/** اسم كوكيز اللغة - نفس StorefrontTranslation::COOKIE_NAME بالظبط لو الإضافة موجودة. */
function k3d_lang_cookie_name(): string {
	if ( class_exists( '\K3D\ShopAPI\Frontend\StorefrontTranslation' ) ) {
		return \K3D\ShopAPI\Frontend\StorefrontTranslation::COOKIE_NAME;
	}

	return 'k3d_lang';
}

/**
 * اللغة الحالية المفعّلة - بنفس أولوية resolved_lang() في الإضافة:
 * ?lang= أولاً، بعدين كوكيز k3d_lang، وبعدين الافتراضي (عربي).
 */
function k3d_current_lang(): string {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$directory = k3d_language_directory();
	$cookie    = k3d_lang_cookie_name();

	$lang = '';

	if ( ! empty( $_GET['lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$lang = sanitize_key( wp_unslash( $_GET['lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	} elseif ( ! empty( $_COOKIE[ $cookie ] ) ) {
		$lang = sanitize_key( wp_unslash( $_COOKIE[ $cookie ] ) );
	}

	if ( '' === $lang || ! isset( $directory[ $lang ] ) ) {
		$lang = 'ar';
	}

	return $cache = $lang;
}

function k3d_current_lang_meta(): array {
	$directory = k3d_language_directory();

	return $directory[ k3d_current_lang() ] ?? $directory['ar'];
}

function k3d_is_rtl(): bool {
	return 'rtl' === k3d_current_lang_meta()['dir'];
}

/** يطابق dir="rtl"/"ltr" على <html> مع اللغة الفعلية - بدل ما يفضل ثابت على لغة الموقع الأساسية. */
add_filter( 'language_attributes', function ( string $output ): string {
	$meta = k3d_current_lang_meta();

	if ( preg_match( '/dir="(rtl|ltr)"/', $output ) ) {
		$output = preg_replace( '/dir="(rtl|ltr)"/', 'dir="' . esc_attr( $meta['dir'] ) . '"', $output );
	} else {
		$output .= ' dir="' . esc_attr( $meta['dir'] ) . '"';
	}

	return $output . ' data-lang-script="' . esc_attr( $meta['script'] ) . '"';
} );

add_filter( 'body_class', function ( array $classes ): array {
	$classes   = array_diff( $classes, [ 'rtl', 'ltr' ] );
	$classes[] = k3d_is_rtl() ? 'rtl' : 'ltr';

	return $classes;
} );
