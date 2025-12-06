# Contributing to FOS-FM

Thank you for considering contributing to the FOS-FM radio database! 🎵

## 📻 How to Contribute

### Easy Way: Web Interface (Recommended)

Visit **[fos-fm.mxnticek.eu](https://fos-fm.mxnticek.eu)** and:

1. Login with your GitHub account
2. Add new radio stations using the form
3. Submit creates a pull request automatically

### Advanced Way: Direct PR

1. Fork this repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/fos-fm.git`
3. Create a new branch: `git checkout -b add-radio-station`
4. Edit `radios.json` and add your station
5. Commit your changes: `git commit -m "Add [Station Name]"`
6. Push to your fork: `git push origin add-radio-station`
7. Create a pull request

## ✅ Submission Guidelines

### Good Submissions

- ✅ Working, direct stream URLs (test them first!)
- ✅ Accurate station information
- ✅ Legal, publicly accessible streams
- ✅ Complete required fields (name, stream_url, country)

### Avoid

- ❌ Pirated or illegal streams
- ❌ Password-protected streams
- ❌ Streams requiring login/subscription
- ❌ Broken or offline URLs
- ❌ Duplicate entries

## 📋 JSON Format

Each radio station entry must follow this format:

```json
{
  "name": "Radio Name",
  "stream_url": "https://stream.example.com/radio.mp3",
  "url": "https://radiowebsite.com",
  "country": "CZ",
  "region": "Prague",
  "genre": "Pop"
}
```

### Required Fields

- `name` - Station name
- `stream_url` - Direct link to the stream
- `country` - ISO 3166-1 alpha-2 country code (2 letters)

### Optional Fields

- `url` - Homepage/website URL
- `region` - City or region
- `genre` - Music genre or type (Pop, Rock, News, etc.)

## 🔍 Testing Your Stream

Before submitting, test your stream URL:

```bash
# Using curl
curl -I "https://your-stream-url.com/stream.mp3"

# Using VLC
vlc "https://your-stream-url.com/stream.mp3"
```

## 🤖 Automated Checks

When you submit a PR, automated checks will:

- ✅ Validate JSON syntax
- ✅ Check required fields
- ✅ Validate URL formats
- ✅ Check for duplicates
- ⚠️ Test stream accessibility (warnings only)

## 📝 Commit Message Format

Use clear, descriptive commit messages:

- `Add [Radio Name]` - for new stations
- `Update [Radio Name]` - for updates
- `Remove [Radio Name]` - for removals
- `Fix [Radio Name] stream URL` - for fixes

## 🚫 What Gets Rejected

PRs will be rejected if they:

- Add illegal or pirated content
- Contain broken/offline streams
- Have incomplete required information
- Duplicate existing entries
- Don't follow the JSON format

## 💡 Tips

- **Test streams first** - Make sure they work before submitting
- **Use proper country codes** - CZ, SK, DE, etc. (2 letters)
- **Be accurate** - Double-check all information
- **One station per PR** - Makes review easier (or related group)
- **Provide context** - Explain in PR description if needed

## 🌍 Country Codes

Use ISO 3166-1 alpha-2 codes:

- 🇨🇿 Czech Republic: `CZ`
- 🇸🇰 Slovakia: `SK`
- 🇩🇪 Germany: `DE`
- 🇵🇱 Poland: `PL`
- 🇦🇹 Austria: `AT`
- 🇭🇺 Hungary: `HU`

[Full list of country codes](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)

## 📊 After Your PR is Merged

Once merged, GitHub Actions will automatically:

- Export stations by country
- Generate M3U playlists
- Update statistics
- Create JSON exports

These exports will be available in the `exports/` directory.

## ❓ Questions?

- Open an issue for questions
- Check existing issues for similar questions
- Join our community discussions

## 🙏 Thank You!

Every contribution helps build a better radio database for the community. We appreciate your effort!

---

**Happy contributing! 🎵**
