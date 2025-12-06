#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Read radios.json
const radios = JSON.parse(fs.readFileSync('radios.json', 'utf8'));

// Group by country
const byCountry = {};

radios.forEach(radio => {
  const country = radio.country || 'UNKNOWN';
  if (!byCountry[country]) {
    byCountry[country] = [];
  }
  byCountry[country].push(radio);
});

// Function to generate M3U content
function generateM3U(stations, title = 'Radio Stations') {
  let m3u = '#EXTM3U\n';
  m3u += `#PLAYLIST:${title}\n\n`;
  
  stations.forEach(station => {
    m3u += `#EXTINF:-1,${station.name}`;
    if (station.genre) {
      m3u += ` - ${station.genre}`;
    }
    if (station.region) {
      m3u += ` (${station.region})`;
    }
    m3u += '\n';
    m3u += `${station.stream_url}\n\n`;
  });
  
  return m3u;
}

// Create exports directory
const exportsDir = path.join('exports', 'by-country');
if (!fs.existsSync(exportsDir)) {
  fs.mkdirSync(exportsDir, { recursive: true });
}

// Generate M3U for each country
Object.keys(byCountry).forEach(country => {
  const m3uFile = path.join(exportsDir, `${country}.m3u`);
  const m3uContent = generateM3U(byCountry[country], `${country} Radio Stations`);
  fs.writeFileSync(m3uFile, m3uContent, 'utf8');
  console.log(`✅ Generated ${country}.m3u (${byCountry[country].length} stations)`);
});

// Generate all.m3u
const allM3U = generateM3U(radios, 'All Radio Stations');
fs.writeFileSync(path.join('exports', 'all.m3u'), allM3U, 'utf8');
console.log(`✅ Generated all.m3u (${radios.length} stations)`);

console.log('\n📻 M3U playlists generated successfully!');
