# FOS-FM Radio Database

Community-driven FM radio station database for potential implementation in Euro Truck Simulator 2 and American Truck Simulator via [RadioStation 3D](https://nequeststudios.itch.io/radiostation).

## 🎶 About

FOS-FM is a collaborative platform where the community can contribute and maintain a comprehensive database of FM radio stations from around the world. While not yet integrated, this database is being built as a **prototype** for future implementation into RadioStation 3D addon.

### What is RadioStation 3D?

🎶🚚 **In-Cabin Radio & Music Player**  
Ever wished your music felt like it was truly coming from inside your truck's cabin? RadioStation 3D simulates exactly that — a radio & music player designed for an authentic in-cabin audio experience.

#### ✨ RadioStation 3D Features

- **Immersive cabin simulation** → Hear your music as if it's coming from your truck's speakers
- **Spatialized sound** → Camera position and rotation affect how the music reaches you, just like in real life
- **Game integration** → Works with Euro Truck Simulator 2 and American Truck Simulator, detecting your truck model and simulating its cabin acoustics
- **Beautiful skyboxes** → Not in the mood for driving? Relax while listening to your favorite tracks under stunning skies
- **Full mod support** → Add custom cabins, dashboards, and more to personalize your experience

#### 🎵 RadioStation 3D Versions

- **Free version** → Add your own music folders and enjoy them with all cabin simulation features
- **Premium version** → Unlock streaming radio stations for a true radio-on-the-road experience

### Why FOS-FM?

FOS-FM aims to provide the **radio station database** that could power RadioStation 3D's premium streaming features:

- **Community-Driven**: Everyone can contribute their favorite local radio stations
- **GitHub-Based**: All submissions go through pull requests for quality control
- **Always Up-to-Date**: Report broken streams and outdated information
- **Global Coverage**: Radio stations from all countries and regions
- **Easy Integration**: Automatically exported to formats ready for future RadioStation 3D integration

## 🚧 Current Status

**⚠️ PROTOTYPE STAGE**

This database is currently a **proof of concept** and is **not yet implemented** in RadioStation 3D. We're building the infrastructure and gathering community contributions in preparation for potential future integration.

## 🚀 How It Works

1. **Submit**: Users log in with GitHub and add radio stations or report issues
2. **Review**: Submissions are reviewed as GitHub pull requests
3. **Merge**: Approved stations are added to the database
4. **Export**: Automated GitHub Actions generate M3U playlists and JSON files organized by country
5. **Future**: When implemented, RadioStation 3D would fetch the latest radio list automatically

## 📻 Radio Database Structure

Each radio station entry contains:

- **Name**: Station name (e.g., "Evropa 2")
- **Stream URL**: Direct link to the stream (required)
- **Homepage**: Official website (optional)
- **Country**: ISO country code (required, e.g., CZ, SK, DE)
- **Region**: City or region (optional, e.g., "Prague")
- **Genre**: Music genre or type (optional, e.g., "Pop", "Rock", "News")

## 🌐 Submit Radio Stations

Visit **[https://fos-fm.mxnticek.eu](https://fos-fm.mxnticek.eu)** to:

- ➕ Add new radio stations
- ⚠️ Report broken or incorrect streams
- 👀 Review your pending submissions
- 📊 Track your contribution history

### Submission Process

1. **Login** with your GitHub account
2. **Add stations** using the web form with validation
3. **Review** all your changes before submitting
4. **Submit** creates a pull request on GitHub under your name
5. **Wait** for maintainers to review and merge

## 🎧 About RadioStation 3D

**[Get RadioStation 3D on itch.io](https://nequeststudios.itch.io/radiostation)**

### Current Features (Free)

- 🎵 Play your own music with cabin simulation
- 🎛️ Camera position affects audio spatialization
- 🚚 Truck-specific cabin acoustics for ETS2/ATS
- 🌌 Beautiful skybox environments
- 🔧 Full mod support for custom cabins

### Premium Features

- 📻 Stream radio stations for authentic radio experience
- 🌍 (Future: Could integrate with FOS-FM database)
- 🔄 (Future: Auto-updated station lists from FOS-FM)
- 🎶 Authentic radio experience on the road

## 🛠️ Technical Details

### Repository Structure

```
radios.json          # Master database (JSON format)
exports/
  ├── by-country/
  │   ├── CZ.json    # Czech stations
  │   ├── CZ.m3u     # Czech stations playlist
  │   ├── SK.json    # Slovak stations
  │   └── ...
  └── all.m3u        # All stations combined
```

### JSON Format

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

### GitHub Actions (Planned)

Automated workflows:
- **Export by Country**: Generates country-specific JSON and M3U files
- **Validate Streams**: Checks stream URLs are accessible
- **Update README**: Auto-generates statistics

## 📈 Statistics

- **Total Stations**: `4` 
- **Countries**: `1`
- **Contributors**: `<!-- AUTO-GENERATED -->`
- **Last Updated**: `2025-12-06`

## 🤝 Contributing

### Via Web Interface (Recommended)

Visit [fos-fm.mxnticek.eu](https://fos-fm.mxnticek.eu) and use the submission form.

### Direct PR (Advanced)

1. Fork this repository
2. Edit `radios.json`
3. Add your station following the JSON format
4. Create a pull request

### Reporting Issues

Use the web interface to report:
- Stream not working
- Wrong information
- Low quality streams
- Duplicate entries

## 📋 Submission Guidelines

✅ **Good Submissions**:
- Working, direct stream URLs (not website links)
- Accurate station information
- Legal, publicly accessible streams
- One station per submission (or related group)

❌ **Avoid**:
- Pirated or illegal streams
- Password-protected streams
- Streams requiring login/subscription
- Broken or offline URLs

## 🔗 Links

- **Submit Radios**: [fos-fm.mxnticek.eu](https://fos-fm.mxnticek.eu)
- **RadioStation 3D**: [itch.io](https://nequeststudios.itch.io/radiostation)
- **Support Development**: [Ko-fi](https://ko-fi.com/vlastimilnovotny)

## 🎯 Future Goals

- Complete the radio station database with global coverage
- Develop GitHub Actions for automated exports
- Implement stream validation and health checks
- Create API endpoint for RadioStation 3D integration
- Build community of contributors

## 💖 Support

If you enjoy this project and want to see it integrated into RadioStation 3D:

- ⭐ Star this repository
- 📻 Submit your local stations
- 💰 Support via [PayPal](https://paypal.me/mxnticek) or [Ko-fi](https://ko-fi.com/vlastimilnovotny)
- 🔗 Crypto: Polygon, Solana, Litecoin addresses in [bio](https://bio.odjezdy.online)

## 🎧 Why Support RadioStation 3D?

This is a passion project combining music, simulation, and creativity. By supporting RadioStation 3D, you help:

- Add new features and customizations faster
- Expand modding support and integrations
- Build a vibrant community of driving + music lovers
- Potentially integrate FOS-FM database for streaming radios

## 📜 License

Database: **CC0 1.0 Universal** (Public Domain)  
Web Interface: **MIT License**

Radio streams are property of their respective owners. This database only provides links to publicly accessible streams.

## 👤 Maintainer

**Vlastimil Novotný** ([@VlastikYoutubeKo](https://github.com/VlastikYoutubeKo))

- 🌐 Website: [odjezdy.online](https://odjezdy.online)
- 💼 Bio: [bio.odjezdy.online](https://bio.odjezdy.online)

---

**🎵 Building the future of in-cabin radio for ETS2/ATS! 🚚**
