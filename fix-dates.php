<?php
// Veritabanı bilgileri
$host = 'localhost';
$dbname = 'toprakna_sozluk_db';
$username = 'toprakna';
$password = '71135123512Mct';

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bağlantıyı kur
$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) {
    die("Bağlantı hatası: " . $mysqli->connect_error);
}

// 1. Geçersiz tarihleri say
$count = $mysqli->query("
    SELECT COUNT(*) as count 
    FROM wp_sozluk_kelimeler_v2 
    WHERE eklenme_tarihi IS NULL 
    OR eklenme_tarihi = '0000-00-00 00:00:00'
    OR eklenme_tarihi = ''
")->fetch_assoc()['count'];

echo "<h2>Geçersiz Tarih Düzeltme</h2>";
echo "<p>Düzeltilecek kayıt sayısı: $count</p>";

if ($count > 0) {
    // 2. Düzeltme işlemi
    $result = $mysqli->query("
        UPDATE wp_sozluk_kelimeler_v2 
        SET eklenme_tarihi = NOW() 
        WHERE eklenme_tarihi IS NULL 
        OR eklenme_tarihi = '0000-00-00 00:00:00'
        OR eklenme_tarihi = ''
    ");
    
    if ($result) {
        $affected = $mysqli->affected_rows;
        echo "<p style='color:green;'>✓ $affected kayıt güncellendi.</p>";
    } else {
        echo "<p style='color:red;'>Hata: " . $mysqli->error . "</p>";
    }
} else {
    echo "<p>Düzeltilecek kayıt bulunamadı.</p>";
}

// 3. Son durumu göster
$stats = $mysqli->query("
    SELECT 
        COUNT(*) as total,
        MIN(eklenme_tarihi) as oldest,
        MAX(eklenme_tarihi) as newest,
        COUNT(CASE WHEN eklenme_tarihi IS NULL OR eklenme_tarihi = '0000-00-00 00:00:00' THEN 1 END) as invalid_dates
    FROM wp_sozluk_kelimeler_v2
")->fetch_assoc();

echo "<h3>Güncel Durum:</h3>";
echo "<ul>";
echo "<li>Toplam Kayıt: " . number_format($stats['total']) . "</li>";
echo "<li>En Eski Tarih: " . $stats['oldest'] . "</li>";
echo "<li>En Yeni Tarih: " . $stats['newest'] . "</li>";
echo "<li>Geçersiz Tarih: " . $stats['invalid_dates'] . "</li>";
echo "</ul>";

$mysqli->close();
?>
