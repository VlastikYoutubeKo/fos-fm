# FOS-FM GitHub Repository Setup

Quick guide pro nastavení GitHub repozitáře.

## 1. Vytvoř GitHub Repozitář

```bash
# Na GitHubu vytvoř nový repozitář: fos-fm
# Pak lokálně:

git clone https://github.com/TVUJ_USERNAME/fos-fm.git
cd fos-fm

# Rozbal soubory z archivu
tar -xzf fos-fm-github-files.tar.gz
mv fos-fm-github/* .
mv fos-fm-github/.* . 2>/dev/null
rmdir fos-fm-github

# První commit
git add .
git commit -m "Initial commit: FOS-FM radio database"
git push origin main
```

## 2. Zkontroluj Strukturu

```
fos-fm/
├── .github/
│   ├── workflows/
│   │   ├── export.yml          # Auto-export při změnách
│   │   └── validate.yml        # Validace PRs
│   ├── ISSUE_TEMPLATE/
│   │   ├── broken-stream.md
│   │   └── feature-request.md
│   └── PULL_REQUEST_TEMPLATE.md
├── scripts/
│   ├── export-by-country.js    # Export podle zemí
│   ├── generate-m3u.js         # Generování M3U
│   ├── update-stats.js         # Update statistik
│   ├── validate-streams.js     # Validace streamů
│   └── check-duplicates.js     # Kontrola duplikátů
├── exports/
│   ├── by-country/             # Country exports (auto-gen)
│   └── README.md
├── radios.json                 # HLAVNÍ databáze
├── package.json
├── CONTRIBUTING.md
└── .gitignore
```

## 3. Přidej První Rádio

Edituj `radios.json`:

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

Commit:

```bash
git add radios.json
git commit -m "Add Evropa 2"
git push
```

## 4. GitHub Actions Se Aktivují

Po push do `main` větve:

1. **export.yml** se spustí automaticky
2. Vygeneruje soubory v `exports/`
3. Commitne je zpět do repo

## 5. Test Lokálně (Optional)

```bash
# Nainstaluj Node.js pokud nemáš
# Pak spusť:

npm run validate         # Validace radios.json
npm run export          # Generuj exports
npm run check-duplicates # Kontrola duplikátů
npm run update-stats    # Update statistik
```

## 6. Povol GitHub Actions

V nastavení repozitáře:
1. Jdi na **Settings** → **Actions** → **General**
2. Povol **"Allow all actions and reusable workflows"**
3. V **Workflow permissions** vyber **"Read and write permissions"**
4. ✅ Zaškrtni **"Allow GitHub Actions to create and approve pull requests"**

## 7. Otestuj Workflow

```bash
# Změň radios.json
git add radios.json
git commit -m "Test: Add test radio"
git push

# Sleduj Actions tab na GitHubu
# Měl by se spustit "Export Radio Stations" workflow
```

## 8. Propoj s Web Interface

V `.env` na webhostingu nastav:

```env
GITHUB_REPO_OWNER=tvuj_username
GITHUB_REPO_NAME=fos-fm
GITHUB_JSON_FILE=radios.json
```

## 🎉 Hotovo!

Teď máš:
- ✅ Automatické exporty při každé změně
- ✅ Validace PRs
- ✅ Issue templates
- ✅ Statistiky
- ✅ M3U playlists

## 📊 Přístup k Exportům

Exporty budou dostupné na:

```
https://raw.githubusercontent.com/TVUJ_USERNAME/fos-fm/main/exports/all.json
https://raw.githubusercontent.com/TVUJ_USERNAME/fos-fm/main/exports/all.m3u
https://raw.githubusercontent.com/TVUJ_USERNAME/fos-fm/main/exports/by-country/CZ.json
https://raw.githubusercontent.com/TVUJ_USERNAME/fos-fm/main/exports/by-country/CZ.m3u
```

## 🔧 Troubleshooting

### Actions nefungují?
- Zkontroluj Workflow permissions v Settings
- Ujisti se že Actions jsou povolené

### Export se nespustil?
- Zkontroluj Actions tab → klikni na failed workflow → zobraz logy
- Možná chyba v radios.json syntaxi

### Chci přidat další GitHub Actions?
- Vytvoř nový `.yml` v `.github/workflows/`
- Následuj existující vzory

---

**Happy coding! 🎵**
