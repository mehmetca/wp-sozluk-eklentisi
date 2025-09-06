<?php
/**
 * Test script for WP Sözlük Eklentisi
 */

define('WP_USE_THEMES', false);
require_once('wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Include necessary files
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

// Check if Sozluk_V2_DB class exists
if (!class_exists('Sozluk_V2_DB')) {
    die('Sozluk_V2_DB sınıfı bulunamadı!');
}

$db = Sozluk_V2_DB::get_instance();

echo '<h1>WP Sözlük Veritabanı Testi</h1>';
echo '<div style="font-family: monospace; white-space: pre;">';

// Check database connection
if (!$db || !$db->db) {
    die('Veritabanı bağlantısı kurulamadı!');
}

echo "Veritabanı bağlantısı başarılı.\n\n";

// Check if table exists
$table_name = $db->db->prefix . 'sozluk_kelimeler_v2';
$table_exists = $db->db->get_var($db->db->prepare(
    "SHOW TABLES LIKE %s",
    $db->db->esc_like($table_name)
));

if (!$table_exists) {
    die("HATA: $table_name tablosu bulunamadı!");
}

echo "Tablo bulundu: $table_name\n\n";

// Get table structure
$columns = $db->db->get_results("SHOW COLUMNS FROM $table_name");
echo "TABLO YAPISI:\n";
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";}

echo "\nİLK 5 KAYIT:\n";
$results = $db->db->get_results("SELECT * FROM $table_name LIMIT 5");
if ($results) {
    foreach ($results as $row) {
        print_r($row);
        echo "\n";
    }
} else {
    echo "Tabloda kayıt bulunamadı.\n";
    
    // Try to add a test record
    echo "\nTest kaydı ekleniyor...\n";
    $inserted = $db->db->insert(
        $table_name,
        array(
            'almanca_kelime' => 'Haus',
            'turkce_kelime' => 'ev',
            'artikel' => 'das',
            'turu' => 'isim',
            'tarih' => current_time('mysql'),
            'onay' => 1,
            'ekleyen' => 'test',
            'guncelleyen' => 'test',
            'guncelleme_tarihi' => current_time('mysql'),
            'almanca_anlam' => 'Ein Gebäude, in dem Menschen wohnen',
            'turkce_anlam' => 'İnsanların yaşadığı bina'
        ),
        array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s')
    );
    
    if ($inserted) {
        echo "Test kaydı başarıyla eklendi. ID: " . $db->db->insert_id . "\n";
        
        // Show the test record
        $test_record = $db->db->get_row("SELECT * FROM $table_name WHERE id = " . $db->db->insert_id);
        echo "\nEklenen Kayıt:\n";
        print_r($test_record);
    } else {
        echo "Test kaydı eklenirken hata oluştu: " . $db->db->last_error . "\n";
    }
}

echo '</div>';
?>
