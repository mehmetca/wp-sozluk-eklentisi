<?php
/**
 * Basit MySQL Bağlantı Testi
 * Bu dosyayı web sunucunuzda çalıştırarak veritabanı bağlantısını test edin
 */

// Veritabanı bilgileri
$host = 'localhost';
$dbname = 'toprakna_sozluk_db';
$username = 'toprakna';
$password = '71135123512Mct';

echo "<h1>MySQL Bağlantı Testi</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;}</style>";

// 1. MySQLi ile test
echo "<h2>1. MySQLi Bağlantı Testi</h2>";
$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error) {
    echo "<p class='error'>✗ MySQLi Bağlantı Hatası: " . $mysqli->connect_error . "</p>";
} else {
    echo "<p class='success'>✓ MySQLi bağlantısı başarılı</p>";
    
    // Tablo kontrolü
    $result = $mysqli->query("SHOW TABLES LIKE 'wp_sozluk_kelimeler_v2'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='success'>✓ wp_sozluk_kelimeler_v2 tablosu bulundu</p>";
        
        // Kayıt sayısı
        $count_result = $mysqli->query("SELECT COUNT(*) as total FROM wp_sozluk_kelimeler_v2");
        if ($count_result) {
            $count = $count_result->fetch_assoc();
            echo "<p class='success'>✓ Toplam kayıt: " . $count['total'] . "</p>";
        }
        
        // Örnek kayıt
        $sample_result = $mysqli->query("SELECT * FROM wp_sozluk_kelimeler_v2 LIMIT 1");
        if ($sample_result && $sample_result->num_rows > 0) {
            $sample = $sample_result->fetch_assoc();
            echo "<p class='success'>✓ Örnek kayıt:</p>";
            echo "<ul>";
            echo "<li>ID: " . $sample['id'] . "</li>";
            echo "<li>Almanca: " . $sample['almanca_kelime'] . "</li>";
            echo "<li>Türkçe: " . $sample['turkce_kelime'] . "</li>";
            echo "</ul>";
        }
        
    } else {
        echo "<p class='error'>✗ wp_sozluk_kelimeler_v2 tablosu bulunamadı</p>";
        
        // Mevcut tabloları listele
        $tables_result = $mysqli->query("SHOW TABLES");
        if ($tables_result) {
            echo "<p>Mevcut tablolar:</p><ul>";
            while ($table = $tables_result->fetch_array()) {
                echo "<li>" . $table[0] . "</li>";
            }
            echo "</ul>";
        }
    }
    
    $mysqli->close();
}

// 2. PDO ile test
echo "<h2>2. PDO Bağlantı Testi</h2>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✓ PDO bağlantısı başarılı</p>";
    
    // Test sorgusu
    $stmt = $pdo->query("SELECT COUNT(*) FROM wp_sozluk_kelimeler_v2");
    $count = $stmt->fetchColumn();
    echo "<p class='success'>✓ PDO ile kayıt sayısı: " . $count . "</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>✗ PDO Bağlantı Hatası: " . $e->getMessage() . "</p>";
}

// 3. Sunucu bilgileri
echo "<h2>3. Sunucu Bilgileri</h2>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>MySQL Extension: " . (extension_loaded('mysql') ? 'Yüklü' : 'Yüklü değil') . "</li>";
echo "<li>MySQLi Extension: " . (extension_loaded('mysqli') ? 'Yüklü' : 'Yüklü değil') . "</li>";
echo "<li>PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Yüklü' : 'Yüklü değil') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Test tamamlandı.</strong></p>";
?>
