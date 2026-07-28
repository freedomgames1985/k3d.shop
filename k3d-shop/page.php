<?php
/**
 * صفحات ووردبريس العادية (من نحن، اتصل بنا، إلخ).
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="primary" class="container" style="padding:48px 0 72px;max-width:820px;">
		<?php
		k3d_breadcrumb( [
			[ 'label' => __( 'الرئيسية', 'k3d-shop' ), 'url' => home_url( '/' ) ],
			[ 'label' => get_the_title() ],
		] );
		?>
		<h1 style="margin-bottom:24px;"><?php the_title(); ?></h1>

		<?php if ( has_post_thumbnail() ) : ?>
			<img src="<?php echo esc_url( get_the_post_thumbnail_url( null, 'large' ) ); ?>" alt="" style="width:100%;border-radius:16px;margin-bottom:32px;">
		<?php endif; ?>

		<div class="page-content" style="font-size:15.5px;line-height:1.85;color:var(--text);">
			<?php the_content(); ?>
		</div>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</main>
	<?php
endwhile;

get_footer();
