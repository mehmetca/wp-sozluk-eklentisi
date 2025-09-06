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

// Toplam kelime sayısını al
$total = $mysqli->query("SELECT COUNT(*) as total FROM wp_sozluk_kelimeler_v2")->fetch_assoc()['total'];

// Tüm benzersiz kelime türlerini al
$result = $mysqli->query(
    "SELECT 
        kelime_turu, 
        COUNT(*) as sayi 
    FROM 
        wp_sozluk_kelimeler_v2 
    WHERE 
        kelime_turu IS NOT NULL 
        AND kelime_turu != '' 
    GROUP BY 
        kelime_turu 
    ORDER BY 
        sayi DESC"
);

// HTML çıktısı
echo "<html><head><style>
    body{font-family:Arial;margin:20px;}
    table{border-collapse:collapse;width:100%;max-width:800px;}
    th,td{border:1px solid #ddd;padding:8px;text-align:left;}
    th{background:#f2f2f2;}
    .count{text-align:right;}
    .percentage{color:#666;}
</style></head><body>";

echo "<h2>Kelime Türleri (Toplam: $total kelime)</h2>";
echo "<table>";
echo "<tr><th>Kelime Türü</th><th>Adet</th><th>Yüzde</th></tr>";

while ($row = $result->fetch_assoc()) {
    $percentage = round(($row['sayi'] / $total) * 100, 2);
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['kelime_turu']) . "</td>";
    echo "<td class='count'>" . number_format($row['sayi'], 0, ',', '.') . "</td>";
    echo "<td class='percentage'>$percentage%</td>";
    echo "</tr>";
}

echo "</table>";

// Önerilen standart türler
echo "<h3>Önerilen Standart Türler:</h3>";
echo "<ol>
    <li>isim - İsim (der/die/das)</li>
    <li>sıfat - Sıfat</li>
    <li>fiil - Fiil</li>
    <li>zarf - Zarf</li>
    <li>edat - Edat</li>
    <li>bağlaç - Bağlaç</li>
    <li>zamir - Zamir</li>
    <li>ünlem - Ünlem</li>
</ol>";

echo "</body></html>";

$mysqli->close();
?>
