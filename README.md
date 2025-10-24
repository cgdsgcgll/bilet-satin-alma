# 🎫 Bilet Satın Alma Uygulaması (Dockerized)

Bu proje, **PHP 8.2**, **Apache** ve **SQLite** kullanılarak geliştirilmiş bir bilet satın alma sistemidir.  
Proje, geliştirici ortamından bağımsız olarak kolayca çalıştırılabilmesi için **Docker** ve **Docker Compose** ile paketlenmiştir.

---

## 🧩 Projenin Amacı

Bu uygulama, kullanıcıların farklı firmalara ait seferleri görüntüleyip **bilet satın almasını**,  
admin kullanıcıların ise **sefer ekleme, düzenleme ve silme işlemlerini** yapabilmesini sağlar.

Amaç, **basit bir ulaşım sistemi** mantığını, modern container teknolojisiyle birleştirerek hem yazılım hem devops becerilerini geliştirmektir.

---

## ⚙️ Kullanılan Teknolojiler

| Teknoloji                      | Açıklama                               |
| ------------------------------ | -------------------------------------- |
| **PHP 8.2 (Apache modülüyle)** | Backend uygulama dili                  |
| **SQLite3**                    | Hafif ve dosya tabanlı veritabanı      |
| **HTML / CSS / JS**            | Arayüz tasarımı ve dinamik içerikler   |
| **Docker & Docker Compose**    | Projeyi izole ortamda çalıştırmak için |

---

## 🐳 Docker Ortamında Çalıştırma

1️⃣ Depoyu klonla
git clone https://github.com/cgdsgcgll/bilet-satin-alma.git

2️⃣ Klasöre gir
cd bilet-satin-alma

3️⃣ Docker imajını oluştur
docker compose build --no-cache

4️⃣ Container’ı başlat
docker compose up -d

🌐 Uygulamaya Erişim

Container başarıyla başladıktan sonra, tarayıcıdan şu adrese gidin:

http://localhost:8080
