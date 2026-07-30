<?php
/**
 * صفحة منتج واحد - أوفررايد لقالب ووكومرس الافتراضي. بنسيب منطق
 * ووكومرس الحقيقي شغال كامل (المعرض، نموذج الاختيارات/Variations،
 * الإضافة للسلة، التبويبات، منتجات ذات صلة) عبر الـhooks بتاعته، وبس
 * بنحط شكل الصفحة (Grid، الفراغات) حوالين مخرجاته.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	global $product;
	?>

	<section class="container shop-header">
		<?php
		k3d_breadcrumb( [
			[ 'label' => __( 'الرئيسية', 'k3d-shop' ), 'url' => home_url( '/' ) ],
			[ 'label' => __( 'المنتجات', 'k3d-shop' ), 'url' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ],
			[ 'label' => get_the_title() ],
		] );
		?>
	</section>

	<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

		<?php do_action( 'woocommerce_before_single_product' ); // إشعارات ووكومرس (نجاح إضافة للسلة، إلخ). ?>

		<?php if ( post_password_required() ) : ?>
			<?php echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php else : ?>

			<section class="container product-layout">
				<div class="product-gallery">
					<?php do_action( 'woocommerce_before_single_product_summary' ); ?>
				</div>
				<div class="product-info summary entry-summary">
					<?php do_action( 'woocommerce_single_product_summary' ); ?>
				</div>
			</section>

			<section class="container product-tabs-section">
				<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
			</section>

		<?php endif; ?>
	</div>

	<?php
endwhile;

get_footer();
