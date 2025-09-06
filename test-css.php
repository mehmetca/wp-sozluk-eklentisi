<?php
/**
 * CSS ve JS Yükleme Test Dosyası
 */

// WordPress'i yükle
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>CSS ve JS Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// 1. CSS Dosyası Kontrolü
echo "<h2>1. CSS Dosyası Kontrolü</h2>";
$css_path = WP_PLUGIN_DIR . '/wp-sozluk/assets/css/frontend.css';
$css_url = WP_PLUGIN_URL . '/wp-sozluk/assets/css/frontend.css';

if (file_exists($css_path)) {
    echo "<p class='success'>✓ frontend.css dosyası mevcut</p>";
    echo "<p class='info'>Dosya yolu: " . $css_path . "</p>";
    echo "<p class='info'>URL: " . $css_url . "</p>";
    
    // Dosya boyutu
    $size = filesize($css_path);
    echo "<p class='info'>Dosya boyutu: " . number_format($size) . " bytes</p>";
} else {
    echo "<p class='error'>✗ frontend.css dosyası bulunamadı</p>";
    echo "<p class='info'>Aranan yol: " . $css_path . "</p>";
}

// 2. JS Dosyası Kontrolü
echo "<h2>2. JS Dosyası Kontrolü</h2>";
$js_path = WP_PLUGIN_DIR . '/wp-sozluk/assets/js/frontend.js';
$js_url = WP_PLUGIN_URL . '/wp-sozluk/assets/js/frontend.js';

if (file_exists($js_path)) {
    echo "<p class='success'>✓ frontend.js dosyası mevcut</p>";
    echo "<p class='info'>Dosya yolu: " . $js_path . "</p>";
    echo "<p class='info'>URL: " . $js_url . "</p>";
} else {
    echo "<p class='error'>✗ frontend.js dosyası bulunamadı</p>";
    echo "<p class='info'>Aranan yol: " . $js_path . "</p>";
}

// 3. WordPress Hook Kontrolü
echo "<h2>3. WordPress Hook Kontrolü</h2>";

// wp_enqueue_scripts hook'u test et
global $wp_filter;
if (isset($wp_filter['wp_enqueue_scripts'])) {
    echo "<p class='success'>✓ wp_enqueue_scripts hook'u aktif</p>";
    
    // WP_Sozluk_Frontend sınıfının hook'u kayıtlı mı?
    $found = false;
    foreach ($wp_filter['wp_enqueue_scripts']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function']) && 
                is_object($callback['function'][0]) && 
                get_class($callback['function'][0]) === 'WP_Sozluk_Frontend') {
                $found = true;
                echo "<p class='success'>✓ WP_Sozluk_Frontend enqueue hook'u kayıtlı (Priority: $priority)</p>";
                break 2;
            }
        }
    }
    
    if (!$found) {
        echo "<p class='error'>✗ WP_Sozluk_Frontend enqueue hook'u kayıtlı değil</p>";
    }
} else {
    echo "<p class='error'>✗ wp_enqueue_scripts hook'u bulunamadı</p>";
}

// 4. Enqueue Test
echo "<h2>4. Manuel Enqueue Test</h2>";
if (class_exists('WP_Sozluk_Frontend')) {
    $frontend = new WP_Sozluk_Frontend();
    
    // Manuel olarak enqueue_scripts çağır
    $frontend->enqueue_scripts();
    
    // Kayıtlı script ve style'ları kontrol et
    global $wp_scripts, $wp_styles;
    
    if (isset($wp_styles->registered['wp-sozluk-frontend'])) {
        echo "<p class='success'>✓ wp-sozluk-frontend CSS kayıtlı</p>";
        $style = $wp_styles->registered['wp-sozluk-frontend'];
        echo "<p class='info'>CSS URL: " . $style->src . "</p>";
    } else {
        echo "<p class='error'>✗ wp-sozluk-frontend CSS kayıtlı değil</p>";
    }
    
    if (isset($wp_scripts->registered['wp-sozluk-frontend'])) {
        echo "<p class='success'>✓ wp-sozluk-frontend JS kayıtlı</p>";
        $script = $wp_scripts->registered['wp-sozluk-frontend'];
        echo "<p class='info'>JS URL: " . $script->src . "</p>";
    } else {
        echo "<p class='error'>✗ wp-sozluk-frontend JS kayıtlı değil</p>";
    }
}

// 5. URL Erişim Testi
echo "<h2>5. URL Erişim Testi</h2>";

// CSS URL'sine erişim testi
if (file_exists($css_path)) {
    $css_url_test = wp_remote_get($css_url);
    if (!is_wp_error($css_url_test) && wp_remote_retrieve_response_code($css_url_test) === 200) {
        echo "<p class='success'>✓ CSS URL'sine erişim başarılı</p>";
    } else {
        echo "<p class='error'>✗ CSS URL'sine erişim başarısız</p>";
        if (is_wp_error($css_url_test)) {
            echo "<p class='error'>Hata: " . $css_url_test->get_error_message() . "</p>";
        }
    }
}

// 6. Tema Uyumluluğu
echo "<h2>6. Tema Uyumluluğu</h2>";
$current_theme = wp_get_theme();
echo "<p class='info'>Aktif tema: " . $current_theme->get('Name') . " v" . $current_theme->get('Version') . "</p>";

// wp_head ve wp_footer hook'ları var mı?
if (has_action('wp_head')) {
    echo "<p class='success'>✓ wp_head hook'u aktif</p>";
} else {
    echo "<p class='error'>✗ wp_head hook'u bulunamadı</p>";
}

if (has_action('wp_footer')) {
    echo "<p class='success'>✓ wp_footer hook'u aktif</p>";
} else {
    echo "<p class='error'>✗ wp_footer hook'u bulunamadı</p>";
}

echo "<hr>";
echo "<p><strong>Test tamamlandı.</strong></p>";
echo "<p class='info'>Eğer CSS yüklenmiyor ise, sayfanın kaynak kodunu kontrol edin ve wp-sozluk-frontend CSS dosyasının yüklenip yüklenmediğine bakın.</p>";
?>
