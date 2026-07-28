<?php
/**
 * نموذج تسجيل الدخول/حساب جديد - أوفررايد لقالب ووكومرس الافتراضي.
 * أسماء الحقول والـnonce زي الأصل بالظبط (WooCommerce بيتحقق منها بالاسم:
 * username/password/login، email/password/register) - بس الشكل والتنظيم
 * (تابات + أزرار جوجل/آبل) بتاعنا. تسجيل الدخول بجوجل/آبل بيتم عبر
 * assets/js/social-login.js اللي بيكلّم AJAX action "k3d_social_login"
 * (inc/social-login.php) بعد ما Firebase يتحقق من الهوية في المتصفح.
 *
 * @package K3D_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_user_logged_in() ) {
	return;
}

$registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );

do_action( 'woocommerce_before_customer_login_form' );
?>

<section class="account-section">
	<div class="account-card">
		<?php if ( $registration_enabled ) : ?>
			<div class="account-tabs">
				<button type="button" class="account-tab is-active" data-pane="login"><?php esc_html_e( 'تسجيل الدخول', 'k3d-shop' ); ?></button>
				<button type="button" class="account-tab" data-pane="register"><?php esc_html_e( 'حساب جديد', 'k3d-shop' ); ?></button>
			</div>
		<?php endif; ?>

		<div class="account-pane" data-pane="login">
			<h1><?php esc_html_e( 'تسجيل الدخول', 'k3d-shop' ); ?></h1>

			<?php if ( k3d_firebase_web_configured() ) : ?>
				<div class="social-auth">
					<button type="button" class="social-btn k3d-social-btn" data-provider="google">
						<svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
						<span><?php esc_html_e( 'المتابعة بواسطة جوجل', 'k3d-shop' ); ?></span>
					</button>
					<button type="button" class="social-btn k3d-social-btn" data-provider="apple">
						<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/></svg>
						<span><?php esc_html_e( 'المتابعة بواسطة Apple', 'k3d-shop' ); ?></span>
					</button>
				</div>
				<div class="auth-divider"><span><?php esc_html_e( 'أو', 'k3d-shop' ); ?></span></div>
			<?php endif; ?>

			<form class="account-form woocommerce-form woocommerce-form-login login" method="post">
				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<label class="form-field form-field-full">
					<span><?php esc_html_e( 'البريد الإلكتروني أو اسم المستخدم', 'k3d-shop' ); ?></span>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
				</label>

				<label class="form-field form-field-full">
					<span><?php esc_html_e( 'كلمة المرور', 'k3d-shop' ); ?></span>
					<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
				</label>

				<?php do_action( 'woocommerce_login_form' ); ?>

				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit" class="btn btn-primary account-submit" name="login" value="<?php esc_attr_e( 'دخول', 'k3d-shop' ); ?>"><?php esc_html_e( 'دخول', 'k3d-shop' ); ?></button>

				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="account-forgot"><?php esc_html_e( 'نسيت كلمة المرور؟', 'k3d-shop' ); ?></a>

				<?php do_action( 'woocommerce_login_form_end' ); ?>
			</form>

			<?php if ( $registration_enabled ) : ?>
				<p class="account-switch">
					<span><?php esc_html_e( 'مفيش حساب عندك؟', 'k3d-shop' ); ?></span>
					<button type="button" class="account-switch-link" data-pane="register"><?php esc_html_e( 'سجّل دلوقتي', 'k3d-shop' ); ?></button>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( $registration_enabled ) : ?>
			<div class="account-pane" data-pane="register" hidden>
				<h1><?php esc_html_e( 'إنشاء حساب', 'k3d-shop' ); ?></h1>

				<?php if ( k3d_firebase_web_configured() ) : ?>
					<div class="social-auth">
						<button type="button" class="social-btn k3d-social-btn" data-provider="google">
							<svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
							<span><?php esc_html_e( 'المتابعة بواسطة جوجل', 'k3d-shop' ); ?></span>
						</button>
						<button type="button" class="social-btn k3d-social-btn" data-provider="apple">
							<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/></svg>
							<span><?php esc_html_e( 'المتابعة بواسطة Apple', 'k3d-shop' ); ?></span>
						</button>
					</div>
					<div class="auth-divider"><span><?php esc_html_e( 'أو', 'k3d-shop' ); ?></span></div>
				<?php endif; ?>

				<form method="post" class="account-form woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
						<label class="form-field form-field-full">
							<span><?php esc_html_e( 'اسم المستخدم', 'k3d-shop' ); ?></span>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
						</label>
					<?php endif; ?>

					<label class="form-field form-field-full">
						<span><?php esc_html_e( 'البريد الإلكتروني', 'k3d-shop' ); ?></span>
						<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
					</label>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
						<label class="form-field form-field-full">
							<span><?php esc_html_e( 'كلمة المرور', 'k3d-shop' ); ?></span>
							<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
						</label>
					<?php else : ?>
						<p><?php esc_html_e( 'هيتبعتلك كلمة مرور على الإيميل.', 'k3d-shop' ); ?></p>
					<?php endif; ?>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
					<button type="submit" class="btn btn-primary account-submit" name="register" value="<?php esc_attr_e( 'إنشاء الحساب', 'k3d-shop' ); ?>"><?php esc_html_e( 'إنشاء الحساب', 'k3d-shop' ); ?></button>

					<?php do_action( 'woocommerce_register_form_end' ); ?>
				</form>

				<p class="account-switch">
					<span><?php esc_html_e( 'عندك حساب بالفعل؟', 'k3d-shop' ); ?></span>
					<button type="button" class="account-switch-link" data-pane="login"><?php esc_html_e( 'سجّل دخولك', 'k3d-shop' ); ?></button>
				</p>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
