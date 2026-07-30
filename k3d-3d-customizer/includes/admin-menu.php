<?php
/**
 * صفحة الإدارة في القائمة الجانبية بتاعة ووردبريس - تبويبين: (1) المنتجات
 * والتصاميم المفعّلة، (2) إعدادات مولّد ملفات التصنيع (Generation
 * Manager / OpenSCAD Adapter) زي ما هو موصوف في مستند SRS بتاع المنصة:
 * OpenSCAD مجرد "Adapter" قابل للاستبدال بيولّد ملفات التصنيع بس، مش
 * جزء من منطق المعاينة أو العرض.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function (): void {
	add_menu_page(
		__( 'K3D 3D Customizer', 'k3d-3d-customizer' ),
		__( 'K3D 3D Customizer', 'k3d-3d-customizer' ),
		'manage_woocommerce',
		'k3d-3dc',
		'k3d_3dc_render_admin_page',
		'dashicons-media-interactive',
		56
	);
} );

function k3d_3dc_render_admin_page(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'products';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'K3D 3D Customizer', 'k3d-3d-customizer' ); ?></h1>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=k3d-3dc&tab=products' ) ); ?>" class="nav-tab <?php echo 'products' === $tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'المنتجات والتصاميم', 'k3d-3d-customizer' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=k3d-3dc&tab=settings' ) ); ?>" class="nav-tab <?php echo 'settings' === $tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'مولّد ملفات التصنيع (OpenSCAD Adapter)', 'k3d-3d-customizer' ); ?>
			</a>
		</h2>
		<div style="margin-top:20px;max-width:960px;">
			<?php
			if ( 'settings' === $tab ) {
				k3d_3dc_render_settings_tab();
			} else {
				k3d_3dc_render_products_tab();
			}
			?>
		</div>
	</div>
	<?php
}

function k3d_3dc_render_products_tab(): void {
	$product_ids = get_posts( [
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => K3D_3DC_META_ENABLED, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => 'yes', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	] );
	?>
	<h2><?php esc_html_e( 'المنتجات المفعّل عليها المعاينة 3D', 'k3d-3d-customizer' ); ?></h2>
	<?php if ( empty( $product_ids ) ) : ?>
		<p>
			<?php esc_html_e( 'لسه مفيش منتجات مفعّل عليها المعاينة. افتح أي منتج من "المنتجات" وفعّلها من بوكس "المعاينة 3D الحية" في السايدبار.', 'k3d-3d-customizer' ); ?>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>"><?php esc_html_e( 'روح لقائمة المنتجات ←', 'k3d-3d-customizer' ); ?></a>
		</p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'المنتج', 'k3d-3d-customizer' ); ?></th>
					<th><?php esc_html_e( 'نوع التصميم', 'k3d-3d-customizer' ); ?></th>
					<th><?php esc_html_e( 'قالب OpenSCAD', 'k3d-3d-customizer' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $product_ids as $product_id ) : ?>
					<?php
					$design_key = k3d_3dc_product_design_key( $product_id );
					$design     = k3d_3dc_get_design( $design_key );
					$template   = get_post_meta( $product_id, K3D_3DC_META_TEMPLATE, true );
					?>
					<tr>
						<td><strong><?php echo esc_html( get_the_title( $product_id ) ); ?></strong></td>
						<td><?php echo esc_html( $design['label'] ?? $design_key ); ?></td>
						<td><?php echo esc_html( $template ?: '—' ); ?></td>
						<td><a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>"><?php esc_html_e( 'تعديل', 'k3d-3d-customizer' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<h2 style="margin-top:36px;"><?php esc_html_e( 'أنواع التصاميم المسجّلة', 'k3d-3d-customizer' ); ?></h2>
	<p class="description"><?php esc_html_e( 'أي تصميم جديد يتضاف مستقبلًا (عن طريق فلتر k3d_3dc_designs) هيظهر هنا تلقائيًا.', 'k3d-3d-customizer' ); ?></p>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'المفتاح', 'k3d-3d-customizer' ); ?></th>
				<th><?php esc_html_e( 'الاسم', 'k3d-3d-customizer' ); ?></th>
				<th><?php esc_html_e( 'الوصف', 'k3d-3d-customizer' ); ?></th>
				<th><?php esc_html_e( 'عدد الألوان', 'k3d-3d-customizer' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( k3d_3dc_get_designs() as $key => $def ) : ?>
				<tr>
					<td><code><?php echo esc_html( $key ); ?></code></td>
					<td><?php echo esc_html( $def['label'] ); ?></td>
					<td><?php echo esc_html( $def['description'] ); ?></td>
					<td><?php echo esc_html( (string) count( $def['colors'] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

function k3d_3dc_render_settings_tab(): void {
	if ( isset( $_POST['k3d_3dc_settings_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['k3d_3dc_settings_nonce'] ), 'k3d_3dc_save_settings' ) && current_user_can( 'manage_woocommerce' ) ) {
		update_option( 'k3d_3dc_settings', [
			'adapter'        => 'openscad',
			'webhook_url'    => esc_url_raw( wp_unslash( $_POST['webhook_url'] ?? '' ) ),
			'webhook_secret' => sanitize_text_field( wp_unslash( $_POST['webhook_secret'] ?? '' ) ),
			'auto_generate'  => ! empty( $_POST['auto_generate'] ) ? 'yes' : 'no',
		] );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'اتحفظت الإعدادات.', 'k3d-3d-customizer' ) . '</p></div>';
	}

	$settings = k3d_3dc_settings();
	?>
	<h2><?php esc_html_e( 'مولّد ملفات التصنيع', 'k3d-3d-customizer' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'زي ما هو موصوف في معمارية المنصة: OpenSCAD مجرد "Adapter" بيولّد ملفات STL/3MF بس - مش جزء من المعاينة أو منطق الموقع. الإضافة مش بتشغّل OpenSCAD على نفس السيرفر (استضافة ووردبريس العادية مش بتسمح بكده)؛ بدل كده بتبعت "Design Object" (القالب + النص + الخط + الألوان) لخدمة خارجية (Webhook) انت شغّلها على سيرفر عنده OpenSCAD مثبّت، وهي بترجع روابط الملفات الجاهزة.', 'k3d-3d-customizer' ); ?>
	</p>
	<form method="post">
		<?php wp_nonce_field( 'k3d_3dc_save_settings', 'k3d_3dc_settings_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="webhook_url"><?php esc_html_e( 'رابط خدمة التوليد (Webhook URL)', 'k3d-3d-customizer' ); ?></label></th>
				<td>
					<input type="url" id="webhook_url" name="webhook_url" class="regular-text" placeholder="https://example.com/generate" value="<?php echo esc_attr( $settings['webhook_url'] ); ?>" />
					<p class="description"><?php esc_html_e( 'الخدمة دي المفروض تستقبل POST بصيغة JSON: {"template":"...","parameters":{"text":"...","font":"...","baseColor":"...","textColor":"...","border":2.0}} وترجّع {"success":true,"files":{"stl":"..."}}.', 'k3d-3d-customizer' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="webhook_secret"><?php esc_html_e( 'مفتاح سري (اختياري)', 'k3d-3d-customizer' ); ?></label></th>
				<td>
					<input type="text" id="webhook_secret" name="webhook_secret" class="regular-text" value="<?php echo esc_attr( $settings['webhook_secret'] ); ?>" />
					<p class="description"><?php esc_html_e( 'بيتبعت في هيدر X-K3D-Secret عشان خدمة التوليد تتأكد إن الطلب فعلاً منك.', 'k3d-3d-customizer' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'التوليد التلقائي', 'k3d-3d-customizer' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="auto_generate" value="yes" <?php checked( 'yes', $settings['auto_generate'] ); ?> />
						<?php esc_html_e( 'ولّد ملفات التصنيع تلقائيًا لما الطلب يبقى "قيد التنفيذ"', 'k3d-3d-customizer' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'حفظ الإعدادات', 'k3d-3d-customizer' ) ); ?>
	</form>
	<?php
}
