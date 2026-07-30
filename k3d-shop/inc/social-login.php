<?php
/**
 * جسر تسجيل الدخول بجوجل/آبل للموقع (مش التطبيق). إضافة k3d-shop-api عندها
 * REST endpoints (/customer/google-login, /customer/apple-login) لكنها
 * مصممة للتطبيق: بترجع JWT مستقل، من غير ما تعمل تسجيل دخول WordPress
 * حقيقي (wp_set_auth_cookie) - وده مطلوب هنا عشان WooCommerce (السلة،
 * الحساب، الطلبات) يتعرف على الزائر كمسجّل دخول على الموقع نفسه.
 *
 * الحل: بنستخدم نفس كلاسات التحقق اللي الإضافة بتستخدمها داخليًا
 * (FirebaseIdTokenVerifier + CustomerAuthService) مباشرة من نفس عملية
 * PHP - من غير ما نعيد كتابة منطق التحقق من Firebase token - وبعدين
 * نعمل wp_set_auth_cookie() بنفسنا. لو الإضافة مش شغالة، الـendpoint
 * بيرجع خطأ واضح بدل ما يكسر.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_nopriv_k3d_social_login', 'k3d_handle_social_login' );
add_action( 'wp_ajax_k3d_social_login', 'k3d_handle_social_login' );

function k3d_handle_social_login(): void {
	check_ajax_referer( 'k3d_shop_social_login', 'nonce' );

	$provider = isset( $_POST['provider'] ) ? sanitize_key( wp_unslash( $_POST['provider'] ) ) : '';
	$id_token = isset( $_POST['id_token'] ) ? (string) wp_unslash( $_POST['id_token'] ) : '';

	if ( ! in_array( $provider, [ 'google', 'apple' ], true ) || '' === $id_token ) {
		wp_send_json_error( [ 'message' => __( 'بيانات ناقصة.', 'k3d-shop' ) ], 422 );
	}

	if ( ! class_exists( '\K3D\ShopAPI\Auth\FirebaseIdTokenVerifier' )
		|| ! class_exists( '\K3D\ShopAPI\V1\Services\CustomerAuthService' )
	) {
		wp_send_json_error( [ 'message' => __( 'خدمة تسجيل الدخول غير متاحة حاليًا.', 'k3d-shop' ) ], 503 );
	}

	$claims = \K3D\ShopAPI\Auth\FirebaseIdTokenVerifier::verify( $id_token );

	if ( null === $claims ) {
		wp_send_json_error( [ 'message' => __( 'تعذّر التحقق من تسجيل الدخول.', 'k3d-shop' ) ], 401 );
	}

	$user_id = \K3D\ShopAPI\V1\Services\CustomerAuthService::find_or_create_customer( $claims );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( [ 'message' => $user_id->get_error_message() ], 422 );
	}

	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	if ( function_exists( 'wc_get_page_permalink' ) ) {
		do_action( 'wp_login', get_userdata( $user_id )->user_login, get_userdata( $user_id ) ); // phpcs:ignore WordPress.NamingConventions.ValidHookName
	}

	wp_send_json_success( [
		'redirect' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ),
	] );
}
