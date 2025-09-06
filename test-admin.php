<?php
/**
 * Admin Menü Test Dosyası
 */

// WordPress'i yükle
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>Admin Menü Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// 1. Admin sınıfı kontrolü
echo "<h2>1. Admin Sınıfı Kontrolü</h2>";
if (class_exists('WP_Sozluk_Admin')) {
    echo "<p class='success'>✓ WP_Sozluk_Admin sınıfı yüklendi</p>";
} else {
    echo "<p class='error'>✗ WP_Sozluk_Admin sınıfı yüklenemedi</p>";
}

// 2. Admin hook kontrolü
echo "<h2>2. Admin Hook Kontrolü</h2>";
global $wp_filter;

if (isset($wp_filter['admin_menu'])) {
    echo "<p class='success'>✓ admin_menu hook'u aktif</p>";
    
    // WP_Sozluk_Admin sınıfının hook'u kayıtlı mı?
    $found = false;
    foreach ($wp_filter['admin_menu']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function']) && 
                is_object($callback['function'][0]) && 
                get_class($callback['function'][0]) === 'WP_Sozluk_Admin') {
                $found = true;
                echo "<p class='success'>✓ WP_Sozluk_Admin admin_menu hook'u kayıtlı (Priority: $priority)</p>";
                break 2;
            }
        }
    }
    
    if (!$found) {
        echo "<p class='error'>✗ WP_Sozluk_Admin admin_menu hook'u kayıtlı değil</p>";
    }
} else {
    echo "<p class='error'>✗ admin_menu hook'u bulunamadı</p>";
}

// 3. Manuel admin sınıfı test
echo "<h2>3. Manuel Admin Sınıfı Test</h2>";
if (class_exists('WP_Sozluk_Admin')) {
    $admin = new WP_Sozluk_Admin();
    echo "<p class='success'>✓ WP_Sozluk_Admin sınıfı manuel olarak oluşturuldu</p>";
    
    // add_admin_menu metodunu manuel çağır
    if (method_exists($admin, 'add_admin_menu')) {
        $admin->add_admin_menu();
        echo "<p class='success'>✓ add_admin_menu metodu çağrıldı</p>";
    } else {
        echo "<p class='error'>✗ add_admin_menu metodu bulunamadı</p>";
    }
}

// 4. Global menü kontrolü
echo "<h2>4. WordPress Menü Kontrolü</h2>";
global $menu, $submenu;

$sozluk_menu_found = false;
if (isset($menu) && is_array($menu)) {
    foreach ($menu as $menu_item) {
        if (is_array($menu_item) && isset($menu_item[0])) {
            if (strpos($menu_item[0], 'Sözlük') !== false || strpos($menu_item[0], 'WP Sözlük') !== false) {
                $sozluk_menu_found = true;
                echo "<p class='success'>✓ Sözlük menüsü bulundu: " . esc_html($menu_item[0]) . "</p>";
                echo "<p class='info'>Menü slug: " . esc_html($menu_item[2]) . "</p>";
                break;
            }
        }
    }
}

if (!$sozluk_menu_found) {
    echo "<p class='error'>✗ Sözlük menüsü bulunamadı</p>";
    echo "<p class='info'>Mevcut menüler:</p>";
    echo "<ul>";
    if (isset($menu) && is_array($menu)) {
        foreach ($menu as $menu_item) {
            if (is_array($menu_item) && isset($menu_item[0]) && !empty($menu_item[0])) {
                echo "<li>" . esc_html($menu_item[0]) . " (" . esc_html($menu_item[2]) . ")</li>";
            }
        }
    }
    echo "</ul>";
}

// 5. Kullanıcı yetkileri kontrolü
echo "<h2>5. Kullanıcı Yetkileri Kontrolü</h2>";
$current_user = wp_get_current_user();
echo "<p class='info'>Mevcut kullanıcı: " . $current_user->user_login . "</p>";

if (current_user_can('manage_options')) {
    echo "<p class='success'>✓ Kullanıcı manage_options yetkisine sahip</p>";
} else {
    echo "<p class='error'>✗ Kullanıcı manage_options yetkisine sahip değil</p>";
}

// 6. Plugin aktif kontrolü
echo "<h2>6. Plugin Aktif Kontrolü</h2>";
$active_plugins = get_option('active_plugins');
$plugin_file = 'wp-sozluk-eklentisi/wp-sozluk.php';

if (in_array($plugin_file, $active_plugins)) {
    echo "<p class='success'>✓ Plugin aktif: wp-sozluk-eklentisi</p>";
} else {
    echo "<p class='error'>✗ Plugin aktif değil</p>";
    echo "<p class='info'>Aktif pluginler:</p>";
    echo "<ul>";
    foreach ($active_plugins as $plugin) {
        echo "<li>" . esc_html($plugin) . "</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Test tamamlandı.</strong></p>";
?>
