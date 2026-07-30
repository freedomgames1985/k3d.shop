<?php
/**
 * رسم واجهة المعاينة 3D (نفس الماركب بيتستخدم في صفحة المنتج وفي هيرو
 * الصفحة الرئيسية) - محرك الـJS بيقرأ كل حاجة من data-* attributes، فمش
 * محتاجين نكرر منطق التصميم هنا، بس نمرر القيم.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $mode 'product' (جوه فورم "أضف للسلة"، الحقول بتتبعت مع الطلب) أو 'hero' (تفاعلي بس، من غير submit).
 */
function k3d_3dc_render_widget( string $design_key, string $default_value = '', string $mode = 'product', array $args = [] ): void {
	$design = k3d_3dc_get_design( $design_key );

	if ( ! $design ) {
		return;
	}

	$default_value = '' !== $default_value ? $default_value : $design['default_value'];
	$widget_id      = 'k3d-3dc-' . wp_unique_id();
	$field_name     = 'product' === $mode ? 'k3d_3dc_value' : '';
	$color_field    = 'product' === $mode ? 'k3d_3dc_color' : '';
	$default_color  = $design['colors'][0]['id'] ?? '';
	?>
	<div
		id="<?php echo esc_attr( $widget_id ); ?>"
		class="k3d-3dc-widget k3d-3dc-mode-<?php echo esc_attr( $mode ); ?>"
		data-k3d-3dc
		data-design="<?php echo esc_attr( $design_key ); ?>"
	>
		<div class="k3d-3dc-stage">
			<span class="k3d-3dc-badge"><i></i><?php echo esc_html( $args['live_label'] ?? __( 'معاينة حية', 'k3d-3d-customizer' ) ); ?></span>
			<canvas class="k3d-3dc-canvas"></canvas>
			<div class="k3d-3dc-loading"><?php esc_html_e( 'جاري تجهيز المعاينة...', 'k3d-3d-customizer' ); ?></div>
			<span class="k3d-3dc-hint"><?php esc_html_e( 'اسحب للتدوير · عجلة الماوس للتكبير', 'k3d-3d-customizer' ); ?></span>
		</div>
		<div class="k3d-3dc-controls">
			<input
				type="text"
				class="k3d-3dc-input"
				<?php echo $field_name ? 'name="' . esc_attr( $field_name ) . '"' : ''; ?>
				maxlength="<?php echo esc_attr( (string) $design['max_length'] ); ?>"
				value="<?php echo esc_attr( $default_value ); ?>"
				placeholder="<?php echo esc_attr( $design['value_label'] ); ?>"
				autocomplete="off"
			/>
			<div class="k3d-3dc-colors">
				<?php foreach ( $design['colors'] as $i => $color ) : ?>
					<button
						type="button"
						class="k3d-3dc-color<?php echo 0 === $i ? ' is-active' : ''; ?>"
						style="--k3d-3dc-sw:<?php echo esc_attr( $color['hex'] ); ?>"
						data-color-id="<?php echo esc_attr( $color['id'] ); ?>"
						aria-label="<?php echo esc_attr( $color['label'] ); ?>"
					></button>
				<?php endforeach; ?>
			</div>
			<?php if ( $color_field ) : ?>
				<input type="hidden" class="k3d-3dc-color-field" name="<?php echo esc_attr( $color_field ); ?>" value="<?php echo esc_attr( $default_color ); ?>" />
			<?php endif; ?>
		</div>
	</div>
	<?php
}

add_action( 'woocommerce_before_add_to_cart_button', function (): void {
	global $product;

	if ( ! $product instanceof WC_Product || ! k3d_3dc_product_enabled( $product->get_id() ) ) {
		return;
	}

	echo '<div class="k3d-3dc-product-wrap">';
	k3d_3dc_render_widget(
		k3d_3dc_product_design_key( $product->get_id() ),
		k3d_3dc_product_default_value( $product->get_id() ),
		'product'
	);
	echo '</div>';
} );
