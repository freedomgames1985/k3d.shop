<?php
/**
 * دوال مساعدة بتتكرر في أكتر من قالب (اللوجو، الهيدر، بطاقة منتج، فتات الخبز).
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * اللوجو - صورة لو متظبطة من الإضافة (Settings > عام > رابط الشعار)، وإلا
 * الشكل النصي الافتراضي (Shop جوه مربع + K3D جنبه).
 */
function k3d_render_logo(): void {
	$logo_url = trim( (string) ( k3d_app_links()['logo'] ?? '' ) );
	?>
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php if ( '' !== $logo_url ) : ?>
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="logo-image" />
		<?php else : ?>
			<span class="logo-mark"><?php esc_html_e( 'Shop', 'k3d-shop' ); ?></span>
			<span class="logo-text">K3D<small><?php esc_html_e( 'طباعة ثلاثية الأبعاد مخصصة', 'k3d-shop' ); ?></small></span>
		<?php endif; ?>
	</a>
	<?php
}

/** رقم الهاتف من إعدادات المتجر، كرابط واتساب - بيفضل LTR حتى جوا صفحة عربي/عبري. */
function k3d_whatsapp_link(): void {
	$phone = preg_replace( '/[^0-9+]/', '', (string) ( k3d_app_links()['contact_us_call'] ?? k3d_store_settings()['phone'] ?? '' ) );

	if ( '' === $phone ) {
		return;
	}

	$digits_only = ltrim( str_replace( '+', '', $phone ), '0' );
	?>
	<a class="wa-link" href="https://wa.me/<?php echo esc_attr( $digits_only ); ?>" target="_blank" rel="noopener">
		<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Zm5.6 14.3c-.2.6-1.3 1.2-1.9 1.3-.5.1-1.1.1-1.8-.1-.4-.1-1-.3-1.7-.6-3-1.3-5-4.3-5.1-4.5-.2-.2-1.2-1.6-1.2-3s.7-2.1 1-2.4c.3-.3.6-.4.8-.4h.6c.2 0 .4 0 .6.5.2.6.7 1.9.8 2 .1.2.1.4 0 .6-.1.2-.1.3-.3.5l-.4.5c-.1.2-.3.4-.1.7.2.3.9 1.4 1.8 2.3 1.3 1.2 2.3 1.6 2.6 1.8.3.1.5.1.7-.1.2-.2.7-.8.9-1.1.2-.3.4-.2.6-.1l1.8.9c.2.1.4.2.5.3.1.2.1.9-.1 1.5Z"/></svg>
		<bdi class="mono ltr-num"><?php echo esc_html( $phone ); ?></bdi>
	</a>
	<?php
}

/** قائمة اللغة (Dropdown) - بتقرا من StorefrontTranslation عبر [k3d_language_switcher]. */
function k3d_render_language_switcher(): void {
	if ( shortcode_exists( 'k3d_language_switcher' ) ) {
		echo do_shortcode( '[k3d_language_switcher]' );
		return;
	}

	// احتياطي بسيط لو الإضافة مش شغالة - بيدّي نفس شكل الـselect بس من غير وظيفة فعلية.
	$directory = k3d_language_directory();
	$current   = k3d_current_lang();
	?>
	<div class="lang-select-wrap">
		<select class="lang-select" disabled>
			<option><?php echo esc_html( $directory[ $current ]['flag'] . '  ' . $directory[ $current ]['name'] ); ?></option>
		</select>
	</div>
	<?php
}

/**
 * قائمة افتراضية بسيطة تظهر لو الأدمن لسه ما ظبطش قائمة من Appearance > Menus -
 * عشان الهيدر متفضلش فاضي أول ما القالب يتفعّل.
 */
function k3d_default_menu_fallback(): void {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	?>
	<ul>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'الرئيسية', 'k3d-shop' ); ?></a></li>
		<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'كل المنتجات', 'k3d-shop' ); ?></a></li>
		<?php
		$cats = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0 ] );
		if ( ! is_wp_error( $cats ) ) :
			foreach ( $cats as $cat ) :
				?>
				<li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
				<?php
			endforeach;
		endif;
		?>
	</ul>
	<p class="menu-admin-hint" style="padding:8px 16px;font-size:11px;color:var(--paper-200);">
		<?php esc_html_e( 'قائمة مؤقتة - اضبط قائمتك من Appearance > Menus', 'k3d-shop' ); ?>
	</p>
	<?php
}

/** فتات الخبز (Breadcrumb) بسيطة، متوافقة مع RTL/LTR. */
function k3d_breadcrumb( array $trail ): void {
	// $trail = [ ['label' => '...', 'url' => '...'|null] ... ] - آخر عنصر من غير رابط (الصفحة الحالية).
	?>
	<nav class="breadcrumb" aria-label="breadcrumb">
		<?php foreach ( $trail as $i => $item ) : ?>
			<?php if ( $i > 0 ) : ?><span>/</span><?php endif; ?>
			<?php if ( ! empty( $item['url'] ) ) : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
			<?php else : ?>
				<span><?php echo esc_html( $item['label'] ); ?></span>
			<?php endif; ?>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * بطاقة منتج بشكل نظام التصميم (spec-ticket) - بتاخد WC_Product حقيقي.
 * لو مفيش صورة، بيظهر نفس نمط الخلفية المتقاطعة اللي في المعاينة بدل صورة مكسورة.
 */
function k3d_product_card( WC_Product $product ): void {
	$image_id  = $product->get_image_id();
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'k3d-product-card' ) : '';
	$cats      = wc_get_product_category_list( $product->get_id() );
	$is_new    = ( time() - strtotime( get_the_date( 'c', $product->get_id() ) ) ) < ( 14 * DAY_IN_SECONDS );
	?>
	<div class="prod-card">
		<a class="prod-media" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" style="<?php echo $image_url ? 'background-image:url(' . esc_url( $image_url ) . ');background-size:cover;background-position:center;' : ''; ?>">
			<?php if ( $product->is_on_sale() ) : ?>
				<span class="prod-tag mono"><?php esc_html_e( 'عرض', 'k3d-shop' ); ?></span>
			<?php elseif ( $is_new ) : ?>
				<span class="prod-tag mono"><?php esc_html_e( 'جديد', 'k3d-shop' ); ?></span>
			<?php endif; ?>
			<?php if ( ! $image_url ) : ?>
				<svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><circle cx="12" cy="13" r="7"/><path d="m9 8-2-5h10l-2 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
			<?php endif; ?>
		</a>
		<div class="prod-body">
			<?php if ( $cats ) : ?><span class="prod-cat"><?php echo wp_kses_post( $cats ); ?></span><?php endif; ?>
			<a class="prod-name" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
			<div class="prod-foot">
				<span class="prod-price mono"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
				<?php echo apply_filters( 'woocommerce_loop_add_to_cart_link', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					sprintf(
						'<a href="%s" data-quantity="1" class="prod-add %s" %s aria-label="%s"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg></a>',
						esc_url( $product->add_to_cart_url() ),
						esc_attr( implode( ' ', array_filter( [ 'ajax_add_to_cart', $product->is_purchasable() && $product->is_in_stock() ? '' : 'disabled' ] ) ) ),
						'data-product_id="' . esc_attr( $product->get_id() ) . '"',
						esc_attr__( 'أضف للسلة', 'k3d-shop' )
					),
					$product
				);
				?>
			</div>
		</div>
	</div>
	<?php
}
