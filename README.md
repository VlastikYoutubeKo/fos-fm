# Radio Database Submit - MVP

Web aplikace pro přidávání rádiových stanic do GitHub repozitáře přes pull requesty. Uživatelé se přihlásí přes GitHub OAuth, mohou přidávat rádia a hlásit problémy, vše se ukládá jako pending changes a odesílá najednou v jednom PR.

## Features

- ✅ GitHub OAuth přihlášení
- ✅ Přidávání nových rádií (název, stream URL, homepage, země, region, žánr)
- ✅ Hlášení problémů s existujícími rádii
- ✅ Pending changes pro každého uživatele (multi-device sync přes SQLite)
- ✅ Preview změn před submitnutím
- ✅ Automatické vytváření PR na GitHubu
- ✅ Varování při více než 50 změnách
- ✅ Historie odeslaných PRs

## Požadavky

- PHP 8.0+ s rozšířeními:
  - PDO
  - SQLite3
  - cURL
  - JSON
- Webserver (Caddy nebo Apache/NGINX)
- GitHub OAuth App

## Instalace

### 1. GitHub OAuth App

1. Jdi na https://github.com/settings/developers
2. Klikni na "New OAuth App"
3. Vyplň:
   - **Application name**: Radio Submit
   - **Homepage URL**: `https://fos-fm.mxnticek.eu`
   - **Authorization callback URL**: `https://fos-fm.mxnticek.eu/callback.php`
4. Ulož **Client ID** a **Client Secret**

### 2. GitHub Repository

1. Vytvoř nový repozitář (např. `radio-database`)
2. Vytvoř soubor `radios.json` s obsahem:
```json
[]
```
3. Commitni a pushni

### 3. Instalace aplikace

```bash
# Naklonuj nebo zkopíruj soubory do /var/www/radio-submit
cd /var/www
sudo mkdir radio-submit
sudo chown www-data:www-data radio-submit

# Zkopíruj soubory
cp -r /path/to/files/* /var/www/radio-submit/

# Vytvoř .env z .env.example
cd /var/www/radio-submit
cp .env.example .env

# Uprav .env
nano .env
```

### 4. Konfigurace .env

```env
# GitHub OAuth
GITHUB_CLIENT_ID=tvůj_client_id
GITHUB_CLIENT_SECRET=tvůj_client_secret
GITHUB_REDIRECT_URI=https://fos-fm.mxnticek.eu/callback.php

# GitHub Repository
GITHUB_REPO_OWNER=tvůj_username
GITHUB_REPO_NAME=radio-database
GITHUB_JSON_FILE=radios.json

# Database
DB_PATH=/var/www/radio-submit/data/database.db

# App
APP_URL=https://fos-fm.mxnticek.eu
SESSION_SECRET=nějaký_random_string
```

### 5. Inicializace databáze

```bash
php init_db.php
```

### 6. Nastavení oprávnění

```bash
sudo chown -R www-data:www-data /var/www/radio-submit
sudo chmod -R 755 /var/www/radio-submit
sudo chmod 775 /var/www/radio-submit/data
sudo chmod 664 /var/www/radio-submit/data/database.db
```

### 7. Caddy konfigurace

Zkopíruj Caddyfile do `/etc/caddy/sites/` nebo přidej do hlavního Caddyfile:

```bash
sudo cp Caddyfile /etc/caddy/sites/radio-submit.caddy
sudo caddy reload
```

Alternativně pro Apache použij dodaný `.htaccess`.

## Struktura databáze

### Tabulka `users`
- `id` - PRIMARY KEY
- `github_id` - GitHub user ID
- `github_username` - GitHub username
- `access_token` - GitHub OAuth token
- `created_at` - Timestamp

### Tabulka `pending_changes`
- `id` - PRIMARY KEY
- `user_id` - Foreign key na users
- `change_type` - 'add_radio' nebo 'report_issue'
- `data` - JSON s detaily
- `created_at` - Timestamp

### Tabulka `submitted_prs`
- `id` - PRIMARY KEY
- `user_id` - Foreign key na users
- `pr_number` - Číslo PR
- `pr_url` - URL PR
- `changes_count` - Počet změn v PR
- `status` - 'pending', 'merged', 'closed'
- `created_at` - Timestamp

## JSON struktura rádií

```json
[
  {
    "name": "Evropa 2",
    "stream_url": "https://stream.example.com/evropa2",
    "url": "https://evropa2.cz",
    "country": "CZ",
    "region": "Prague",
    "genre": "Pop"
  }
]
```

Všechna pole kromě `name`, `stream_url` a `country` jsou volitelná.

## Použití

1. Uživatel se přihlásí přes GitHub
2. Přidá rádia nebo nahlásí problémy
3. Vše se ukládá jako "pending changes"
4. V Review stránce vidí všechny změny
5. Submitne → vytvoří se PR na GitHub pod jeho jménem
6. Maintainer repo schválí nebo zamítne

## Bezpečnost

- GitHub OAuth pro autentizaci
- CSRF ochrana přes session state
- SQLite databáze s přístupem jen pro www-data
- Validace všech inputů
- Security headers (X-Frame-Options, CSP...)

## Údržba

### Backup databáze

```bash
# Denní backup
0 2 * * * cp /var/www/radio-submit/data/database.db /backups/radio-submit-$(date +\%Y\%m\%d).db
```

### Kontrola PR statusů

Pro automatickou aktualizaci statusu PRs můžeš přidat cron job nebo webhook z GitHubu.

## Troubleshooting

### Chyba: "Failed to get repository information"
- Zkontroluj GITHUB_REPO_OWNER a GITHUB_REPO_NAME v .env
- Ověř že máš přístup k repozitáři

### Chyba: "Database connection failed"
- Zkontroluj oprávnění na složku data/
- Ujisti se že PHP má povoleno SQLite

### OAuth nefunguje
- Zkontroluj že callback URL v GitHub OAuth App sedí s .env
- Ověř Client ID a Secret

## Vylepšení pro budoucnost

- [ ] Automatický export do složek podle countries
- [ ] M3U generování přes GitHub Actions
- [ ] Validace stream URL (test dostupnosti)
- [ ] Admin dashboard pro správu PRs
- [ ] Email notifikace při merge PR
- [ ] API pro načítání rádií
- [ ] Vyhledávání v existujících rádiích

## License

MIT

## Autor

Vlastimil Novotný
