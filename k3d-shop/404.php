<?php
/**
 * صفحة 404.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="container" style="padding:96px 0;text-align:center;">
	<span class="eyebrow mono" style="justify-content:center;color:var(--accent);">404</span>
	<h1 style="margin:12px 0 16px;"><?php esc_html_e( 'الصفحة مش موجودة', 'k3d-shop' ); ?></h1>
	<p style="color:var(--text-muted);margin-bottom:28px;"><?php esc_html_e( 'ممكن الرابط اتغيّر أو المنتج مبقاش متاح.', 'k3d-shop' ); ?></p>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'الرجوع للرئيسية', 'k3d-shop' ); ?></a>
</main>

<?php
get_footer();
