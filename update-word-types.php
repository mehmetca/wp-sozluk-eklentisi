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

// Karakter setini ayarla
$mysqli->set_charset("utf8mb4");

// Dönüşüm tablosu
$donusum_tablosu = [
    // Mevcut Tür => Yeni Tür
    'İsim' => 'isim',
    'isim' => 'isim',
    'isim (çoğul)' => 'isim',
    'özel isim' => 'isim',
    
    'Sıfat' => 'sıfat',
    'sıfat' => 'sıfat',
    'sıfat/zarf' => 'sıfat',
    'zarf/sıfat' => 'sıfat',
    
    'Fiil' => 'fiil',
    'fiil' => 'fiil',
    
    'Zarf' => 'zarf',
    'zarf' => 'zarf',
    
    'zamir' => 'zamir',
    'Zamir' => 'zamir',
    
    'edat' => 'edat',
    'Edat' => 'edat',
    
    'bağlaç' => 'bağlaç',
    'Bağlaç' => 'bağlaç',
    
    'ünlem' => 'ünlem',
    'Ünlem' => 'ünlem',
    
    // Diğer tüm türler
    'sayı' => 'diğer',
    'sıra sayısı' => 'diğer',
    'ifade' => 'diğer',
    'deyim' => 'diğer',
    'soru kelimesi' => 'diğer',
    'ön ek' => 'diğer',
    'önek' => 'diğer',
    'belirleyici' => 'diğer',
    'olumsuzluk belirteci' => 'diğer',
    'otomatik' => 'diğer',
    'Diğer' => 'diğer',
    'Belirtilmemiş' => 'diğer',
    
    // Varsayılan olarak diğer
    '' => 'diğer',
    null => 'diğer'
];

// HTML çıktısı başlangıcı
echo "<html><head><style>
    body{font-family:Arial;margin:20px;}
    table{border-collapse:collapse;width:100%;max-width:1000px;}
    th,td{border:1px solid #ddd;padding:8px;text-align:left;}
    th{background:#f2f2f2;}
    .success{color:green;}
    .error{color:red;}
    .count{text-align:right;}
    .summary{margin:20px 0;padding:10px;background:#f8f8f8;border-left:4px solid #4CAF50;}
</style></head><body>";

echo "<h1>Kelime Türleri Güncelleme</h1>";

try {
    // İşlem başlangıcında yedek al
    $backup_table = "wp_sozluk_kelimeler_v2_backup_" . date('Ymd_His');
    $mysqli->query("CREATE TABLE IF NOT EXISTS `$backup_table` LIKE wp_sozluk_kelimeler_v2");
    $mysqli->query("INSERT INTO `$backup_table` SELECT * FROM wp_sozluk_kelimeler_v2");
    
    echo "<div class='summary'>";
    echo "<p>Yedek oluşturuldu: <strong>$backup_table</strong></p>";
    
    // Güncelleme işlemi
    $updated = 0;
    $total = 0;
    
    foreach ($donusum_tablosu as $eski_tur => $yeni_tur) {
        if ($eski_tur === null) {
            $query = $mysqli->prepare("UPDATE wp_sozluk_kelimeler_v2 SET kelime_turu = ? WHERE kelime_turu IS NULL OR kelime_turu = ''");
            $query->bind_param('s', $yeni_tur);
        } else {
            $query = $mysqli->prepare("UPDATE wp_sozluk_kelimeler_v2 SET kelime_turu = ? WHERE kelime_turu = ?");
            $query->bind_param('ss', $yeni_tur, $eski_tur);
        }
        
        $query->execute();
        $affected = $query->affected_rows;
        
        if ($affected > 0) {
            echo "<p class='success'>✓ '" . htmlspecialchars($eski_tur) . "' → '" . htmlspecialchars($yeni_tur) . "' (" . number_format($affected) . " kayıt güncellendi)</p>";
            $updated += $affected;
        }
        
        $total += $affected;
        $query->close();
    }
    
    // Sonuçları göster
    echo "<p><strong>Toplam " . number_format($updated) . " kayıt güncellendi.</strong></p>";
    echo "</div>";
    
    // Güncel durumu göster
    echo "<h2>Güncel Kelime Türleri Dağılımı</h2>";
    
    $result = $mysqli->query(
        "SELECT 
            kelime_turu, 
            COUNT(*) as sayi 
        FROM 
            wp_sozluk_kelimeler_v2 
        GROUP BY 
            kelime_turu 
        ORDER BY 
            sayi DESC"
    );
    
    echo "<table>";
    echo "<tr><th>Kelime Türü</th><th>Adet</th><th>Yüzde</th></tr>";
    
    $total_words = $mysqli->query("SELECT COUNT(*) as total FROM wp_sozluk_kelimeler_v2")->fetch_assoc()['total'];
    
    while ($row = $result->fetch_assoc()) {
        $percentage = round(($row['sayi'] / $total_words) * 100, 2);
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['kelime_turu']) . "</td>";
        echo "<td class='count'>" . number_format($row['sayi'], 0, ',', '.') . "</td>";
        echo "<td>$percentage%</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p class='error'>Hata oluştu: " . $e->getMessage() . "</p>";
}

echo "<p><strong>İşlem tamamlandı.</strong> Yedek tablo adı: <code>$backup_table</code></p>";
echo "<p>Eğer sonuçlardan memnunsanız, yedek tabloyu silebilirsiniz: <code>DROP TABLE IF EXISTS `$backup_table`;</code></p>";

echo "</body></html>";

$mysqli->close();
?>
