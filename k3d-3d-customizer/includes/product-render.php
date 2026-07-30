<?php
/**
 * رسم واجهة المعاينة 3D (نفس الماركب بيتستخدم في صفحة المنتج وفي هيرو
 * الصفحة الرئيسية) - محرك الـJS بيقرأ كل حاجة من data-* attributes، فمش
 * محتاجين نكرر منطق التصميم هنا، بس نمرر القيم.
 *
 * في وضع "hero" الودجت بلوك واحد (stage + controls سوا) زي ما كان دايمًا.
 * في وضع "product" بنقسم الودجت لبلوكين منفصلين في الصفحة: الـstage
 * (الكانفاس) بيتحط جوه معرض صور المنتج نفسه (product-gallery) ومخفي
 * لحد ما العميل يبدأ يكتب، والـcontrols (حقل الاسم/الألوان) فاضل مكانه
 * القديم قرب زرار "أضف للسلة" - بالظبط زي أي متجر تخصيص حقيقي، صورة
 * المنتج نفسها بتتحول لمعاينة حية بدل ما تبقى بلوك منفصل تحت.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function k3d_3dc_render_stage_inner( array $args ): void {
	?>
	<span class="k3d-3dc-badge"><i></i><?php echo esc_html( $args['live_label'] ?? __( 'معاينة حية', 'k3d-3d-customizer' ) ); ?></span>
	<div class="k3d-3dc-stage-actions">
		<button type="button" class="k3d-3dc-refresh" aria-label="<?php esc_attr_e( 'إعادة ضبط المعاينة', 'k3d-3d-customizer' ); ?>" title="<?php esc_attr_e( 'إعادة ضبط المعاينة', 'k3d-3d-customizer' ); ?>">↻</button>
		<button type="button" class="k3d-3dc-expand" aria-label="<?php esc_attr_e( 'ملء الشاشة', 'k3d-3d-customizer' ); ?>" title="<?php esc_attr_e( 'ملء الشاشة', 'k3d-3d-customizer' ); ?>">⛶</button>
	</div>
	<canvas class="k3d-3dc-canvas"></canvas>
	<div class="k3d-3dc-loading"><?php esc_html_e( 'جاري تجهيز المعاينة...', 'k3d-3d-customizer' ); ?></div>
	<span class="k3d-3dc-hint"><?php esc_html_e( 'اسحب للتدوير · عجلة الماوس للتكبير', 'k3d-3d-customizer' ); ?></span>
	<?php
}

function k3d_3dc_render_controls_inner( array $design, string $default_value, string $field_name, string $color_field ): void {
	$default_color = $design['colors'][0]['id'] ?? '';
	?>
	<?php if ( $field_name ) : ?>
		<button type="button" class="k3d-3dcb-trigger" id="k3d-3dcb-trigger">🎁 <?php esc_html_e( 'عايز أكتر من قطعة؟ اطلب دفعة واحدة', 'k3d-3d-customizer' ); ?></button>
	<?php endif; ?>
	<p class="k3d-3dc-input-hint">
		<?php
		printf(
			/* translators: %d: أقصى عدد حروف */
			esc_html__( 'حتى %d حرف · عربي / إنجليزي / أي لغة', 'k3d-3d-customizer' ),
			(int) $design['max_length']
		);
		?>
	</p>
	<input
		type="text"
		class="k3d-3dc-input"
		<?php echo $field_name ? 'name="' . esc_attr( $field_name ) . '"' : ''; ?>
		maxlength="<?php echo esc_attr( (string) $design['max_length'] ); ?>"
		value="<?php echo esc_attr( $default_value ); ?>"
		placeholder="<?php echo esc_attr( $design['value_label'] ); ?>"
		autocomplete="off"
	/>
	<?php if ( ! empty( $design['secondary'] ) ) : $sec = $design['secondary']; ?>
		<input
			type="text"
			class="k3d-3dc-input-2"
			<?php echo $field_name ? 'name="k3d_3dc_value2"' : ''; ?>
			maxlength="<?php echo esc_attr( (string) ( $sec['max_length'] ?? 20 ) ); ?>"
			value=""
			placeholder="<?php echo esc_attr( $sec['placeholder'] ?? $sec['label'] ?? '' ); ?>"
			autocomplete="off"
		/>
	<?php endif; ?>
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
		<input type="hidden" class="k3d-3dc-snapshot-field" name="k3d_3dc_snapshot" value="" />
	<?php endif; ?>
	<p class="k3d-3dc-warning">
		<?php esc_html_e( 'ℹ️ الألوان في المعاينة تقريبية وممكن تختلف شوية عن اللون المطبوع فعليًا. المنتج بيتصنّع بالظبط زي ما اتكتب هنا - راجع الإملاء وحالة الحروف قبل الطلب.', 'k3d-3d-customizer' ); ?>
	</p>
	<?php
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
	?>
	<div
		id="<?php echo esc_attr( $widget_id ); ?>"
		class="k3d-3dc-widget k3d-3dc-mode-<?php echo esc_attr( $mode ); ?>"
		data-k3d-3dc
		data-design="<?php echo esc_attr( $design_key ); ?>"
	>
		<div class="k3d-3dc-stage">
			<?php k3d_3dc_render_stage_inner( $args ); ?>
		</div>
		<div class="k3d-3dc-controls">
			<?php k3d_3dc_render_controls_inner( $design, $default_value, $field_name, $color_field ); ?>
		</div>
	</div>
	<?php
}

/** معرّف ثابت لكل منتج (مش عشوائي) عشان بلوك الـstage (جوه المعرض) وبلوك الـcontrols (قرب زرار السلة) يتفقوا على نفس الـID من غير ما نحتاج نمرر حاجة بينهم. */
function k3d_3dc_product_widget_id( int $product_id ): string {
	return 'k3d-3dc-p' . $product_id;
}

/** بلوك الـstage بس (الكانفاس) - بيتحط جوه معرض صور المنتج، قبل صور ووكومرس نفسها، عشان يقدر يحل محلها بصريًا. */
add_action( 'woocommerce_before_single_product_summary', function (): void {
	global $product;

	if ( ! $product instanceof WC_Product || ! k3d_3dc_product_enabled( $product->get_id() ) ) {
		return;
	}

	$design_key = k3d_3dc_product_design_key( $product->get_id() );
	$design     = k3d_3dc_get_design( $design_key );

	if ( ! $design ) {
		return;
	}
	?>
	<div
		id="<?php echo esc_attr( k3d_3dc_product_widget_id( $product->get_id() ) ); ?>"
		class="k3d-3dc-widget k3d-3dc-mode-product"
		data-k3d-3dc
		data-design="<?php echo esc_attr( $design_key ); ?>"
	>
		<div class="k3d-3dc-stage">
			<?php k3d_3dc_render_stage_inner( [] ); ?>
		</div>
	</div>
	<?php
}, 5 );

/** بلوك الـcontrols بس (الاسم/الألوان) - في نفس مكانه القديم قرب زرار "أضف للسلة". */
add_action( 'woocommerce_before_add_to_cart_button', function (): void {
	global $product;

	if ( ! $product instanceof WC_Product || ! k3d_3dc_product_enabled( $product->get_id() ) ) {
		return;
	}

	$design_key = k3d_3dc_product_design_key( $product->get_id() );
	$design     = k3d_3dc_get_design( $design_key );

	if ( ! $design ) {
		return;
	}

	$default_value = k3d_3dc_product_default_value( $product->get_id() );
	?>
	<div class="k3d-3dc-product-wrap">
		<div class="k3d-3dc-controls-ext" data-k3d-3dc-for="<?php echo esc_attr( k3d_3dc_product_widget_id( $product->get_id() ) ); ?>">
			<div class="k3d-3dc-controls">
				<?php k3d_3dc_render_controls_inner( $design, $default_value, 'k3d_3dc_value', 'k3d_3dc_color' ); ?>
			</div>
		</div>
		<?php k3d_3dc_render_specs( $product->get_id() ); ?>
	</div>
	<?php
} );

/** قايمة مواصفات ثابتة (طول، ارتفاع الحرف، الخامة...) - نص حر يظبطه الأدمن لكل منتج، سطر لكل مواصفة بصيغة "التسمية: القيمة". */
function k3d_3dc_render_specs( int $product_id ): void {
	$raw = trim( (string) get_post_meta( $product_id, K3D_3DC_META_SPECS, true ) );

	if ( '' === $raw ) {
		return;
	}

	$lines = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );

	if ( empty( $lines ) ) {
		return;
	}
	?>
	<div class="k3d-3dc-specs">
		<h4><?php esc_html_e( 'مواصفات المنتج', 'k3d-3d-customizer' ); ?></h4>
		<ul>
			<?php foreach ( $lines as $line ) : ?>
				<?php
				$parts = explode( ':', $line, 2 );
				$label = trim( $parts[0] ?? '' );
				$value = trim( $parts[1] ?? '' );
				?>
				<li>
					<span><?php echo esc_html( $label ); ?></span>
					<?php if ( '' !== $value ) : ?><b><?php echo esc_html( $value ); ?></b><?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}
