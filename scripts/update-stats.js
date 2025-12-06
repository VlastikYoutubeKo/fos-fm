#!/usr/bin/env node

const fs = require('fs');

// Read radios.json
const radios = JSON.parse(fs.readFileSync('radios.json', 'utf8'));

// Count countries
const countries = new Set();
radios.forEach(radio => {
  if (radio.country) {
    countries.add(radio.country);
  }
});

// Count contributors (would need Git history, simplified here)
const stats = {
  totalStations: radios.length,
  countries: countries.size,
  lastUpdated: new Date().toISOString().split('T')[0]
};

console.log('\n📊 Statistics:');
console.log(`Total Stations: ${stats.totalStations}`);
console.log(`Countries: ${stats.countries}`);
console.log(`Last Updated: ${stats.lastUpdated}`);

// Update README.md if it exists
if (fs.existsSync('README.md')) {
  let readme = fs.readFileSync('README.md', 'utf8');
  
  // Replace statistics (if markers exist)
  readme = readme.replace(
    /\*\*Total Stations\*\*: `<!-- AUTO-GENERATED -->`/,
    `**Total Stations**: \`${stats.totalStations}\``
  );
  readme = readme.replace(
    /\*\*Countries\*\*: `<!-- AUTO-GENERATED -->`/,
    `**Countries**: \`${stats.countries}\``
  );
  readme = readme.replace(
    /\*\*Last Updated\*\*: `<!-- AUTO-GENERATED -->`/,
    `**Last Updated**: \`${stats.lastUpdated}\``
  );
  
  fs.writeFileSync('README.md', readme, 'utf8');
  console.log('✅ README.md updated with latest statistics');
}

// Save stats to JSON
fs.writeFileSync(
  'exports/stats.json',
  JSON.stringify(stats, null, 2),
  'utf8'
);
console.log('✅ Stats saved to exports/stats.json');
