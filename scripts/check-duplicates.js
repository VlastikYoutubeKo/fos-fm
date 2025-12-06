#!/usr/bin/env node

const fs = require('fs');

// Read radios.json
const radios = JSON.parse(fs.readFileSync('radios.json', 'utf8'));

console.log(`🔍 Checking for duplicates in ${radios.length} stations...\n`);

const seenUrls = new Map();
const seenNames = new Map();
let duplicateCount = 0;

radios.forEach((radio, index) => {
  // Check duplicate stream URLs
  if (radio.stream_url) {
    if (seenUrls.has(radio.stream_url)) {
      console.log(`⚠️  Duplicate stream URL found:`);
      console.log(`   [${seenUrls.get(radio.stream_url) + 1}] ${radios[seenUrls.get(radio.stream_url)].name}`);
      console.log(`   [${index + 1}] ${radio.name}`);
      console.log(`   URL: ${radio.stream_url}\n`);
      duplicateCount++;
    } else {
      seenUrls.set(radio.stream_url, index);
    }
  }
  
  // Check duplicate names (same name + country)
  const nameKey = `${radio.name}|${radio.country}`;
  if (radio.name && radio.country) {
    if (seenNames.has(nameKey)) {
      console.log(`⚠️  Duplicate name + country found:`);
      console.log(`   [${seenNames.get(nameKey) + 1}] ${radios[seenNames.get(nameKey)].name} (${radios[seenNames.get(nameKey)].country})`);
      console.log(`   [${index + 1}] ${radio.name} (${radio.country})\n`);
      duplicateCount++;
    } else {
      seenNames.set(nameKey, index);
    }
  }
});

if (duplicateCount === 0) {
  console.log('✅ No duplicates found!');
  process.exit(0);
} else {
  console.log(`⚠️  Found ${duplicateCount} potential duplicates.`);
  console.log('Please review and remove duplicates if necessary.');
  // Don't fail the check, just warn
  process.exit(0);
}
