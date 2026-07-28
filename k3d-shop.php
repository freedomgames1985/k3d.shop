<?php
/**
 * Plugin Name: K3D Shop Custom
 * Description: بلجن مخصص لموقع k3d.shop، متصل تلقائيًا عبر GitHub Deployments.
 * Version: 1.0.0
 * Author: freedomgames1985
 */

if (!defined('ABSPATH')) {
    exit;
}

// إضافة شريط بسيط في تذييل الموقع للتأكد من أن النشر شغال
add_action('wp_footer', function () {
    echo '<div style="text-align:center;padding:8px;font-size:12px;color:#888;">تم تحديث الموقع عبر GitHub — نسخة 1.0.0</div>';
});
