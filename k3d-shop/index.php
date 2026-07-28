<?php
/**
 * القالب الاحتياطي العام - أي صفحة مفيهاش قالب أكتر تحديدًا (index.php
 * مطلوب في أي ثيم ووردبريس حتى لو front-page.php/page.php/single.php
 * بيغطوا معظم الحالات).
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="container" style="padding:64px 0;">
	<?php if ( have_posts() ) : ?>

		<?php if ( is_search() ) : ?>
			<div class="shop-header">
				<h1><?php printf( esc_html__( 'نتائج البحث عن: %s', 'k3d-shop' ), '<span>' . esc_html( get_search_query() ) . '</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
			</div>
		<?php endif; ?>

		<div class="prod-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<div class="prod-card">
					<a class="prod-media" href="<?php the_permalink(); ?>" style="<?php echo has_post_thumbnail() ? 'background-image:url(' . esc_url( get_the_post_thumbnail_url( null, 'k3d-product-card' ) ) . ');background-size:cover;background-position:center;' : ''; ?>"></a>
					<div class="prod-body">
						<a class="prod-name" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</div>
				</div>
				<?php
			endwhile;
			?>
		</div>

		<div class="pagination"><?php the_posts_pagination(); ?></div>

	<?php else : ?>
		<p><?php esc_html_e( 'مفيش نتائج.', 'k3d-shop' ); ?></p>
	<?php endif; ?>
</main>

<?php
get_footer();
