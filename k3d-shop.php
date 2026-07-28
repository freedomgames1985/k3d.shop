<?php
/**
 * Plugin Name: K3D Shop Custom
 * Description: بلجن مخصص لموقع k3d.shop، متصل تلقائيًا عبر GitHub Deployments.
 * Version: 1.2.0
 * Author: freedomgames1985
 */

if (!defined('ABSPATH')) {
    exit;
}

// إضافة شريط بسيط في تذييل الموقع للتأكد من أن النشر شغال
add_action('wp_footer', function () {
    echo '<div style="text-align:center;padding:8px;font-size:12px;color:#888;">تم تحديث الموقع عبر GitHub — نسخة 1.2.0</div>';
});

/*
 * ألوان تصميم كروت المنتجات وصفحة المنتج والصفحة الرئيسية.
 * القيم دي مأخوذة من هوية الموقع الفعلية (الأزرار الذهبية). عدّلها هنا
 * لو حبيت تغيّر الألوان لاحقًا، من غير ما تلمس أي حاجة تانية.
 */
define('K3D_SHOP_BUTTON_COLOR', '#f5c842');       // ذهبي - خلفية الأزرار
define('K3D_SHOP_BUTTON_HOVER_COLOR', '#e5b832'); // ذهبي غامق - الأزرار عند الهوفر
define('K3D_SHOP_BUTTON_TEXT_COLOR', '#333333');  // نص الأزرار
define('K3D_SHOP_ACCENT_COLOR', '#e0ad3e');       // ذهبي غامق - اسم تصنيف المنتج
define('K3D_SHOP_SALE_COLOR', '#d92d20');         // أحمر - باج الخصم
define('K3D_SHOP_NEW_COLOR', '#333333');          // رمادي غامق - باج "جديد"

/**
 * الصفحة الحالية هي الصفحة الرئيسية المبنية بقالب K3D؟
 */
function k3d_is_home_template() {
    return is_page() && 'k3d-home-template.php' === get_page_template_slug(get_queried_object_id());
}

// تحميل تنسيقات كروت المنتجات وصفحة المنتج، في صفحات ووكومرس وفي الصفحة الرئيسية
add_action('wp_enqueue_scripts', function () {
    $is_woo_page = function_exists('is_woocommerce')
        && (is_shop() || is_product_category() || is_product_tag() || is_product());

    if (!$is_woo_page && !k3d_is_home_template()) {
        return;
    }

    wp_enqueue_style(
        'k3d-shop-style',
        plugins_url('assets/css/k3d-shop.css', __FILE__),
        [],
        '1.2.0'
    );

    wp_add_inline_style('k3d-shop-style', k3d_shop_color_vars());
});

/**
 * متغيرات CSS الخاصة بألوان الموقع، مستخدمة في أكتر من مكان.
 */
function k3d_shop_color_vars() {
    return sprintf(
        ':root{--k3d-btn-bg:%1$s;--k3d-btn-bg-hover:%2$s;--k3d-btn-text:%3$s;--k3d-accent:%4$s;--k3d-sale:%5$s;--k3d-new:%6$s;--k3d-primary:%3$s;}',
        esc_html(K3D_SHOP_BUTTON_COLOR),
        esc_html(K3D_SHOP_BUTTON_HOVER_COLOR),
        esc_html(K3D_SHOP_BUTTON_TEXT_COLOR),
        esc_html(K3D_SHOP_ACCENT_COLOR),
        esc_html(K3D_SHOP_SALE_COLOR),
        esc_html(K3D_SHOP_NEW_COLOR)
    );
}

/*
 * قالب صفحة رئيسية مستقل عن الثيم، عشان يفضل شغال حتى لو اتغيّر الثيم.
 */
add_filter('theme_page_templates', function ($templates) {
    $templates['k3d-home-template.php'] = 'الصفحة الرئيسية - K3D';
    return $templates;
});

add_filter('template_include', function ($template) {
    if (k3d_is_home_template()) {
        $custom = plugin_dir_path(__FILE__) . 'templates/k3d-home-template.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
});

add_action('wp_enqueue_scripts', function () {
    if (!k3d_is_home_template()) {
        return;
    }

    // k3d-shop-style بتديها شكل الكروت (بتتحمل فوق من نفس الفانكشن).
    wp_enqueue_style('k3d-home-style', plugins_url('assets/css/k3d-home.css', __FILE__), ['k3d-shop-style'], '1.2.0');
    wp_enqueue_script('k3d-home-script', plugins_url('assets/js/k3d-home.js', __FILE__), [], '1.2.0', true);
}, 20);

// إضافة كلاس على الـ body للصفحة الرئيسية عشان نقدر نخفي عناصر الثيم (عنوان/فتات) بأمان
add_filter('body_class', function ($classes) {
    if (k3d_is_home_template()) {
        $classes[] = 'k3d-home-page';
    }
    return $classes;
});

// رقم الواتساب اللي بيتحط بدل رقم الهاتف في الهيدر. عدّله هنا لو اتغيّر لاحقًا.
define('K3D_SHOP_WHATSAPP_NUMBER', '972586050540');
define('K3D_SHOP_WHATSAPP_DISPLAY', '+972 58 605 0540');

// إخفاء زرار "تصفح التصنيفات" البرتقالي في الهيدر، في كل الصفحات
add_action('wp_head', function () {
    ?>
    <style>
    .header--seven .product-categories,
    .header--seven .product-categories-btn {
        display: none !important;
    }
    .header-search-form,
    .header-search-form input.header-search-input {
        direction: rtl;
        text-align: right;
    }
    </style>
    <?php
}, 100);

// إخفاء سطر "Powered by WP Fable"، وتعبئة مكان الودجت الفاضي في الشريط العلوي
// بعرض شحن، وتحويل رقم الهاتف في الهيدر لرابط واتساب - في كل الصفحات
add_action('wp_footer', function () {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1) إخفاء "Powered by WP Fable"
        document.querySelectorAll('a[href*="wpfable.com"]').forEach(function (link) {
            var line = link.closest('p, div, span, li') || link;
            line.style.display = 'none';
        });

        // 2) تعبئة مكان الودجت الفاضي في الشريط العلوي بعرض شحن
        document.querySelectorAll('.wf_header-topbar .widget.widget_none').forEach(function (el) {
            el.innerHTML = '🚚 شحن مجاني للضفة فوق 300 شيكل، القدس فوق 400 شيكل، مناطق 48 فوق 500 شيكل';
        });

        // 3) تحويل رقم الهاتف في الهيدر (بس) لرابط واتساب
        document.querySelectorAll('.wf_navbar-info-contact a[href^="tel:"]').forEach(function (link) {
            link.setAttribute('href', 'https://wa.me/<?php echo esc_js(K3D_SHOP_WHATSAPP_NUMBER); ?>');
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener');

            var walker = document.createTreeWalker(link, NodeFilter.SHOW_TEXT);
            var node;
            while ((node = walker.nextNode())) {
                if (node.nodeValue.trim().length > 0) {
                    node.nodeValue = '<?php echo esc_js(K3D_SHOP_WHATSAPP_DISPLAY); ?>';
                }
            }
        });
    });
    </script>
    <?php
}, 100);

/*
 * نموذج الاشتراك بالنشرة البريدية في الصفحة الرئيسية.
 */
function k3d_handle_newsletter_subscribe() {
    $redirect = wp_get_referer() ?: home_url('/');

    if (
        !isset($_POST['k3d_newsletter_nonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['k3d_newsletter_nonce'])), 'k3d_newsletter')
    ) {
        wp_safe_redirect(add_query_arg('k3d_subscribed', '0', $redirect));
        exit;
    }

    $email = isset($_POST['k3d_email']) ? sanitize_email(wp_unslash($_POST['k3d_email'])) : '';

    if ($email && is_email($email)) {
        $subscribers = get_option('k3d_newsletter_subscribers', []);
        if (!in_array($email, $subscribers, true)) {
            $subscribers[] = $email;
            update_option('k3d_newsletter_subscribers', $subscribers);
        }
        wp_safe_redirect(add_query_arg('k3d_subscribed', '1', $redirect));
    } else {
        wp_safe_redirect(add_query_arg('k3d_subscribed', '0', $redirect));
    }
    exit;
}
add_action('admin_post_k3d_newsletter_subscribe', 'k3d_handle_newsletter_subscribe');
add_action('admin_post_nopriv_k3d_newsletter_subscribe', 'k3d_handle_newsletter_subscribe');

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
