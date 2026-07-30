<?php
/**
 * Plugin Name: K3D 3D Customizer
 * Plugin URI: https://k3d.shop
 * Description: معاينة 3D حية (Three.js) لتخصيص المنتجات - سلاسل مفاتيح بالاسم، لوحات/أرقام، وأي تصميم يتضاف لاحقًا. تفعيلها اختياري لكل منتج على حدة، وقابلة للتوسيع بتصاميم جديدة من غير ما تلمس كود الثيم. بتسجّل النص/اللون اللي العميل يختارهم كبيانات حقيقية على الطلب - من غير أي توليد ملفات طباعة تلقائي.
 * Version: 1.5.0
 * Author: K3D Shop
 * Text Domain: k3d-3d-customizer
 * Requires Plugins: woocommerce
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'K3D_3DC_VERSION', '1.5.0' );
define( 'K3D_3DC_DIR', plugin_dir_path( __FILE__ ) );
define( 'K3D_3DC_URL', plugin_dir_url( __FILE__ ) );

/**
 * سجل التصاميم قابل للتوسيع - أي تصميم جديد (مستقبلًا) بيتسجل عن طريق
 * فلتر k3d_3dc_designs من غير ما نلمس أي كود هنا، فبيكفي نضيف ملف JS
 * صغير يسجل نفسه في K3DCustomizer.registerDesign(...) + دخول في الفلتر
 * يوصف شكل التصميم (اللون الافتراضي، أقصى عدد حروف...) للوحة تحكم الأدمن.
 */
require K3D_3DC_DIR . 'includes/designs.php';
require K3D_3DC_DIR . 'includes/assets.php';
require K3D_3DC_DIR . 'includes/product-meta.php';
require K3D_3DC_DIR . 'includes/product-render.php';
require K3D_3DC_DIR . 'includes/cart.php';
require K3D_3DC_DIR . 'includes/shortcodes.php';
require K3D_3DC_DIR . 'includes/admin-menu.php';

add_action( 'admin_notices', function (): void {
	if ( class_exists( 'WooCommerce' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p>';
	esc_html_e( 'إضافة "K3D 3D Customizer" محتاجة WooCommerce مفعّلة عشان تشتغل.', 'k3d-3d-customizer' );
	echo '</p></div>';
} );
