<?php
/**
 * أقسام الصفحة الرئيسية - كل قسم دالة مستقلة، بتترسم بالترتيب اللي
 * SettingsService::get_home_layout() بيرجعه (نفس ترتيب التطبيق بالظبط).
 * أي قسم مفيش له محتوى (بانر مش متظبط، مفيش منتجات...) بيرجع من غير ما
 * يطبع حاجة، عشان الصفحة متفضلش فيها فراغات.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function k3d_render_home_section( string $key ): void {
	$fn = 'k3d_home_section_' . $key;

	if ( function_exists( $fn ) ) {
		$fn();
	}
}

/** البانر الرئيسي (Hero) - من مجموعة home_primary، أو نص افتراضي لو لسه ملوش محتوى. */
function k3d_home_section_hero_banner(): void {
	$block = k3d_first_content_block( 'home_primary' );

	$title = $block['title'] ?? __( 'قصّتك… مطبوعة بأدق التفاصيل', 'k3d-shop' );
	$desc  = $block['description'] ?? __( 'ميداليات وهدايا وديكورات بتصميم خاص بيك — من فكرة على الشاشة، لقطعة حقيقية بين إيديك خلال أيام.', 'k3d-shop' );
	$image = $block['image_url'] ?? '';
	$link  = k3d_content_block_link_url( $block );
	?>
	<section class="hero">
		<div class="container hero-inner">
			<div>
				<span class="eyebrow mono">K3D · <?php esc_html_e( 'طباعة تجسيمية بالطلب', 'k3d-shop' ); ?></span>
				<h1><?php echo esc_html( $title ); ?></h1>
				<p class="lead"><?php echo esc_html( $desc ); ?></p>
				<div class="hero-ctas">
					<a href="<?php echo esc_url( $link ?: ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'تصفح المنتجات', 'k3d-shop' ); ?></a>
				</div>
			</div>
			<?php if ( $image ) : ?>
				<div class="medal-wrap">
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" style="max-width:100%;border-radius:20px;position:relative;z-index:1;">
				</div>
			<?php else : ?>
				<div class="medal-wrap">
					<div class="medal-ring r1"></div>
					<div class="medal-ring r2"></div>
					<div class="medal-ribbon"><i></i><i></i></div>
					<div class="medal"></div>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/** شريط سلايدر - من مجموعة slider، عنصر واحد ظاهر بالتنقل التلقائي بينهم. */
function k3d_home_section_slider(): void {
	$items = k3d_content_blocks( 'slider' );

	if ( ! $items ) {
		return;
	}
	?>
	<section class="container">
		<div class="home-slider" data-k3d-slider data-autoplay="5000">
			<div class="home-slider-track">
				<?php foreach ( $items as $item ) : ?>
					<a class="home-slider-item" href="<?php echo esc_url( k3d_content_block_link_url( $item ) ?: '#' ); ?>">
						<?php if ( ! empty( $item['image_url'] ) ) : ?>
							<img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['title'] ?? '' ); ?>">
						<?php endif; ?>
						<?php if ( ! empty( $item['title'] ) ) : ?><span><?php echo esc_html( $item['title'] ); ?></span><?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
			<?php if ( count( $items ) > 1 ) : ?>
				<div class="home-slider-dots">
					<?php foreach ( $items as $i => $item ) : ?>
						<button type="button" class="<?php echo 0 === $i ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr( (string) $i ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'سلايد %d', 'k3d-shop' ), $i + 1 ) ); ?>"></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/** فئات المنتجات الحقيقية (WooCommerce) - أول 4 فئات رئيسية. */
function k3d_home_section_categories(): void {
	$cats = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
		'number'     => 4,
	] );

	if ( is_wp_error( $cats ) || empty( $cats ) ) {
		return;
	}
	?>
	<section class="container">
		<div class="section-head">
			<div>
				<span class="kicker"><?php esc_html_e( 'تصفح حسب الفئة', 'k3d-shop' ); ?></span>
				<h2><?php esc_html_e( 'إيه اللي بتدور عليه؟', 'k3d-shop' ); ?></h2>
			</div>
		</div>
		<div class="cat-grid">
			<?php foreach ( $cats as $cat ) : ?>
				<?php $thumb_id = get_term_meta( $cat->term_id, 'thumbnail_id', true ); ?>
				<a class="cat-card" href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
					<div class="icon">
						<?php if ( $thumb_id ) : ?>
							<?php echo wp_get_attachment_image( $thumb_id, [ 32, 32 ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<svg viewBox="0 0 24 24" fill="none" stroke-width="2"><circle cx="12" cy="14" r="6"/><path d="m9 9-2-6h10l-2 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<?php endif; ?>
					</div>
					<h3><?php echo esc_html( $cat->name ); ?></h3>
					<span><?php echo esc_html( sprintf( _n( '%d منتج', '%d منتج', $cat->count, 'k3d-shop' ), $cat->count ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

function k3d_home_section_trending_products(): void {
	k3d_home_product_row(
		__( 'الأكثر رواجًا', 'k3d-shop' ),
		__( 'المنتجات الرائجة', 'k3d-shop' ),
		[ 'orderby' => 'popularity' ]
	);
}

function k3d_home_section_featured_products(): void {
	k3d_home_product_row(
		__( 'مختاراتنا', 'k3d-shop' ),
		__( 'منتجات مميزة', 'k3d-shop' ),
		[ 'featured' => true ]
	);
}

function k3d_home_section_latest_products(): void {
	k3d_home_product_row(
		__( 'جديدنا', 'k3d-shop' ),
		__( 'أحدث القطع المطبوعة', 'k3d-shop' ),
		[ 'orderby' => 'date' ]
	);
}

function k3d_home_section_on_sale_products(): void {
	$ids = wc_get_product_ids_on_sale();

	if ( empty( $ids ) ) {
		return;
	}

	k3d_home_product_row(
		__( 'عروض خاصة', 'k3d-shop' ),
		__( 'عروض التخفيضات', 'k3d-shop' ),
		[ 'include' => $ids ]
	);
}

/** بانر ثانوي/إضافي - نفس شكل "عندك فكرة؟ خليها قطعة حقيقية". */
function k3d_home_section_secondary_banner(): void {
	k3d_render_promo_banner( 'home_secondary' );
}

function k3d_home_section_other_banner(): void {
	k3d_render_promo_banner( 'home_other' );
}

function k3d_render_promo_banner( string $group ): void {
	$block = k3d_first_content_block( $group );

	if ( ! $block ) {
		return;
	}
	?>
	<section class="container">
		<div class="custom-cta">
			<div>
				<?php if ( ! empty( $block['title'] ) ) : ?><h2><?php echo esc_html( $block['title'] ); ?></h2><?php endif; ?>
				<?php if ( ! empty( $block['description'] ) ) : ?><p><?php echo esc_html( $block['description'] ); ?></p><?php endif; ?>
				<?php $link = k3d_content_block_link_url( $block ); ?>
				<?php if ( $link ) : ?>
					<a href="<?php echo esc_url( $link ); ?>" class="btn btn-primary"><?php esc_html_e( 'اكتشف أكتر', 'k3d-shop' ); ?></a>
				<?php endif; ?>
			</div>
			<div class="medal-wrap" style="height:220px;">
				<?php if ( ! empty( $block['image_url'] ) ) : ?>
					<img src="<?php echo esc_url( $block['image_url'] ); ?>" alt="" style="max-width:100%;border-radius:16px;">
				<?php else : ?>
					<div class="medal-ring r2" style="width:180px;height:180px;"></div>
					<div class="medal" style="width:120px;height:120px;"></div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/** أقسام حسب التصنيف - صف منتجات صغير لكل فئة رئيسية. */
function k3d_home_section_category_shelves(): void {
	$cats = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0 ] );

	if ( is_wp_error( $cats ) || empty( $cats ) ) {
		return;
	}

	foreach ( $cats as $cat ) {
		k3d_home_product_row(
			$cat->name,
			$cat->name,
			[ 'category' => [ $cat->slug ] ],
			get_term_link( $cat )
		);
	}
}

/** أحدث تدوينات المدونة - بيختفي القسم كله لو مفيش تدوينات لسه. */
function k3d_home_section_blog(): void {
	$posts = get_posts( [ 'numberposts' => 3, 'post_status' => 'publish' ] );

	if ( ! $posts ) {
		return;
	}
	?>
	<section class="container">
		<div class="section-head">
			<div>
				<span class="kicker"><?php esc_html_e( 'من المدونة', 'k3d-shop' ); ?></span>
				<h2><?php esc_html_e( 'آخر المقالات', 'k3d-shop' ); ?></h2>
			</div>
			<a class="all-link" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'عرض الكل ←', 'k3d-shop' ); ?></a>
		</div>
		<div class="prod-grid">
			<?php foreach ( $posts as $p ) : ?>
				<div class="prod-card">
					<a class="prod-media" href="<?php echo esc_url( get_permalink( $p ) ); ?>" style="<?php echo has_post_thumbnail( $p ) ? 'background-image:url(' . esc_url( get_the_post_thumbnail_url( $p, 'k3d-product-card' ) ) . ');background-size:cover;background-position:center;' : ''; ?>"></a>
					<div class="prod-body">
						<a class="prod-name" href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

/**
 * صف منتجات عام تستخدمه كل أقسام "trending/featured/latest/on-sale/shelves".
 *
 * @param array<string, mixed> $query_args وسائط إضافية لـ wc_get_products().
 */
function k3d_home_product_row( string $kicker, string $title, array $query_args, ?string $view_all_url = null ): void {
	$args = array_merge( [ 'limit' => 4, 'status' => 'publish' ], $query_args );

	$products = wc_get_products( $args );

	if ( empty( $products ) ) {
		return;
	}
	?>
	<section class="container">
		<div class="section-head">
			<div>
				<span class="kicker"><?php echo esc_html( $kicker ); ?></span>
				<h2><?php echo esc_html( $title ); ?></h2>
			</div>
			<?php if ( $view_all_url ) : ?>
				<a class="all-link" href="<?php echo esc_url( $view_all_url ); ?>"><?php esc_html_e( 'عرض الكل ←', 'k3d-shop' ); ?></a>
			<?php endif; ?>
		</div>
		<div class="prod-grid">
			<?php foreach ( $products as $product ) : ?>
				<?php k3d_product_card( $product ); ?>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

/** رابط عنصر محتوى ترويجي - منتج/تصنيف/رابط خارجي حسب redirect_type. */
function k3d_content_block_link_url( ?array $block ): ?string {
	if ( ! $block ) {
		return null;
	}

	if ( 'product' === ( $block['redirect_type'] ?? '' ) && ! empty( $block['redirect_id'] ) ) {
		$url = get_permalink( (int) $block['redirect_id'] );
		return $url ?: null;
	}

	if ( 'category' === ( $block['redirect_type'] ?? '' ) && ! empty( $block['redirect_id'] ) ) {
		$term = get_term( (int) $block['redirect_id'], 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$url = get_term_link( $term );
			return is_wp_error( $url ) ? null : $url;
		}
	}

	return ! empty( $block['link_url'] ) ? $block['link_url'] : null;
}
