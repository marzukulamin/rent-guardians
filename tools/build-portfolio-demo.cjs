const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const output = path.join(root, 'demo-dist');
fs.mkdirSync(path.join(output, 'assets'), { recursive: true });
for (const file of ['index.html', 'demo.css', 'demo.js']) {
  fs.copyFileSync(path.join(root, 'portfolio-demo', file), path.join(output, file));
}
for (const file of ['brand-logo.png', 'brand-monogram.png', 'home-1.jpg', 'home-2.jpg', 'hero-web.mp4', 'hero-poster.jpg']) {
  fs.copyFileSync(path.join(root, 'portfolio-demo', 'assets', file), path.join(output, 'assets', file));
}
console.log(`Portfolio demo ready: ${output}`);
