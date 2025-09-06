<?php
/**
 * WP Sözlük Debug Aracı
 * 
 * Bu dosyayı WordPress root dizininize yükleyin ve tarayıcıdan çalıştırın.
 * Sözlük veritabanı bağlantısını test eder ve sorunları tespit eder.
 */

// WordPress'i yükle
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>WP Sözlük Debug Raporu</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// 1. Sozluk_V2_DB sınıfı kontrolü
echo "<h2>1. Sozluk_V2_DB Sınıfı Kontrolü</h2>";
if (class_exists('Sozluk_V2_DB')) {
    echo "<p class='success'>✓ Sozluk_V2_DB sınıfı bulundu</p>";
    
    // 2. Veritabanı sabitleri kontrolü
    echo "<h2>2. Veritabanı Sabitleri</h2>";
    $constants = ['SOZLUK_DB_HOST', 'SOZLUK_DB_NAME', 'SOZLUK_DB_USER', 'SOZLUK_DB_PASSWORD'];
    $all_defined = true;
    
    foreach ($constants as $constant) {
        if (defined($constant)) {
            echo "<p class='success'>✓ {$constant} tanımlı</p>";
        } else {
            echo "<p class='error'>✗ {$constant} tanımlı değil</p>";
            $all_defined = false;
        }
    }
    
    if ($all_defined) {
        // 3. Bağlantı testi
        echo "<h2>3. Veritabanı Bağlantı Testi</h2>";
        try {
            $sozluk_db = Sozluk_V2_DB::get_instance();
            
            if ($sozluk_db && $sozluk_db->db) {
                echo "<p class='success'>✓ Veritabanı bağlantısı başarılı</p>";
                
                // 4. Tablo kontrolü
                echo "<h2>4. Tablo Kontrolü</h2>";
                $table_exists = $sozluk_db->db->get_var("SHOW TABLES LIKE 'wp_sozluk_kelimeler_v2'");
                
                if ($table_exists) {
                    echo "<p class='success'>✓ wp_sozluk_kelimeler_v2 tablosu mevcut</p>";
                    
                    // 5. Kayıt sayısı
                    $count = $sozluk_db->db->get_var("SELECT COUNT(*) FROM wp_sozluk_kelimeler_v2");
                    echo "<p class='success'>✓ Toplam kelime sayısı: " . number_format($count) . "</p>";
                    
                    // 6. Örnek kayıt
                    echo "<h2>5. Örnek Kayıt</h2>";
                    $sample = $sozluk_db->db->get_row("SELECT * FROM wp_sozluk_kelimeler_v2 LIMIT 1");
                    if ($sample) {
                        echo "<p class='success'>✓ Örnek kayıt bulundu:</p>";
                        echo "<ul>";
                        echo "<li>ID: " . $sample->id . "</li>";
                        echo "<li>Almanca: " . $sample->almanca_kelime . "</li>";
                        echo "<li>Türkçe: " . $sample->turkce_kelime . "</li>";
                        echo "<li>Artikel: " . $sample->artikel . "</li>";
                        echo "</ul>";
                    } else {
                        echo "<p class='warning'>⚠ Tablo boş</p>";
                    }
                    
                } else {
                    echo "<p class='error'>✗ wp_sozluk_kelimeler_v2 tablosu bulunamadı</p>";
                    
                    // Mevcut tabloları listele
                    $tables = $sozluk_db->db->get_results("SHOW TABLES", ARRAY_N);
                    echo "<p>Mevcut tablolar:</p><ul>";
                    foreach ($tables as $table) {
                        echo "<li>" . $table[0] . "</li>";
                    }
                    echo "</ul>";
                }
                
            } else {
                echo "<p class='error'>✗ Veritabanı bağlantısı kurulamadı</p>";
                
                // Bağlantı detayları
                echo "<h3>Bağlantı Detayları:</h3>";
                echo "<ul>";
                echo "<li>Host: " . SOZLUK_DB_HOST . "</li>";
                echo "<li>Database: " . SOZLUK_DB_NAME . "</li>";
                echo "<li>User: " . SOZLUK_DB_USER . "</li>";
                echo "</ul>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ Hata: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p class='error'>Lütfen wp-config.php dosyasında eksik sabitleri tanımlayın.</p>";
    }
    
} else {
    echo "<p class='error'>✗ Sozluk_V2_DB sınıfı bulunamadı</p>";
    echo "<p>wp-config.php dosyasında Sozluk_V2_DB sınıfının tanımlı olduğundan emin olun.</p>";
}

// 7. WordPress veritabanı kontrolü
echo "<h2>6. WordPress Veritabanı Kontrolü</h2>";
global $wpdb;
if ($wpdb) {
    echo "<p class='success'>✓ WordPress veritabanı bağlantısı aktif</p>";
    echo "<p>WordPress DB Host: " . DB_HOST . "</p>";
    echo "<p>WordPress DB Name: " . DB_NAME . "</p>";
} else {
    echo "<p class='error'>✗ WordPress veritabanı bağlantısı yok</p>";
}

echo "<hr>";
echo "<p><strong>Debug tamamlandı.</strong> Sorunlar varsa yukarıdaki bilgileri kontrol edin.</p>";
?>
