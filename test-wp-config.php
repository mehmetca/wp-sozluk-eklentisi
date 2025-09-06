<?php
/**
 * wp-config.php Test Aracı
 * Bu dosyayı WordPress root dizininize yükleyin ve çalıştırın
 */

// WordPress'i yükle
require_once('wp-config.php');

echo "<h1>wp-config.php Test Raporu</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;}</style>";

// 1. Sozluk_V2_DB sınıfı kontrolü
echo "<h2>1. Sozluk_V2_DB Sınıfı</h2>";
if (class_exists('Sozluk_V2_DB')) {
    echo "<p class='success'>✓ Sozluk_V2_DB sınıfı tanımlı</p>";
    
    // 2. Sabitler kontrolü
    echo "<h2>2. Veritabanı Sabitleri</h2>";
    $constants = ['SOZLUK_DB_HOST', 'SOZLUK_DB_NAME', 'SOZLUK_DB_USER', 'SOZLUK_DB_PASSWORD'];
    foreach ($constants as $constant) {
        if (defined($constant)) {
            echo "<p class='success'>✓ {$constant}: " . constant($constant) . "</p>";
        } else {
            echo "<p class='error'>✗ {$constant} tanımlı değil</p>";
        }
    }
    
    // 3. Bağlantı testi
    echo "<h2>3. Bağlantı Testi</h2>";
    try {
        $sozluk_db = Sozluk_V2_DB::get_instance();
        if ($sozluk_db && $sozluk_db->db && $sozluk_db->db->dbh) {
            echo "<p class='success'>✓ Veritabanı bağlantısı başarılı</p>";
            
            // Test sorgusu
            $count = $sozluk_db->db->get_var("SELECT COUNT(*) FROM wp_sozluk_kelimeler_v2");
            echo "<p class='success'>✓ Toplam kelime: " . number_format($count) . "</p>";
            
        } else {
            echo "<p class='error'>✗ Veritabanı bağlantısı kurulamadı</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Hata: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p class='error'>✗ Sozluk_V2_DB sınıfı bulunamadı</p>";
    echo "<p>wp-config.php dosyasına Sozluk_V2_DB sınıfını ekleyin.</p>";
}

echo "<hr>";
echo "<p><strong>Test tamamlandı.</strong></p>";
?>
