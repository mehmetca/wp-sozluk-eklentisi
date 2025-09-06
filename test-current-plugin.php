<?php
/**
 * Mevcut Plugin Durumu Test
 */

// WordPress'i yükle
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>Mevcut Plugin Durumu</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;}</style>";

// 1. Plugin aktif mi?
echo "<h2>1. Plugin Durumu</h2>";
$active_plugins = get_option('active_plugins');
$plugin_file = 'wp-sozluk/wp-sozluk.php';

if (in_array($plugin_file, $active_plugins)) {
    echo "<p class='success'>✓ WP Sözlük eklentisi aktif</p>";
} else {
    echo "<p class='error'>✗ WP Sözlük eklentisi aktif değil</p>";
}

// 2. WP_Sozluk sınıfı var mı?
echo "<h2>2. Plugin Sınıfları</h2>";
if (class_exists('WP_Sozluk')) {
    echo "<p class='success'>✓ WP_Sozluk sınıfı yüklü</p>";
    
    $instance = WP_Sozluk::get_instance();
    if ($instance->external_db) {
        echo "<p class='success'>✓ external_db bağlantısı var</p>";
    } else {
        echo "<p class='error'>✗ external_db bağlantısı yok</p>";
    }
} else {
    echo "<p class='error'>✗ WP_Sozluk sınıfı yüklü değil</p>";
}

// 3. Sozluk_V2_DB test
echo "<h2>3. Veritabanı Testi</h2>";
if (class_exists('Sozluk_V2_DB')) {
    $sozluk_db = Sozluk_V2_DB::get_instance();
    if ($sozluk_db && $sozluk_db->db) {
        $count = $sozluk_db->db->get_var("SELECT COUNT(*) FROM wp_sozluk_kelimeler_v2");
        echo "<p class='success'>✓ Doğrudan bağlantı çalışıyor: " . number_format($count) . " kelime</p>";
    } else {
        echo "<p class='error'>✗ Doğrudan bağlantı çalışmıyor</p>";
    }
} else {
    echo "<p class='error'>✗ Sozluk_V2_DB sınıfı yok</p>";
}

echo "<hr>";
echo "<p><strong>Test tamamlandı.</strong></p>";
?>
