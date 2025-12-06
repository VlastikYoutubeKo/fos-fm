#!/usr/bin/env node

const fs = require('fs');
const https = require('https');
const http = require('http');

// Read radios.json
const radios = JSON.parse(fs.readFileSync('radios.json', 'utf8'));

console.log(`🔍 Validating ${radios.length} radio streams...\n`);

let validCount = 0;
let invalidCount = 0;
let warningCount = 0;

// Simple URL validation
radios.forEach((radio, index) => {
  const errors = [];
  const warnings = [];
  
  // Check required fields
  if (!radio.name) {
    errors.push('Missing name');
  }
  if (!radio.stream_url) {
    errors.push('Missing stream_url');
  }
  if (!radio.country) {
    errors.push('Missing country');
  }
  
  // Validate URLs
  if (radio.stream_url) {
    try {
      new URL(radio.stream_url);
      if (!radio.stream_url.startsWith('http://') && !radio.stream_url.startsWith('https://')) {
        warnings.push('Stream URL should use HTTP/HTTPS protocol');
      }
    } catch (e) {
      errors.push('Invalid stream_url format');
    }
  }
  
  if (radio.url) {
    try {
      new URL(radio.url);
    } catch (e) {
      errors.push('Invalid homepage url format');
    }
  }
  
  // Validate country code
  if (radio.country && radio.country.length !== 2) {
    warnings.push('Country code should be 2 letters (ISO 3166-1 alpha-2)');
  }
  
  // Print results
  if (errors.length > 0) {
    console.log(`❌ [${index + 1}] ${radio.name || 'Unknown'}`);
    errors.forEach(err => console.log(`   - ${err}`));
    invalidCount++;
  } else if (warnings.length > 0) {
    console.log(`⚠️  [${index + 1}] ${radio.name}`);
    warnings.forEach(warn => console.log(`   - ${warn}`));
    warningCount++;
  } else {
    console.log(`✅ [${index + 1}] ${radio.name}`);
    validCount++;
  }
});

console.log('\n📊 Validation Summary:');
console.log(`✅ Valid: ${validCount}`);
console.log(`⚠️  Warnings: ${warningCount}`);
console.log(`❌ Invalid: ${invalidCount}`);

if (invalidCount > 0) {
  console.log('\n❌ Validation failed! Please fix the errors above.');
  process.exit(1);
} else {
  console.log('\n✅ All stations validated successfully!');
  process.exit(0);
}
