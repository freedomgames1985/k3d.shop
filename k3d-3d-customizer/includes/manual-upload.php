<?php
/**
 * بديل مجاني لسيرفر OpenSCAD الآلي - بدل ما تشغّل خدمة أوتوماتيكية على
 * سيرفر مدفوع، تقدر تفتح OpenSCAD على جهازك (مجاني، وأصلاً مثبّت عندك)،
 * تكتب فيه اسم العميل يدويًا بنفس أداة "Customizer" الموجودة جوه
 * البرنامج نفسه، تصدّر STL، وترفعه هنا على الطلب مباشرة - بيتسجل بنفس
 * الشكل بالظبط اللي مولّد الملفات الآلي كان هيسجله، فمفيش أي فرق في
 * شاشة الطلب بين الطريقتين.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', function (): void {
	$screen = 'shop_order';

	if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
		$screen = wc_get_page_screen_id( 'shop-order' );
	}

	add_meta_box(
		'k3d_3dc_order_files',
		__( 'ملفات الطباعة (K3D 3D Customizer)', 'k3d-3d-customizer' ),
		'k3d_3dc_render_order_files_box',
		$screen,
		'normal',
		'high'
	);
} );

function k3d_3dc_render_order_files_box( $post_or_order ): void {
	$order = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order( $post_or_order->ID );

	if ( ! $order instanceof WC_Order ) {
		return;
	}

	$items = array_filter(
		$order->get_items(),
		static fn( $item ) => $item instanceof WC_Order_Item_Product && k3d_3dc_product_enabled( $item->get_product_id() )
	);

	if ( empty( $items ) ) {
		echo '<p>' . esc_html__( 'مفيش عناصر في الطلب ده عليها تخصيص 3D.', 'k3d-3d-customizer' ) . '</p>';
		return;
	}
	?>
	<p class="description">
		<?php esc_html_e( 'مفيش عندك سيرفر بيولّد ملفات STL تلقائي؟ افتح OpenSCAD على جهازك (مجاني)، اكتب فيه نفس النص/اللون الظاهرين تحت، صدّر STL، وارفعه هنا يدويًا لكل عنصر - هيتسجل على الطلب بنفس الشكل بالظبط.', 'k3d-3d-customizer' ); ?>
	</p>
	<?php foreach ( $items as $item ) : ?>
		<?php
		$item_id    = $item->get_id();
		$design_key = k3d_3dc_product_design_key( $item->get_product_id() );
		$design     = k3d_3dc_get_design( $design_key );
		$value      = $item->get_meta( '_k3d_3dc_value', true );
		$color_id   = $item->get_meta( '_k3d_3dc_color', true );
		$color      = $color_id ? k3d_3dc_color_label( $design_key, $color_id ) : '';
		$existing   = [];

		foreach ( $item->get_meta_data() as $meta ) {
			if ( str_starts_with( (string) $meta->key, '_k3d_3dc_file_' ) ) {
				$existing[ substr( (string) $meta->key, strlen( '_k3d_3dc_file_' ) ) ] = $meta->value;
			}
		}
		?>
		<div style="border:1px solid #ddd;border-radius:6px;padding:14px;margin-bottom:14px;">
			<p style="margin:0 0 8px;">
				<strong><?php echo esc_html( $item->get_name() ); ?></strong>
				— <?php echo esc_html( $design['value_label'] ?? '' ); ?>: <strong><?php echo esc_html( $value ); ?></strong>
				<?php if ( $color ) : ?> — <?php esc_html_e( 'اللون', 'k3d-3d-customizer' ); ?>: <strong><?php echo esc_html( $color ); ?></strong><?php endif; ?>
			</p>

			<?php if ( $existing ) : ?>
				<p style="margin:0 0 8px;">
					<?php esc_html_e( 'ملفات مرفوعة حاليًا:', 'k3d-3d-customizer' ); ?>
					<?php foreach ( $existing as $type => $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" style="margin-inline-end:10px;">
							<?php echo esc_html( strtoupper( $type ) ); ?> ↓
						</a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>

			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'k3d_3dc_upload_' . $item_id, 'k3d_3dc_upload_nonce' ); ?>
				<input type="hidden" name="action" value="k3d_3dc_upload_file" />
				<input type="hidden" name="order_id" value="<?php echo esc_attr( (string) $order->get_id() ); ?>" />
				<input type="hidden" name="item_id" value="<?php echo esc_attr( (string) $item_id ); ?>" />
				<input type="file" name="k3d_file" accept=".stl,.3mf,.obj,.zip" required />
				<button type="submit" class="button"><?php esc_html_e( 'رفع الملف', 'k3d-3d-customizer' ); ?></button>
			</form>
		</div>
	<?php endforeach; ?>
	<?php
}

add_action( 'admin_post_k3d_3dc_upload_file', function (): void {
	if ( ! current_user_can( 'edit_shop_orders' ) ) {
		wp_die( esc_html__( 'مفيش عندك صلاحية.', 'k3d-3d-customizer' ) );
	}

	$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	$item_id  = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;

	if ( ! isset( $_POST['k3d_3dc_upload_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['k3d_3dc_upload_nonce'] ), 'k3d_3dc_upload_' . $item_id ) ) {
		wp_die( esc_html__( 'انتهت صلاحية النموذج، رجّع الصفحة وجرّب تاني.', 'k3d-3d-customizer' ) );
	}

	$order = wc_get_order( $order_id );
	$item  = $order ? $order->get_item( $item_id ) : null;

	if ( ! $order instanceof WC_Order || ! $item instanceof WC_Order_Item_Product ) {
		wp_die( esc_html__( 'الطلب أو العنصر مش موجود.', 'k3d-3d-customizer' ) );
	}

	if ( empty( $_FILES['k3d_file'] ) || UPLOAD_ERR_OK !== $_FILES['k3d_file']['error'] ) {
		wp_safe_redirect( $order->get_edit_order_url() . '#k3d-3dc-upload-error' );
		exit;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';

	$allowed = [
		'stl' => 'model/stl',
		'3mf' => 'model/3mf',
		'obj' => 'text/plain',
		'zip' => 'application/zip',
	];

	add_filter( 'upload_mimes', function ( array $mimes ) use ( $allowed ): array {
		return array_merge( $mimes, $allowed );
	} );

	$uploaded = wp_handle_upload( $_FILES['k3d_file'], [ 'test_form' => false ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( empty( $uploaded['url'] ) ) {
		wp_safe_redirect( $order->get_edit_order_url() . '#k3d-3dc-upload-error' );
		exit;
	}

	$ext = strtolower( (string) pathinfo( $uploaded['file'], PATHINFO_EXTENSION ) );

	$item->add_meta_data( '_k3d_3dc_file_' . sanitize_key( $ext ), esc_url_raw( $uploaded['url'] ), true );
	$item->add_meta_data( '_k3d_3dc_generation_status', 'done', true );
	$item->add_meta_data(
		__( 'ملفات التصنيع', 'k3d-3d-customizer' ),
		strtoupper( $ext ) . ': ' . esc_url_raw( $uploaded['url'] ),
		true
	);
	$item->save();

	$order->add_order_note(
		sprintf(
			/* translators: %s: اسم الملف المرفوع */
			__( 'اترفع ملف تصنيع يدويًا: %s', 'k3d-3d-customizer' ),
			basename( $uploaded['file'] )
		)
	);

	wp_safe_redirect( $order->get_edit_order_url() . '#k3d-3dc-upload-success' );
	exit;
} );
