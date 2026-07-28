<?php
/**
 * K3D Shop theme bootstrap.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'K3D_SHOP_THEME_DIR', get_template_directory() );
define( 'K3D_SHOP_THEME_URI', get_template_directory_uri() );

require K3D_SHOP_THEME_DIR . '/inc/k3d-integration.php';
require K3D_SHOP_THEME_DIR . '/inc/i18n.php';
require K3D_SHOP_THEME_DIR . '/inc/setup.php';
require K3D_SHOP_THEME_DIR . '/inc/template-tags.php';
require K3D_SHOP_THEME_DIR . '/inc/home-sections.php';
require K3D_SHOP_THEME_DIR . '/inc/woocommerce.php';
require K3D_SHOP_THEME_DIR . '/inc/social-login.php';
require K3D_SHOP_THEME_DIR . '/inc/customizer.php';

/**
 * تنبيه واضح في wp-admin لو الإضافة مش شغالة - الثيم ده مصمم يشتغل معاها،
 * مش بديل عنها.
 */
add_action( 'admin_notices', function (): void {
	if ( k3d_plugin_active() ) {
		return;
	}

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p>';
	esc_html_e( 'قالب K3D Shop مصمم للعمل مع إضافة k3d-shop-api. الإضافة مش شغالة حاليًا، فبعض المحتوى (البانرات، تعدد اللغات، تسجيل الدخول بجوجل/آبل) هيستخدم قيم افتراضية بس.', 'k3d-shop' );
	echo '</p></div>';
} );
