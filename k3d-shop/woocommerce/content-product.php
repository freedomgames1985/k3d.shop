<?php
/**
 * بطاقة منتج داخل أي حلقة منتجات (المتجر، "منتجات ذات صلة"، الصفحة
 * الرئيسية) - أوفررايد لقالب ووكومرس الافتراضي بشكل بطاقاتنا
 * (k3d_product_card في inc/template-tags.php).
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

k3d_product_card( $product );
