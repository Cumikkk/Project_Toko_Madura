<style>
    .download-hero {
        background: linear-gradient(135deg, var(--primary-color) 0%, #1a5490 100%);
        padding: 40px 0;
        margin-bottom: 40px;
        border-radius: 8px;
    }

    .download-hero h1 {
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .download-hero p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.1rem;
    }

    .platform-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        border: none;
    }

    .platform-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .platform-icon {
        font-size: 4rem;
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    .platform-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .platform-description {
        font-size: 0.95rem;
        margin-bottom: 20px;
        line-height: 1.6;
    }

    .feature-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 25px;
    }

    .feature-list li {
        padding: 8px 0;
        font-size: 0.9rem;
        position: relative;
        padding-left: 25px;
    }

    .feature-list li:before {
        content: "\f00c";
        font-family: "Font Awesome 6 Pro";
        font-weight: 300;
        position: absolute;
        left: 0;
        color: var(--primary-color);
    }

    .download-btn {
        width: 100%;
        padding: 12px 24px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .download-btn:hover {
        transform: scale(1.02);
    }

    .btn-primary-download {
        background: var(--primary-color);
        border: none;
        color: white;
    }

    .btn-primary-download:hover {
        background: #1a5490;
        color: white;
    }

    .btn-outline-download {
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        background: transparent;
    }

    .btn-outline-download:hover {
        background: var(--primary-color);
        color: white;
    }

    .platform-image {
        width: 100%;
        max-width: 300px;
        margin: 20px auto;
        display: block;
    }

    .quick-links {
        background: var(--panel-bg);
        padding: 30px;
        border-radius: 8px;
        margin-top: 40px;
    }

    .quick-links h4 {
        margin-bottom: 20px;
        font-weight: 600;
    }

    .quick-link-item {
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .quick-link-item:hover {
        background: rgba(0, 0, 0, 0.05);
    }

    .quick-link-icon {
        font-size: 2rem;
        color: var(--primary-color);
    }

    .system-requirements {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 15px;
    }

    .badge-new {
        background: #dc3545;
        color: white;
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 3px;
        margin-left: 8px;
    }

    .platform-section {
        margin-bottom: 50px;
    }

    @media (max-width: 768px) {
        .download-hero h1 {
            font-size: 1.8rem;
        }

        .platform-icon {
            font-size: 3rem;
        }
    }
</style>

<div class="dashboard-breadcrumb mb-25">
    <h2>Download Platform</h2>
</div>
<!-- Desktop Section -->
<div class="platform-section">
    <div class="row">
        <div class="col-12">
            <div class="panel">
                <div class="panel-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="platform-icon">
                                <i class="fa-light fa-desktop"></i>
                            </div>
                            <h3 class="platform-title">MetaTrader 5 for Desktop</h3>
                            <p class="platform-description">
                                Download MetaTrader 5 and start trading Forex, Stocks and Futures! Rich trading functionality, 
                                technical and fundamental market analysis, copy trading and automated trading are all exciting 
                                features that you can access for free right now!
                            </p>
                            <ul class="feature-list">
                                <li>Full set of trading orders for flexible Forex, Stocks and other securities trading</li>
                                <li>Two position accounting systems: netting and hedging</li>
                                <li>Unlimited amount of charts with 21 timeframes</li>
                                <li>Technical analysis with over 80 built-in indicators and analytical tools</li>
                                <li>Fundamental analysis based on financial news and economic calendar</li>
                                <li>Algorithmic trading with MQL5 development environment</li>
                                <li>Trading Signals for automated copying of deals</li>
                                <li>Built-in Forex VPS</li>
                            </ul>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <button class="download-btn btn-primary" onclick="downloadMT5('windows')">
                                        <i class="fa-brands fa-windows me-2"></i>Download for Windows
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button class="download-btn btn-outline" onclick="downloadMT5('macos')">
                                        <i class="fa-brands fa-apple me-2"></i>Download for macOS
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button class="download-btn btn-outline" onclick="downloadMT5('linux')">
                                        <i class="fa-brands fa-linux me-2"></i>Download for Linux
                                    </button>
                                </div>
                            </div>
                            <div class="system-requirements">
                                <strong>System Requirements:</strong> Windows 7 or higher, macOS 10.10 or higher, Linux (Wine required)
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <img src="/assets/images/metatrader-5-desktop.jpg" alt="MetaTrader 5 Desktop" class="platform-image">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Web Terminal Section -->
<div class="platform-section">
    <div class="row">
        <div class="col-12">
            <div class="panel">
                <div class="panel-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center order-md-1 order-2">
                            <img src="/assets/images/metatrader-5-windows.jpg" alt="MetaTrader 5 WebTerminal" class="platform-image">
                        </div>
                        <div class="col-md-8 order-md-2 order-1">
                            <div class="platform-icon">
                                <i class="fa-brands fa-windows"></i>
                            </div>
                            <h3 class="platform-title">MetaTrader 5 for Windows</h3>
                            <p class="platform-description">
                                Download MetaTrader 5 for Windows and experience the most powerful trading platform! 
                                Designed specifically for Windows OS, this desktop application provides the ultimate trading 
                                experience with advanced charting, technical analysis, and algorithmic trading capabilities.
                            </p>
                            <ul class="feature-list">
                                <li>Optimized performance for Windows operating system</li>
                                <li>Full set of trading orders and execution modes</li>
                                <li>Advanced charting with 21 timeframes and 100+ indicators</li>
                                <li>Built-in MQL5 IDE for automated trading development</li>
                                <li>One-click trading and market depth functionality</li>
                                <li>Economic calendar and financial news integration</li>
                                <li>Support for Expert Advisors and trading robots</li>
                                <li>VPS integration for 24/7 automated trading</li>
                            </ul>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <button class="download-btn btn-primary" onclick="downloadMT5('windows')">
                                        <i class="fa-brands fa-windows me-2"></i>Download for Windows
                                    </button>
                                </div>
                            </div>
                            <div class="system-requirements">
                                <strong>Requirements:</strong> Windows 7/8/10/11 (32-bit or 64-bit), 1 GB RAM minimum, Internet connection
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Section -->
<div class="platform-section">
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="panel platform-card">
                <div class="panel-body text-center">
                    <div class="platform-icon">
                        <i class="fa-brands fa-apple"></i>
                    </div>
                    <h3 class="platform-title">MetaTrader 5 for iPhone & iPad</h3>
                    <p class="platform-description">
                        Install the mobile application on your iPhone or iPad to have access to the markets at any time! 
                        Over a million users have already downloaded MT5 Mobile to trade Forex, Stocks and other securities.
                    </p>
                    <ul class="feature-list text-start">
                        <li>Trading currencies and stocks anywhere in the world</li>
                        <li>30 technical indicators and 24 analytical objects</li>
                        <li>Full-featured trading system with Market Depth</li>
                        <li>Netting and hedging position accounting</li>
                        <li>All types of trade orders and pending orders</li>
                        <li>3 chart types and 9 timeframes</li>
                        <li>Built-in chat, financial news, alerts</li>
                        <li>Extended version for iPad</li>
                    </ul>
                    <div class="row g-2">
                        <div class="col-12">
                            <button class="download-btn btn-primary" onclick="downloadMT5('ios')">
                                <i class="fa-brands fa-app-store me-2"></i>Download from App Store
                            </button>
                        </div>
                    </div>
                    <div class="system-requirements">
                        <strong>Requirements:</strong> iOS 11.0 or later
                    </div>
                    <img src="/assets/images/metatrader-5-iphone-ipad.jpg" alt="MT5 iOS" class="platform-image mt-3">
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="panel platform-card">
                <div class="panel-body text-center">
                    <div class="platform-icon">
                        <i class="fa-brands fa-android"></i>
                    </div>
                    <h3 class="platform-title">MetaTrader 5 for Android</h3>
                    <p class="platform-description">
                        Download the mobile application for Android and take the trading platform with you wherever you go! 
                        Trade financial instruments — currencies, futures, options and stocks on your Android device.
                    </p>
                    <ul class="feature-list text-start">
                        <li>Trading Forex, stocks and futures anywhere</li>
                        <li>2 trading systems: netting and hedging</li>
                        <li>Powerful trading with Market Depth</li>
                        <li>All types of trade orders</li>
                        <li>3 chart types and 9 timeframes</li>
                        <li>30 indicators and 24 analytical objects</li>
                        <li>Chat with MQL5.community members</li>
                        <li>Financial news, alerts and notifications</li>
                        <li>Extended version for tablets</li>
                    </ul>
                    <div class="row g-2">
                        <div class="col-12">
                            <button class="download-btn btn-primary" onclick="downloadMT5('android')">
                                <i class="fa-brands fa-google-play me-2"></i>Download from Google Play
                            </button>
                        </div>
                        <!-- <div class="col-12">
                            <button class="download-btn btn-outline" onclick="downloadMT5('apk')">
                                <i class="fa-light fa-download me-2"></i>Download APK
                            </button>
                        </div> -->
                    </div>
                    <div class="system-requirements">
                        <strong>Requirements:</strong> Android 5.0 or later
                    </div>
                    <img src="/assets/images/metatrader-5-android.jpg" alt="MT5 Android" class="platform-image mt-3">
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function downloadMT5(platform) {
    const urls = {
        'windows': 'https://download.mql5.com/cdn/web/24207/mt5/rrfx5setup.exe',
        'macos': 'https://download.mql5.com/cdn/web/metaquotes.software.corp/mt5/MetaTrader5.pkg.zip',
        'linux': 'https://www.metatrader5.com/en/download/linux',
        'ios': 'https://apps.apple.com/app/metatrader-5/id413251709',
        'android': 'https://play.google.com/store/apps/details?id=net.metaquotes.metatrader5',
        'apk': 'https://download.mql5.com/cdn/mobile/mt5/android?hl=en',
        'rrfx_app': 'https://play.google.com/store/apps/details?id=com.rrfx.app&hl=id'
    };

    const platformNames = {
        'windows': 'MetaTrader 5 for Windows',
        'macos': 'MetaTrader 5 for macOS',
        'linux': 'MetaTrader 5 for Linux',
        'ios': 'MetaTrader 5 for iOS',
        'android': 'MetaTrader 5 for Android',
        'apk': 'MetaTrader 5 APK',
        'rrfx_app': 'RRFX App for Android'
    };

    if (urls[platform]) {
        Swal.fire({
            title: 'Downloading...',
            text: `Starting download of ${platformNames[platform]}`,
            icon: 'info',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.open(urls[platform], '_blank');
            
            // Log download activity
            $.ajax({
                url: '/ajax/appPost.php',
                method: 'POST',
                data: {
                    action: 'log_download',
                    platform: platform
                }
            });
        });
    } else {
        Swal.fire({
            title: 'Error',
            text: 'Download link not available',
            icon: 'error'
        });
    }
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>
