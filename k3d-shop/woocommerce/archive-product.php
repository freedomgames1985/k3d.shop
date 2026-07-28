<?php
/**
 * صفحة المتجر/التصنيف - أوفررايد لقالب ووكومرس الافتراضي. الفلاتر هنا
 * ودجتات ووكومرس الحقيقية (WC_Widget_Product_Categories،
 * WC_Widget_Price_Filter) عشان تفلتر فعليًا (submit عادي، مش شكل بس)،
 * مرسومة بستايل نظام التصميم بتاعنا.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$is_search = is_search();
?>

<section class="container shop-header">
	<?php
	k3d_breadcrumb( [
		[ 'label' => __( 'الرئيسية', 'k3d-shop' ), 'url' => home_url( '/' ) ],
		[ 'label' => $is_search ? __( 'نتائج البحث', 'k3d-shop' ) : woocommerce_page_title( false ) ],
	] );
	?>
	<h1><?php echo $is_search ? esc_html__( 'نتائج البحث', 'k3d-shop' ) : wp_kses_post( woocommerce_page_title( false ) ); ?></h1>
	<?php do_action( 'woocommerce_archive_description' ); ?>
</section>

<section class="container shop-layout">
	<aside class="shop-filters">
		<div class="filters-head">
			<h2><?php esc_html_e( 'الفلاتر', 'k3d-shop' ); ?></h2>
		</div>

		<div class="filter-group">
			<h3><?php esc_html_e( 'الفئة', 'k3d-shop' ); ?></h3>
			<?php the_widget( 'WC_Widget_Product_Categories', [ 'title' => '', 'hierarchical' => 1, 'count' => 1 ] ); ?>
		</div>

		<div class="filter-group">
			<h3><?php esc_html_e( 'السعر', 'k3d-shop' ); ?></h3>
			<?php the_widget( 'WC_Widget_Price_Filter', [ 'title' => '' ] ); ?>
		</div>
	</aside>

	<div class="shop-main" data-k3d-infinite-scroll>
		<?php if ( woocommerce_product_loop() ) : ?>

			<div class="shop-toolbar">
				<span class="shop-results mono"><?php woocommerce_result_count(); ?></span>
				<div class="shop-sort"><?php woocommerce_catalog_ordering(); ?></div>
			</div>

			<?php woocommerce_product_loop_start(); ?>

			<div class="prod-grid shop-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					wc_get_template_part( 'content', 'product' );
				endwhile;
				?>
			</div>

			<?php woocommerce_product_loop_end(); ?>

			<div class="pagination"><?php woocommerce_pagination(); ?></div>
			<div class="shop-infinite-loading" hidden><?php esc_html_e( 'جاري تحميل المزيد...', 'k3d-shop' ); ?></div>
			<div class="shop-infinite-sentinel" aria-hidden="true"></div>

		<?php else : ?>
			<?php do_action( 'woocommerce_no_products_found' ); ?>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
