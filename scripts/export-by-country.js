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

// Create exports directory
const exportsDir = path.join('exports', 'by-country');
if (!fs.existsSync(exportsDir)) {
  fs.mkdirSync(exportsDir, { recursive: true });
}

// Write country-specific JSON files
Object.keys(byCountry).forEach(country => {
  const countryFile = path.join(exportsDir, `${country}.json`);
  fs.writeFileSync(
    countryFile,
    JSON.stringify(byCountry[country], null, 2),
    'utf8'
  );
  console.log(`✅ Exported ${byCountry[country].length} stations to ${country}.json`);
});

// Write all.json
fs.writeFileSync(
  path.join('exports', 'all.json'),
  JSON.stringify(radios, null, 2),
  'utf8'
);
console.log(`✅ Exported ${radios.length} total stations to all.json`);

console.log('\n📊 Summary:');
console.log(`Total stations: ${radios.length}`);
console.log(`Countries: ${Object.keys(byCountry).length}`);
Object.keys(byCountry).sort().forEach(country => {
  console.log(`  ${country}: ${byCountry[country].length} stations`);
});
