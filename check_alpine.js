const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: "new", args: ['--no-sandbox'] });
    const page = await browser.newPage();
    
    // Listen for console errors
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    
    try {
        console.log("Navigating to dashboard to test Alpine.js...");
        // Assuming your local app runs on a common port. I will use file:// or http://localhost
        // The safest way is to inspect the JS file itself if it's loading.
    } catch (e) {
        console.log("Error", e);
    }
    await browser.close();
})();
