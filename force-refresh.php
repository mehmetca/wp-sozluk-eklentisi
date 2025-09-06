<?php
/**
 * WordPress Plugin Cache Temizleme Aracı
 * Bu dosyayı WordPress root dizininize yükleyip çalıştırın
 */

// WordPress'i yükle
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>Plugin Cache Temizleme</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;}</style>";

// 1. Plugin'i deaktive et
echo "<h2>1. Plugin Deaktivasyonu</h2>";
$active_plugins = get_option('active_plugins');
$plugin_file = 'wp-sozluk/wp-sozluk.php';

if (in_array($plugin_file, $active_plugins)) {
    $key = array_search($plugin_file, $active_plugins);
    unset($active_plugins[$key]);
    update_option('active_plugins', $active_plugins);
    echo "<p class='success'>✓ WP Sözlük eklentisi deaktive edildi</p>";
} else {
    echo "<p>Plugin zaten deaktif</p>";
}

// 2. Cache temizle
echo "<h2>2. Cache Temizleme</h2>";
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "<p class='success'>✓ WordPress cache temizlendi</p>";
}

// 3. Plugin'i tekrar aktive et
echo "<h2>3. Plugin Aktivasyonu</h2>";
$active_plugins[] = $plugin_file;
update_option('active_plugins', $active_plugins);
echo "<p class='success'>✓ WP Sözlük eklentisi tekrar aktive edildi</p>";

// 4. Test
echo "<h2>4. Bağlantı Testi</h2>";
if (class_exists('Sozluk_V2_DB')) {
    $sozluk_db = Sozluk_V2_DB::get_instance();
    if ($sozluk_db && $sozluk_db->db) {
        echo "<p class='success'>✓ Sozluk_V2_DB bağlantısı çalışıyor</p>";
    } else {
        echo "<p class='error'>✗ Sozluk_V2_DB bağlantı sorunu</p>";
    }
} else {
    echo "<p class='error'>✗ Sozluk_V2_DB sınıfı bulunamadı</p>";
}

echo "<hr>";
echo "<p><strong>İşlem tamamlandı.</strong> Admin paneline gidip Sözlük menüsünü kontrol edin.</p>";
?>
