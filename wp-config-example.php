<?php
/**
 * WP Sözlük - Sozluk_V2_DB Sınıfı Örneği
 * 
 * Bu dosyayı wp-config.php dosyanıza ekleyin veya ayrı bir dosya olarak dahil edin.
 */

// Sözlük veritabanı ayarları
define('SOZLUK_DB_HOST', 'localhost');
define('SOZLUK_DB_NAME', 'sozluk_veritabani');
define('SOZLUK_DB_USER', 'veritabani_kullanicisi');
define('SOZLUK_DB_PASSWORD', 'veritabani_sifresi');

/**
 * Sozluk_V2_DB Sınıfı
 * Harici sözlük veritabanı bağlantısını yönetir
 */
class Sozluk_V2_DB {
    private static $instance = null;
    public $db;
    
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
     * Constructor - Veritabanı bağlantısını kur
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Veritabanı bağlantısını kur
     */
    private function connect() {
        try {
            // WordPress wpdb sınıfını kullan
            $this->db = new wpdb(
                SOZLUK_DB_USER,
                SOZLUK_DB_PASSWORD,
                SOZLUK_DB_NAME,
                SOZLUK_DB_HOST
            );
            
            // Karakter setini ayarla
            if ($this->db->dbh) {
                $this->db->set_charset($this->db->dbh, 'utf8mb4');
            }
            
            // Bağlantıyı test et
            if (!$this->db->dbh) {
                throw new Exception('Veritabanı bağlantısı kurulamadı');
            }
            
            // Test sorgusu
            $test = $this->db->get_var("SELECT 1");
            if ($test !== '1') {
                throw new Exception('Veritabanı sorgu testi başarısız');
            }
            
        } catch (Exception $e) {
            error_log('Sozluk_V2_DB Bağlantı Hatası: ' . $e->getMessage());
            $this->db = null;
        }
    }
    
    /**
     * Bağlantı durumunu kontrol et
     */
    public function is_connected() {
        return ($this->db && $this->db->dbh);
    }
    
    /**
     * Veritabanı hatasını logla
     */
    public function log_error($message) {
        if ($this->db && $this->db->last_error) {
            error_log('Sozluk_V2_DB SQL Hatası: ' . $this->db->last_error . ' - ' . $message);
        }
    }
}

// wp-config.php dosyanızın sonuna bu satırı ekleyin:
// require_once(ABSPATH . 'wp-config-sozluk.php');
