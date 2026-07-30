<?php
/**
 * أداتر OpenSCAD - مسؤول بس عن استدعاء خدمة توليد ملفات خارجية (Webhook)
 * وترجمة ردها لشكل موحّد. المفروض الخدمة دي شغالة على سيرفر عنده
 * OpenSCAD مثبّت فعليًا (استضافة ووردبريس العادية ما بتسمحش بتشغيل
 * OpenSCAD مباشرة) - راجع openscad-service-example/ جوه البلجن لمثال
 * تنفيذ حقيقي بايثون تقدر تشغّله على أي سيرفر/VPS عندك.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class K3D_3DC_OpenSCAD_Adapter implements K3D_3DC_Generation_Adapter {

	public function generate( array $design ): array {
		$settings = k3d_3dc_settings();
		$url      = trim( (string) $settings['webhook_url'] );

		if ( '' === $url ) {
			return [
				'success'   => false,
				'errorCode' => 'ADAPTER_NOT_CONFIGURED',
				'message'   => __( 'لسه ماتظبطش رابط خدمة توليد الملفات (OpenSCAD Adapter) من K3D 3D Customizer > الإعدادات.', 'k3d-3d-customizer' ),
			];
		}

		$response = wp_remote_post( $url, [
			'timeout' => 30,
			'headers' => [
				'Content-Type'   => 'application/json',
				'X-K3D-Secret'   => (string) $settings['webhook_secret'],
			],
			'body'    => wp_json_encode( $design ),
		] );

		if ( is_wp_error( $response ) ) {
			return [
				'success'   => false,
				'errorCode' => 'ADAPTER_UNREACHABLE',
				'message'   => $response->get_error_message(),
			];
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 || ! is_array( $body ) ) {
			return [
				'success'   => false,
				'errorCode' => 'SCAD_GENERATION_FAILED',
				/* translators: %d: HTTP status code */
				'message'   => sprintf( __( 'رد غير متوقع من خدمة التوليد (HTTP %d).', 'k3d-3d-customizer' ), $code ),
			];
		}

		return wp_parse_args( $body, [ 'success' => false ] );
	}
}
