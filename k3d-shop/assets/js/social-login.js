/**
 * تسجيل الدخول بجوجل/آبل على الموقع، عبر Firebase JS SDK (نفس مشروع
 * Firebase بتاع التطبيق). بيتحمّل بس في صفحة "حسابي"، وبس لو إعدادات
 * الويب (K3D_FIREBASE_WEB_CONFIG) متظبطة - k3d_firebase_web_configured().
 *
 * التدفق: Firebase بيرجع id_token في المتصفح → بنبعته لـ
 * admin-ajax.php?action=k3d_social_login → PHP (inc/social-login.php)
 * بيتحقق منه بنفس كلاس FirebaseIdTokenVerifier اللي الإضافة بتستخدمه،
 * ويعمل تسجيل دخول ووردبريس حقيقي (wp_set_auth_cookie) عشان ووكومرس
 * يتعرف على الزائر كمسجّل دخول على الموقع مش بس التطبيق.
 */
( function () {
	'use strict';

	if ( typeof firebase === 'undefined' || ! window.K3D_SHOP || ! window.K3D_SHOP.firebase ) {
		return;
	}

	if ( ! firebase.apps.length ) {
		firebase.initializeApp( window.K3D_SHOP.firebase );
	}

	function setLoading( btn, loading ) {
		btn.disabled = loading;
		btn.style.opacity = loading ? '0.6' : '';
	}

	function showError( message ) {
		var existing = document.querySelector( '.k3d-social-login-error' );
		if ( existing ) {
			existing.remove();
		}

		var card = document.querySelector( '.account-card' );
		if ( ! card ) {
			return;
		}

		var el = document.createElement( 'p' );
		el.className = 'k3d-social-login-error woocommerce-error';
		el.textContent = message;
		card.insertBefore( el, card.firstChild );
	}

	function completeLogin( idToken, provider, btn ) {
		var formData = new URLSearchParams();
		formData.set( 'action', 'k3d_social_login' );
		formData.set( 'nonce', window.K3D_SHOP.nonce );
		formData.set( 'provider', provider );
		formData.set( 'id_token', idToken );

		fetch( window.K3D_SHOP.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: formData.toString(),
		} )
			.then( function ( res ) { return res.json(); } )
			.then( function ( data ) {
				if ( data && data.success ) {
					window.location.href = data.data.redirect || window.K3D_SHOP.accountUrl;
				} else {
					showError( ( data && data.data && data.data.message ) || 'تعذّر تسجيل الدخول.' );
					setLoading( btn, false );
				}
			} )
			.catch( function () {
				showError( 'حصل خطأ في الاتصال. حاول تاني.' );
				setLoading( btn, false );
			} );
	}

	function signIn( provider, btn ) {
		setLoading( btn, true );

		var authProvider = 'google' === provider
			? new firebase.auth.GoogleAuthProvider()
			: new firebase.auth.OAuthProvider( 'apple.com' );

		firebase.auth().signInWithPopup( authProvider )
			.then( function ( result ) {
				return result.user.getIdToken();
			} )
			.then( function ( idToken ) {
				completeLogin( idToken, provider, btn );
			} )
			.catch( function ( error ) {
				if ( 'auth/popup-closed-by-user' === error.code ) {
					setLoading( btn, false );
					return;
				}
				showError( 'تعذّر تسجيل الدخول: ' + error.message );
				setLoading( btn, false );
			} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.k3d-social-btn' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				signIn( btn.dataset.provider, btn );
			} );
		} );
	} );
} )();
