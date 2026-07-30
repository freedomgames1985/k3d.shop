<?php
/**
 * صفحة إدارة "تصاميم مخصّصة" - بتسيب الأدمن يرفع تصميم 3D جديد (ملف JS
 * بيسجل نفسه بـregisterDesign()، بالظبط زي أي تصميم مدمج في الإضافة)
 * ويعبّي بياناته (الاسم، الألوان، أقصى عدد حروف...) من غير ما يلمس كود
 * الإضافة نفسه. أي تصميم يتضاف من هنا بيظهر أوتوماتيك في قائمة "نوع
 * التصميم" في كل منتج، بالظبط زي التصاميم الجاهزة.
 *
 * تنبيه أمان: رفع تصميم بيتطلب صلاحية manage_woocommerce (نفس صلاحية
 * إدارة الووكومرس والإضافات) - الملف اللي بيترفع بيشتغل كـJavaScript
 * حقيقي في متصفح كل زائر للموقع، فده لازم يفضل مقصور على أدمن موثوق
 * بيه، بالظبط زي محرر ملفات الثيم/الإضافات المدمج في ووردبريس.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const K3D_3DC_CUSTOM_DESIGNS_OPTION = 'k3d_3dc_custom_designs';
const K3D_3DC_CUSTOM_DESIGNS_DIR    = 'k3d-3dc-custom-designs';

/** @return array<string, array<string, mixed>> */
function k3d_3dc_get_custom_designs(): array {
	$stored = get_option( K3D_3DC_CUSTOM_DESIGNS_OPTION, [] );

	return is_array( $stored ) ? $stored : [];
}

function k3d_3dc_custom_designs_dir(): array {
	$upload_dir = wp_upload_dir();

	return [
		'path' => trailingslashit( $upload_dir['basedir'] ) . K3D_3DC_CUSTOM_DESIGNS_DIR,
		'url'  => trailingslashit( $upload_dir['baseurl'] ) . K3D_3DC_CUSTOM_DESIGNS_DIR,
	];
}

/** التصاميم المخصّصة بتتضاف لسجل التصاميم العادي - أي حاجة تانية في الإضافة (product-meta.php، product-render.php...) بتتعامل معاها زي أي تصميم مدمج من غير أي كود إضافي. */
add_filter( 'k3d_3dc_designs', function ( array $designs ): array {
	foreach ( k3d_3dc_get_custom_designs() as $key => $def ) {
		if ( isset( $designs[ $key ] ) ) {
			continue; // تصميم مدمج بنفس المفتاح له الأولوية.
		}

		$entry = [
			'label'         => $def['label'] ?? $key,
			'description'   => $def['description'] ?? '',
			'js_module'     => $key,
			'default_value' => $def['default_value'] ?? '',
			'value_label'   => $def['value_label'] ?? __( 'النص', 'k3d-3d-customizer' ),
			'max_length'    => (int) ( $def['max_length'] ?? 20 ),
			'charset'       => $def['charset'] ?? 'text',
			'colors'        => $def['colors'] ?? [],
		];

		if ( ! empty( $def['secondary'] ) ) {
			$entry['secondary'] = $def['secondary'];
		}

		$designs[ $key ] = $entry;
	}

	return $designs;
} );

/** روابط ملفات الـJS بتاعة التصاميم المخصّصة - init.js بيحمّلهم ديناميكيًا زي التصاميم المدمجة بالظبط. */
function k3d_3dc_custom_design_js_urls(): array {
	$urls = [];

	foreach ( k3d_3dc_get_custom_designs() as $def ) {
		if ( ! empty( $def['js_url'] ) ) {
			$urls[] = $def['js_url'];
		}
	}

	return $urls;
}

add_filter( 'k3d_3dc_config', function ( array $config ): array {
	$config['customDesignUrls'] = array_values( k3d_3dc_custom_design_js_urls() );

	return $config;
} );

add_action( 'admin_menu', function (): void {
	add_submenu_page(
		'k3d-3dc',
		__( 'تصاميم مخصّصة', 'k3d-3d-customizer' ),
		__( 'تصاميم مخصّصة', 'k3d-3d-customizer' ),
		'manage_woocommerce',
		'k3d-3dc-custom-designs',
		'k3d_3dc_render_custom_designs_page'
	);
} );

function k3d_3dc_custom_design_boilerplate(): string {
	return <<<'JS'
import { registerDesign } from '../customizer.js';

registerDesign( 'REPLACE_WITH_YOUR_KEY', function ( { THREE, scene, camera, controls, value, colorHex, value2 } ) {
	// اعمل هنا أي THREE.Mesh / THREE.Group واللي هتحتاجه، وضيفه لـscene.
	const group = new THREE.Group();
	scene.add( group );

	const geometry = new THREE.BoxGeometry( 2, 1, 0.3 );
	const material = new THREE.MeshStandardMaterial( { color: colorHex || '#D9583A' } );
	const mesh = new THREE.Mesh( geometry, material );
	group.add( mesh );

	return {
		object3D: group,
		// بيتنادى كل مرة العميل يغيّر النص/اللون (value2 = الخانة التانية
		// الاختيارية لو التصميم عرّفها، وإلا هتيجي فاضية دايمًا).
		update( newValue, newColorHex, newValue2 ) {
			material.color.set( newColorHex || '#D9583A' );
		},
		// بيتنادى لما الودجت يتشال من الصفحة - امسح هنا أي geometry/material عملتهم.
		dispose() {
			geometry.dispose();
			material.dispose();
		},
	};
} );
JS;
}

function k3d_3dc_render_custom_designs_page(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	$notice = '';
	$error  = '';

	if ( isset( $_POST['k3d_3dc_cd_action'] ) && check_admin_referer( 'k3d_3dc_custom_designs' ) ) {
		if ( 'add' === $_POST['k3d_3dc_cd_action'] ) {
			$error = k3d_3dc_handle_custom_design_upload();
			if ( ! $error ) {
				$notice = __( 'اتضاف التصميم بنجاح - هيظهر في قائمة "نوع التصميم" في كل منتج.', 'k3d-3d-customizer' );
			}
		} elseif ( 'delete' === $_POST['k3d_3dc_cd_action'] && ! empty( $_POST['design_key'] ) ) {
			k3d_3dc_delete_custom_design( sanitize_key( wp_unslash( $_POST['design_key'] ) ) );
			$notice = __( 'اتحذف التصميم.', 'k3d-3d-customizer' );
		}
	}

	$custom_designs = k3d_3dc_get_custom_designs();
	$builtin_keys   = [ 'name', 'name_layers', 'plate', 'car_plate' ];
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'تصاميم مخصّصة', 'k3d-3d-customizer' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'ارفع تصميم 3D جديد بالكامل (ملف JS يبني المجسم بنفسه) من غير ما تلمس كود الإضافة - هيظهر تلقائيًا كخيار جديد في قائمة "نوع التصميم" في أي منتج.', 'k3d-3d-customizer' ); ?>
		</p>

		<?php if ( $notice ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>
		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>

		<h2 style="margin-top:24px;"><?php esc_html_e( 'قالب جاهز لملف التصميم', 'k3d-3d-customizer' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'انسخ القالب ده، غيّر المفتاح والهندسة 3D اللي جوّاه بأي محرر أكواد، واحفظه كملف .js، وارفعه بالفورم تحت.', 'k3d-3d-customizer' ); ?>
		</p>
		<textarea readonly rows="14" style="width:100%;max-width:800px;font-family:monospace;font-size:12.5px;direction:ltr;text-align:left;" onclick="this.select()"><?php echo esc_textarea( k3d_3dc_custom_design_boilerplate() ); ?></textarea>

		<h2 style="margin-top:32px;"><?php esc_html_e( 'إضافة تصميم جديد', 'k3d-3d-customizer' ); ?></h2>
		<form method="post" enctype="multipart/form-data" style="max-width:640px;">
			<?php wp_nonce_field( 'k3d_3dc_custom_designs' ); ?>
			<input type="hidden" name="k3d_3dc_cd_action" value="add" />
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="k3d_cd_key"><?php esc_html_e( 'المفتاح (إنجليزي/underscore، بدون مسافات)', 'k3d-3d-customizer' ); ?></label></th>
					<td><input type="text" id="k3d_cd_key" name="key" class="regular-text" pattern="[a-z0-9_]+" required placeholder="my_design" /></td>
				</tr>
				<tr>
					<th><label for="k3d_cd_label"><?php esc_html_e( 'الاسم اللي يظهر للأدمن', 'k3d-3d-customizer' ); ?></label></th>
					<td><input type="text" id="k3d_cd_label" name="label" class="regular-text" required /></td>
				</tr>
				<tr>
					<th><label for="k3d_cd_desc"><?php esc_html_e( 'وصف قصير', 'k3d-3d-customizer' ); ?></label></th>
					<td><input type="text" id="k3d_cd_desc" name="description" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="k3d_cd_default"><?php esc_html_e( 'قيمة افتراضية للمعاينة', 'k3d-3d-customizer' ); ?></label></th>
					<td><input type="text" id="k3d_cd_default" name="default_value" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="k3d_cd_value_label"><?php esc_html_e( 'تسمية حقل الإدخال الأساسي', 'k3d-3d-customizer' ); ?></label></th>
					<td><input type="text" id="k3d_cd_value_label" name="value_label" class="regular-text" value="<?php esc_attr_e( 'الاسم أو النص', 'k3d-3d-customizer' ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="k3d_cd_max"><?php esc_html_e( 'أقصى عدد حروف', 'k3d-3d-customizer' ); ?></label></th>
					<td><input type="number" id="k3d_cd_max" name="max_length" class="small-text" min="1" max="64" value="14" /></td>
				</tr>
				<tr>
					<th><label for="k3d_cd_charset"><?php esc_html_e( 'نوع الإدخال', 'k3d-3d-customizer' ); ?></label></th>
					<td>
						<select id="k3d_cd_charset" name="charset">
							<option value="text"><?php esc_html_e( 'أي نص (اسم، عربي/إنجليزي)', 'k3d-3d-customizer' ); ?></option>
							<option value="digits-dash"><?php esc_html_e( 'أرقام وشرطة بس (رقم لوحة/تليفون)', 'k3d-3d-customizer' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="k3d_cd_colors"><?php esc_html_e( 'الألوان (سطر لكل لون: id|hex|الاسم)', 'k3d-3d-customizer' ); ?></label></th>
					<td>
						<textarea id="k3d_cd_colors" name="colors" rows="5" style="width:100%;max-width:420px;font-family:monospace;" placeholder="clay|#D9583A|طوبي&#10;ink|#0E2024|كحلي"></textarea>
						<p class="description"><?php esc_html_e( 'مثال: clay|#D9583A|طوبي - سطر منفصل لكل لون.', 'k3d-3d-customizer' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'خانة ثانية اختيارية', 'k3d-3d-customizer' ); ?></th>
					<td>
						<label><input type="checkbox" name="secondary_enabled" value="1" /> <?php esc_html_e( 'فعّل حقل إدخال تاني (اسم/نص إضافي)', 'k3d-3d-customizer' ); ?></label>
						<p>
							<input type="text" name="secondary_label" class="regular-text" placeholder="<?php esc_attr_e( 'تسمية الخانة التانية', 'k3d-3d-customizer' ); ?>" />
							<input type="number" name="secondary_max_length" class="small-text" min="1" max="64" value="16" placeholder="<?php esc_attr_e( 'أقصى حروف', 'k3d-3d-customizer' ); ?>" />
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="k3d_cd_file"><?php esc_html_e( 'ملف JS التصميم', 'k3d-3d-customizer' ); ?></label></th>
					<td><input type="file" id="k3d_cd_file" name="design_js" accept=".js" required /></td>
				</tr>
			</table>
			<?php submit_button( __( 'إضافة التصميم', 'k3d-3d-customizer' ) ); ?>
		</form>

		<?php if ( ! empty( $custom_designs ) ) : ?>
			<h2 style="margin-top:32px;"><?php esc_html_e( 'التصاميم المخصّصة الحالية', 'k3d-3d-customizer' ); ?></h2>
			<table class="widefat striped" style="max-width:800px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'المفتاح', 'k3d-3d-customizer' ); ?></th>
						<th><?php esc_html_e( 'الاسم', 'k3d-3d-customizer' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $custom_designs as $key => $def ) : ?>
						<tr>
							<td><code><?php echo esc_html( $key ); ?></code></td>
							<td><?php echo esc_html( $def['label'] ?? $key ); ?></td>
							<td>
								<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'متأكد؟', 'k3d-3d-customizer' ) ); ?>');">
									<?php wp_nonce_field( 'k3d_3dc_custom_designs' ); ?>
									<input type="hidden" name="k3d_3dc_cd_action" value="delete" />
									<input type="hidden" name="design_key" value="<?php echo esc_attr( $key ); ?>" />
									<button type="submit" class="button-link-delete"><?php esc_html_e( 'حذف', 'k3d-3d-customizer' ); ?></button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

function k3d_3dc_handle_custom_design_upload(): string {
	$key = isset( $_POST['key'] ) ? sanitize_key( wp_unslash( $_POST['key'] ) ) : '';

	if ( '' === $key || ! preg_match( '/^[a-z][a-z0-9_]*$/', $key ) ) {
		return __( 'المفتاح لازم يكون إنجليزي/underscore بس، ويبدأ بحرف.', 'k3d-3d-customizer' );
	}

	if ( in_array( $key, [ 'name', 'name_layers', 'plate', 'car_plate' ], true ) ) {
		return __( 'المفتاح ده محجوز لتصميم مدمج - اختار مفتاح تاني.', 'k3d-3d-customizer' );
	}

	$custom_designs = k3d_3dc_get_custom_designs();

	if ( isset( $custom_designs[ $key ] ) ) {
		return __( 'فيه تصميم مخصّص بنفس المفتاح ده خالص.', 'k3d-3d-customizer' );
	}

	if ( empty( $_FILES['design_js'] ) || UPLOAD_ERR_OK !== ( $_FILES['design_js']['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
		return __( 'محتاج ترفع ملف JS.', 'k3d-3d-customizer' );
	}

	$file = $_FILES['design_js'];

	if ( ! preg_match( '/\.js$/i', (string) $file['name'] ) ) {
		return __( 'الملف لازم يكون بامتداد ‎.js.', 'k3d-3d-customizer' );
	}

	if ( (int) $file['size'] > 300000 ) {
		return __( 'حجم الملف كبير أوي (أقصى حد 300 كيلوبايت).', 'k3d-3d-customizer' );
	}

	$js_content = file_get_contents( $file['tmp_name'] );

	if ( false === $js_content || ! str_contains( $js_content, 'registerDesign' ) ) {
		return __( 'الملف لازم يستخدم registerDesign(...) عشان يسجّل نفسه في محرك المعاينة.', 'k3d-3d-customizer' );
	}

	$dir = k3d_3dc_custom_designs_dir();

	if ( ! wp_mkdir_p( $dir['path'] ) ) {
		return __( 'مقدرناش نجهّز مجلد الرفع - راجع صلاحيات الملفات على السيرفر.', 'k3d-3d-customizer' );
	}

	$target_path = trailingslashit( $dir['path'] ) . $key . '.js';

	if ( false === move_uploaded_file( $file['tmp_name'], $target_path ) ) {
		return __( 'فشل حفظ الملف على السيرفر.', 'k3d-3d-customizer' );
	}

	$colors = [];
	$raw_colors = isset( $_POST['colors'] ) ? (string) wp_unslash( $_POST['colors'] ) : '';

	foreach ( preg_split( '/\r?\n/', $raw_colors ) as $line ) {
		$line = trim( $line );

		if ( '' === $line ) {
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line, 3 ) );

		if ( count( $parts ) < 2 || ! preg_match( '/^#[0-9a-fA-F]{3,8}$/', $parts[1] ) ) {
			continue;
		}

		$colors[] = [
			'id'    => sanitize_key( $parts[0] ),
			'hex'   => $parts[1],
			'label' => $parts[2] ?? $parts[0],
		];
	}

	if ( empty( $colors ) ) {
		$colors[] = [ 'id' => 'default', 'hex' => '#D9583A', 'label' => __( 'افتراضي', 'k3d-3d-customizer' ) ];
	}

	$entry = [
		'label'         => sanitize_text_field( wp_unslash( $_POST['label'] ?? $key ) ),
		'description'   => sanitize_text_field( wp_unslash( $_POST['description'] ?? '' ) ),
		'default_value' => sanitize_text_field( wp_unslash( $_POST['default_value'] ?? '' ) ),
		'value_label'   => sanitize_text_field( wp_unslash( $_POST['value_label'] ?? __( 'النص', 'k3d-3d-customizer' ) ) ),
		'max_length'    => max( 1, min( 64, (int) ( $_POST['max_length'] ?? 20 ) ) ),
		'charset'       => 'digits-dash' === ( $_POST['charset'] ?? '' ) ? 'digits-dash' : 'text',
		'colors'        => $colors,
		'js_url'        => trailingslashit( $dir['url'] ) . $key . '.js',
	];

	if ( ! empty( $_POST['secondary_enabled'] ) ) {
		$entry['secondary'] = [
			'label'       => sanitize_text_field( wp_unslash( $_POST['secondary_label'] ?? __( 'نص إضافي', 'k3d-3d-customizer' ) ) ),
			'placeholder' => sanitize_text_field( wp_unslash( $_POST['secondary_label'] ?? '' ) ),
			'max_length'  => max( 1, min( 64, (int) ( $_POST['secondary_max_length'] ?? 16 ) ) ),
		];
	}

	$custom_designs[ $key ] = $entry;
	update_option( K3D_3DC_CUSTOM_DESIGNS_OPTION, $custom_designs );

	return '';
}

function k3d_3dc_delete_custom_design( string $key ): void {
	$custom_designs = k3d_3dc_get_custom_designs();

	if ( ! isset( $custom_designs[ $key ] ) ) {
		return;
	}

	$dir  = k3d_3dc_custom_designs_dir();
	$file = trailingslashit( $dir['path'] ) . $key . '.js';

	if ( file_exists( $file ) ) {
		wp_delete_file( $file );
	}

	unset( $custom_designs[ $key ] );
	update_option( K3D_3DC_CUSTOM_DESIGNS_OPTION, $custom_designs );
}
