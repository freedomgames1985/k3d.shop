<?php
/**
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="ticker-bar">
	<div class="container">
		<div class="ticker-track mono"><?php echo esc_html( k3d_announcement_text() ); ?></div>
		<div class="top-actions">
			<?php k3d_whatsapp_link(); ?>
			<?php k3d_render_language_switcher(); ?>
		</div>
	</div>
</div>

<header class="site">
	<div class="container header-row">
		<?php k3d_render_logo(); ?>

		<form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="hidden" name="post_type" value="product">
			<input type="text" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'دور على ميدالية، هدية، ديكور...', 'k3d-shop' ); ?>">
			<button type="submit" aria-label="<?php esc_attr_e( 'بحث', 'k3d-shop' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
			</button>
		</form>

		<div class="header-icons">
			<a class="icon-btn" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'حسابي', 'k3d-shop' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6" stroke-linecap="round"/></svg>
			</a>
			<a class="icon-btn" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'السلة', 'k3d-shop' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h2l2.2 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 8H6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="21" r="1.4"/><circle cx="18" cy="21" r="1.4"/></svg>
				<?php if ( function_exists( 'WC' ) && WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) : ?>
					<span class="badge mono"><?php echo esc_html( (string) WC()->cart->get_cart_contents_count() ); ?></span>
				<?php endif; ?>
			</a>
		</div>
	</div>

	<nav class="mainnav">
		<div class="container">
			<?php
			wp_nav_menu( [
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<ul>%3$s</ul>',
				'fallback_cb'    => 'k3d_default_menu_fallback',
			] );
			?>
		</div>
	</nav>
</header>
