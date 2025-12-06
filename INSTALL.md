# Quick Install Guide

## Rychlý start

### 1. GitHub OAuth App
1. Jdi na https://github.com/settings/developers
2. "New OAuth App"
3. Callback URL: `https://tvoje-domena.cz/callback.php`
4. Ulož Client ID a Secret

### 2. GitHub Repository  
1. Vytvoř nový repo
2. Vytvoř `radios.json` s `[]`
3. Commit & push

### 3. Instalace
```bash
cd /var/www
sudo mkdir radio-submit
sudo cp -r * /var/www/radio-submit/
cd /var/www/radio-submit
cp .env.example .env
nano .env  # Vyplň údaje
php init_db.php
sudo chown -R www-data:www-data /var/www/radio-submit
```

### 4. Caddy
```bash
sudo cp Caddyfile /etc/caddy/sites/radio.caddy
sudo caddy reload
```

### 5. Test
Otevři `https://tvoje-domena.cz` a přihlaš se přes GitHub!

## Struktura .env
```env
GITHUB_CLIENT_ID=...
GITHUB_CLIENT_SECRET=...
GITHUB_REDIRECT_URI=https://tvoje-domena.cz/callback.php
GITHUB_REPO_OWNER=tvuj-username
GITHUB_REPO_NAME=nazev-repo
```

Done! ✅
