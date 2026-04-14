const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: "new", args: ['--no-sandbox'] });
    const page = await browser.newPage();
    
    // Listen for console errors
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    
    // The server needs to be running. I will start it in the background if it's not.
    console.log("Checking dropdown...");
    
    await browser.close();
})();
