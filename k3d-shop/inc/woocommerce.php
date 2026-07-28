<?php
/**
 * تكامل ووكومرس: بنشتغل مع الـhooks والفورمات الحقيقية بتاعته (مش بنعيد
 * كتابتها) عشان الـVariable Products، الـAJAX add-to-cart، وحساب السعر
 * حسب الاختيارات يفضلوا شغالين صح من غير ما نلمسهم - وبس نغيّر شكلهم
 * بالـCSS/قوالب الأوفررايد.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// هنستخدم تنسيقنا الخاص بدل ستايل شيت ووكومرس الافتراضي.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// نفس الراپر الافتراضي (مطلوب من ووكومرس)، بس بكلاس الـcontainer بتاعنا.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', function (): void {
	echo '<main class="container woocommerce-main">';
} );
add_action( 'woocommerce_after_main_content', function (): void {
	echo '</main>';
} );

// فتات الخبز الافتراضية بتاعة ووكومرس بنستخدم بدالها k3d_breadcrumb() جوه القوالب نفسها.
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

// السايد بار مش محتاجينه - القالب مبني كامل العرض مع فلاتر مخصصة في صفحة المتجر.
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_filter( 'loop_shop_columns', function (): int {
	return 3;
} );

add_filter( 'woocommerce_product_thumbnails_columns', function (): int {
	return 4;
} );

// شكل زرار "أضف للسلة" في صفحة المنتج - داخل .product-buy-row زي المعاينة.
add_filter( 'woocommerce_product_single_add_to_cart_button_html', function ( $html ) {
	return $html;
} );

// عدد المنتجات في صفحة المتجر - متسق مع باجيناشن 3 صفوف × 3 أعمدة.
add_filter( 'loop_shop_per_page', function () {
	return 9;
}, 20 );

// تبويب "معلومات الشحن" جنب الوصف في صفحة المنتج - نص قابل للتعديل من Customizer.
add_filter( 'woocommerce_product_tabs', function ( array $tabs ): array {
	$tabs['k3d_shipping_info'] = [
		'title'    => __( 'معلومات الشحن', 'k3d-shop' ),
		'priority' => 15,
		'callback' => function (): void {
			echo '<div class="k3d-shipping-info">' . wp_kses_post( wpautop( esc_html( k3d_shipping_info_text() ) ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		},
	];

	return $tabs;
} );
