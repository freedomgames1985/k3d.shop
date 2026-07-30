<?php
/**
 * Generation Manager - المسؤول الوحيد عن اختيار الأداتر المناسب وتنفيذ
 * توليد ملف التصنيع لما طلب يتأكد. مش بيعرف حاجة عن OpenSCAD تحديدًا -
 * بيتعامل مع أي أداتر مسجّل عن طريق K3D_3DC_Generation_Adapter بس.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function k3d_3dc_settings(): array {
	$defaults = [
		'adapter'        => 'openscad',
		'webhook_url'    => '',
		'webhook_secret' => '',
		'auto_generate'  => 'yes',
	];

	return wp_parse_args( get_option( 'k3d_3dc_settings', [] ), $defaults );
}

/** @return array<string, K3D_3DC_Generation_Adapter> */
function k3d_3dc_generation_adapters(): array {
	return apply_filters( 'k3d_3dc_generation_adapters', [
		'openscad' => new K3D_3DC_OpenSCAD_Adapter(),
	] );
}

/** @return array{success:bool, files?:array<string,string>, errorCode?:string, message?:string} */
function k3d_3dc_generation_manager( array $design ): array {
	$settings = k3d_3dc_settings();
	$adapters = k3d_3dc_generation_adapters();
	$adapter  = $adapters[ $settings['adapter'] ] ?? null;

	if ( ! $adapter instanceof K3D_3DC_Generation_Adapter ) {
		return [
			'success'   => false,
			'errorCode' => 'ADAPTER_NOT_FOUND',
			'message'   => __( 'محرك توليد الملفات المختار مش مسجّل.', 'k3d-3d-customizer' ),
		];
	}

	return $adapter->generate( $design );
}

/**
 * بناء "Design Object" الموحّد من عنصر طلب حقيقي - نفس الشكل اللي أي
 * أداتر (OpenSCAD دلوقتي، أي حاجة تانية مستقبلًا) بيستناه، بغض النظر
 * عن نوع المعاينة أو المنتج.
 */
function k3d_3dc_build_design_object( WC_Order_Item_Product $item ): ?array {
	$product_id = (int) $item->get_product_id();

	if ( ! k3d_3dc_product_enabled( $product_id ) ) {
		return null;
	}

	$value = (string) $item->get_meta( '_k3d_3dc_value', true );

	if ( '' === $value ) {
		return null;
	}

	$design_key = k3d_3dc_product_design_key( $product_id );
	$design     = k3d_3dc_get_design( $design_key );
	$color_id   = (string) $item->get_meta( '_k3d_3dc_color', true );
	$color_hex  = '';

	foreach ( $design['colors'] ?? [] as $color ) {
		if ( $color['id'] === $color_id ) {
			$color_hex = $color['hex'];
			break;
		}
	}

	$template = (string) get_post_meta( $product_id, K3D_3DC_META_TEMPLATE, true );

	return apply_filters( 'k3d_3dc_design_object', [
		'template'   => '' !== $template ? $template : $design_key,
		'parameters' => [
			'text'      => $value,
			'font'      => (string) get_post_meta( $product_id, K3D_3DC_META_FONT, true ),
			'baseColor' => (string) get_post_meta( $product_id, K3D_3DC_META_BASE_COLOR, true ),
			'textColor' => $color_hex,
			'border'    => (float) get_post_meta( $product_id, K3D_3DC_META_BORDER, true ),
		],
	], $item, $product_id );
}

/** لما الطلب يتأكد (قيد التنفيذ/مكتمل)، ولّد ملفات التصنيع لكل عنصر فيه تخصيص 3D، وسجّل الروابط/حالة الفشل على عنصر الطلب نفسه. */
function k3d_3dc_maybe_generate_order_files( int $order_id ): void {
	$settings = k3d_3dc_settings();

	if ( 'yes' !== $settings['auto_generate'] ) {
		return;
	}

	$order = wc_get_order( $order_id );

	if ( ! $order instanceof WC_Order ) {
		return;
	}

	foreach ( $order->get_items() as $item ) {
		if ( ! $item instanceof WC_Order_Item_Product ) {
			continue;
		}

		// عشان ما نولدش نفس الملف مرتين لو الطلب اتنقل بين الحالتين (processing ثم completed مثلًا).
		if ( '' !== (string) $item->get_meta( '_k3d_3dc_generation_status', true ) ) {
			continue;
		}

		$design = k3d_3dc_build_design_object( $item );

		if ( ! $design ) {
			continue;
		}

		$result = k3d_3dc_generation_manager( $design );
		$item->add_meta_data( '_k3d_3dc_generation_status', ! empty( $result['success'] ) ? 'done' : 'failed', true );

		if ( ! empty( $result['success'] ) && ! empty( $result['files'] ) && is_array( $result['files'] ) ) {
			$lines = [];

			foreach ( $result['files'] as $type => $url ) {
				$item->add_meta_data( '_k3d_3dc_file_' . sanitize_key( (string) $type ), esc_url_raw( (string) $url ), true );
				$lines[] = strtoupper( (string) $type ) . ': ' . esc_url_raw( (string) $url );
			}

			$item->add_meta_data( __( 'ملفات التصنيع', 'k3d-3d-customizer' ), implode( ' | ', $lines ) );
		} else {
			$item->add_meta_data(
				__( 'حالة توليد ملف التصنيع', 'k3d-3d-customizer' ),
				$result['message'] ?? __( 'فشل التوليد.', 'k3d-3d-customizer' )
			);
		}

		$item->save();
	}
}

add_action( 'woocommerce_order_status_processing', 'k3d_3dc_maybe_generate_order_files' );
add_action( 'woocommerce_order_status_completed', 'k3d_3dc_maybe_generate_order_files' );
