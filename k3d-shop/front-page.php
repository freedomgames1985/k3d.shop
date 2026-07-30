<?php
/**
 * الصفحة الرئيسية - بترسم الأقسام بنفس الترتيب اللي الأدمن ظبطه من
 * Settings > الصفحة الرئيسية في إضافة k3d-shop-api (أو الترتيب
 * الافتراضي لو لسه ما اتغيّرش).
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary">
	<?php foreach ( k3d_home_layout() as $section_key ) : ?>
		<?php k3d_render_home_section( $section_key ); ?>
	<?php endforeach; ?>
	<?php k3d_render_how_it_works(); ?>
</main>

<?php
get_footer();
