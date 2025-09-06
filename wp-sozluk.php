<?php
/**
 * Plugin Name: WP Sözlük
 * Plugin URI: https://example.com/wp-sozluk
 * Description: WordPress için gelişmiş sözlük eklentisi. Terimler ve tanımları yönetmenizi sağlar.
 * Version: 1.0.10
 * Author: MehmetCan Toprak
 * Author URI: toprak.name.tr
 * License: GPL v2 or later
 * Text Domain: wp-sozluk
 * Domain Path: /languages
 */

// Doğrudan erişimi engelle
if (!defined('ABSPATH')) {
    exit;
}

// Plugin sabitleri
define('WP_SOZLUK_VERSION', '1.0.4');
define('WP_SOZLUK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_SOZLUK_PLUGIN_PATH', plugin_dir_path(__FILE__));

// DeepL API anahtarını sadece bir kez tanımla
if (!defined('DEEPL_API_KEY')) {
    define('DEEPL_API_KEY', 'dcaf6605-8876-40cb-abdf-9e660b1382af:fx');
}

// Lingvanex API yapılandırması (wp-config.php'ye taşınması önerilir)
if (!defined('LINGVANEX_API_KEY')) {
    define('LINGVANEX_API_KEY', ''); // TODO: API anahtarınızı girin
}
if (!defined('LINGVANEX_DICT_URL')) {
    // Örnek: 'https://api-b2b.backenster.com/b1/api/v3/translate' yerine sözlük/synonyms endpoint'i
    define('LINGVANEX_DICT_URL', ''); // TODO: Lingvanex sözlük endpoint URL
}

/**
 * Ana WP_Sozluk sınıfı
 */
class WP_Sozluk {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Dış veritabanı bağlantısı
     */
    public $external_db = null;
    
    /**
     * Singleton pattern
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Dış veritabanı bağlantısını başlat
        $this->init_external_db();
    }
    
    /**
     * Dış veritabanı bağlantısını başlat
     */
    private function init_external_db() {
        // WordPress yüklenmesini bekle
        add_action('wp_loaded', array($this, 'setup_external_db'));
    }
    
    public function setup_external_db() {
        // WordPress tam yüklenene kadar bekle
        if (!did_action('wp_loaded')) {
            add_action('wp_loaded', array($this, 'setup_external_db'));
            return;
        }
        
        if (class_exists('Sozluk_V2_DB')) {
            try {
                $this->external_db = Sozluk_V2_DB::get_instance();
                
                // Bağlantıyı test et
                if (!$this->external_db || !$this->external_db->db || !$this->external_db->db->dbh) {
                    add_action('admin_notices', array($this, 'db_connection_error'));
                    return;
                }
                
                // Test sorgusu
                $test = $this->external_db->db->get_var("SELECT COUNT(*) FROM wp_sozluk_kelimeler_v2 LIMIT 1");
                if ($test === null && $this->external_db->db->last_error) {
                    add_action('admin_notices', array($this, 'db_table_error'));
                }
                
            } catch (Exception $e) {
                add_action('admin_notices', array($this, 'db_connection_error'));
                error_log('WP Sözlük DB Hatası: ' . $e->getMessage());
            }
        } else {
            add_action('admin_notices', array($this, 'db_class_missing_error'));
        }
    }
    
    /**
     * Veritabanı bağlantı hatası bildirimi
     */
    public function db_connection_error() {
        echo '<div class="notice notice-error"><p><strong>WP Sözlük:</strong> Sözlük veritabanı bağlantısı kurulamadı. wp-config.php dosyasında Sozluk_V2_DB ayarlarını kontrol edin.</p></div>';
    }
    
    /**
     * Veritabanı tablo hatası bildirimi
     */
    public function db_table_error() {
        echo '<div class="notice notice-error"><p><strong>WP Sözlük:</strong> wp_sozluk_kelimeler_v2 tablosuna erişilemiyor. Tablo varlığını kontrol edin.</p></div>';
    }
    
    /**
     * Sozluk_V2_DB sınıf eksik hatası
     */
    public function db_class_missing_error() {
        echo '<div class="notice notice-error"><p><strong>WP Sözlük:</strong> Sozluk_V2_DB sınıfı bulunamadı. wp-config.php dosyasında sınıfın tanımlı olduğundan emin olun.</p></div>';
    }
    
    /**
     * Plugin başlatma
     */
    public function init() {
        // Dil dosyalarını yükle
        load_plugin_textdomain('wp-sozluk', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Admin paneli
        if (is_admin()) {
            $this->load_admin();
        }
        
        // Frontend
        $this->load_frontend();
        
        // AJAX işlemleri
        $this->load_ajax();
    }
    
    /**
     * Plugin aktivasyonu
     */
    public function activate() {
        $this->create_tables();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deaktivasyonu
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Veritabanı tablolarını oluştur
     */
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'sozluk_terimler';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            terim varchar(255) NOT NULL,
            tanim longtext NOT NULL,
            kategori varchar(100) DEFAULT '',
            etiketler text DEFAULT '',
            durum varchar(20) DEFAULT 'aktif',
            olusturma_tarihi datetime DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            olusturan_kullanici bigint(20) UNSIGNED,
            PRIMARY KEY (id),
            KEY terim (terim),
            KEY kategori (kategori),
            KEY durum (durum)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Sürüm bilgisini kaydet
        add_option('wp_sozluk_db_version', WP_SOZLUK_VERSION);
    }
    
    /**
     * Admin paneli yükle
     */
    private function load_admin() {
        // Sınıfları yükle
        require_once WP_SOZLUK_PLUGIN_PATH . 'includes/admin/class-wp-sozluk-admin.php';
        require_once WP_SOZLUK_PLUGIN_PATH . 'includes/admin/class-wp-sozluk-bulk-import.php';
        require_once WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-frontend.php';
        require_once WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-ajax.php';
        require_once WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-deepl.php';
        // Lingvanex entegrasyonu (opsiyonel)
        $lingvanex_path_admin = WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-lingvanex.php';
        if (file_exists($lingvanex_path_admin)) {
            require_once $lingvanex_path_admin;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WP Sözlük: Lingvanex sınıfı bulunamadı: ' . $lingvanex_path_admin);
            }
        }
        // Wiktionary entegrasyonu (opsiyonel)
        $wiktionary_path_admin = WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-wiktionary.php';
        if (file_exists($wiktionary_path_admin)) {
            require_once $wiktionary_path_admin;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WP Sözlük: Wiktionary sınıfı bulunamadı: ' . $wiktionary_path_admin);
            }
        }
        // Glosbe entegrasyonu (opsiyonel): Dosya yoksa fatal vermesin
        $glosbe_path = WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-glosbe.php';
        if (file_exists($glosbe_path)) {
            require_once $glosbe_path;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WP Sözlük: Glosbe sınıfı bulunamadı: ' . $glosbe_path);
            }
        }
        // Tatoeba entegrasyonu (opsiyonel)
        $tatoeba_path = WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-tatoeba.php';
        if (file_exists($tatoeba_path)) {
            require_once $tatoeba_path;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WP Sözlük: Tatoeba sınıfı bulunamadı: ' . $tatoeba_path);
            }
        }
        new WP_Sozluk_Admin();
        new WP_Sozluk_Bulk_Import();
        new WP_Sozluk_Frontend();
        new WP_Sozluk_Ajax();
    }
    
    /**
     * Frontend yükle
     */
    private function load_frontend() {
        require_once WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-frontend.php';
        require_once WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-ajax.php';
        require_once WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-deepl.php';
        // Lingvanex entegrasyonu (opsiyonel)
        $lingvanex_path = WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-lingvanex.php';
        if (file_exists($lingvanex_path)) {
            require_once $lingvanex_path;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WP Sözlük: Lingvanex sınıfı bulunamadı: ' . $lingvanex_path);
            }
        }
        // Wiktionary entegrasyonu (opsiyonel)
        $wiktionary_path = WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-wiktionary.php';
        if (file_exists($wiktionary_path)) {
            require_once $wiktionary_path;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WP Sözlük: Wiktionary sınıfı bulunamadı: ' . $wiktionary_path);
            }
        }
        new WP_Sozluk_Frontend();
        new WP_Sozluk_Ajax();
    }
    
    /**
     * AJAX işlemlerini yükle
     */
    private function load_ajax() {
        require_once WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-ajax.php';
        // Wiktionary sınıfını da AJAX bağlamında yükle (zıt anlamlı doldurma için gerekli)
        $wiktionary_path = WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-wiktionary.php';
        if (file_exists($wiktionary_path)) {
            require_once $wiktionary_path;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WP Sözlük: Wiktionary sınıfı bulunamadı: ' . $wiktionary_path);
            }
        }
        $glosbe_path = WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-glosbe.php';
        if (file_exists($glosbe_path)) {
            require_once $glosbe_path;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WP Sözlük: Glosbe sınıfı bulunamadı: ' . $glosbe_path);
            }
        }
        $tatoeba_path = WP_SOZLUK_PLUGIN_PATH . 'includes/class-wp-sozluk-tatoeba.php';
        if (file_exists($tatoeba_path)) {
            require_once $tatoeba_path;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WP Sözlük: Tatoeba sınıfı bulunamadı: ' . $tatoeba_path);
            }
        }
        new WP_Sozluk_Ajax();
    }
}

// Debug modunu etkinleştir
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}

// DeepL test fonksiyonu ve admin_init hook'u kaldırıldı (gereksiz logları önlemek için)

// Plugin'i başlat
WP_Sozluk::get_instance();

// Global tanılama kutusu: ?sozluk_diag=1 iken footer'da her sayfada göster
add_action('wp_footer', function () {
    if (!isset($_GET['sozluk_diag']) || $_GET['sozluk_diag'] != '1') {
        return;
    }
    if (!function_exists('shortcode_exists')) {
        return;
    }
    $checks = array(
        'wp-sozluk' => shortcode_exists('wp-sozluk'),
        'almanca-sozluk' => shortcode_exists('almanca-sozluk'),
        'sozluk-kelime' => shortcode_exists('sozluk-kelime'),
        'sozluk-populer' => shortcode_exists('sozluk-populer'),
        'sozluk-w-fragen' => shortcode_exists('sozluk-w-fragen'),
        'wp-sozluk-w-fragen' => shortcode_exists('wp-sozluk-w-fragen'),
        'sozluk-son-eklenenler' => shortcode_exists('sozluk-son-eklenenler'),
        'sozluk-son' => shortcode_exists('sozluk-son'),
        'sozluk_son_eklenenler' => shortcode_exists('sozluk_son_eklenenler'),
        'wp-sozluk-ana' => shortcode_exists('wp-sozluk-ana'),
        'wp-sozluk-ping' => function_exists('add_shortcode') && has_shortcode('[wp-sozluk-ping]') ? true : shortcode_exists('wp-sozluk-ping'),
        'sozluk-diagnose' => shortcode_exists('sozluk-diagnose'),
    );
    echo '<div class="wp-sozluk-debug" style="margin:20px auto;max-width:900px;padding:12px;border:2px dashed #999;background:#fffef5;">';
    echo '<strong>WP Sözlük Global Debug</strong><br>';
    echo 'FILE: ' . esc_html(__FILE__) . '<br>';
    echo 'FILE MTIME: ' . esc_html(@date('Y-m-d H:i:s', @filemtime(__FILE__))) . '<br>';
    if (defined('WP_SOZLUK_VERSION')) echo 'VERSION: ' . esc_html(WP_SOZLUK_VERSION) . '<br>';
    echo '<table style="margin-top:8px;width:100%;border-collapse:collapse;">';
    echo '<thead><tr><th style="text-align:left;border-bottom:1px solid #ddd;">Kısa Kod</th><th style="text-align:left;border-bottom:1px solid #ddd;">Kayıtlı mı?</th></tr></thead><tbody>';
    foreach ($checks as $name => $ok) {
        echo '<tr><td style="padding:4px 0;">' . esc_html($name) . '</td><td>' . ($ok ? 'Evet' : 'Hayır') . '</td></tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
});
