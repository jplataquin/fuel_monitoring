const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: "new", args: ['--no-sandbox'] });
    const page = await browser.newPage();
    
    // Listen for console errors
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    
    try {
        console.log("Navigating to local site...");
        await page.goto('http://127.0.0.1:8000', { waitUntil: 'networkidle0', timeout: 5000 }).catch(e => console.log("Timeout waiting for idle, continuing..."));
        
        const hasAlpine = await page.evaluate(() => {
            return window.Alpine !== undefined;
        });
        
        console.log("Is Alpine.js loaded in window?", hasAlpine);
    } catch (e) {
        console.log("Error testing local site", e.message);
    }
    
    await browser.close();
})();
