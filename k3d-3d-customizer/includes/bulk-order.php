<?php
/**
 * "طلب أكتر من قطعة" - نافذة منبثقة بتسيب العميل يكتب/يلصق أكتر من قيمة
 * (اسم أو رقم لوحة) - سطر لكل واحدة - يعاين كل واحدة لوحدها بنفس محرك
 * المعاينة 3D، وبعدين يضيفهم كلهم للسلة كسطور منفصلة بضغطة واحدة، بدل
 * ما يكرر "أضف للسلة" يدويًا لكل قطعة على حدة.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const K3D_3DC_BULK_MAX_ITEMS = 20;

function k3d_3dc_bulk_current_product(): ?WC_Product {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return null;
	}

	global $product;

	if ( ! $product instanceof WC_Product || ! k3d_3dc_product_enabled( $product->get_id() ) ) {
		return null;
	}

	return $product;
}

add_action( 'wp_enqueue_scripts', function (): void {
	$product = k3d_3dc_bulk_current_product();

	if ( ! $product ) {
		return;
	}

	wp_enqueue_style( 'k3d-3dcb-style', K3D_3DC_URL . 'assets/css/bulk.css', [ 'k3d-3dc-style' ], K3D_3DC_VERSION );
	wp_enqueue_script( 'k3d-3dcb-js', K3D_3DC_URL . 'assets/js/bulk.js', [], K3D_3DC_VERSION, true );

	wp_localize_script( 'k3d-3dcb-js', 'K3D_3DCB_CONFIG', [
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'k3d_3dc_bulk' ),
		'productId'    => $product->get_id(),
		'design'       => k3d_3dc_product_design_key( $product->get_id() ),
		'isDigits'     => 'digits-dash' === ( k3d_3dc_get_design( k3d_3dc_product_design_key( $product->get_id() ) )['charset'] ?? '' ),
		'price'        => (float) wc_get_price_to_display( $product ),
		'currency'     => get_woocommerce_currency_symbol(),
		'maxItems'     => K3D_3DC_BULK_MAX_ITEMS,
		'i18n'         => [
			'empty' => __( 'القائمة فاضية - أضف قيمة واحدة على الأقل.', 'k3d-3d-customizer' ),
			'adding' => __( 'بنضيف للسلة...', 'k3d-3d-customizer' ),
			'error' => __( 'حصل خطأ - حاول تاني.', 'k3d-3d-customizer' ),
		],
	] );
}, 20 );

add_action( 'wp_footer', function (): void {
	$product = k3d_3dc_bulk_current_product();

	if ( ! $product ) {
		return;
	}

	$design_key = k3d_3dc_product_design_key( $product->get_id() );
	$design     = k3d_3dc_get_design( $design_key );

	if ( ! $design ) {
		return;
	}
	?>
	<div class="k3d-3dcb-overlay" id="k3d-3dcb-overlay" hidden>
		<div class="k3d-3dcb-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'طلب أكتر من قطعة', 'k3d-3d-customizer' ); ?>">
			<div class="k3d-3dcb-head">
				<div>
					<h2>🎁 <?php esc_html_e( 'طلب أكتر من قطعة دفعة واحدة', 'k3d-3d-customizer' ); ?></h2>
					<p>
						<?php
						printf(
							/* translators: %d: أقصى عدد القطع */
							esc_html__( 'اكتب أو الصق أكتر من قيمة (حتى %d) - سطر لكل واحدة - وعاين كل واحدة قبل ما تضيفهم كلهم.', 'k3d-3d-customizer' ),
							(int) K3D_3DC_BULK_MAX_ITEMS
						);
						?>
					</p>
				</div>
				<button type="button" class="k3d-3dcb-close" id="k3d-3dcb-close" aria-label="<?php esc_attr_e( 'إغلاق', 'k3d-3d-customizer' ); ?>">✕</button>
			</div>
			<div class="k3d-3dcb-body">
				<div class="k3d-3dcb-main">
					<div class="k3d-3dcb-card">
						<h3><span class="n">1</span> <?php esc_html_e( 'إضافة القيم', 'k3d-3d-customizer' ); ?></h3>
						<textarea id="k3d-3dcb-ta" class="k3d-3dcb-ta" <?php echo 'digits-dash' === ( $design['charset'] ?? '' ) ? 'dir="ltr"' : ''; ?> placeholder="<?php echo esc_attr( $design['value_label'] . ' — ' . __( 'سطر لكل واحدة', 'k3d-3d-customizer' ) ); ?>"></textarea>
						<div class="k3d-3dcb-acts">
							<button type="button" class="k3d-3dcb-btn k3d-3dcb-btn-primary" id="k3d-3dcb-addList">＋ <?php esc_html_e( 'أضف للقائمة', 'k3d-3d-customizer' ); ?></button>
						</div>
						<p class="k3d-3dcb-hint"><?php esc_html_e( 'ممكن تلصق قايمة كاملة - كل قيمة في سطر منفصل.', 'k3d-3d-customizer' ); ?></p>
					</div>
					<div class="k3d-3dcb-card">
						<h3><?php esc_html_e( 'اللون (لكل القطع)', 'k3d-3d-customizer' ); ?></h3>
						<div class="k3d-3dcb-colors" id="k3d-3dcb-colors">
							<?php foreach ( $design['colors'] as $i => $color ) : ?>
								<button
									type="button"
									class="k3d-3dcb-color<?php echo 0 === $i ? ' is-active' : ''; ?>"
									style="--sw:<?php echo esc_attr( $color['hex'] ); ?>"
									data-color-id="<?php echo esc_attr( $color['id'] ); ?>"
									aria-label="<?php echo esc_attr( $color['label'] ); ?>"
								></button>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="k3d-3dcb-card">
						<div class="k3d-3dcb-lhead">
							<span class="cnt"><?php esc_html_e( 'القائمة', 'k3d-3d-customizer' ); ?> · <b id="k3d-3dcb-cnt">0</b></span>
							<button type="button" class="k3d-3dcb-btn k3d-3dcb-btn-ghost k3d-3dcb-btn-sm" id="k3d-3dcb-clear">🗑 <?php esc_html_e( 'تفريغ الكل', 'k3d-3d-customizer' ); ?></button>
						</div>
						<div class="k3d-3dcb-rows" id="k3d-3dcb-rows"></div>
					</div>
				</div>
				<div class="k3d-3dcb-side">
					<div class="k3d-3dcb-card k3d-3dcb-pvcard">
						<div class="k3d-3dcb-stage" id="k3d-3dcb-stage">
							<canvas class="k3d-3dcb-canvas" id="k3d-3dcb-canvas"></canvas>
							<span class="k3d-3dcb-live"><i></i> <?php esc_html_e( 'معاينة حية', 'k3d-3d-customizer' ); ?></span>
						</div>
						<div class="k3d-3dcb-selrow">
							<button type="button" class="k3d-3dcb-step" id="k3d-3dcb-prev" aria-label="<?php esc_attr_e( 'السابق', 'k3d-3d-customizer' ); ?>">›</button>
							<select id="k3d-3dcb-sel" class="k3d-3dcb-sel"></select>
							<button type="button" class="k3d-3dcb-step" id="k3d-3dcb-next" aria-label="<?php esc_attr_e( 'التالي', 'k3d-3d-customizer' ); ?>">‹</button>
						</div>
					</div>
				</div>
			</div>
			<div class="k3d-3dcb-foot">
				<div class="k3d-3dcb-err" id="k3d-3dcb-err" hidden></div>
				<div class="k3d-3dcb-footrow">
					<div class="k3d-3dcb-total"><span><?php esc_html_e( 'الإجمالي', 'k3d-3d-customizer' ); ?></span><b id="k3d-3dcb-total">-</b></div>
					<button type="button" class="k3d-3dcb-btn k3d-3dcb-btn-primary k3d-3dcb-cart" id="k3d-3dcb-cart" disabled><?php esc_html_e( 'أضف الكل للسلة', 'k3d-3d-customizer' ); ?> →</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}, 30 );

add_action( 'wp_ajax_k3d_3dc_bulk_add', 'k3d_3dc_handle_bulk_add' );
add_action( 'wp_ajax_nopriv_k3d_3dc_bulk_add', 'k3d_3dc_handle_bulk_add' );

function k3d_3dc_handle_bulk_add(): void {
	check_ajax_referer( 'k3d_3dc_bulk', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;

	if ( ! $product_id || ! k3d_3dc_product_enabled( $product_id ) ) {
		wp_send_json_error( [ 'message' => __( 'المنتج ده مش متاح للتخصيص.', 'k3d-3d-customizer' ) ] );
	}

	$design_key = k3d_3dc_product_design_key( $product_id );
	$design     = k3d_3dc_get_design( $design_key );

	if ( ! $design ) {
		wp_send_json_error( [ 'message' => __( 'حصل خطأ، حاول تاني.', 'k3d-3d-customizer' ) ] );
	}

	$raw_items = isset( $_POST['items'] ) ? wp_unslash( $_POST['items'] ) : '';
	$items     = json_decode( is_string( $raw_items ) ? $raw_items : '', true );

	if ( ! is_array( $items ) || empty( $items ) ) {
		wp_send_json_error( [ 'message' => __( 'القائمة فاضية.', 'k3d-3d-customizer' ) ] );
	}

	$items = array_slice( $items, 0, K3D_3DC_BULK_MAX_ITEMS );
	$color = isset( $_POST['color'] ) ? sanitize_key( wp_unslash( $_POST['color'] ) ) : '';

	if ( ! k3d_3dc_color_label( $design_key, $color ) ) {
		$color = $design['colors'][0]['id'] ?? '';
	}

	$added = 0;

	foreach ( $items as $raw_value ) {
		$value = trim( sanitize_text_field( (string) $raw_value ) );

		if ( '' === $value ) {
			continue;
		}

		$value = mb_substr( $value, 0, (int) $design['max_length'] );

		$cart_item_key = WC()->cart->add_to_cart(
			$product_id,
			1,
			0,
			[],
			[
				'k3d_3dc_value'  => $value,
				'k3d_3dc_color'  => $color,
				'k3d_3dc_unique' => md5( microtime() . wp_rand() . $value ),
			]
		);

		if ( $cart_item_key ) {
			++$added;
		}
	}

	if ( ! $added ) {
		wp_send_json_error( [ 'message' => __( 'مقدرناش نضيف أي قطعة - جرب تاني.', 'k3d-3d-customizer' ) ] );
	}

	wp_send_json_success( [
		'added'   => $added,
		'cartUrl' => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ),
	] );
}
