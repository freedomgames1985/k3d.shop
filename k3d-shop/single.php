<?php
/**
 * مقال مدونة واحد.
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
	<main id="primary" class="container" style="padding:48px 0 72px;max-width:760px;">
		<?php
		k3d_breadcrumb( [
			[ 'label' => __( 'الرئيسية', 'k3d-shop' ), 'url' => home_url( '/' ) ],
			[ 'label' => __( 'المدونة', 'k3d-shop' ), 'url' => get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ],
			[ 'label' => get_the_title() ],
		] );
		?>
		<span class="kicker"><?php echo esc_html( get_the_date() ); ?></span>
		<h1 style="margin:8px 0 24px;font-size:clamp(24px,3.4vw,38px);"><?php the_title(); ?></h1>

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
