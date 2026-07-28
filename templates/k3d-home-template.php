<?php
/**
 * Template Name: الصفحة الرئيسية - K3D
 *
 * قالب صفحة رئيسية مستقل عن الثيم النشط، عشان يفضل شغال حتى لو
 * اتغيّر الثيم في المستقبل.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$k3d_category_cards = [
    [
        'title'    => 'عروض 🎁',
        'subtitle' => 'اكتشف العروض الخاصة',
        'image'    => 'https://k3d.shop/wp-content/uploads/2025/11/ChatGPT-Image-Nov-12-2025-12_36_20-AM.webp',
        'url'      => 'https://k3d.shop/product-category/%d8%b9%d8%b1%d9%88%d8%b6-%d8%ae%d8%a7%d8%b5%d8%a9-%d8%a7%d9%84%d8%aa%d8%ae%d9%81%d9%8a%d8%b6%d8%a7%d8%aa/',
    ],
    [
        'title'    => 'الأكثر مبيعًا 🔥',
        'subtitle' => 'المنتجات الأكثر طلباً',
        'image'    => 'https://k3d.shop/wp-content/uploads/2025/11/ChatGPT-Image-Nov-12-2025-12_56_34-AM.webp',
        'url'      => 'https://k3d.shop/product-category/%d9%87%d8%af%d8%a7%d9%8a%d8%a7-%d9%88-%d8%aa%d9%88%d8%b2%d9%8a%d8%b9%d8%a7%d8%aa/',
    ],
    [
        'title'    => 'جديدنا ✨',
        'subtitle' => 'آخر الإضافات الجديدة',
        'image'    => 'https://k3d.shop/wp-content/uploads/2025/11/ChatGPT-Image-Nov-11-2025-11_51_37-PM.webp',
        'url'      => 'https://k3d.shop/product-category/%d8%ac%d8%af%d9%8a%d8%af%d9%86%d8%a7/',
    ],
];

$k3d_shop_by_category = [
    [
        'title'   => '🏅 ميداليات مخصصة',
        'caption' => 'ميداليات مخصصة بأسمائكم',
        'image'   => 'https://k3d.shop/wp-content/uploads/2025/11/ChatGPT-Image-Nov-12-2025-02_00_19-AM.webp',
        'url'     => 'https://k3d.shop/product/%d8%b3%d9%84%d8%b3%d9%84%d8%a9-%d9%85%d9%81%d8%a7%d8%aa%d9%8a%d8%ad-%d8%a8%d8%a7%d8%b3%d9%85%d9%83-%d8%a3%d9%88-%d8%a7%d8%b3%d9%85-%d9%85%d9%86-%d8%aa%d8%ad%d8%a8-%d8%a8%d9%84%d8%b9%d8%b1%d8%a8%d9%8a/',
    ],
    [
        'title'   => '🎴 ديكورات شخصية',
        'caption' => 'ديكورات مخصصة بأسمائكم',
        'image'   => 'https://k3d.shop/wp-content/uploads/2025/11/ChatGPT-Image-Nov-12-2025-01_23_35-AM.webp',
        'url'     => 'https://k3d.shop/product/%d8%a3%d8%ad%d8%b1%d9%81-%d9%85%d8%b2%d8%ae%d8%b1%d9%81-a-%d8%a5%d9%84%d9%8a-z-%d9%85%d8%b9-%d8%a3%d8%b3%d9%85/',
    ],
    [
        'title'   => '🌿 ديكورات منزلية',
        'caption' => 'قطع فنية تزين منزلك',
        'image'   => 'https://k3d.shop/wp-content/uploads/2025/11/ChatGPT-Image-Nov-12-2025-01_52_48-AM.webp',
        'url'     => 'https://k3d.shop/product-category/new-arrivals/decor/',
    ],
];

/**
 * يعرض قائمة منتجات بنفس تنسيق كروت المتجر (يعتمد على k3d-shop.css).
 */
function k3d_home_render_products($ids) {
    if (empty($ids) || !function_exists('wc_get_product')) {
        return;
    }

    $query = new WP_Query([
        'post_type'      => 'product',
        'post__in'       => $ids,
        'orderby'        => 'post__in',
        'posts_per_page' => count($ids),
    ]);

    if (!$query->have_posts()) {
        wp_reset_postdata();
        return;
    }

    woocommerce_product_loop_start();
    while ($query->have_posts()) {
        $query->the_post();
        wc_get_template_part('content', 'product');
    }
    woocommerce_product_loop_end();
    wp_reset_postdata();
}
?>

<div class="k3d-home">

    <section class="k3d-hero" data-k3d-animate>
        <div class="k3d-hero__inner">
            <div class="k3d-hero__content">
                <h1>منتجات طباعة ثلاثية الأبعاد<span>بتصاميم مميزة ومبتكرة</span></h1>
                <p>تصاميم ثلاثية الأبعاد فريدة… نبتكرها خصيصًا لك. منتجات مميزة تجمع بين الإبداع والدقة.</p>
                <a class="k3d-btn" href="<?php echo esc_url('https://k3d.shop/product-category/new-arrivals/'); ?>">تسوق الآن 🛍️</a>
            </div>
            <div class="k3d-hero__image">
                <img src="<?php echo esc_url('https://k3d.shop/wp-content/uploads/2025/09/2025-02-21_4103a606e4d72.webp'); ?>" alt="منتجات K3D" loading="eager">
            </div>
        </div>
    </section>

    <section class="k3d-home__cards-section">
        <div class="k3d-home__inner">
            <div class="k3d-cards">
                <?php foreach ($k3d_category_cards as $i => $card): ?>
                    <a class="k3d-card" data-k3d-animate style="background-image:url('<?php echo esc_url($card['image']); ?>')" href="<?php echo esc_url($card['url']); ?>">
                        <div class="k3d-card__content">
                            <h3><?php echo esc_html($card['title']); ?></h3>
                            <p><?php echo esc_html($card['subtitle']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if (function_exists('wc_get_product_ids_on_sale')) : ?>
    <section>
        <div class="k3d-home__inner">
            <div class="k3d-section-title" data-k3d-animate>
                <h2>المنتجات الجديدة</h2>
                <p>اكتشف أحدث إضافاتنا من منتجات الطباعة ثلاثية الأبعاد</p>
            </div>
            <div data-k3d-animate>
                <?php
                $k3d_new_ids = get_posts([
                    'post_type'      => 'product',
                    'posts_per_page' => 4,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'fields'         => 'ids',
                ]);
                k3d_home_render_products($k3d_new_ids);
                ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="k3d-home__promo-section">
        <div class="k3d-promo" data-k3d-animate>
            <p>🚚 استمتع بالتوصيل المجاني على طلباتك<br>شحن مجاني للضفة للطلبات فوق 300 شيكل وداخل القدس للطلبات فوق 400 شيكل ومناطق 48 للطلبات فوق 500 شيكل</p>
            <a class="k3d-btn" href="<?php echo esc_url('https://k3d.shop/product-category/new-arrivals/'); ?>">اتسوق الآن</a>
        </div>
    </section>

    <section class="k3d-categories">
        <div class="k3d-home__inner">
            <div class="k3d-section-title" data-k3d-animate>
                <h2>تسوق حسب الفئة</h2>
                <p>اختر الفئة المناسبة لك</p>
            </div>
            <div class="k3d-categories__grid">
                <?php foreach ($k3d_shop_by_category as $cat) : ?>
                    <a class="k3d-category-item" data-k3d-animate href="<?php echo esc_url($cat['url']); ?>">
                        <img src="<?php echo esc_url($cat['image']); ?>" alt="<?php echo esc_attr($cat['title']); ?>" loading="lazy">
                        <h3><?php echo esc_html($cat['title']); ?></h3>
                        <p><?php echo esc_html($cat['caption']); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if (function_exists('wc_get_product_ids_on_sale')) :
        $k3d_sale_ids = array_slice(wc_get_product_ids_on_sale(), 0, 4);
        if (!empty($k3d_sale_ids)) : ?>
    <section>
        <div class="k3d-home__inner">
            <div class="k3d-section-title" data-k3d-animate>
                <h2>العروض الخاصة 🎉</h2>
                <p>لا تفوت الفرصة – عروض محدودة المدة!</p>
            </div>
            <div data-k3d-animate>
                <?php k3d_home_render_products($k3d_sale_ids); ?>
            </div>
        </div>
    </section>
        <?php endif;
    endif; ?>

    <section class="k3d-newsletter">
        <div class="k3d-newsletter__inner" data-k3d-animate>
            <h2>📧 أشترك لتصلك أحدث أخبار ومنتجات K3D</h2>
            <p>أشترك في نشرتنا البريدية لتصلك أحدث العروض والمنتجات الجديدة. نشاركك دائماً كل ما هو جديد ومميز من K3D.</p>

            <form class="k3d-newsletter-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="k3d_newsletter_subscribe">
                <?php wp_nonce_field('k3d_newsletter', 'k3d_newsletter_nonce'); ?>
                <input type="email" name="k3d_email" placeholder="بريدك الإلكتروني" required>
                <button type="submit" class="k3d-btn">اشترك الآن</button>
            </form>

            <?php if (isset($_GET['k3d_subscribed'])) : ?>
                <?php if ('1' === $_GET['k3d_subscribed']) : ?>
                    <p class="k3d-newsletter__message k3d-newsletter__message--success">شكراً لك! تم الاشتراك بنجاح 🎉</p>
                <?php else : ?>
                    <p class="k3d-newsletter__message k3d-newsletter__message--error">في مشكلة في البريد الإلكتروني، جرّب تاني.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

</div>

<?php get_footer(); ?>
