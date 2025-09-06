<?php
/**
 * Kelime türlerini listelemek için yardımcı script
 */

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// WordPress çekirdeğini yükle
try {
    if (!file_exists('wp-load.php')) {
        throw new Exception('wp-load.php dosyası bulunamadı. Bu dosya WordPress ana dizininde olmalıdır.');
    }
    require_once('wp-load.php');
} catch (Exception $e) {
    die('Hata: ' . $e->getMessage());
}

// Eğer Sozluk_V2_DB sınıfı tanımlı değilse, wp-config.php'den yükle
if (!class_exists('Sozluk_V2_DB')) {
    if (file_exists(ABSPATH . 'wp-config.php')) {
        require_once(ABSPATH . 'wp-config.php');
    } else {
        die("WordPress yapılandırma dosyası bulunamadı.");
    }
}

// Veritabanı bağlantısını kontrol et
if (!class_exists('Sozluk_V2_DB')) {
    die("Sozluk_V2_DB sınıfı bulunamadı. wp-config.php dosyanızı kontrol edin.");
}

$sozluk_db = Sozluk_V2_DB::get_instance();

if (!$sozluk_db || !$sozluk_db->db) {
    die("Veritabanı bağlantısı kurulamadı.");
}

// Tüm benzersiz kelime türlerini al
$results = $sozluk_db->db->get_results("SELECT DISTINCT kelime_turu, COUNT(*) as sayi FROM wp_sozluk_kelimeler_v2 WHERE kelime_turu IS NOT NULL AND kelime_turu != '' GROUP BY kelime_turu ORDER BY sayi DESC");

// Sonuçları göster
echo "<h2>Mevcut Kelime Türleri ve Sayıları</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Kelime Türü</th><th>Kelime Sayısı</th></tr>";

$toplam_kelime = 0;

foreach ($results as $row) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row->kelime_turu) . "</td>";
    echo "<td align='right">" . number_format($row->sayi, 0, ',', '.') . "</td>";
    echo "</tr>";
    $toplam_kelime += $row->sayi;
}

echo "<tr><td><strong>Toplam</strong></td><td align='right'><strong>" . number_format($toplam_kelime, 0, ',', '.') . "</strong></td></tr>";
echo "</table>";

// Önerilen standart türler
echo "<h2>Önerilen Standart Kelime Türleri</h2>";
echo "<ol>";
echo "<li>isim - İsim (der/die/das)</li>";
echo "<li>sıfat - Sıfat</li>";
echo "<li>fiil - Fiil</li>";
echo "<li>zarf - Zarf</li>";
echo "<li>edat - Edat</li>";
echo "<li>bağlaç - Bağlaç</li>";
echo "<li>zamir - Zamir</li>";
echo "<li>ünlem - Ünlem</li>";
echo "</ol>";
?>

<h2>Kullanım Talimatları</h2>
<ol>
    <li>Yukarıdaki listede mevcut tüm kelime türlerini ve kaçar adet olduklarını görebilirsiniz.</li>
    <li>Bu türleri yukarıdaki 8 standart türe dönüştürmek için bir güncelleme scripti oluşturabiliriz.</li>
    <li>Örneğin, "sifat", "sıfat" olarak güncellenebilir.</li>
</ol>

<p>Devam etmek için lütfen hangi türlerin hangi standart türe dönüştürüleceğini belirtin.</p>
