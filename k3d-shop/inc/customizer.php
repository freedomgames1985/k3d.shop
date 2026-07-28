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
} );

function k3d_announcement_text(): string {
	return get_theme_mod( 'k3d_announcement_text', __( 'شحن مجاني — الضفة فوق ₪300 · القدس فوق ₪400 · مناطق 48 فوق ₪500', 'k3d-shop' ) );
}

function k3d_shipping_info_text(): string {
	return get_theme_mod( 'k3d_shipping_info_text', __( 'نحن هنا لتقديم أفضل خدمة لكم ❤️', 'k3d-shop' ) );
}
