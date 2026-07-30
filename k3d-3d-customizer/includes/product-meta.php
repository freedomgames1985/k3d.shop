<?php
/**
 * ميتا بوكس في صفحة تعديل المنتج - الأدمن يختار المنتجات اللي عايز
 * يفعّل عليها المعاينة 3D الحية (مش كل المنتجات)، ونوع التصميم
 * المناسب ليها من السجل (designs.php).
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const K3D_3DC_META_ENABLED    = '_k3d_3dc_enabled';
const K3D_3DC_META_DESIGN     = '_k3d_3dc_design';
const K3D_3DC_META_DEFAULT    = '_k3d_3dc_default_value';
const K3D_3DC_META_TEMPLATE   = '_k3d_3dc_template';
const K3D_3DC_META_FONT       = '_k3d_3dc_font';
const K3D_3DC_META_BASE_COLOR = '_k3d_3dc_base_color';
const K3D_3DC_META_BORDER     = '_k3d_3dc_border';

function k3d_3dc_product_enabled( int $product_id ): bool {
	return $product_id > 0 && 'yes' === get_post_meta( $product_id, K3D_3DC_META_ENABLED, true );
}

function k3d_3dc_product_design_key( int $product_id ): string {
	$design = (string) get_post_meta( $product_id, K3D_3DC_META_DESIGN, true );
	$design = $design && k3d_3dc_get_design( $design ) ? $design : 'name';

	return $design;
}

function k3d_3dc_product_default_value( int $product_id ): string {
	$value = (string) get_post_meta( $product_id, K3D_3DC_META_DEFAULT, true );

	if ( '' !== $value ) {
		return $value;
	}

	$design = k3d_3dc_get_design( k3d_3dc_product_design_key( $product_id ) );

	return $design['default_value'] ?? '';
}

add_action( 'add_meta_boxes', function (): void {
	add_meta_box(
		'k3d_3dc_product_box',
		__( 'المعاينة 3D الحية (K3D 3D Customizer)', 'k3d-3d-customizer' ),
		'k3d_3dc_render_product_meta_box',
		'product',
		'side',
		'default'
	);
} );

function k3d_3dc_render_product_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'k3d_3dc_save_meta', 'k3d_3dc_meta_nonce' );

	$enabled = k3d_3dc_product_enabled( $post->ID );
	$design  = k3d_3dc_product_design_key( $post->ID );
	$default = (string) get_post_meta( $post->ID, K3D_3DC_META_DEFAULT, true );
	?>
	<p>
		<label>
			<input type="checkbox" name="k3d_3dc_enabled" value="yes" <?php checked( $enabled ); ?> />
			<?php esc_html_e( 'فعّل المعاينة 3D الحية لهذا المنتج', 'k3d-3d-customizer' ); ?>
		</label>
	</p>
	<p>
		<label for="k3d_3dc_design" style="display:block;font-weight:600;margin-bottom:4px;">
			<?php esc_html_e( 'نوع التصميم', 'k3d-3d-customizer' ); ?>
		</label>
		<select name="k3d_3dc_design" id="k3d_3dc_design" style="width:100%;">
			<?php foreach ( k3d_3dc_get_designs() as $key => $def ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $design, $key ); ?>>
					<?php echo esc_html( $def['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="k3d_3dc_default" style="display:block;font-weight:600;margin-bottom:4px;">
			<?php esc_html_e( 'القيمة الافتراضية في المعاينة (اختياري)', 'k3d-3d-customizer' ); ?>
		</label>
		<input type="text" name="k3d_3dc_default" id="k3d_3dc_default" style="width:100%;" value="<?php echo esc_attr( $default ); ?>" />
	</p>
	<hr />
	<p style="font-weight:600;margin-bottom:2px;"><?php esc_html_e( 'ملف التصنيع (OpenSCAD Adapter)', 'k3d-3d-customizer' ); ?></p>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'دي مش بتأثر على المعاينة اللي الزبون شايفها - بس بتتبعت لخدمة توليد ملف الطباعة (STL) لما الطلب يتأكد.', 'k3d-3d-customizer' ); ?>
	</p>
	<p>
		<label for="k3d_3dc_template" style="display:block;font-weight:600;margin-bottom:4px;">
			<?php esc_html_e( 'اسم القالب (Template)', 'k3d-3d-customizer' ); ?>
		</label>
		<input type="text" name="k3d_3dc_template" id="k3d_3dc_template" style="width:100%;" placeholder="name_keychain" value="<?php echo esc_attr( get_post_meta( $post->ID, K3D_3DC_META_TEMPLATE, true ) ); ?>" />
	</p>
	<p>
		<label for="k3d_3dc_font" style="display:block;font-weight:600;margin-bottom:4px;">
			<?php esc_html_e( 'الخط (Font)', 'k3d-3d-customizer' ); ?>
		</label>
		<input type="text" name="k3d_3dc_font" id="k3d_3dc_font" style="width:100%;" placeholder="Lateef" value="<?php echo esc_attr( get_post_meta( $post->ID, K3D_3DC_META_FONT, true ) ); ?>" />
	</p>
	<p>
		<label for="k3d_3dc_base_color" style="display:block;font-weight:600;margin-bottom:4px;">
			<?php esc_html_e( 'لون القاعدة (Base Color)', 'k3d-3d-customizer' ); ?>
		</label>
		<input type="text" name="k3d_3dc_base_color" id="k3d_3dc_base_color" style="width:100%;" placeholder="White" value="<?php echo esc_attr( get_post_meta( $post->ID, K3D_3DC_META_BASE_COLOR, true ) ); ?>" />
	</p>
	<p>
		<label for="k3d_3dc_border" style="display:block;font-weight:600;margin-bottom:4px;">
			<?php esc_html_e( 'سمك الحافة بالمم (Border)', 'k3d-3d-customizer' ); ?>
		</label>
		<input type="number" step="0.1" min="0" name="k3d_3dc_border" id="k3d_3dc_border" style="width:100%;" placeholder="2.0" value="<?php echo esc_attr( get_post_meta( $post->ID, K3D_3DC_META_BORDER, true ) ); ?>" />
	</p>
	<p class="description">
		<?php esc_html_e( 'التصاميم الجديدة (لو اتضافت مستقبلًا كإضافة صغيرة للبلجن) هتظهر تلقائيًا في قايمة "نوع التصميم".', 'k3d-3d-customizer' ); ?>
	</p>
	<?php
}

add_action( 'save_post_product', function ( int $post_id ): void {
	if ( ! isset( $_POST['k3d_3dc_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['k3d_3dc_meta_nonce'] ), 'k3d_3dc_save_meta' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, K3D_3DC_META_ENABLED, ! empty( $_POST['k3d_3dc_enabled'] ) ? 'yes' : 'no' );

	if ( isset( $_POST['k3d_3dc_design'] ) ) {
		$design = sanitize_key( wp_unslash( $_POST['k3d_3dc_design'] ) );

		if ( k3d_3dc_get_design( $design ) ) {
			update_post_meta( $post_id, K3D_3DC_META_DESIGN, $design );
		}
	}

	if ( isset( $_POST['k3d_3dc_default'] ) ) {
		update_post_meta( $post_id, K3D_3DC_META_DEFAULT, sanitize_text_field( wp_unslash( $_POST['k3d_3dc_default'] ) ) );
	}

	if ( isset( $_POST['k3d_3dc_template'] ) ) {
		update_post_meta( $post_id, K3D_3DC_META_TEMPLATE, sanitize_text_field( wp_unslash( $_POST['k3d_3dc_template'] ) ) );
	}

	if ( isset( $_POST['k3d_3dc_font'] ) ) {
		update_post_meta( $post_id, K3D_3DC_META_FONT, sanitize_text_field( wp_unslash( $_POST['k3d_3dc_font'] ) ) );
	}

	if ( isset( $_POST['k3d_3dc_base_color'] ) ) {
		update_post_meta( $post_id, K3D_3DC_META_BASE_COLOR, sanitize_text_field( wp_unslash( $_POST['k3d_3dc_base_color'] ) ) );
	}

	if ( isset( $_POST['k3d_3dc_border'] ) && '' !== $_POST['k3d_3dc_border'] ) {
		update_post_meta( $post_id, K3D_3DC_META_BORDER, (float) wp_unslash( $_POST['k3d_3dc_border'] ) );
	}
} );
