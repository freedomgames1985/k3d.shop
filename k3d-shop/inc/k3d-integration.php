<?php
/**
 * طبقة الربط بين القالب وإضافة k3d-shop-api. كل القالب بيقرا من هنا بس -
 * لو الإضافة مش شغالة (اتعطلت أو اتشالت)، الدوال دي بترجع قيم افتراضية
 * آمنة (Fallback) بدل ما توقع خطأ فادح.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * الإضافة شغالة فعلاً؟ كل الدوال التانية هنا بتتأكد من الشرط ده الأول.
 */
function k3d_plugin_active(): bool {
	return class_exists( '\K3D\ShopAPI\V1\Services\SettingsService' )
		&& class_exists( '\K3D\ShopAPI\V1\Services\ContentBlockService' );
}

/**
 * معلومات المتجر (SettingsService::get_store) - الاسم، الإيميل، الهاتف، العنوان.
 */
function k3d_store_settings(): array {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	if ( ! k3d_plugin_active() ) {
		return $cache = [
			'name'  => get_bloginfo( 'name' ),
			'email' => get_bloginfo( 'admin_email' ),
			'phone' => '',
		];
	}

	return $cache = \K3D\ShopAPI\V1\Services\SettingsService::get_store();
}

/**
 * روابط ومعلومات التواصل + اللوجو (SettingsService::get_app_links).
 * الحقل 'logo' لو موجود، القالب بيعرضه بدل اسم المتجر النصي.
 */
function k3d_app_links(): array {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	if ( ! k3d_plugin_active() ) {
		return $cache = [
			'logo'             => '',
			'app_name'         => get_bloginfo( 'name' ),
			'contact_us_email' => get_bloginfo( 'admin_email' ),
			'contact_us_call'  => '',
			'about_us_url'     => '',
			'privacy_policy_url'   => '',
			'terms_conditions_url' => '',
			'return_policy_url'    => '',
			'home_layout'      => k3d_default_home_layout(),
		];
	}

	return $cache = \K3D\ShopAPI\V1\Services\SettingsService::get_app_links();
}

function k3d_default_home_layout(): array {
	return [
		'hero_banner',
		'slider',
		'categories',
		'trending_products',
		'secondary_banner',
		'on_sale_products',
		'category_shelves',
		'featured_products',
		'other_banner',
		'latest_products',
		'blog',
	];
}

/**
 * ترتيب أقسام الصفحة الرئيسية زي ما الأدمن ظبطه من Settings > الصفحة الرئيسية
 * (نفس الترتيب اللي التطبيق بيستخدمه بالظبط).
 */
function k3d_home_layout(): array {
	if ( ! k3d_plugin_active() ) {
		return k3d_default_home_layout();
	}

	return \K3D\ShopAPI\V1\Services\SettingsService::get_home_layout();
}

/**
 * عناصر "المحتوى الترويجي" لمجموعة معيّنة (home_primary, home_secondary, home_other,
 * slider, intro, banners, ads) - نفس المنطق اللي CustomerContentController::home_banners
 * بيستخدمه: بس العناصر المفعّلة (active=true)، مرتبة بـ sort_order.
 *
 * @return array<int, array<string, mixed>>
 */
function k3d_content_blocks( string $group ): array {
	if ( ! k3d_plugin_active() ) {
		return [];
	}

	$valid_groups = \K3D\ShopAPI\V1\Services\ContentBlockService::GROUPS;

	if ( ! in_array( $group, $valid_groups, true ) ) {
		return [];
	}

	return array_values(
		array_filter(
			\K3D\ShopAPI\V1\Services\ContentBlockService::list( $group ),
			static fn( array $block ) => ! empty( $block['active'] )
		)
	);
}

/**
 * أول عنصر مفعّل بس في مجموعة معينة - مفيد للبانرات اللي بتاخد عنصر واحد بس
 * (زي البانر الرئيسي في الهيرو).
 */
function k3d_first_content_block( string $group ): ?array {
	$items = k3d_content_blocks( $group );

	return $items[0] ?? null;
}
