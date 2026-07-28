<?php
/**
 * إعدادات بسيطة عبر مخصص المظهر (Customizer) للحاجات اللي مفيش لها مكان
 * في إضافة k3d-shop-api (زي شريط الشحن العلوي) - عشان تتعدل من غير كود.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section( 'k3d_shop_announcement', [
		'title'    => __( 'شريط الإعلان العلوي', 'k3d-shop' ),
		'priority' => 30,
	] );

	$wp_customize->add_setting( 'k3d_announcement_text', [
		'default'           => __( 'شحن مجاني — الضفة فوق ₪300 · القدس فوق ₪400 · مناطق 48 فوق ₪500', 'k3d-shop' ),
		'sanitize_callback' => 'sanitize_text_field',
	] );

	$wp_customize->add_control( 'k3d_announcement_text', [
		'label'   => __( 'نص شريط الإعلان (فوق الهيدر مباشرة)', 'k3d-shop' ),
		'section' => 'k3d_shop_announcement',
		'type'    => 'text',
	] );

	$wp_customize->add_section( 'k3d_shop_shipping_info', [
		'title'    => __( 'تبويب معلومات الشحن (صفحة المنتج)', 'k3d-shop' ),
		'priority' => 31,
	] );

	$wp_customize->add_setting( 'k3d_shipping_info_text', [
		'default'           => __( 'نحن هنا لتقديم أفضل خدمة لكم ❤️', 'k3d-shop' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	] );

	$wp_customize->add_control( 'k3d_shipping_info_text', [
		'label'   => __( 'ملاحظة إضافية أسفل تبويب "معلومات الشحن" في صفحة المنتج', 'k3d-shop' ),
		'section' => 'k3d_shop_shipping_info',
		'type'    => 'textarea',
	] );

	$wp_customize->add_section( 'k3d_shop_sale_countdown', [
		'title'    => __( 'عدّاد العروض والتخفيضات', 'k3d-shop' ),
		'priority' => 32,
	] );

	$wp_customize->add_setting( 'k3d_sale_countdown_enabled', [
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	] );

	$wp_customize->add_control( 'k3d_sale_countdown_enabled', [
		'label'   => __( 'إظهار عدّاد تنازلي في صفحة المنتج لو عليه تخفيض', 'k3d-shop' ),
		'section' => 'k3d_shop_sale_countdown',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'k3d_sale_countdown_days', [
		'default'           => 7,
		'sanitize_callback' => function ( $value ): int {
			return min( 90, max( 1, absint( $value ) ) );
		},
	] );

	$wp_customize->add_control( 'k3d_sale_countdown_days', [
		'label'       => __( 'مدة العدّاد الافتراضية (بالأيام) - لو المنتج مفيهوش تاريخ انتهاء عرض محدد في ووكومرس', 'k3d-shop' ),
		'description' => __( 'التغيير هنا بيأثر بس على العروض اللي هتتظبط جديدة أو اللي عدّادها خلص فعلاً - مش العروض اللي عدّادها شغال دلوقتي.', 'k3d-shop' ),
		'section'     => 'k3d_shop_sale_countdown',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 1, 'max' => 90, 'step' => 1 ],
	] );

	$wp_customize->add_section( 'k3d_shop_homepage_effects', [
		'title'    => __( 'تأثيرات الصفحة الرئيسية', 'k3d-shop' ),
		'priority' => 33,
	] );

	$wp_customize->add_setting( 'k3d_home_animations_enabled', [
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	] );

	$wp_customize->add_control( 'k3d_home_animations_enabled', [
		'label'   => __( 'تفعيل حركات الظهور التدريجي والميدالية المتحركة في الصفحة الرئيسية', 'k3d-shop' ),
		'section' => 'k3d_shop_homepage_effects',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_section( 'k3d_shop_products_page', [
		'title'    => __( 'صفحة المنتجات', 'k3d-shop' ),
		'priority' => 34,
	] );

	$wp_customize->add_setting( 'k3d_shop_infinite_scroll_enabled', [
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	] );

	$wp_customize->add_control( 'k3d_shop_infinite_scroll_enabled', [
		'label'       => __( 'تحميل المنتجات تلقائيًا عند النزول لآخر الصفحة', 'k3d-shop' ),
		'description' => __( 'لو متوقف، هيرجع ترقيم الصفحات العادي (الصفحة التالية).', 'k3d-shop' ),
		'section'     => 'k3d_shop_products_page',
		'type'        => 'checkbox',
	] );
} );

/** كلاس على body لما حركات الصفحة الرئيسية موقوفة من Customizer، عشان الـCSS يقدر يوقفها بدل ما نكرر شرط PHP في كل مكان. */
add_filter( 'body_class', function ( array $classes ): array {
	if ( ! get_theme_mod( 'k3d_home_animations_enabled', true ) ) {
		$classes[] = 'k3d-no-animations';
	}

	return $classes;
} );

function k3d_announcement_text(): string {
	return get_theme_mod( 'k3d_announcement_text', __( 'شحن مجاني — الضفة فوق ₪300 · القدس فوق ₪400 · مناطق 48 فوق ₪500', 'k3d-shop' ) );
}

function k3d_shipping_info_text(): string {
	return get_theme_mod( 'k3d_shipping_info_text', __( 'نحن هنا لتقديم أفضل خدمة لكم ❤️', 'k3d-shop' ) );
}
