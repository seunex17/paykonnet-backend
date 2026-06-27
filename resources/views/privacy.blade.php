<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy – Paykonet</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f9fafb;
            color: #374151;
            line-height: 1.75;
        }

        /* ── Top nav bar ── */
        .site-nav {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 32px;
            display: flex;
            align-items: center;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .site-nav .logo {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
            text-decoration: none;
            letter-spacing: -0.02em;
        }

        .site-nav .logo span {
            color: #2563eb;
        }

        /* ── Page layout ── */
        .page-wrap {
            display: grid;
            grid-template-columns: 240px 1fr;
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 24px 80px;
            gap: 48px;
            align-items: start;
        }

        @media (max-width: 768px) {
            .page-wrap { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }

        /* ── Sidebar TOC ── */
        .sidebar {
            position: sticky;
            top: 80px;
        }

        .sidebar-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9ca3af;
            margin-bottom: 12px;
        }

        .sidebar nav ol {
            list-style: none;
            padding: 0;
            counter-reset: toc;
        }

        .sidebar nav ol li {
            counter-increment: toc;
            margin-bottom: 2px;
        }

        .sidebar nav ol li a {
            display: flex;
            align-items: baseline;
            gap: 8px;
            font-size: 0.8125rem;
            color: #6b7280;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 6px;
            transition: background 0.15s, color 0.15s;
        }

        .sidebar nav ol li a::before {
            content: counter(toc, decimal-leading-zero);
            font-size: 0.7rem;
            font-weight: 700;
            color: #d1d5db;
            flex-shrink: 0;
        }

        .sidebar nav ol li a:hover {
            background: #eff6ff;
            color: #2563eb;
        }

        /* ── Main content ── */
        .main-content {
            min-width: 0;
        }

        /* Header */
        .page-header {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 32px 36px;
            margin-bottom: 32px;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.025em;
            margin-bottom: 8px;
        }

        .page-header .meta {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .page-header .meta span {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 99px;
            margin-left: 10px;
        }

        .page-header .intro {
            margin-top: 16px;
            font-size: 0.9375rem;
            color: #4b5563;
        }

        .page-header .intro a {
            color: #2563eb;
            text-decoration: none;
        }

        .page-header .intro a:hover { text-decoration: underline; }

        /* Summary cards */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            margin-bottom: 32px;
        }

        @media (max-width: 900px) {
            .summary-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 560px) {
            .summary-grid { grid-template-columns: 1fr; }
        }

        .summary-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 18px 20px;
        }

        .summary-card .card-q {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            margin-bottom: 6px;
        }

        .summary-card p {
            font-size: 0.875rem;
            color: #374151;
            line-height: 1.6;
        }

        .summary-card a {
            color: #2563eb;
            font-size: 0.8125rem;
            text-decoration: none;
            font-weight: 500;
        }

        .summary-card a:hover { text-decoration: underline; }

        /* Sections */
        .privacy-section {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 32px 36px;
            margin-bottom: 20px;
            scroll-margin-top: 80px;
        }

        .section-eyebrow {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #2563eb;
            margin-bottom: 8px;
        }

        .privacy-section h2 {
            font-size: 1.0625rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f3f4f6;
        }

        .privacy-section h3 {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #1f2937;
            margin: 24px 0 8px;
        }

        .privacy-section p {
            font-size: 0.9375rem;
            color: #4b5563;
            margin-bottom: 12px;
        }

        .privacy-section ul {
            padding-left: 20px;
            margin-bottom: 12px;
        }

        .privacy-section ul li {
            font-size: 0.9375rem;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .privacy-section a {
            color: #2563eb;
            text-decoration: none;
        }

        .privacy-section a:hover { text-decoration: underline; }

        /* Short summary callout */
        .callout {
            background: #eff6ff;
            border-left: 3px solid #2563eb;
            border-radius: 0 6px 6px 0;
            padding: 10px 16px;
            font-size: 0.875rem;
            color: #1d4ed8;
            margin-bottom: 16px;
            font-style: italic;
            line-height: 1.6;
        }

        /* Contact block */
        .contact-block {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px 24px;
            margin-top: 14px;
        }

        .contact-block p {
            margin-bottom: 4px !important;
        }

        /* Footer */
        .page-footer {
            text-align: center;
            font-size: 0.8125rem;
            color: #9ca3af;
            padding: 32px 0 0;
            margin-top: 12px;
        }
    </style>
</head>
<body>

    <!-- Nav -->
    <header class="site-nav">
        <a href="/" class="logo">Pay<span>konet</span></a>
    </header>

    <div class="page-wrap">

        <!-- Sidebar -->
        <aside class="sidebar">
            <p class="sidebar-label">Contents</p>
            <nav>
                <ol>
                    <li><a href="#infocollect">What information do we collect?</a></li>
                    <li><a href="#infouse">How do we process your information?</a></li>
                    <li><a href="#whoshare">When and with whom do we share it?</a></li>
                    <li><a href="#inforetain">How long do we keep it?</a></li>
                    <li><a href="#infosafe">How do we keep it safe?</a></li>
                    <li><a href="#infominors">Do we collect data from minors?</a></li>
                    <li><a href="#privacyrights">What are your privacy rights?</a></li>
                    <li><a href="#dnt">Do-Not-Track controls</a></li>
                    <li><a href="#otherlaws">Regional privacy rights</a></li>
                    <li><a href="#policyupdates">Do we update this notice?</a></li>
                    <li><a href="#contact">How can you contact us?</a></li>
                    <li><a href="#request">Review, update, or delete your data</a></li>
                </ol>
            </nav>
        </aside>

        <!-- Main -->
        <main class="main-content">

            <!-- Page header -->
            <div class="page-header">
                <h1>Privacy Policy</h1>
                <p class="meta">Last updated: January 28, 2024 <span>Version 1.0</span></p>
                <p class="intro">
                    This privacy notice for <strong>Paykonet</strong> ("we," "us," or "our") describes how and why we
                    collect, store, use, and share your information when you use our services — such as when you download
                    and use the <strong>Paykonet</strong> mobile app, or engage with us through sales, marketing, or events.
                    <br><br>
                    <strong>Questions or concerns?</strong> If you do not agree with our policies and practices, please do
                    not use our Services. You can reach us at
                    <a href="mailto:support@paykonet.com">support@paykonet.com</a>.
                </p>
            </div>

            <!-- Summary cards -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="card-q">Personal information</div>
                    <p>Names, emails, phone numbers, usernames, and passwords you provide directly. <a href="#infocollect">Learn more &rarr;</a></p>
                </div>
                <div class="summary-card">
                    <div class="card-q">Sensitive information</div>
                    <p>Financial and biometric data, processed with your consent or as permitted by law. <a href="#sensitiveinfo">Learn more &rarr;</a></p>
                </div>
                <div class="summary-card">
                    <div class="card-q">Third-party data</div>
                    <p>We do not receive personal information from third parties.</p>
                </div>
                <div class="summary-card">
                    <div class="card-q">Data sharing</div>
                    <p>Shared only in specific situations with specific parties. <a href="#whoshare">Learn more &rarr;</a></p>
                </div>
                <div class="summary-card">
                    <div class="card-q">Data security</div>
                    <p>Organizational and technical measures protect your data, though no method is 100% secure. <a href="#infosafe">Learn more &rarr;</a></p>
                </div>
                <div class="summary-card">
                    <div class="card-q">Your rights</div>
                    <p>You may review, change, or terminate your account at any time. <a href="#privacyrights">Learn more &rarr;</a></p>
                </div>
            </div>

            <!-- Section 1 -->
            <div class="privacy-section" id="infocollect">
                <p class="section-eyebrow">Section 01</p>
                <h2>What Information Do We Collect?</h2>

                <h3>Personal information you disclose to us</h3>
                <div class="callout">In short: We collect personal information that you provide to us.</div>
                <p>We collect personal information you voluntarily provide when you register on the Services, express interest in obtaining information about us or our products, participate in activities on the Services, or otherwise contact us. The personal information we collect may include:</p>
                <ul>
                    <li>Names</li>
                    <li>Phone numbers</li>
                    <li>Email addresses</li>
                    <li>Usernames</li>
                    <li>Passwords</li>
                </ul>

                <h3 id="sensitiveinfo">Sensitive information</h3>
                <p>When necessary, with your consent or as otherwise permitted by applicable law, we process the following categories of sensitive information:</p>
                <ul>
                    <li>Financial data</li>
                    <li>Biometric data</li>
                </ul>

                <h3>Payment data</h3>
                <p>
                    We may collect data necessary to process your payment, such as your payment instrument number and the
                    associated security code. All payment data is stored by <strong>Paystack</strong> and
                    <strong>Interswitch</strong>. You may find their privacy policies here:
                    <a href="https://paystack.com/terms" target="_blank" rel="noopener">https://paystack.com/terms</a>
                    and
                    <a href="https://www.interswitchgroup.com/privacy" target="_blank" rel="noopener">https://www.interswitchgroup.com/privacy</a>.
                </p>

                <h3>Application data</h3>
                <p>If you use our application(s), we may also collect the following if you choose to provide access or permission:</p>
                <ul>
                    <li><strong>Geolocation information.</strong> We may request permission to track location-based information from your mobile device to provide certain location-based services. You can change this in your device's settings.</li>
                    <li><strong>Mobile device access.</strong> We may request access to your device's camera, contacts, sensors, storage, and other features. You can change permissions in your device's settings.</li>
                    <li><strong>Mobile device data.</strong> We automatically collect device information such as your device ID, model, manufacturer, operating system, browser type, hardware model, ISP/mobile carrier, and IP address.</li>
                    <li><strong>Push notifications.</strong> We may request to send you push notifications regarding your account or certain features of the app. You can opt out in your device's settings.</li>
                </ul>

                <h3>Information automatically collected</h3>
                <div class="callout">In short: Some information — such as your IP address and device characteristics — is collected automatically when you visit our Services.</div>
                <p>We automatically collect certain information when you visit, use, or navigate the Services. This information does not reveal your specific identity but may include device and usage information such as your IP address, browser type, operating system, language preferences, and referring URLs. The information we collect includes:</p>
                <ul>
                    <li><strong>Log and usage data.</strong> Service-related diagnostic and performance information our servers automatically collect — including your IP address, device info, browser type, pages and files viewed, searches, and system activity (such as crash reports and hardware settings).</li>
                    <li><strong>Device data.</strong> Information about your computer, phone, or tablet such as your IP address, device and application identification numbers, location, browser type, hardware model, ISP/carrier, and system configuration.</li>
                    <li><strong>Location data.</strong> Geolocation data based on your IP address or GPS. You can opt out by disabling the Location setting on your device, though some aspects of the Services may then be unavailable.</li>
                </ul>
                <p>All personal information you provide must be true, complete, and accurate. Please notify us of any changes to such personal information.</p>
            </div>

            <!-- Section 2 -->
            <div class="privacy-section" id="infouse">
                <p class="section-eyebrow">Section 02</p>
                <h2>How Do We Process Your Information?</h2>
                <div class="callout">In short: We process your information to provide, improve, and administer our Services, communicate with you, for security and fraud prevention, and to comply with law.</div>
                <p>We process your personal information for a variety of reasons depending on how you interact with our Services, including:</p>
                <ul>
                    <li><strong>To facilitate account creation and authentication.</strong> So you can create and log in to your account and keep it in working order.</li>
                    <li><strong>To deliver and facilitate delivery of services to the user.</strong> To provide you with the requested service.</li>
                    <li><strong>To fulfill and manage your orders.</strong> To process payments, returns, and exchanges made through the Services.</li>
                    <li><strong>To enable user-to-user communications.</strong> If you use any of our offerings that allow communication with another user.</li>
                    <li><strong>To deliver targeted advertising to you.</strong> To develop and display personalized content and advertising tailored to your interests and location.</li>
                    <li><strong>To protect our Services.</strong> As part of our efforts to keep our Services safe and secure, including fraud monitoring and prevention.</li>
                    <li><strong>To identify usage trends.</strong> To better understand how our Services are being used so we can improve them.</li>
                    <li><strong>To comply with our legal obligations.</strong> To respond to legal requests and exercise, establish, or defend our legal rights.</li>
                </ul>
            </div>

            <!-- Section 3 -->
            <div class="privacy-section" id="whoshare">
                <p class="section-eyebrow">Section 03</p>
                <h2>When and With Whom Do We Share Your Personal Information?</h2>
                <div class="callout">In short: We may share information in specific situations with specific third parties.</div>
                <p>We may need to share your personal information in the following situations:</p>
                <ul>
                    <li><strong>Business transfers.</strong> We may share or transfer your information in connection with, or during negotiations of, any merger, sale of company assets, financing, or acquisition of all or a portion of our business to another company.</li>
                </ul>
            </div>

            <!-- Section 4 -->
            <div class="privacy-section" id="inforetain">
                <p class="section-eyebrow">Section 04</p>
                <h2>How Long Do We Keep Your Information?</h2>
                <div class="callout">In short: We keep your information for as long as necessary to fulfill the purposes outlined in this notice unless otherwise required by law.</div>
                <p>We will only keep your personal information for as long as it is necessary for the purposes set out in this privacy notice, unless a longer retention period is required or permitted by law (such as tax, accounting, or other legal requirements). No purpose in this notice will require us to keep your personal information for longer than the period of time in which users have an account with us.</p>
                <p>When we have no ongoing legitimate business need to process your personal information, we will either delete or anonymize it, or — if this is not possible (for example, because your information has been stored in backup archives) — we will securely store your personal information and isolate it from any further processing until deletion is possible.</p>
            </div>

            <!-- Section 5 -->
            <div class="privacy-section" id="infosafe">
                <p class="section-eyebrow">Section 05</p>
                <h2>How Do We Keep Your Information Safe?</h2>
                <div class="callout">In short: We aim to protect your personal information through organizational and technical security measures.</div>
                <p>We have implemented appropriate and reasonable technical and organizational security measures designed to protect the security of any personal information we process. However, despite our safeguards and efforts to secure your information, no electronic transmission over the Internet or information storage technology can be guaranteed to be 100% secure. We cannot promise or guarantee that hackers, cybercriminals, or other unauthorized third parties will not be able to defeat our security and improperly collect, access, steal, or modify your information.</p>
                <p>Although we will do our best to protect your personal information, transmission of personal information to and from our Services is at your own risk. You should only access the Services within a secure environment.</p>
            </div>

            <!-- Section 6 -->
            <div class="privacy-section" id="infominors">
                <p class="section-eyebrow">Section 06</p>
                <h2>Do We Collect Information From Minors?</h2>
                <div class="callout">In short: We do not knowingly collect data from or market to children under 18 years of age.</div>
                <p>We do not knowingly solicit data from or market to children under 18 years of age. By using the Services, you represent that you are at least 18 years old, or that you are the parent or guardian of a minor and consent to that minor's use of the Services. If we learn that personal information from users less than 18 years of age has been collected, we will deactivate the account and take reasonable measures to promptly delete such data from our records.</p>
                <p>If you become aware of any data we may have collected from children under age 18, please contact us at <a href="mailto:support@paykonet.com">support@paykonet.com</a>.</p>
            </div>

            <!-- Section 7 -->
            <div class="privacy-section" id="privacyrights">
                <p class="section-eyebrow">Section 07</p>
                <h2>What Are Your Privacy Rights?</h2>
                <div class="callout">In short: You may review, change, or terminate your account at any time.</div>

                <h3>Withdrawing your consent</h3>
                <p>If we are relying on your consent to process your personal information — which may be express and/or implied consent depending on the applicable law — you have the right to withdraw your consent at any time. You can do so by contacting us using the details in the <a href="#contact">How Can You Contact Us?</a> section below.</p>
                <p>Please note that this will not affect the lawfulness of the processing before its withdrawal, nor will it affect the processing of your personal information conducted in reliance on lawful processing grounds other than consent.</p>

                <h3>Account information</h3>
                <p>If you would at any time like to review or change the information in your account or terminate your account, you can:</p>
                <ul>
                    <li>Log in to your account settings and update your user account.</li>
                </ul>
                <p>Upon your request to terminate your account, we will deactivate or delete your account and information from our active databases. However, we may retain some information in our files to prevent fraud, troubleshoot problems, assist with any investigations, enforce our legal terms, and/or comply with applicable legal requirements.</p>
                <p>If you have questions or comments about your privacy rights, you may email us at <a href="mailto:support@paykonet.com">support@paykonet.com</a>.</p>
            </div>

            <!-- Section 8 -->
            <div class="privacy-section" id="dnt">
                <p class="section-eyebrow">Section 08</p>
                <h2>Controls for Do-Not-Track Features</h2>
                <p>Most web browsers and some mobile operating systems and mobile applications include a Do-Not-Track ("DNT") feature or setting you can activate to signal your privacy preference not to have data about your online browsing activities monitored and collected. At this stage, no uniform technology standard for recognizing and implementing DNT signals has been finalized. As such, we do not currently respond to DNT browser signals or any other mechanism that automatically communicates your choice not to be tracked online. If a standard for online tracking is adopted that we must follow in the future, we will inform you about that practice in a revised version of this privacy notice.</p>
            </div>

            <!-- Section 9 -->
            <div class="privacy-section" id="otherlaws">
                <p class="section-eyebrow">Section 09</p>
                <h2>Do Other Regions Have Specific Privacy Rights?</h2>
                <div class="callout">In short: You may have additional rights based on the country you reside in.</div>

                <h3>Republic of South Africa</h3>
                <p>At any time, you have the right to request access to or correction of your personal information. You can make such a request by contacting us using the contact details provided in the <a href="#request">How Can You Review, Update, or Delete the Data We Collect From You?</a> section below.</p>
                <p>If you are unsatisfied with the manner in which we address any complaint with regard to our processing of personal information, you can contact the office of the regulator:</p>
                <div class="contact-block">
                    <p><strong><a href="https://inforegulator.org.za/" target="_blank" rel="noopener">The Information Regulator (South Africa)</a></strong></p>
                    <p>General enquiries: <a href="mailto:enquiries@inforegulator.org.za">enquiries@inforegulator.org.za</a></p>
                    <p>Complaints (complete POPIA/PAIA form 5):
                        <a href="mailto:PAIAComplaints@inforegulator.org.za">PAIAComplaints@inforegulator.org.za</a>
                        &amp;
                        <a href="mailto:POPIAComplaints@inforegulator.org.za">POPIAComplaints@inforegulator.org.za</a>
                    </p>
                </div>
            </div>

            <!-- Section 10 -->
            <div class="privacy-section" id="policyupdates">
                <p class="section-eyebrow">Section 10</p>
                <h2>Do We Make Updates to This Notice?</h2>
                <div class="callout">In short: Yes, we will update this notice as necessary to stay compliant with relevant laws.</div>
                <p>We may update this privacy notice from time to time. The updated version will be indicated by an updated "Revised" date and will be effective as soon as it is accessible. If we make material changes to this privacy notice, we may notify you either by prominently posting a notice of such changes or by directly sending you a notification. We encourage you to review this privacy notice frequently to be informed of how we are protecting your information.</p>
            </div>

            <!-- Section 11 -->
            <div class="privacy-section" id="contact">
                <p class="section-eyebrow">Section 11</p>
                <h2>How Can You Contact Us About This Notice?</h2>
                <p>If you have questions or comments about this notice, you may email us or contact us by post at:</p>
                <div class="contact-block">
                    <p><strong>Paykonet</strong></p>
                    <p>Abuja, Abuja 900001</p>
                    <p>Nigeria</p>
                    <p>Email: <a href="mailto:support@paykonet.com">support@paykonet.com</a></p>
                </div>
            </div>

            <!-- Section 12 -->
            <div class="privacy-section" id="request">
                <p class="section-eyebrow">Section 12</p>
                <h2>How Can You Review, Update, or Delete the Data We Collect From You?</h2>
                <p>You have the right to request access to the personal information we collect from you, change that information, or delete it. To request to review, update, or delete your personal information, please contact us at: <a href="mailto:support@paykonet.com">support@paykonet.com</a>.</p>
            </div>

            <div class="page-footer">
                &copy; <?php echo date('Y'); ?> Paykonet. All rights reserved.
            </div>

        </main>
    </div>

</body>
</html>
