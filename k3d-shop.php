<?php
/**
 * Plugin Name: K3D Shop Custom
 * Description: بلجن مخصص لموقع k3d.shop، متصل تلقائيًا عبر GitHub Deployments.
 * Version: 1.1.0
 * Author: freedomgames1985
 */

if (!defined('ABSPATH')) {
    exit;
}

// إضافة شريط بسيط في تذييل الموقع للتأكد من أن النشر شغال
add_action('wp_footer', function () {
    echo '<div style="text-align:center;padding:8px;font-size:12px;color:#888;">تم تحديث الموقع عبر GitHub — نسخة 1.1.0</div>';
});

/*
 * ألوان تصميم كروت المنتجات وصفحة المنتج.
 * عدّل القيم دي لو عايز تغيّر الألوان لاحقًا، من غير ما تلمس أي حاجة تانية.
 */
define('K3D_SHOP_PRIMARY_COLOR', '#111111'); // أسود - الأزرار والعناوين
define('K3D_SHOP_ACCENT_COLOR', '#b8860b');  // ذهبي - اسم تصنيف المنتج
define('K3D_SHOP_SALE_COLOR', '#d92d20');    // أحمر - باج الخصم
define('K3D_SHOP_NEW_COLOR', '#111111');     // أسود - باج "جديد"

// تحميل تنسيقات كروت المنتجات وصفحة المنتج، فقط في صفحات ووكومرس
add_action('wp_enqueue_scripts', function () {
    if (!function_exists('is_woocommerce')) {
        return;
    }
    if (!(is_shop() || is_product_category() || is_product_tag() || is_product())) {
        return;
    }

    wp_enqueue_style(
        'k3d-shop-style',
        plugins_url('assets/css/k3d-shop.css', __FILE__),
        [],
        '1.1.0'
    );

    $custom_vars = sprintf(
        ':root{--k3d-primary:%s;--k3d-accent:%s;--k3d-sale:%s;--k3d-new:%s;}',
        esc_html(K3D_SHOP_PRIMARY_COLOR),
        esc_html(K3D_SHOP_ACCENT_COLOR),
        esc_html(K3D_SHOP_SALE_COLOR),
        esc_html(K3D_SHOP_NEW_COLOR)
    );
    wp_add_inline_style('k3d-shop-style', $custom_vars);
});

// عرض اسم تصنيف المنتج فوق اسم المنتج في صفحة المتجر
add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if (!$product) {
        return;
    }
    $terms = get_the_terms($product->get_id(), 'product_cat');
    if ($terms && !is_wp_error($terms)) {
        $term = reset($terms);
        echo '<span class="k3d-product-cat">' . esc_html($term->name) . '</span>';
    }
}, 9);

// باج "جديد" على المنتجات المضافة خلال آخر 14 يوم
add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if (!$product) {
        return;
    }
    $created = $product->get_date_created();
    if ($created && (time() - $created->getTimestamp()) < (14 * DAY_IN_SECONDS)) {
        echo '<span class="k3d-badge k3d-badge--new">جديد</span>';
    }
}, 5);

// عرض باج الخصم كنسبة مئوية بدل كلمة "Sale" الافتراضية
add_filter('woocommerce_sale_flash', function ($html, $post, $product) {
    $regular_price = (float) $product->get_regular_price();
    $sale_price = (float) $product->get_sale_price();

    if ($regular_price > 0 && $sale_price > 0 && $sale_price < $regular_price) {
        $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
        return '<span class="onsale k3d-badge k3d-badge--sale">-' . $percentage . '%</span>';
    }

    return '<span class="onsale k3d-badge k3d-badge--sale">خصم</span>';
}, 10, 3);
