<?php
/**
 * تكامل ووكومرس: بنشتغل مع الـhooks والفورمات الحقيقية بتاعته (مش بنعيد
 * كتابتها) عشان الـVariable Products، الـAJAX add-to-cart، وحساب السعر
 * حسب الاختيارات يفضلوا شغالين صح من غير ما نلمسهم - وبس نغيّر شكلهم
 * بالـCSS/قوالب الأوفررايد.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// هنستخدم تنسيقنا الخاص بدل ستايل شيت ووكومرس الافتراضي.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// نفس الراپر الافتراضي (مطلوب من ووكومرس)، بس بكلاس الـcontainer بتاعنا.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', function (): void {
	echo '<main class="container woocommerce-main">';
} );
add_action( 'woocommerce_after_main_content', function (): void {
	echo '</main>';
} );

// فتات الخبز الافتراضية بتاعة ووكومرس بنستخدم بدالها k3d_breadcrumb() جوه القوالب نفسها.
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

// السايد بار مش محتاجينه - القالب مبني كامل العرض مع فلاتر مخصصة في صفحة المتجر.
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_filter( 'loop_shop_columns', function (): int {
	return 3;
} );

add_filter( 'woocommerce_product_thumbnails_columns', function (): int {
	return 4;
} );

// شكل زرار "أضف للسلة" في صفحة المنتج - داخل .product-buy-row زي المعاينة.
add_filter( 'woocommerce_product_single_add_to_cart_button_html', function ( $html ) {
	return $html;
} );

// عدد المنتجات في صفحة المتجر - متسق مع باجيناشن 3 صفوف × 3 أعمدة.
add_filter( 'loop_shop_per_page', function () {
	return 9;
}, 20 );

/**
 * وقت انتهاء العرض لمنتج عليه تخفيض - من تاريخ "نهاية العرض" الحقيقي في
 * ووكومرس (Product data > عام > تاريخ انتهاء السعر) لو الأدمن حدده، وإلا
 * المدة الافتراضية المظبوطة من Customizer > العروض والتخفيضات (أسبوع لو
 * الأدمن لسه ما غيّرش حاجة)، بتبدأ من أول ظهور للعرض. بيتخزن في الميتا
 * عشان العد التنازلي يفضل ثابت مع كل تحميل للصفحة، ولو انتهى فعليًا
 * بيتجدد تلقائيًا طول ما المنتج لسه عليه تخفيض (يعني لو الأدمن مدّ
 * العرض من غير ما يحدد تاريخ، بيكمل عد جديد بدل ما يفضل واقف على صفر).
 */
function k3d_sale_countdown_end( WC_Product $product ): ?int {
	$to = $product->get_date_on_sale_to();

	if ( $to instanceof WC_DateTime ) {
		return $to->getTimestamp();
	}

	$stored = (int) $product->get_meta( '_k3d_sale_countdown_end' );

	if ( $stored > time() ) {
		return $stored;
	}

	$days = max( 1, (int) get_theme_mod( 'k3d_sale_countdown_days', 7 ) );
	$end  = time() + ( $days * DAY_IN_SECONDS );
	$product->update_meta_data( '_k3d_sale_countdown_end', $end );
	$product->save_meta_data();

	return $end;
}

// عد تنازلي تحت سعر المنتج في صفحة المنتج المفرد لو عليه تخفيض - عشان
// يشجع على الشراء قبل ما ينتهي العرض. قابل للإيقاف من Customizer.
add_action( 'woocommerce_single_product_summary', function (): void {
	global $product;

	if ( ! get_theme_mod( 'k3d_sale_countdown_enabled', true ) ) {
		return;
	}

	if ( ! $product instanceof WC_Product || ! $product->is_on_sale() ) {
		return;
	}

	$end = k3d_sale_countdown_end( $product );

	if ( ! $end || $end <= time() ) {
		return;
	}
	?>
	<div class="sale-countdown" data-k3d-countdown data-end="<?php echo esc_attr( (string) ( $end * 1000 ) ); ?>">
		<span class="sale-countdown-label">⏳ <?php esc_html_e( 'ينتهي العرض خلال', 'k3d-shop' ); ?></span>
		<div class="sale-countdown-clock mono">
			<div class="sale-countdown-unit"><span data-unit="d">00</span><small><?php esc_html_e( 'يوم', 'k3d-shop' ); ?></small></div>
			<div class="sale-countdown-unit"><span data-unit="h">00</span><small><?php esc_html_e( 'ساعة', 'k3d-shop' ); ?></small></div>
			<div class="sale-countdown-unit"><span data-unit="m">00</span><small><?php esc_html_e( 'دقيقة', 'k3d-shop' ); ?></small></div>
			<div class="sale-countdown-unit"><span data-unit="s">00</span><small><?php esc_html_e( 'ثانية', 'k3d-shop' ); ?></small></div>
		</div>
	</div>
	<?php
}, 15 );

// تبويب "معلومات الشحن" جنب الوصف في صفحة المنتج.
add_filter( 'woocommerce_product_tabs', function ( array $tabs ): array {
	$tabs['k3d_shipping_info'] = [
		'title'    => __( 'معلومات الشحن', 'k3d-shop' ),
		'priority' => 15,
		'callback' => 'k3d_render_shipping_info_tab',
	];

	return $tabs;
} );

/**
 * مربع عائم "فلان اشترى كذا من كذا دقايق" - من طلبات حقيقية (processing/completed)
 * بس، من غير بيانات وهمية.
 */
add_action( 'wp_footer', function (): void {
	if ( is_admin() || ! function_exists( 'wc_get_orders' ) ) {
		return;
	}

	$orders = wc_get_orders( [
		'status'  => [ 'processing', 'completed' ],
		'limit'   => 6,
		'orderby' => 'date',
		'order'   => 'DESC',
	] );

	$notices = [];

	foreach ( $orders as $order ) {
		$order_items = $order->get_items();
		$first_item  = reset( $order_items );

		if ( ! $first_item ) {
			continue;
		}

		$first_name = trim( (string) $order->get_billing_first_name() );
		$last_name  = trim( (string) $order->get_billing_last_name() );
		$display    = $first_name . ( $last_name ? ' ' . mb_substr( $last_name, 0, 1 ) . '.' : '' );

		if ( '' === trim( $display ) ) {
			$display = __( 'عميل', 'k3d-shop' );
		}

		$notices[] = sprintf(
			/* translators: 1: اسم العميل، 2: اسم المنتج، 3: منذ متى */
			__( '%1$s اشترى %2$s منذ %3$s', 'k3d-shop' ),
			esc_html( $display ),
			esc_html( $first_item->get_name() ),
			esc_html( human_time_diff( $order->get_date_created()->getTimestamp(), time() ) )
		);
	}

	if ( ! $notices ) {
		return;
	}
	?>
	<div id="k3d-recent-orders" class="k3d-recent-orders" data-items="<?php echo esc_attr( wp_json_encode( $notices ) ); ?>"></div>
	<?php
} );

function k3d_render_shipping_info_tab(): void {
	?>
	<div class="k3d-shipping-info">
		<div class="k3d-shipping-card">
			<h3>📦 <?php esc_html_e( 'معلومات الشحن والطباعة', 'k3d-shop' ); ?></h3>

			<table class="k3d-shipping-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'طرق الدفع', 'k3d-shop' ); ?></th>
						<th><?php esc_html_e( 'رسوم التوصيل', 'k3d-shop' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'نقداً عند الاستلام', 'k3d-shop' ); ?></td>
						<td>
							<?php esc_html_e( 'الضفة الغربية: 20 شيكل', 'k3d-shop' ); ?><br>
							<?php esc_html_e( 'داخل القدس: 30 شيكل', 'k3d-shop' ); ?><br>
							<?php esc_html_e( 'مناطق 48: 70 شيكل', 'k3d-shop' ); ?>
						</td>
					</tr>
				</tbody>
			</table>

			<div class="k3d-shipping-meta">
				<span>🚚 <?php esc_html_e( 'مدة التوصيل: 2-5 أيام.', 'k3d-shop' ); ?></span>
				<span>🖨️ <?php esc_html_e( 'مدة الطباعة للمنتجات المخصصة: 1-2 أيام.', 'k3d-shop' ); ?></span>
			</div>

			<p class="k3d-shipping-note">
				<?php esc_html_e( 'لإجراء طلبك بسهولة، يمكنك الدفع نقداً عند الاستلام. تختلف رسوم التوصيل حسب الموقع. بعد ذلك، قم بتعبئة الخيارات المطلوبة مثل اللون، ثم اضغط على "أضف إلى السلة".', 'k3d-shop' ); ?>
			</p>
			<p class="k3d-shipping-note">
				<?php echo esc_html( k3d_shipping_info_text() ); ?>
			</p>
		</div>
	</div>
	<?php
}
