<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="CrewSwap Privacy Policy — how we collect, use, and protect your personal data in compliance with GDPR, CCPA, COPPA, and applicable app store policies." />
    <meta name="robots" content="index, follow" />
    <meta property="og:title" content="Privacy Policy | CrewSwap" />
    <meta property="og:description" content="Learn how CrewSwap collects, uses, and protects your data." />
    <meta property="og:type" content="website" />
    <title>Privacy Policy | CrewSwap</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f2f7;
            color: #1e2235;
            line-height: 1.8;
            font-size: 15px;
        }
        .site-header {
            background: linear-gradient(145deg, #0f172a 0%, #1e3a5f 60%, #0ea5e9 100%);
            color: #fff;
            padding: 64px 24px 52px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .site-header .logo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 18px;
        }
        .site-header .logo-icon {
            width: 52px;
            height: 52px;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }
        .site-header .brand-name {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fff;
        }
        .site-header h1 {
            font-size: 22px;
            font-weight: 400;
            opacity: 0.88;
            margin-bottom: 14px;
        }
        .site-header .meta-pills {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-top: 6px;
        }
        .site-header .meta-pill {
            background: rgba(255,255,255,0.11);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 12.5px;
            font-weight: 500;
        }
        .page-wrap {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }
        .compliance-banner {
            background: #fff;
            border-radius: 16px;
            padding: 28px 32px;
            margin: 36px 0 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 24px rgba(0,0,0,.06);
            border-top: 4px solid #0ea5e9;
        }
        .compliance-banner h2 {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 16px;
        }
        .badge-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 24px;
            font-size: 12.5px;
            font-weight: 600;
            letter-spacing: .3px;
            border: 1.5px solid transparent;
        }
        .badge-gdpr  { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .badge-ccpa  { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
        .badge-coppa { background: #fefce8; color: #a16207; border-color: #fde68a; }
        .badge-pdpa  { background: #fdf4ff; color: #7e22ce; border-color: #e9d5ff; }
        .badge-apple { background: #f8fafc; color: #374151; border-color: #e5e7eb; }
        .badge-google{ background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
        .toc-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 24px rgba(0,0,0,.06);
        }
        .toc-card h2 {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 16px;
        }
        .toc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 6px 20px;
            list-style: none;
        }
        .toc-grid li { display: flex; align-items: center; gap: 8px; }
        .toc-grid li a { color: #0ea5e9; text-decoration: none; font-size: 13.5px; font-weight: 500; padding: 5px 0; display: block; }
        .toc-grid li a:hover { color: #0284c7; text-decoration: underline; }
        .toc-grid li .num { color: #9ca3af; font-size: 12px; font-weight: 600; min-width: 22px; }
        .section {
            background: #fff;
            border-radius: 16px;
            padding: 36px 40px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 24px rgba(0,0,0,.06);
            scroll-margin-top: 20px;
        }
        .section-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1px solid #f1f5f9;
        }
        .section-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            background: #f0f9ff;
        }
        .section-title-group h2 { font-size: 18px; font-weight: 700; color: #0f172a; line-height: 1.3; }
        .section-title-group .section-subtitle { font-size: 12.5px; color: #94a3b8; font-weight: 500; margin-top: 2px; }
        h3 { font-size: 14.5px; font-weight: 700; color: #1e40af; margin: 22px 0 10px; display: flex; align-items: center; gap: 6px; }
        h3::before { content: ''; display: inline-block; width: 3px; height: 16px; background: #0ea5e9; border-radius: 2px; }
        p { font-size: 14.5px; color: #374151; margin-bottom: 12px; }
        p:last-child { margin-bottom: 0; }
        ul { padding-left: 0; list-style: none; margin-bottom: 14px; }
        ul li { position: relative; padding: 7px 0 7px 22px; font-size: 14.5px; color: #374151; border-bottom: 1px solid #f8fafc; }
        ul li:last-child { border-bottom: none; }
        ul li::before { content: '›'; position: absolute; left: 6px; color: #0ea5e9; font-size: 16px; font-weight: 700; line-height: 1.5; }
        ul li strong { color: #0f172a; }
        .info-block { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 16px 20px; margin: 16px 0; }
        .info-block p { font-size: 14px; color: #0c4a6e; margin: 0; }
        .warn-block { background: #fefce8; border: 1px solid #fde68a; border-radius: 10px; padding: 16px 20px; margin: 16px 0; }
        .warn-block p { font-size: 14px; color: #78350f; margin: 0; }
        .rights-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px; margin-top: 14px; }
        .rights-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; }
        .rights-item .ri-title { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .rights-item .ri-desc { font-size: 12.5px; color: #64748b; line-height: 1.5; }
        .third-party-list { display: grid; gap: 12px; margin-top: 12px; }
        .third-party-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; }
        .third-party-item .tp-name { font-size: 14px; font-weight: 600; color: #0f172a; }
        .third-party-item .tp-purpose { font-size: 12.5px; color: #64748b; margin-top: 2px; }
        .third-party-item a { font-size: 12.5px; color: #0ea5e9; text-decoration: none; font-weight: 600; white-space: nowrap; flex-shrink: 0; }
        .contact-box { background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 1.5px solid #bae6fd; border-radius: 14px; padding: 24px 28px; margin-top: 18px; }
        .contact-box .contact-title { font-size: 15px; font-weight: 700; color: #0c4a6e; margin-bottom: 12px; }
        .contact-box .contact-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #e0f2fe; font-size: 14px; color: #1e40af; }
        .contact-box .contact-row:last-child { border-bottom: none; }
        .contact-box .contact-row .label { font-weight: 600; color: #374151; min-width: 80px; }
        .contact-box .contact-row a { color: #0ea5e9; text-decoration: none; font-weight: 500; }
        .response-times { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 16px; }
        .rt-item { flex: 1; min-width: 160px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; text-align: center; }
        .rt-item .rt-law { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #94a3b8; margin-bottom: 4px; }
        .rt-item .rt-time { font-size: 28px; font-weight: 800; color: #0f172a; }
        .rt-item .rt-unit { font-size: 11px; color: #6b7280; line-height: 1.4; }
        .site-footer { background: #0f172a; color: #94a3b8; text-align: center; padding: 28px 24px; font-size: 13px; }
        .site-footer a { color: #38bdf8; text-decoration: none; }
        .footer-divider { margin: 0 8px; opacity: .4; }
        @media (max-width: 640px) {
            .section { padding: 24px 20px; }
            .toc-card, .compliance-banner { padding: 22px 20px; }
            .rights-grid { grid-template-columns: 1fr; }
            .third-party-item { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="logo-row">
        <div class="logo-icon">&#9992;</div>
        <span class="brand-name">CrewSwap</span>
    </div>
    <h1>Privacy Policy</h1>
    <div class="meta-pills">
        <span class="meta-pill">&#128197; Effective Date: April 07, 2026</span>
        <span class="meta-pill">&#128196; Version 1.0</span>
        <span class="meta-pill">&#127760; Global Coverage</span>
    </div>
</header>

<div class="page-wrap">

    <div class="compliance-banner">
        <h2>Legal Compliance Coverage</h2>
        <p style="font-size:13.5px;color:#64748b;margin-bottom:16px;">This Privacy Policy is designed to comply with applicable data protection laws depending on where our users are located:</p>
        <div class="badge-grid">
            <span class="badge badge-gdpr">GDPR &mdash; EU / EEA</span>
            <span class="badge badge-ccpa">CCPA / CPRA &mdash; California</span>
            <span class="badge badge-coppa">COPPA &mdash; Children under 13</span>
            <span class="badge badge-pdpa">PDPA &mdash; Thailand</span>
            <span class="badge badge-apple">Apple App Store</span>
            <span class="badge badge-google">Google Play Store</span>
        </div>
    </div>

    <div class="toc-card">
        <h2>Table of Contents</h2>
        <ol class="toc-grid">
            <li><span class="num">01.</span><a href="#who-we-are">Who We Are</a></li>
            <li><span class="num">02.</span><a href="#information-we-collect">Information We Collect</a></li>
            <li><span class="num">03.</span><a href="#how-we-use">How We Use Your Information</a></li>
            <li><span class="num">04.</span><a href="#sharing">Sharing of Information</a></li>
            <li><span class="num">05.</span><a href="#data-retention">Data Retention</a></li>
            <li><span class="num">06.</span><a href="#security">Security</a></li>
            <li><span class="num">07.</span><a href="#your-rights">Your Rights</a></li>
            <li><span class="num">08.</span><a href="#children">Children&apos;s Privacy</a></li>
            <li><span class="num">09.</span><a href="#international">International Data Transfers</a></li>
            <li><span class="num">10.</span><a href="#third-party">Third-Party Services</a></li>
            <li><span class="num">11.</span><a href="#push-notifications">Push Notifications</a></li>
            <li><span class="num">12.</span><a href="#changes">Changes to This Policy</a></li>
            <li><span class="num">13.</span><a href="#contact">Contact Us</a></li>
        </ol>
    </div>

    <div class="section" id="who-we-are">
        <div class="section-header">
            <div class="section-icon">&#127970;</div>
            <div class="section-title-group">
                <h2>1. Who We Are</h2>
                <div class="section-subtitle">Data Controller Information</div>
            </div>
        </div>
        <p><strong>CrewSwap</strong> (&ldquo;we&rdquo;, &ldquo;us&rdquo;, or &ldquo;our&rdquo;) is a flight crew scheduling and swap management application (&ldquo;App&rdquo;). We operate the App and act as the <strong>data controller</strong> responsible for the processing of personal data collected through it.</p>
        <p>By downloading, registering, or using CrewSwap, you acknowledge that you have read and understood this Privacy Policy and agree to the collection and use of your information as described herein.</p>
        <div class="info-block"><p><strong>Data Controller:</strong> CrewSwap &nbsp;|&nbsp; <strong>Contact:</strong> privacy@crewswap.app &nbsp;|&nbsp; <strong>App Category:</strong> Productivity / Aviation</p></div>
    </div>

    <div class="section" id="information-we-collect">
        <div class="section-header">
            <div class="section-icon">&#128202;</div>
            <div class="section-title-group">
                <h2>2. Information We Collect</h2>
                <div class="section-subtitle">What data we collect and why</div>
            </div>
        </div>
        <h3>2.1 Information You Provide Directly</h3>
        <ul>
            <li><strong>Account registration:</strong> Full name, email address, phone number, employee ID, airline, crew position, and password.</li>
            <li><strong>Profile data:</strong> Profile photo, base airport, certifications, and language preference.</li>
            <li><strong>Trip &amp; swap data:</strong> Flight numbers, dates, routes, and swap requests you create or respond to.</li>
            <li><strong>Messages:</strong> In-app chat messages exchanged with other crew members.</li>
            <li><strong>Support communications:</strong> Messages and attachments you send to our support team.</li>
        </ul>
        <h3>2.2 Information Collected Automatically</h3>
        <ul>
            <li><strong>Device information:</strong> Device model, operating system version, app version, and unique device identifiers.</li>
            <li><strong>Log data:</strong> IP address, access timestamps, pages/screens viewed, API request logs, and crash diagnostics.</li>
            <li><strong>Push notification tokens:</strong> Firebase Cloud Messaging (FCM) tokens used solely to deliver in-app notifications to your device.</li>
        </ul>
        <h3>2.3 Information We Do NOT Collect</h3>
        <div class="warn-block"><p>We do <strong>not</strong> collect precise GPS or real-time location data. We do <strong>not</strong> collect payment card or financial information. We do <strong>not</strong> sell, rent, or trade your personal data to any third party.</p></div>
    </div>

    <div class="section" id="how-we-use">
        <div class="section-header">
            <div class="section-icon">&#9881;</div>
            <div class="section-title-group">
                <h2>3. How We Use Your Information</h2>
                <div class="section-subtitle">Purposes of processing</div>
            </div>
        </div>
        <ul>
            <li>Provide, operate, and maintain the App and all its features.</li>
            <li>Authenticate your identity and keep your account secure.</li>
            <li>Match crew members for flight swaps and vacation swaps.</li>
            <li>Send push notifications about swap requests, approvals, and new messages.</li>
            <li>Respond to support inquiries and resolve technical issues.</li>
            <li>Monitor, diagnose, and improve App performance and security.</li>
            <li>Comply with legal obligations, including fraud prevention and law-enforcement requests.</li>
            <li>Generate anonymized, aggregated analytics to understand usage patterns and improve the service.</li>
        </ul>
        <h3>Legal Bases for Processing (GDPR &mdash; Article 6)</h3>
        <ul>
            <li><strong>Contract performance (Art. 6(1)(b)):</strong> Processing necessary to provide the service you signed up for.</li>
            <li><strong>Legitimate interests (Art. 6(1)(f)):</strong> Security monitoring, fraud prevention, and service improvement.</li>
            <li><strong>Legal obligation (Art. 6(1)(c)):</strong> Where processing is required by applicable law.</li>
            <li><strong>Consent (Art. 6(1)(a)):</strong> For push notifications &mdash; you may withdraw at any time via device settings.</li>
        </ul>
    </div>

    <div class="section" id="sharing">
        <div class="section-header">
            <div class="section-icon">&#129309;</div>
            <div class="section-title-group">
                <h2>4. Sharing of Information</h2>
                <div class="section-subtitle">Who we share your data with</div>
            </div>
        </div>
        <p>We do <strong>not</strong> sell, rent, or trade your personal information. We may share data only in the following limited circumstances:</p>
        <ul>
            <li><strong>Other crew members:</strong> Your name, position, airline, and trip details are visible to other registered users when you create or respond to a swap request &mdash; this is core to the App&apos;s function.</li>
            <li><strong>Airline administrators:</strong> Authorized administrators from your airline may view swap records relevant to their employees for operational purposes.</li>
            <li><strong>Service providers:</strong> Firebase (Google) for push notifications and crash analytics; cloud hosting providers. These parties process data strictly on our behalf under written data-processing agreements.</li>
            <li><strong>Legal authorities:</strong> If required by applicable law, valid court order, or to protect the rights, property, or safety of CrewSwap, our users, or the public.</li>
            <li><strong>Business transfers:</strong> In the event of a merger, acquisition, or asset sale, your data may be transferred &mdash; we will notify you before it becomes subject to a different privacy policy.</li>
        </ul>
    </div>

    <div class="section" id="data-retention">
        <div class="section-header">
            <div class="section-icon">&#128197;</div>
            <div class="section-title-group">
                <h2>5. Data Retention</h2>
                <div class="section-subtitle">How long we keep your data</div>
            </div>
        </div>
        <ul>
            <li><strong>Account data:</strong> Retained for as long as your account remains active. You may request deletion at any time &mdash; we will act within 30 days.</li>
            <li><strong>Swap &amp; trip records:</strong> Retained for <strong>24 months</strong> after the swap date for audit and dispute-resolution purposes, then permanently deleted or anonymized.</li>
            <li><strong>Chat messages:</strong> Retained for <strong>12 months</strong>, then automatically deleted.</li>
            <li><strong>Log &amp; crash data:</strong> Retained for <strong>90 days</strong>, then purged.</li>
            <li><strong>Deleted accounts:</strong> Personal data is removed within 30 days; anonymized aggregate data may be retained indefinitely.</li>
        </ul>
    </div>

    <div class="section" id="security">
        <div class="section-header">
            <div class="section-icon">&#128274;</div>
            <div class="section-title-group">
                <h2>6. Security</h2>
                <div class="section-subtitle">How we protect your data</div>
            </div>
        </div>
        <ul>
            <li><strong>Encryption in transit:</strong> All data is transmitted over TLS 1.2+ encrypted connections.</li>
            <li><strong>Password security:</strong> Passwords are hashed using bcrypt &mdash; we never store plaintext passwords.</li>
            <li><strong>Authentication:</strong> API access is secured via Laravel Sanctum token-based authentication.</li>
            <li><strong>Access controls:</strong> Strict role-based access controls limit data access to authorised personnel only.</li>
            <li><strong>Regular audits:</strong> We conduct periodic security reviews and vulnerability assessments.</li>
        </ul>
        <div class="warn-block"><p>No method of electronic transmission or storage is 100% secure. We encourage you to use a strong, unique password and notify us immediately if you suspect any unauthorized access to your account.</p></div>
        <div class="info-block"><p><strong>Data Breach Notification:</strong> In the event of a breach likely to risk your rights, we will notify relevant supervisory authorities within <strong>72 hours</strong> and affected users without undue delay, as required by GDPR Article 33&ndash;34.</p></div>
    </div>

    <div class="section" id="your-rights">
        <div class="section-header">
            <div class="section-icon">&#9878;</div>
            <div class="section-title-group">
                <h2>7. Your Rights</h2>
                <div class="section-subtitle">Control over your personal data</div>
            </div>
        </div>
        <h3>All Users &mdash; Universal Rights</h3>
        <div class="rights-grid">
            <div class="rights-item"><div class="ri-title">Access</div><div class="ri-desc">Request a copy of the personal data we hold about you.</div></div>
            <div class="rights-item"><div class="ri-title">Correction</div><div class="ri-desc">Correct inaccurate or incomplete personal data.</div></div>
            <div class="rights-item"><div class="ri-title">Deletion</div><div class="ri-desc">Delete your account and associated personal data.</div></div>
            <div class="rights-item"><div class="ri-title">Withdraw Consent</div><div class="ri-desc">Opt out of push notifications via your device settings at any time.</div></div>
        </div>
        <h3>EU / EEA Users &mdash; GDPR Rights</h3>
        <ul>
            <li><strong>Right to erasure (&ldquo;right to be forgotten&rdquo;):</strong> Request permanent deletion of your personal data.</li>
            <li><strong>Right to data portability:</strong> Receive your data in a structured, machine-readable format (e.g., JSON).</li>
            <li><strong>Right to object:</strong> Object to processing based on legitimate interests at any time.</li>
            <li><strong>Right to restrict:</strong> Request restriction of processing in certain circumstances.</li>
            <li><strong>Right to complain:</strong> Lodge a complaint with your local data protection supervisory authority.</li>
        </ul>
        <h3>California Residents &mdash; CCPA / CPRA Rights</h3>
        <ul>
            <li><strong>Right to know:</strong> Know what personal information is collected, used, disclosed, or sold about you.</li>
            <li><strong>Right to delete:</strong> Request deletion of your personal information, subject to certain exceptions.</li>
            <li><strong>Right to opt out:</strong> Opt out of the sale or sharing of personal information. <em>We do not sell personal information.</em></li>
            <li><strong>Right to non-discrimination:</strong> You will not receive inferior service for exercising your privacy rights.</li>
            <li><strong>Right to correct:</strong> Correct inaccurate personal information we maintain about you.</li>
        </ul>
        <div class="info-block"><p>To exercise any of these rights, please contact us at <strong>privacy@crewswap.app</strong>. We will verify your identity before processing requests and respond within the legally required timeframes.</p></div>
    </div>

    <div class="section" id="children">
        <div class="section-header">
            <div class="section-icon">&#128706;</div>
            <div class="section-title-group">
                <h2>8. Children&apos;s Privacy</h2>
                <div class="section-subtitle">COPPA Compliance</div>
            </div>
        </div>
        <div class="warn-block"><p><strong>CrewSwap is intended solely for airline crew professionals aged 18 years or older.</strong> This App is not directed to children and is not rated for use by minors.</p></div>
        <p>We do not knowingly collect, solicit, or maintain personal information from children under the age of <strong>13</strong> (or <strong>16</strong> in EU member states, per GDPR Article 8). If we become aware that a child under the applicable age has provided us with personal information, we will delete such data immediately.</p>
        <p>If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately at <a href="mailto:privacy@crewswap.app" style="color:#0ea5e9;">privacy@crewswap.app</a>.</p>
    </div>

    <div class="section" id="international">
        <div class="section-header">
            <div class="section-icon">&#127758;</div>
            <div class="section-title-group">
                <h2>9. International Data Transfers</h2>
                <div class="section-subtitle">Cross-border data processing safeguards</div>
            </div>
        </div>
        <p>Your information may be transferred to and processed in countries other than your country of residence, including countries that may not provide the same level of data protection as your home jurisdiction.</p>
        <p>When we transfer personal data from the EEA or the UK to third countries, we rely on appropriate safeguards including:</p>
        <ul>
            <li><strong>Standard Contractual Clauses (SCCs)</strong> approved by the European Commission (Decision 2021/914).</li>
            <li><strong>Adequacy decisions</strong> issued by the European Commission where applicable.</li>
            <li><strong>Data Processing Agreements (DPAs)</strong> with all sub-processors under Article 28 GDPR.</li>
        </ul>
    </div>

    <div class="section" id="third-party">
        <div class="section-header">
            <div class="section-icon">&#128279;</div>
            <div class="section-title-group">
                <h2>10. Third-Party Services</h2>
                <div class="section-subtitle">Sub-processors and external integrations</div>
            </div>
        </div>
        <p>The App integrates the following third-party services. Each is governed by its own privacy policy, and we have Data Processing Agreements with each where required:</p>
        <div class="third-party-list">
            <div class="third-party-item">
                <div>
                    <div class="tp-name">Google Firebase</div>
                    <div class="tp-purpose">Push notifications (FCM) &amp; crash/performance analytics</div>
                </div>
                <a href="https://firebase.google.com/support/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy &rarr;</a>
            </div>
            <div class="third-party-item">
                <div>
                    <div class="tp-name">Africa&apos;s Talking</div>
                    <div class="tp-purpose">SMS delivery for OTP verification</div>
                </div>
                <a href="https://africastalking.com/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy &rarr;</a>
            </div>
        </div>
        <p style="margin-top:14px;font-size:13.5px;color:#64748b;">We are not responsible for the privacy practices of these third parties. Data shared with these services is limited to the minimum necessary for the described function.</p>
    </div>

    <div class="section" id="push-notifications">
        <div class="section-header">
            <div class="section-icon">&#128276;</div>
            <div class="section-title-group">
                <h2>11. Push Notifications</h2>
                <div class="section-subtitle">Notification preferences</div>
            </div>
        </div>
        <p>We use push notifications to inform you about important events including new swap requests, approvals, rejections, and incoming in-app messages. Push notifications require your explicit consent on iOS and are enabled by default on Android (you may disable them at any time).</p>
        <ul>
            <li><strong>Managing notifications:</strong> Go to your device&apos;s <em>Settings &rarr; Notifications &rarr; CrewSwap</em> to enable or disable.</li>
            <li><strong>What we send:</strong> Swap request updates, approval/rejection alerts, new in-app messages, and important account notifications.</li>
            <li><strong>What we don&apos;t send:</strong> We do not send marketing, promotional, or third-party advertisement notifications.</li>
            <li><strong>FCM tokens:</strong> Your device push token is stored securely. You can request deletion by contacting support.</li>
        </ul>
        <div class="info-block"><p>Disabling push notifications does <strong>not</strong> affect your ability to use the App. You will still receive in-app alerts when the App is open.</p></div>
    </div>

    <div class="section" id="changes">
        <div class="section-header">
            <div class="section-icon">&#128221;</div>
            <div class="section-title-group">
                <h2>12. Changes to This Policy</h2>
                <div class="section-subtitle">How we notify you of updates</div>
            </div>
        </div>
        <p>We may update this Privacy Policy from time to time. When we make changes, we will:</p>
        <ul>
            <li>Update the <strong>&ldquo;Effective Date&rdquo;</strong> at the top of this page.</li>
            <li>For <strong>material changes</strong>, notify you via a push notification and/or a prominent in-app banner at least <strong>7 days</strong> before the changes take effect.</li>
            <li>For <strong>minor changes</strong>, the updated policy will be available on this page only.</li>
        </ul>
        <p>Your continued use of the App after the effective date constitutes your acceptance of the changes. If you do not agree, you should stop using the App and may request account deletion.</p>
    </div>

    <div class="section" id="contact">
        <div class="section-header">
            <div class="section-icon">&#128236;</div>
            <div class="section-title-group">
                <h2>13. Contact Us</h2>
                <div class="section-subtitle">Privacy inquiries &amp; data subject requests</div>
            </div>
        </div>
        <p>If you have any questions, concerns, or requests regarding this Privacy Policy or the way we handle your personal data, please contact our Privacy Team:</p>
        <div class="contact-box">
            <div class="contact-title">&#9992; CrewSwap &mdash; Privacy Team</div>
            <div class="contact-row">
                <span class="label">Email</span>
                <a href="mailto:privacy@crewswap.app">privacy@crewswap.app</a>
            </div>
            <div class="contact-row">
                <span class="label">Website</span>
                <a href="{{ url('/') }}" target="_blank" rel="noopener">{{ url('/') }}</a>
            </div>
            <div class="contact-row">
                <span class="label">Policy URL</span>
                <a href="{{ url('/privacy') }}" target="_blank" rel="noopener">{{ url('/privacy') }}</a>
            </div>
        </div>
        <div class="response-times">
            <div class="rt-item">
                <div class="rt-law">GDPR</div>
                <div class="rt-time">30</div>
                <div class="rt-unit">days response time<br>(extendable to 90)</div>
            </div>
            <div class="rt-item">
                <div class="rt-law">CCPA / CPRA</div>
                <div class="rt-time">45</div>
                <div class="rt-unit">days response time<br>(extendable once)</div>
            </div>
            <div class="rt-item">
                <div class="rt-law">Breach Notice</div>
                <div class="rt-time">72</div>
                <div class="rt-unit">hours to notify<br>authorities (GDPR Art.33)</div>
            </div>
        </div>
    </div>

</div>

<footer class="site-footer">
    <p>
        &copy; {{ date('Y') }} CrewSwap. All rights reserved.
        <span class="footer-divider">|</span>
        <a href="{{ url('/privacy') }}">Privacy Policy</a>
        <span class="footer-divider">|</span>
        Effective: April 07, 2026
        <span class="footer-divider">|</span>
        Version 1.0
    </p>
    <p style="margin-top:8px;font-size:12px;opacity:.6;">This page is publicly accessible and may be submitted to Google Play Console and Apple App Store Connect as the App privacy policy URL.</p>
</footer>

</body>
</html>
