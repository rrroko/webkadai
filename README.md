# web (BBS final)
- nginx + php-fpm + mysql (Docker Compose)
- Port: 80 (提出時) / 8080 (同居開発)
- DB: appdb / user: appuser / pass: apppass

## 起動
docker compose -p web up -d --build

## テーブル作成
docker compose -p web exec -T mysql mysql -uappuser -papppass appdb < init.sql
