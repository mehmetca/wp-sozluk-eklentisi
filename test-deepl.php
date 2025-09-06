<?php
/**
 * DeepL API Test Dosyası
 */

// WordPress'i yükle
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>DeepL API Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// 1. API Anahtarı Kontrolü
echo "<h2>1. API Anahtarı Kontrolü</h2>";
if (defined('DEEPL_API_KEY')) {
    echo "<p class='success'>✓ DEEPL_API_KEY tanımlandı: " . substr(DEEPL_API_KEY, 0, 10) . "...</p>";
} else {
    echo "<p class='error'>✗ DEEPL_API_KEY tanımlanmamış</p>";
}

// 2. DeepL Sınıfı Test
echo "<h2>2. DeepL Sınıfı Test</h2>";
require_once('includes/class-wp-sozluk-deepl.php');

if (class_exists('WP_Sozluk_DeepL')) {
    echo "<p class='success'>✓ WP_Sozluk_DeepL sınıfı yüklendi</p>";
    
    $deepl = new WP_Sozluk_DeepL();
    
    // 3. Basit Çeviri Testi
    echo "<h2>3. Çeviri Testi</h2>";
    echo "<p class='info'>Test kelimesi: 'Haus' (Almanca)</p>";
    
    $translation = $deepl->translate_german_to_turkish('Haus');
    
    if ($translation) {
        echo "<p class='success'>✓ Çeviri başarılı: " . esc_html($translation) . "</p>";
    } else {
        echo "<p class='error'>✗ Çeviri başarısız</p>";
        echo "<p class='info'>WordPress error log'unu kontrol edin</p>";
    }
    
    // 4. API Durumu Kontrolü
    echo "<h2>4. API Durumu Kontrolü</h2>";
    
    $args = array(
        'method' => 'GET',
        'headers' => array(
            'Authorization' => 'DeepL-Auth-Key ' . DEEPL_API_KEY
        ),
        'timeout' => 10
    );
    
    $response = wp_remote_get('https://api-free.deepl.com/v2/usage', $args);
    
    if (is_wp_error($response)) {
        echo "<p class='error'>✗ API bağlantı hatası: " . $response->get_error_message() . "</p>";
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        echo "<p class='info'>Response Code: " . $response_code . "</p>";
        
        if ($response_code === 200) {
            $data = json_decode($body, true);
            if (isset($data['character_count'])) {
                echo "<p class='success'>✓ API çalışıyor</p>";
                echo "<p class='info'>Kullanılan karakter: " . $data['character_count'] . "</p>";
                echo "<p class='info'>Limit: " . $data['character_limit'] . "</p>";
            }
        } elseif ($response_code === 403) {
            echo "<p class='error'>✗ 403 Forbidden - API anahtarı geçersiz</p>";
        } elseif ($response_code === 456) {
            echo "<p class='error'>✗ 456 Quota Exceeded - Aylık limit aşıldı</p>";
        } else {
            echo "<p class='error'>✗ API Hatası: " . $response_code . "</p>";
            echo "<p class='info'>Response: " . esc_html($body) . "</p>";
        }
    }
    
} else {
    echo "<p class='error'>✗ WP_Sozluk_DeepL sınıfı yüklenemedi</p>";
}

echo "<hr>";
echo "<p><strong>Test tamamlandı.</strong></p>";
?>
