# WP Sözlük - WordPress Almanca-Türkçe Sözlük Eklentisi

Mevcut Almanca sözlük veritabanınızı WordPress ile entegre eden kapsamlı sözlük eklentisi. 3500+ kelime ile tam işlevsel Almanca-Türkçe sözlük sistemi.

## Özellikler

### Mevcut Veritabanı Entegrasyonu
- **Sozluk_V2_DB Uyumluluğu**: Mevcut sınıfınızla tam entegrasyon
- **wp_sozluk_kelimeler_v2 Tablosu**: Hazır veritabanınızı kullanır
- **3500+ Kelime**: Mevcut Almanca kelime koleksiyonunuz

### Admin Paneli
- **Almanca Kelime Yönetimi**: Kelimeler ekleyin, düzenleyin ve silin
- **Artikel Desteği**: der/die/das artikel yönetimi
- **Kelime Türü Filtreleme**: isim, fiil, sıfat kategorileri
- **İstatistikler Sayfası**: Kategori dağılımı, en çok arananlar
- **Arama ve Filtreleme**: Kapsamlı kelime arama sistemi

### Frontend
- **Almanca Shortcode'lar**: `[wp-sozluk]` ve `[sozluk-kelime]`
- **AJAX Arama**: Anlık kelime arama
- **Artikel Gösterimi**: Renkli der/die/das etiketleri
- **Eş/Zıt Anlamlılar**: Kelime ilişkileri
- **Örnek Cümleler**: Kullanım örnekleri
- **Responsive Tasarım**: Mobil uyumlu arayüz

### Teknik Özellikler
- **Çoklu Veritabanı**: WordPress + Harici DB desteği
- **Performans Optimizasyonu**: İndekslenmiş sorgular

## Kurulum

1. Eklenti dosyalarını `/wp-content/plugins/wp-sozluk/` dizinine yükleyin
2. wp-config.php dosyasında Sozluk_V2_DB sınıfınız tanımlı olmalı
3. Eklentiyi WordPress admin panelinden etkinleştirin
4. Admin menüsünde 'Sözlük' bölümünden kelimelerinizi yönetin

## Kullanım

### Shortcode'lar

#### Tüm Sözlüğü Göster
```php
[wp-sozluk]
```

#### Kategoriye Göre Göster
```php
[wp-sozluk kategori="Günlük Hayat" limit="30"]
```

#### Kelime Türüne Göre Göster
```php
[wp-sozluk kelime_turu="isim" artikel="der"]
```

#### Tekil Kelime Göster
```php
[sozluk-kelime almanca="Haus"]
[sozluk-kelime turkce="ev"]
[sozluk-kelime id="123"]
```

### Admin Özellikleri

- **Almanca Sözlük**: Tüm kelimeleri görüntüleme ve arama
- **Yeni Kelime**: Almanca kelime ekleme formu
- **İstatistikler**: Kategori dağılımı, en çok arananlar
- **Ayarlar**: Eklenti yapılandırması

## Dosya Yapısı

```
wp-sozluk/
├── wp-sozluk.php              # Ana plugin dosyası
├── includes/
│   ├── admin/
│   │   └── class-wp-sozluk-admin.php    # Admin paneli sınıfı
│   ├── class-wp-sozluk-frontend.php     # Frontend sınıfı
│   └── class-wp-sozluk-ajax.php         # AJAX işlemleri sınıfı
├── assets/
│   ├── css/
│   │   ├── admin.css          # Admin paneli stilleri
│   │   └── frontend.css       # Frontend stilleri
│   └── js/
│       ├── admin.js           # Admin paneli JavaScript
│       └── frontend.js        # Frontend JavaScript
└── README.md                  # Bu dosya
```

## Veritabanı Yapısı

Eklenti, mevcut `wp_sozluk_kelimeler_v2` tablosunu kullanır:

- `id`: Benzersiz kelime ID'si
- `almanca_kelime`: Almanca kelime
- `turkce_kelime`: Türkçe karşılık
- `artikel`: der/die/das artikel
- `almanca_anlam`: Almanca anlamı
- `turkce_anlam`: Türkçe anlamı
- `kelime_turu`: Kelime türü (isim, fiil, sıfat vb.)
- `kategori`: Kelime kategorisi
- `alt_kategori_id`: Alt kategori ID'si
- `ornek_cumle`: Örnek cümle
- `telaffuz_url`: Telaffuz dosyası URL'si
- `eklenme_tarihi`: Eklenme tarihi
- `es_anlamlilar`: Eş anlamlı kelimeler
- `zit_anlamlilar`: Zıt anlamlı kelimeler
- `arama_sayisi`: Arama sayısı (otomatik artırılır)

## AJAX Endpoint'leri

### Frontend
- `sozluk_search_terms`: Terim arama
- `sozluk_get_term`: Tekil terim getirme
- `sozluk_frontend_search`: Frontend arama
- `sozluk_get_terms_by_letter`: Harfe göre terimler

### Admin
- `sozluk_delete_term`: Terim silme

## CSS Sınıfları

### Frontend
- `.wp-sozluk-container`: Ana konteyner
- `.wp-sozluk-search`: Arama kutusu
- `.wp-sozluk-alphabet`: Alfabetik navigasyon
- `.wp-sozluk-terms`: Terim listesi
- `.wp-sozluk-term`: Tekil terim
- `.wp-sozluk-modal`: Modal pencere

## Güvenlik

- Tüm AJAX istekleri nonce ile doğrulanır
- Kullanıcı girişleri sanitize edilir
- SQL injection koruması
- XSS koruması

## Performans

- Veritabanı sorguları optimize edilmiştir
- AJAX ile sayfa yenilenmeden içerik yüklenir
- CSS ve JS dosyları sadece gerekli sayfalarda yüklenir

## Destek

Herhangi bir sorun yaşarsanız veya öneriniz varsa lütfen iletişime geçin.

## Lisans

GPL v2 veya üzeri

## Sürüm Geçmişi

### 1.0.0
- İlk sürüm
- Temel sözlük işlevleri
- Admin paneli
- Frontend görüntüleme
- Shortcode desteği
- AJAX arama
