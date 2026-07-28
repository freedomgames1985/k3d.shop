<?php
/**
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$links = k3d_app_links();
?>

<section class="container section-app-cta">
	<div class="app-cta">
		<div class="app-cta-text">
			<span class="kicker"><?php esc_html_e( 'حمّل التطبيق', 'k3d-shop' ); ?></span>
			<h2><?php esc_html_e( 'K3D Shop… في جيبك', 'k3d-shop' ); ?></h2>
			<p><?php esc_html_e( 'تابع طلباتك، صمّم قطعتك، واستلم إشعارات الطباعة أول بأول — من التطبيق.', 'k3d-shop' ); ?></p>
			<div class="store-badges">
				<a href="#" class="store-badge">
					<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/></svg>
					<span class="store-badge-text"><em><?php esc_html_e( 'حمّل من', 'k3d-shop' ); ?></em><b>App Store</b></span>
				</a>
				<a href="#" class="store-badge">
					<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22.018 13.298l-3.919 2.218-3.515-3.493 3.543-3.521 3.891 2.202a1.49 1.49 0 0 1 0 2.594zM1.337.924a1.486 1.486 0 0 0-.112.568v21.017c0 .217.045.419.124.6l11.155-11.087L1.337.924zm12.207 10.065l3.258-3.238L3.45.195a1.466 1.466 0 0 0-.946-.179l11.04 10.973zm0 2.067l-11 10.933c.298.036.612-.016.906-.183l13.324-7.54-3.23-3.21z"/></svg>
					<span class="store-badge-text"><em><?php esc_html_e( 'متوفر على', 'k3d-shop' ); ?></em><b>Google Play</b></span>
				</a>
			</div>
		</div>
		<div class="medal-wrap app-cta-graphic" style="height:200px;">
			<div class="medal-ring r2" style="width:170px;height:170px;"></div>
			<div class="medal" style="width:110px;height:110px;"></div>
		</div>
	</div>
</section>

<footer class="site">
	<div class="container footer-grid">
		<div>
			<div class="logo" style="margin-bottom:14px;">
				<span class="logo-mark"><?php esc_html_e( 'Shop', 'k3d-shop' ); ?></span>
				<span class="logo-text">K3D</span>
			</div>
			<p style="font-size:13.5px;color:var(--paper-200);max-width:32ch;margin:0;">
				<?php esc_html_e( 'متجر طباعة ثلاثية الأبعاد للميداليات والهدايا والديكورات بتصميم خاص.', 'k3d-shop' ); ?>
			</p>
		</div>
		<div>
			<h4><?php esc_html_e( 'تسوّق', 'k3d-shop' ); ?></h4>
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<?php
				wp_nav_menu( [
					'theme_location' => 'footer',
					'container'      => false,
					'items_wrap'     => '<ul>%3$s</ul>',
					'fallback_cb'    => false,
				] );
				?>
			<?php else : ?>
				<ul>
					<?php
					// مفيش قائمة "footer" متظبطة لسه من Appearance > Menus - بنعرض
					// فئات المنتجات الحقيقية بدل عمود فاضي.
					$footer_cats = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'number' => 4 ] );
					if ( ! is_wp_error( $footer_cats ) ) :
						foreach ( $footer_cats as $fcat ) :
							?>
							<li><a href="<?php echo esc_url( get_term_link( $fcat ) ); ?>"><?php echo esc_html( $fcat->name ); ?></a></li>
							<?php
						endforeach;
					endif;
					?>
				</ul>
			<?php endif; ?>
		</div>
		<div>
			<h4><?php esc_html_e( 'الشركة', 'k3d-shop' ); ?></h4>
			<ul>
				<?php if ( ! empty( $links['about_us_url'] ) ) : ?>
					<li><a href="<?php echo esc_url( $links['about_us_url'] ); ?>"><?php esc_html_e( 'من نحن', 'k3d-shop' ); ?></a></li>
				<?php endif; ?>
				<?php if ( ! empty( $links['privacy_policy_url'] ) ) : ?>
					<li><a href="<?php echo esc_url( $links['privacy_policy_url'] ); ?>"><?php esc_html_e( 'سياسة الخصوصية', 'k3d-shop' ); ?></a></li>
				<?php endif; ?>
				<?php if ( ! empty( $links['terms_conditions_url'] ) ) : ?>
					<li><a href="<?php echo esc_url( $links['terms_conditions_url'] ); ?>"><?php esc_html_e( 'الشروط والأحكام', 'k3d-shop' ); ?></a></li>
				<?php endif; ?>
				<li><a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'حسابي', 'k3d-shop' ); ?></a></li>
			</ul>
		</div>
		<div>
			<h4><?php esc_html_e( 'تواصل معنا', 'k3d-shop' ); ?></h4>
			<ul>
				<?php
				$has_phone = '' !== trim( (string) ( $links['contact_us_call'] ?? '' ) )
					|| '' !== trim( (string) ( k3d_store_settings()['phone'] ?? '' ) );
				?>
				<?php if ( $has_phone ) : ?>
					<li><?php k3d_whatsapp_link(); ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $links['contact_us_email'] ) ) : ?>
					<li><a href="mailto:<?php echo esc_attr( $links['contact_us_email'] ); ?>" class="mono"><?php echo esc_html( $links['contact_us_email'] ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
	<div class="container footer-bottom">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — <?php esc_html_e( 'كل الحقوق محفوظة', 'k3d-shop' ); ?></span>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
