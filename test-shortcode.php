<?php
/**
 * Shortcode Test Dosyası
 */

// WordPress'i yükle
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>Shortcode Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// 1. Plugin Aktif mi?
echo "<h2>1. Plugin Durumu</h2>";
$active_plugins = get_option('active_plugins');
$plugin_file = 'wp-sozluk/wp-sozluk.php';

if (in_array($plugin_file, $active_plugins)) {
    echo "<p class='success'>✓ WP Sözlük eklentisi aktif</p>";
} else {
    echo "<p class='error'>✗ WP Sözlük eklentisi aktif değil</p>";
    echo "<p class='info'>Plugin'i aktive edin</p>";
    exit;
}

// 2. Sınıflar yüklendi mi?
echo "<h2>2. Sınıf Kontrolü</h2>";
if (class_exists('WP_Sozluk')) {
    echo "<p class='success'>✓ WP_Sozluk sınıfı yüklendi</p>";
} else {
    echo "<p class='error'>✗ WP_Sozluk sınıfı yüklenemedi</p>";
}

if (class_exists('WP_Sozluk_Frontend')) {
    echo "<p class='success'>✓ WP_Sozluk_Frontend sınıfı yüklendi</p>";
} else {
    echo "<p class='error'>✗ WP_Sozluk_Frontend sınıfı yüklenemedi</p>";
}

if (class_exists('Sozluk_V2_DB')) {
    echo "<p class='success'>✓ Sozluk_V2_DB sınıfı yüklendi</p>";
} else {
    echo "<p class='error'>✗ Sozluk_V2_DB sınıfı yüklenemedi</p>";
}

// 3. Shortcode kayıtlı mı?
echo "<h2>3. Shortcode Kontrolü</h2>";
global $shortcode_tags;

if (isset($shortcode_tags['wp-sozluk'])) {
    echo "<p class='success'>✓ [wp-sozluk] shortcode'u kayıtlı</p>";
} else {
    echo "<p class='error'>✗ [wp-sozluk] shortcode'u kayıtlı değil</p>";
}

if (isset($shortcode_tags['almanca-sozluk'])) {
    echo "<p class='success'>✓ [almanca-sozluk] shortcode'u kayıtlı</p>";
} else {
    echo "<p class='error'>✗ [almanca-sozluk] shortcode'u kayıtlı değil</p>";
}

// 4. Veritabanı bağlantısı
echo "<h2>4. Veritabanı Bağlantısı</h2>";
if (class_exists('Sozluk_V2_DB')) {
    $sozluk_db = Sozluk_V2_DB::get_instance();
    if ($sozluk_db && $sozluk_db->db) {
        echo "<p class='success'>✓ Sozluk_V2_DB bağlantısı var</p>";
        
        // Tablo kontrolü
        $table_exists = $sozluk_db->db->get_var("SHOW TABLES LIKE 'wp_sozluk_kelimeler_v2'");
        if ($table_exists) {
            echo "<p class='success'>✓ wp_sozluk_kelimeler_v2 tablosu mevcut</p>";
            
            // Kayıt sayısı
            $count = $sozluk_db->db->get_var("SELECT COUNT(*) FROM wp_sozluk_kelimeler_v2");
            echo "<p class='info'>Toplam kelime sayısı: " . $count . "</p>";
        } else {
            echo "<p class='error'>✗ wp_sozluk_kelimeler_v2 tablosu bulunamadı</p>";
        }
    } else {
        echo "<p class='error'>✗ Sozluk_V2_DB bağlantı sorunu</p>";
    }
} else {
    echo "<p class='error'>✗ Sozluk_V2_DB sınıfı bulunamadı</p>";
}

// 5. Shortcode Test
echo "<h2>5. Shortcode Çıktı Testi</h2>";
echo "<p class='info'>Shortcode: [wp-sozluk limit=\"2\"]</p>";

$output = do_shortcode('[wp-sozluk limit="2"]');

if (!empty($output)) {
    echo "<p class='success'>✓ Shortcode çıktı üretiyor</p>";
    echo "<div style='border:1px solid #ccc;padding:10px;margin:10px 0;'>";
    echo $output;
    echo "</div>";
} else {
    echo "<p class='error'>✗ Shortcode boş çıktı</p>";
}

// 6. Manuel Frontend Test
echo "<h2>6. Manuel Frontend Test</h2>";
if (class_exists('WP_Sozluk_Frontend')) {
    $frontend = new WP_Sozluk_Frontend();
    $manual_output = $frontend->almanca_sozluk_shortcode(array('limit' => 2));
    
    if (!empty($manual_output)) {
        echo "<p class='success'>✓ Manuel frontend çağrısı çalışıyor</p>";
        echo "<div style='border:1px solid #ccc;padding:10px;margin:10px 0;'>";
        echo $manual_output;
        echo "</div>";
    } else {
        echo "<p class='error'>✗ Manuel frontend çağrısı boş</p>";
    }
}

echo "<hr>";
echo "<p><strong>Test tamamlandı.</strong></p>";
?>
