<?php
/**
 * تسجيل التخصيص (النص/الاسم + اللون) اللي العميل دخّله في المعاينة 3D
 * كبيانات حقيقية على عنصر السلة والطلب - عشان فريق الإنتاج يشوفها في
 * تفاصيل الطلب بالظبط زي ما العميل كتبها.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** لازم العميل يكتب حاجة قبل ما يضيف للسلة، لو المنتج ده معمول عليه تخصيص 3D. */
add_filter( 'woocommerce_add_to_cart_validation', function ( bool $passed, int $product_id ): bool {
	if ( ! k3d_3dc_product_enabled( $product_id ) ) {
		return $passed;
	}

	$value = isset( $_POST['k3d_3dc_value'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['k3d_3dc_value'] ) ) ) : '';

	if ( '' === $value ) {
		$design = k3d_3dc_get_design( k3d_3dc_product_design_key( $product_id ) );
		wc_add_notice(
			sprintf(
				/* translators: %s: اسم حقل التخصيص (مثلاً "الاسم أو النص") */
				__( 'من فضلك اكتب %s قبل الإضافة للسلة.', 'k3d-3d-customizer' ),
				$design['value_label'] ?? __( 'التخصيص', 'k3d-3d-customizer' )
			),
			'error'
		);
		return false;
	}

	return $passed;
}, 10, 2 );

/**
 * لقطة المعاينة 3D (data URI) بتوصل كـPOST لحظة "أضف للسلة" - بنتأكد إنها
 * فعلًا صورة JPEG/PNG بالـbase64 قبل ما نقبلها، وبنحط سقف حجم معقول
 * (~400 كيلوبايت) عشان محدش يقدر يحط أي نص كبير في الحقل ده.
 */
function k3d_3dc_sanitize_snapshot( string $raw ): string {
	$raw = trim( $raw );

	if ( '' === $raw || strlen( $raw ) > 400000 ) {
		return '';
	}

	if ( ! preg_match( '#^data:image/(?:jpeg|png);base64,[A-Za-z0-9+/]+=*$#', $raw ) ) {
		return '';
	}

	return $raw;
}

add_filter( 'woocommerce_add_cart_item_data', function ( array $cart_item_data, int $product_id ): array {
	if ( ! k3d_3dc_product_enabled( $product_id ) || empty( $_POST['k3d_3dc_value'] ) ) {
		return $cart_item_data;
	}

	$cart_item_data['k3d_3dc_value'] = sanitize_text_field( wp_unslash( $_POST['k3d_3dc_value'] ) );
	$cart_item_data['k3d_3dc_color'] = isset( $_POST['k3d_3dc_color'] ) ? sanitize_key( wp_unslash( $_POST['k3d_3dc_color'] ) ) : '';

	if ( ! empty( $_POST['k3d_3dc_value2'] ) ) {
		$cart_item_data['k3d_3dc_value2'] = sanitize_text_field( wp_unslash( $_POST['k3d_3dc_value2'] ) );
	}

	if ( ! empty( $_POST['k3d_3dc_snapshot'] ) ) {
		$cart_item_data['k3d_3dc_snapshot'] = k3d_3dc_sanitize_snapshot( wp_unslash( $_POST['k3d_3dc_snapshot'] ) );
	}

	// كل تخصيص مختلف لازم يفضل سطر منفصل في السلة، مش يتجمع مع تخصيص تاني لنفس المنتج.
	$cart_item_data['k3d_3dc_unique'] = md5( microtime() . wp_rand() );

	return $cart_item_data;
}, 10, 2 );

/** في صفحة/ودجت السلة: بنستبدل صورة المنتج العامة بلقطة المعاينة الحقيقية اللي العميل صممها. */
add_filter( 'woocommerce_cart_item_thumbnail', function ( string $thumbnail_html, array $cart_item ) {
	if ( empty( $cart_item['k3d_3dc_snapshot'] ) ) {
		return $thumbnail_html;
	}

	return '<img src="' . esc_attr( $cart_item['k3d_3dc_snapshot'] ) . '" alt="" class="k3d-3dc-cart-snapshot" style="border-radius:8px;object-fit:cover;" />';
}, 10, 2 );

function k3d_3dc_color_label( string $design_key, string $color_id ): string {
	$design = k3d_3dc_get_design( $design_key );

	foreach ( $design['colors'] ?? [] as $color ) {
		if ( $color['id'] === $color_id ) {
			return $color['label'];
		}
	}

	return '';
}

add_filter( 'woocommerce_get_item_data', function ( array $item_data, array $cart_item ): array {
	if ( empty( $cart_item['k3d_3dc_value'] ) ) {
		return $item_data;
	}

	$design_key = k3d_3dc_product_design_key( (int) $cart_item['product_id'] );
	$design     = k3d_3dc_get_design( $design_key );

	$item_data[] = [
		'key'   => $design['value_label'] ?? __( 'التخصيص', 'k3d-3d-customizer' ),
		'value' => $cart_item['k3d_3dc_value'],
	];

	if ( ! empty( $cart_item['k3d_3dc_value2'] ) ) {
		$item_data[] = [
			'key'   => $design['secondary']['label'] ?? __( 'نص إضافي', 'k3d-3d-customizer' ),
			'value' => $cart_item['k3d_3dc_value2'],
		];
	}

	if ( ! empty( $cart_item['k3d_3dc_color'] ) ) {
		$label = k3d_3dc_color_label( $design_key, $cart_item['k3d_3dc_color'] );

		if ( $label ) {
			$item_data[] = [ 'key' => __( 'اللون', 'k3d-3d-customizer' ), 'value' => $label ];
		}
	}

	return $item_data;
}, 10, 2 );

add_action( 'woocommerce_checkout_create_order_line_item', function ( WC_Order_Item_Product $item, string $cart_item_key, array $values, WC_Order $order ): void {
	if ( empty( $values['k3d_3dc_value'] ) ) {
		return;
	}

	$design_key = k3d_3dc_product_design_key( (int) $values['product_id'] );
	$design     = k3d_3dc_get_design( $design_key );

	$item->add_meta_data( $design['value_label'] ?? __( 'التخصيص', 'k3d-3d-customizer' ), $values['k3d_3dc_value'] );

	// مفاتيح مخفية (بادئة _) ثابتة الاسم بغض النظر عن لغة الموقع - Generation Manager بيقرا منها، مش من العنوان المعروض للعميل.
	$item->add_meta_data( '_k3d_3dc_value', $values['k3d_3dc_value'] );
	$item->add_meta_data( '_k3d_3dc_color', $values['k3d_3dc_color'] ?? '' );

	if ( ! empty( $values['k3d_3dc_value2'] ) ) {
		$item->add_meta_data( $design['secondary']['label'] ?? __( 'نص إضافي', 'k3d-3d-customizer' ), $values['k3d_3dc_value2'] );
		$item->add_meta_data( '_k3d_3dc_value2', $values['k3d_3dc_value2'] );
	}

	if ( ! empty( $values['k3d_3dc_snapshot'] ) ) {
		// مخفي (بادئة _) عشان الـbase64 الطويل ميظهرش كنص جوه تفاصيل الطلب/الإيميل - بيتعرض كصورة بدل كده تحت.
		$item->add_meta_data( '_k3d_3dc_snapshot', $values['k3d_3dc_snapshot'] );
	}

	if ( ! empty( $values['k3d_3dc_color'] ) ) {
		$label = k3d_3dc_color_label( $design_key, $values['k3d_3dc_color'] );

		if ( $label ) {
			$item->add_meta_data( __( 'اللون', 'k3d-3d-customizer' ), $label );
		}
	}
}, 10, 4 );

/** في شاشة الطلب بلوحة التحكم: بنعرض لقطة المعاينة كصورة صغيرة تحت اسم المنتج، عشان فريق الإنتاج يشوف التصميم بالظبط زي ما العميل طلبه. */
add_action( 'woocommerce_after_order_itemmeta', function ( int $item_id, WC_Order_Item $item, $product ): void {
	if ( ! is_admin() || ! $item instanceof WC_Order_Item_Product ) {
		return;
	}

	$snapshot = $item->get_meta( '_k3d_3dc_snapshot' );

	if ( ! $snapshot ) {
		return;
	}

	printf(
		'<img src="%s" alt="" style="display:block;margin-top:6px;max-width:140px;border-radius:8px;border:1px solid #e0e0e0;" />',
		esc_attr( $snapshot )
	);
}, 10, 3 );
