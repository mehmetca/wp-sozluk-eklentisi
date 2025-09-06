<?php
/**
 * Frontend Test
 */

// WordPress'i yükle
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>Frontend Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;}</style>";

// 1. Frontend sınıfı test
echo "<h2>1. Frontend Sınıfı</h2>";
if (class_exists('WP_Sozluk_Frontend')) {
    echo "<p class='success'>✓ WP_Sozluk_Frontend sınıfı var</p>";
    
    // Frontend instance oluştur
    $frontend = new WP_Sozluk_Frontend();
    
    // external_db kontrolü
    $reflection = new ReflectionClass($frontend);
    $property = $reflection->getProperty('external_db');
    $property->setAccessible(true);
    $external_db = $property->getValue($frontend);
    
    if ($external_db && $external_db->db) {
        echo "<p class='success'>✓ Frontend external_db bağlantısı var</p>";
        
        // Test sorgusu
        $count = $external_db->db->get_var("SELECT COUNT(*) FROM wp_sozluk_kelimeler_v2");
        echo "<p class='success'>✓ Frontend veritabanı erişimi: " . number_format($count) . " kelime</p>";
    } else {
        echo "<p class='error'>✗ Frontend external_db bağlantısı yok</p>";
    }
    
} else {
    echo "<p class='error'>✗ WP_Sozluk_Frontend sınıfı yok</p>";
}

// 2. Shortcode test
echo "<h2>2. Shortcode Test</h2>";
$shortcode_output = do_shortcode('[wp-sozluk limit="5"]');
if (strpos($shortcode_output, 'Veritabanı bağlantısı kurulamadı') !== false) {
    echo "<p class='error'>✗ Shortcode veritabanı hatası veriyor</p>";
} elseif (strlen($shortcode_output) > 100) {
    echo "<p class='success'>✓ Shortcode çalışıyor (" . strlen($shortcode_output) . " karakter)</p>";
} else {
    echo "<p class='error'>✗ Shortcode boş çıktı: " . htmlspecialchars($shortcode_output) . "</p>";
}

// 3. AJAX sınıfı test
echo "<h2>3. AJAX Sınıfı</h2>";
if (class_exists('WP_Sozluk_Ajax')) {
    echo "<p class='success'>✓ WP_Sozluk_Ajax sınıfı var</p>";
    
    $ajax = new WP_Sozluk_Ajax();
    $reflection = new ReflectionClass($ajax);
    $property = $reflection->getProperty('external_db');
    $property->setAccessible(true);
    $external_db = $property->getValue($ajax);
    
    if ($external_db && $external_db->db) {
        echo "<p class='success'>✓ AJAX external_db bağlantısı var</p>";
    } else {
        echo "<p class='error'>✗ AJAX external_db bağlantısı yok</p>";
    }
} else {
    echo "<p class='error'>✗ WP_Sozluk_Ajax sınıfı yok</p>";
}

echo "<hr>";
echo "<p><strong>Test tamamlandı.</strong></p>";
?>
