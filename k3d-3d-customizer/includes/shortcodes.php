<?php
/**
 * [k3d_3dc_hero] - نفس ودجت المعاينة 3D لكن بوضع "hero" (تفاعلي بس،
 * من غير فورم submit)، للاستخدام في أي صفحة/قالب - الثيم بيستخدم
 * k3d_3dc_render_hero() مباشرة في الصفحة الرئيسية.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function k3d_3dc_render_hero( array $args = [] ): void {
	k3d_3dc_render_widget(
		$args['design'] ?? 'name',
		$args['default'] ?? '',
		'hero',
		$args
	);
}

add_shortcode( 'k3d_3dc_hero', function ( $atts ): string {
	$atts = shortcode_atts( [
		'design'  => 'name',
		'default' => '',
	], $atts, 'k3d_3dc_hero' );

	ob_start();
	k3d_3dc_render_hero( $atts );

	return (string) ob_get_clean();
} );
