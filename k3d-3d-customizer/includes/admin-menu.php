<?php
/**
 * صفحة الإدارة في القائمة الجانبية بتاعة ووردبريس - قائمة سريعة بكل
 * المنتجات المفعّل عليها المعاينة 3D، وأنواع التصاميم المسجّلة.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// manage_options (مش manage_woocommerce) عشان دي الصلاحية اللي مضمونة
// لأي أدمن حقيقي في الموقع - manage_woocommerce بتتحط لدور "مدير
// المتجر" عن طريق ووكومرس نفسها وقت التفعيل، ولو حصل أي خلل في جدول
// الأدوار (تعطيل/تفعيل ووكومرس، إضافة تانية عدّلت الأدوار...) ممكن
// حتى حساب الأدمن الأساسي يفضل من غيرها ويشوف "غير مسموح لك الوصول".
add_action( 'admin_menu', function (): void {
	add_menu_page(
		__( 'K3D 3D Customizer', 'k3d-3d-customizer' ),
		__( 'K3D 3D Customizer', 'k3d-3d-customizer' ),
		'manage_options',
		'k3d-3dc',
		'k3d_3dc_render_admin_page',
		'dashicons-media-interactive',
		56
	);
} );

function k3d_3dc_render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$product_ids = get_posts( [
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => K3D_3DC_META_ENABLED, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => 'yes', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	] );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'K3D 3D Customizer', 'k3d-3d-customizer' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'معاينة 3D حية للعميل قبل الطلب - النص واللون اللي يختارهم بيتسجلوا تلقائيًا في تفاصيل الطلب (مفيش أي ملف طباعة بيتولّد).', 'k3d-3d-customizer' ); ?>
		</p>

		<h2 style="margin-top:28px;"><?php esc_html_e( 'المنتجات المفعّل عليها المعاينة', 'k3d-3d-customizer' ); ?></h2>
		<?php if ( empty( $product_ids ) ) : ?>
			<p>
				<?php esc_html_e( 'لسه مفيش منتجات مفعّل عليها المعاينة. افتح أي منتج وفعّلها من بوكس "المعاينة 3D الحية" في السايدبار.', 'k3d-3d-customizer' ); ?>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>"><?php esc_html_e( 'روح لقائمة المنتجات ←', 'k3d-3d-customizer' ); ?></a>
			</p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'المنتج', 'k3d-3d-customizer' ); ?></th>
						<th><?php esc_html_e( 'نوع التصميم', 'k3d-3d-customizer' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $product_ids as $product_id ) : ?>
						<?php
						$design_key = k3d_3dc_product_design_key( $product_id );
						$design     = k3d_3dc_get_design( $design_key );
						?>
						<tr>
							<td><strong><?php echo esc_html( get_the_title( $product_id ) ); ?></strong></td>
							<td><?php echo esc_html( $design['label'] ?? $design_key ); ?></td>
							<td><a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>"><?php esc_html_e( 'تعديل', 'k3d-3d-customizer' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<h2 style="margin-top:36px;"><?php esc_html_e( 'أنواع التصاميم المسجّلة', 'k3d-3d-customizer' ); ?></h2>
		<p class="description"><?php esc_html_e( 'أي تصميم جديد يتضاف مستقبلًا (عن طريق فلتر k3d_3dc_designs) هيظهر هنا تلقائيًا.', 'k3d-3d-customizer' ); ?></p>
		<table class="widefat striped" style="max-width:800px;">
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
	</div>
	<?php
}
