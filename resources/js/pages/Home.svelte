<script lang="ts">
    import { Link } from "@inertiajs/svelte";
    import { onMount } from "svelte";

    let mobileMenuOpen = false;
    let isDesktop = false;

    function toggleMenu() {
        mobileMenuOpen = !mobileMenuOpen;
    }

    function closeMenu() {
        mobileMenuOpen = false;
    }

    onMount(() => {
        isDesktop = window.innerWidth > 960;

        const handleResize = () => {
            isDesktop = window.innerWidth > 960;
            if (isDesktop) mobileMenuOpen = false;
        };
        window.addEventListener("resize", handleResize);

        // Scroll reveal
        const revealEls = document.querySelectorAll(".reveal");
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry, i) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add("visible");
                        }, i * 80);
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12 },
        );
        revealEls.forEach((el) => observer.observe(el));

        return () => {
            window.removeEventListener("resize", handleResize);
            observer.disconnect();
        };
    });
</script>

<svelte:head>
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    />
    <title>Paykonet — Move Money at the Speed of Life</title>
</svelte:head>

<!-- NAV -->
<nav>
    <div class="nav-inner">
        <Link href="/" class="flex items-center gap-2 logo">
            <img src="/logo/pk.png" alt="Paykonet Logo" class="w-12 h-12" />
            Paykonet
        </Link>
        <ul class="nav-links">
            <li><a href="#features">Features</a></li>
            <li><a href="#cards">Virtual Cards</a></li>
            <li><a href="#loans">Loans</a></li>
            <li><a href="#testimonials">Reviews</a></li>
        </ul>
        <div style="display:flex;gap:10px;align-items:center;">
            <a
                href="https://play.google.com/store/apps/details?id=com.pay2mecard.pay2me"
                target="_blank"
                class="btn-primary">Download →</a
            >
            <button
                class="hamburger"
                on:click={toggleMenu}
                aria-label="Menu"
                aria-expanded={mobileMenuOpen}
                aria-controls="mobile-navigation"
                type="button"
            >
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<!-- MOBILE MENU -->
{#if mobileMenuOpen}
    <div class="mobile-menu" id="mobile-navigation">
        <a href="#features" on:click={closeMenu}>Features</a>
        <a href="#cards" on:click={closeMenu}>Virtual Cards</a>
        <a href="#loans" on:click={closeMenu}>Loans</a>
        <a href="#testimonials" on:click={closeMenu}>Reviews</a>
        <a
            href="https://play.google.com/store/apps/details?id=com.pay2mecard.pay2me"
            target="_blank"
            class="btn-primary"
            style="margin-top:8px;justify-content:center;"
            on:click={closeMenu}>Download →</a
        >
    </div>
{/if}

<!-- HERO -->
<section class="hero">
    <div class="hero-bg">
        <div
            class="hero-blob"
            style="width:500px;height:500px;background:#7C3AED;top:-150px;right:0;"
        ></div>
        <div
            class="hero-blob"
            style="width:350px;height:350px;background:#10B981;bottom:0;left:-100px;opacity:0.1;"
        ></div>
    </div>

    <div class="hero-inner">
        <!-- LEFT -->
        <div>
            <div class="eyebrow reveal">
                <span class="eyebrow-dot"></span>
                Nigeria's #1 Digital Wallet
            </div>
            <h1 class="hero-title reveal">
                Money moves<br />
                <span class="gradient-text">at your speed.</span>
            </h1>
            <p class="hero-sub reveal">
                Send money, pay bills, buy airtime, fund bets, and spend
                globally — all from one beautifully simple app. With USD &amp;
                NGN virtual cards and instant data loans.
            </p>
            <div class="hero-cta reveal">
                <a
                    href="https://play.google.com/store/apps/details?id=com.pay2mecard.pay2me"
                    target="_blank"
                    class="btn-primary"
                    style="padding:13px 26px;font-size:0.95rem;"
                >
                    📱 Download Free
                </a>
                <a
                    href="#features"
                    class="btn-outline"
                    style="padding:13px 26px;font-size:0.95rem;"
                >
                    See Features
                </a>
            </div>
            <div class="hero-stats reveal">
                <div class="stat-item">
                    <h3>2M+</h3>
                    <p>Active users</p>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <h3>₦50B+</h3>
                    <p>Processed monthly</p>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <h3>4.9★</h3>
                    <p>App Store rating</p>
                </div>
            </div>
        </div>

        <!-- RIGHT — PHONE MOCKUP -->
        <div class="phone-wrap">
            <div class="phone-glow"></div>

            <div class="float-card float-card-1">
                <div class="fc-label">Money Sent 🚀</div>
                <div class="fc-val">₦25,000</div>
                <div class="fc-sub">↑ Delivered instantly</div>
            </div>

            <div class="float-card float-card-2">
                <div class="fc-label">USD Card Spend</div>
                <div class="fc-val">$12.99</div>
                <div style="margin-top:6px;">
                    <span class="fc-badge">✓ Approved</span>
                </div>
            </div>

            <div class="float-card float-card-3" style="padding:10px 12px;">
                <div class="fc-label" style="font-size:0.58rem;">Data Loan</div>
                <div
                    style="font-family:'Sora',sans-serif;font-size:0.85rem;font-weight:700;color:var(--color-emerald);"
                >
                    2GB Unlocked
                </div>
            </div>

            <div class="phone">
                <div class="phone-screen">
                    <div class="phone-notch"></div>
                    <div class="phone-balance-card">
                        <div class="label">Total Balance</div>
                        <div class="amount">₦ 148,520.00</div>
                        <div class="acct">Paykonet • 0812 345 6789</div>
                    </div>
                    <div class="phone-actions">
                        <div class="phone-action">
                            <div class="icon">📤</div>
                            <div class="lbl">Send</div>
                        </div>
                        <div class="phone-action">
                            <div class="icon">📞</div>
                            <div class="lbl">Airtime</div>
                        </div>
                        <div class="phone-action">
                            <div class="icon">📡</div>
                            <div class="lbl">Data</div>
                        </div>
                        <div class="phone-action">
                            <div class="icon">⚽</div>
                            <div class="lbl">Betting</div>
                        </div>
                    </div>
                    <div class="phone-txn-title">Recent Transactions</div>
                    <div class="phone-txn">
                        <div
                            class="txn-icon"
                            style="background:rgba(124,58,237,0.15);"
                        >
                            💸
                        </div>
                        <div class="txn-info">
                            <div class="txn-name">Transfer to Emeka</div>
                            <div class="txn-date">Today, 10:32 AM</div>
                        </div>
                        <div class="txn-amt" style="color:#EF4444;">
                            -₦5,000
                        </div>
                    </div>
                    <div class="phone-txn">
                        <div
                            class="txn-icon"
                            style="background:rgba(16,185,129,0.15);"
                        >
                            📞
                        </div>
                        <div class="txn-info">
                            <div class="txn-name">Airtime – MTN</div>
                            <div class="txn-date">Yesterday, 2:15 PM</div>
                        </div>
                        <div class="txn-amt" style="color:#EF4444;">
                            -₦1,000
                        </div>
                    </div>
                    <div class="phone-txn">
                        <div
                            class="txn-icon"
                            style="background:rgba(16,185,129,0.15);"
                        >
                            💰
                        </div>
                        <div class="txn-info">
                            <div class="txn-name">Wallet Funding</div>
                            <div class="txn-date">Yesterday, 9:00 AM</div>
                        </div>
                        <div class="txn-amt" style="color:#10B981;">
                            +₦50,000
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- LOGOS -->
<div class="logos-bar">
    <div class="logos-inner">
        <span
            style="font-size:0.72rem;color:var(--color-text3);font-weight:500;letter-spacing:0.05em;text-transform:uppercase;"
            >Trusted by users at</span
        >
        <span class="logo-item">MTN</span>
        <span class="logo-item">Airtel</span>
        <span class="logo-item">Glo</span>
        <span class="logo-item">9mobile</span>
        <span class="logo-item">SportyBet</span>
        <span class="logo-item">BetKing</span>
        <span class="logo-item">DSTV</span>
    </div>
</div>

<!-- FEATURES -->
<section id="features">
    <div class="section-inner">
        <div class="reveal">
            <div class="section-tag">Everything you need</div>
            <h2 class="section-title">
                One app.<br /><span class="gradient-text"
                    >Every financial need.</span
                >
            </h2>
            <p class="section-sub">
                From splitting the bill to funding your bets — Paykonet handles
                it all with speed you can feel.
            </p>
        </div>

        <div class="bento-grid reveal">
            <div class="bento-card accent">
                <div class="bento-icon">📤</div>
                <div class="bento-title">Instant Money Transfers</div>
                <div class="bento-sub">
                    Send money to any Nigerian bank account in under 3 seconds.
                    Zero fees on transfers to Paykonet users.
                </div>
                <div
                    style="margin-top:20px;display:flex;flex-wrap:wrap;align-items:center;gap:8px;"
                >
                    <div
                        style="background:rgba(255,255,255,0.2);border-radius:100px;padding:5px 14px;font-size:0.75rem;font-weight:600;"
                    >
                        ⚡ Under 3s
                    </div>
                    <div
                        style="background:rgba(255,255,255,0.2);border-radius:100px;padding:5px 14px;font-size:0.75rem;font-weight:600;"
                    >
                        All Nigerian banks
                    </div>
                </div>
                <div class="bento-card-glow"></div>
            </div>

            <div class="bento-card">
                <div class="bento-icon">📞</div>
                <div class="bento-title">Airtime &amp; Data Top-Up</div>
                <div class="bento-sub">
                    Buy airtime and data bundles for MTN, Airtel, Glo, and
                    9mobile in seconds. Competitive rates, always.
                </div>
                <div
                    style="display:flex;gap:8px;margin-top:16px;flex-wrap:wrap;"
                >
                    <span
                        style="background:rgba(124,58,237,0.1);color:var(--color-violet);padding:3px 10px;border-radius:8px;font-size:0.7rem;font-weight:600;"
                        >MTN</span
                    >
                    <span
                        style="background:rgba(124,58,237,0.1);color:var(--color-violet);padding:3px 10px;border-radius:8px;font-size:0.7rem;font-weight:600;"
                        >Airtel</span
                    >
                    <span
                        style="background:rgba(124,58,237,0.1);color:var(--color-violet);padding:3px 10px;border-radius:8px;font-size:0.7rem;font-weight:600;"
                        >Glo</span
                    >
                    <span
                        style="background:rgba(124,58,237,0.1);color:var(--color-violet);padding:3px 10px;border-radius:8px;font-size:0.7rem;font-weight:600;"
                        >9mobile</span
                    >
                </div>
            </div>

            <div class="bento-card">
                <div class="bento-icon">🏦</div>
                <div class="bento-title">Pay Any Bill</div>
                <div class="bento-sub">
                    Electricity, cable TV, internet, and more — schedule and
                    auto-pay bills so you never miss a due date.
                </div>
            </div>

            <div class="bento-card">
                <div class="bento-icon">⚽</div>
                <div class="bento-title">Betting Wallet Funding</div>
                <div class="bento-sub">
                    Fund your SportyBet, BetKing, Bet9ja, and 20+ betting
                    platforms instantly — no delays, ever.
                </div>
            </div>

            <div class="bento-card wide">
                <div class="wallet-preview">
                    <div>
                        <div class="bento-icon">💰</div>
                        <div class="bento-title">Smart Wallet Funding</div>
                        <div class="bento-sub">
                            Fund your wallet via bank transfer, USSD, or card.
                            Your dedicated virtual account number is always
                            ready to receive.
                        </div>
                    </div>
                    <div
                        style="background:var(--color-bg2);border-radius:14px;padding:18px;border:1px solid var(--color-border);"
                    >
                        <div
                            style="font-size:0.7rem;color:var(--color-text2);margin-bottom:6px;"
                        >
                            Your Account Number
                        </div>
                        <div
                            style="font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:700;color:var(--color-text);letter-spacing:0.05em;"
                        >
                            0812 345 6789
                        </div>
                        <div
                            style="font-size:0.68rem;color:var(--color-text3);margin-top:4px;"
                        >
                            Paykonet MFB • John Okafor
                        </div>
                        <div style="margin-top:14px;display:flex;gap:8px;">
                            <div
                                style="flex:1;background:var(--color-card-glow);border-radius:8px;padding:8px;text-align:center;"
                            >
                                <div
                                    style="font-size:0.6rem;color:var(--color-text2);"
                                >
                                    Balance
                                </div>
                                <div
                                    style="font-size:0.85rem;font-weight:700;color:var(--color-emerald);font-family:'Sora',sans-serif;"
                                >
                                    ₦148,520
                                </div>
                            </div>
                            <div
                                style="flex:1;background:var(--color-card-glow);border-radius:8px;padding:8px;text-align:center;"
                            >
                                <div
                                    style="font-size:0.6rem;color:var(--color-text2);"
                                >
                                    Today's In
                                </div>
                                <div
                                    style="font-size:0.85rem;font-weight:700;color:var(--color-text);font-family:'Sora',sans-serif;"
                                >
                                    ₦50,000
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VIRTUAL CARDS -->
<section class="cards-section" id="cards">
    <div class="section-inner">
        <div class="cards-grid">
            <div class="vc-stack reveal">
                <div class="vc vc-usd">
                    <div class="vc-row">
                        <div class="vc-logo">Paykonet</div>
                        <div class="vc-currency">USD $</div>
                    </div>
                    <div class="vc-chip">💳</div>
                    <div>
                        <div class="vc-number">4532 •••• •••• 7891</div>
                        <div class="vc-row" style="margin-top:6px;">
                            <div class="vc-name">JOHN OKAFOR</div>
                            <div style="font-size:0.65rem;opacity:0.7;">
                                VIRTUAL
                            </div>
                        </div>
                    </div>
                </div>
                <div class="vc vc-ngn">
                    <div class="vc-row">
                        <div class="vc-logo">Paykonet</div>
                        <div class="vc-currency">NGN ₦</div>
                    </div>
                    <div class="vc-chip">💳</div>
                    <div>
                        <div class="vc-number">5399 •••• •••• 2204</div>
                        <div class="vc-row" style="margin-top:6px;">
                            <div class="vc-name">JOHN OKAFOR</div>
                            <div style="font-size:0.65rem;opacity:0.7;">
                                VIRTUAL
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="reveal">
                <div class="section-tag">Virtual Cards</div>
                <h2 class="section-title">
                    Spend globally.<br /><span class="gradient-text"
                        >In two currencies.</span
                    >
                </h2>
                <p class="section-sub" style="margin-bottom:32px;">
                    Get instant USD and NGN virtual cards. Shop on Amazon,
                    Netflix, or any international site without conversion
                    headaches.
                </p>

                <ul class="vc-features">
                    <li class="vc-feat">
                        <div class="vc-feat-icon">🌍</div>
                        <div>
                            <div class="vc-feat-title">USD Virtual Card</div>
                            <div class="vc-feat-sub">
                                Shop on international sites — Amazon, Apple,
                                Google Play and more. Fund in seconds.
                            </div>
                        </div>
                    </li>
                    <li class="vc-feat">
                        <div class="vc-feat-icon">🇳🇬</div>
                        <div>
                            <div class="vc-feat-title">NGN Virtual Card</div>
                            <div class="vc-feat-sub">
                                Use for local online payments. Fully funded from
                                your Paykonet wallet balance.
                            </div>
                        </div>
                    </li>
                    <li class="vc-feat">
                        <div class="vc-feat-icon">🔒</div>
                        <div>
                            <div class="vc-feat-title">
                                Instant Freeze &amp; Control
                            </div>
                            <div class="vc-feat-sub">
                                Block your card with one tap. Full spending
                                control, always in your hands.
                            </div>
                        </div>
                    </li>
                </ul>

                <a
                    href="#"
                    class="btn-primary"
                    style="margin-top:32px;padding:13px 28px;font-size:0.95rem;"
                >
                    Get Your Cards Free →
                </a>
            </div>
        </div>
    </div>
</section>

<!-- LOANS -->
<section id="loans">
    <div class="section-inner">
        <div style="text-align:center;margin-bottom:0;" class="reveal">
            <div class="section-tag" style="justify-content:center;">
                Airtime &amp; Data Loans
            </div>
            <h2 class="section-title">
                Out of data?<br /><span class="gradient-text"
                    >We've got you covered.</span
                >
            </h2>
            <p class="section-sub" style="margin:0 auto 0;">
                Get instant airtime and data loans when your balance is low.
                Repay automatically when you fund your wallet.
            </p>
        </div>

        <div class="loan-grid">
            <div class="loan-visual reveal">
                <div
                    style="font-family:'Sora',sans-serif;font-size:0.75rem;font-weight:700;color:var(--color-text3);letter-spacing:0.06em;text-transform:uppercase;"
                >
                    Your Loan Status
                </div>
                <div style="margin:20px 0 0;">
                    <div
                        style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:6px;"
                    >
                        <div>
                            <div
                                style="font-size:0.72rem;color:var(--color-text2);"
                            >
                                Active loan
                            </div>
                            <div
                                style="font-family:'Sora',sans-serif;font-size:1.6rem;font-weight:800;color:var(--color-text);"
                            >
                                2GB Data
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div
                                style="font-size:0.72rem;color:var(--color-text2);"
                            >
                                Repay on
                            </div>
                            <div
                                style="font-weight:600;font-size:0.9rem;color:var(--color-text);"
                            >
                                Next top-up
                            </div>
                        </div>
                    </div>
                    <div class="loan-progress-wrap">
                        <div class="loan-progress-label">
                            <span>Loan used</span>
                            <span
                                style="color:var(--color-violet);font-weight:600;"
                                >1.3 GB / 2 GB</span
                            >
                        </div>
                        <div class="loan-bar">
                            <div class="loan-bar-fill" style="width:65%"></div>
                        </div>
                    </div>
                </div>
                <div class="loan-amounts">
                    <div class="loan-amt-card">
                        <div class="amt-label">Loan limit</div>
                        <div class="amt-val">5GB / ₦2,000</div>
                    </div>
                    <div class="loan-amt-card">
                        <div class="amt-label">Repayment</div>
                        <div
                            class="amt-val"
                            style="color:var(--color-emerald);"
                        >
                            Auto ✓
                        </div>
                    </div>
                    <div class="loan-amt-card">
                        <div class="amt-label">Interest</div>
                        <div class="amt-val">0% flat</div>
                    </div>
                    <div class="loan-amt-card">
                        <div class="amt-label">Eligibility</div>
                        <div class="amt-val">Instant</div>
                    </div>
                </div>
                <a
                    href="#"
                    class="btn-primary"
                    style="width:100%;justify-content:center;margin-top:20px;padding:12px;"
                >
                    Request a Loan Now →
                </a>
            </div>

            <div class="loan-steps reveal">
                <div style="margin-bottom:8px;">
                    <h3
                        style="font-size:1.3rem;font-weight:700;color:var(--color-text);margin-bottom:8px;"
                    >
                        How it works
                    </h3>
                    <p style="font-size:0.88rem;color:var(--color-text2);">
                        No forms, no waiting. Get your airtime or data in under
                        a minute.
                    </p>
                </div>
                <div class="loan-step">
                    <div class="step-num">1</div>
                    <div>
                        <div class="step-title">Open Paykonet</div>
                        <div class="step-sub">
                            Tap "Airtime &amp; Data Loan" from your dashboard.
                            No paperwork required.
                        </div>
                    </div>
                </div>
                <div class="loan-step">
                    <div class="step-num">2</div>
                    <div>
                        <div class="step-title">Pick your amount</div>
                        <div class="step-sub">
                            Choose how much airtime (up to ₦2,000) or data (up
                            to 5GB) you need right now.
                        </div>
                    </div>
                </div>
                <div class="loan-step">
                    <div class="step-num">3</div>
                    <div>
                        <div class="step-title">Delivered instantly</div>
                        <div class="step-sub">
                            Your airtime or data arrives on your line within
                            seconds. Zero delays.
                        </div>
                    </div>
                </div>
                <div class="loan-step">
                    <div class="step-num">4</div>
                    <div>
                        <div class="step-title">Auto repayment</div>
                        <div class="step-sub">
                            The loan is automatically deducted next time you
                            fund your Paykonet wallet. Simple, stress-free.
                        </div>
                    </div>
                </div>
                <div
                    style="background:linear-gradient(135deg,rgba(124,58,237,0.08),rgba(16,185,129,0.06));border:1px solid var(--color-border);border-radius:14px;padding:18px;margin-top:8px;"
                >
                    <div
                        style="font-size:0.82rem;font-weight:600;color:var(--color-text);margin-bottom:4px;"
                    >
                        🎯 Increase your limit
                    </div>
                    <div style="font-size:0.8rem;color:var(--color-text2);">
                        Use Paykonet regularly and your loan limit grows
                        automatically based on your activity.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section id="testimonials" style="background:var(--color-bg2);">
    <div class="section-inner">
        <div style="text-align:center;" class="reveal">
            <div class="section-tag" style="justify-content:center;">
                User Reviews
            </div>
            <h2 class="section-title">
                Real people.<br /><span class="gradient-text"
                    >Real results.</span
                >
            </h2>
        </div>
        <div class="testimonials-grid reveal">
            <div class="testi-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p class="testi-text">
                    "I fund my Bet9ja account in seconds now. Used to take
                    forever with my old bank app. Paykonet has literally changed
                    how I handle money daily."
                </p>
                <div class="testi-author">
                    <div
                        class="testi-avatar"
                        style="background:linear-gradient(135deg,#7C3AED,#10B981);"
                    >
                        AO
                    </div>
                    <div>
                        <div class="testi-name">Adewale Ogunleye</div>
                        <div class="testi-role">Lagos, Nigeria</div>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p class="testi-text">
                    "The data loan saved me twice this month during important
                    Zoom calls. Instantly repaid itself when I topped up. Can't
                    believe it's free."
                </p>
                <div class="testi-author">
                    <div
                        class="testi-avatar"
                        style="background:linear-gradient(135deg,#F59E0B,#EF4444);"
                    >
                        CN
                    </div>
                    <div>
                        <div class="testi-name">Chidinma Nwosu</div>
                        <div class="testi-role">Enugu, Nigeria</div>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p class="testi-text">
                    "My USD virtual card lets me pay for my Netflix, Spotify,
                    and buy from Amazon. All from one app and my naira gets
                    converted automatically."
                </p>
                <div class="testi-author">
                    <div
                        class="testi-avatar"
                        style="background:linear-gradient(135deg,#3B82F6,#8B5CF6);"
                    >
                        KB
                    </div>
                    <div>
                        <div class="testi-name">Kayode Badmus</div>
                        <div class="testi-role">Abuja, Nigeria</div>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p class="testi-text">
                    "Paying EKEDC and DSTV every month used to stress me out.
                    Now I have auto-pay set up and Paykonet handles it.
                    Genuinely the best fintech app in Nigeria."
                </p>
                <div class="testi-author">
                    <div
                        class="testi-avatar"
                        style="background:linear-gradient(135deg,#10B981,#3B82F6);"
                    >
                        FA
                    </div>
                    <div>
                        <div class="testi-name">Funmi Adewale</div>
                        <div class="testi-role">Ibadan, Nigeria</div>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p class="testi-text">
                    "I run a small business and Paykonet makes paying suppliers
                    and collecting from customers incredibly smooth. The virtual
                    account is a game changer."
                </p>
                <div class="testi-author">
                    <div
                        class="testi-avatar"
                        style="background:linear-gradient(135deg,#F97316,#EF4444);"
                    >
                        EO
                    </div>
                    <div>
                        <div class="testi-name">Emeka Obi</div>
                        <div class="testi-role">Port Harcourt, Nigeria</div>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p class="testi-text">
                    "Sending money to my family in the village used to mean
                    going to the bank. Paykonet works even on USSD. My mum is 60
                    and uses it herself now."
                </p>
                <div class="testi-author">
                    <div
                        class="testi-avatar"
                        style="background:linear-gradient(135deg,#8B5CF6,#EC4899);"
                    >
                        BI
                    </div>
                    <div>
                        <div class="testi-name">Blessing Ibrahim</div>
                        <div class="testi-role">Kano, Nigeria</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="section-inner">
        <div class="cta-card reveal">
            <div
                class="cta-orb"
                style="width:400px;height:400px;background:#fff;top:-200px;left:-100px;"
            ></div>
            <div
                class="cta-orb"
                style="width:300px;height:300px;background:#10B981;bottom:-150px;right:-50px;"
            ></div>
            <div style="position:relative;z-index:2;">
                <div style="font-size:2.5rem;margin-bottom:12px;">⚡</div>
                <h2 class="section-title" style="color:#fff;">
                    Ready to move fast?
                </h2>
                <p>
                    Join over 2 million Nigerians who trust Paykonet for their
                    everyday money needs.
                </p>
                <div class="cta-btns">
                    <a href="#" class="btn-white">📱 Download for iOS</a>
                    <a
                        href="https://play.google.com/store/apps/details?id=com.pay2mecard.pay2me"
                        target="_blank"
                        class="btn-white">🤖 Download for Android</a
                    >
                    <a href="#" class="btn-ghost">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="#" class="logo" style="display:inline-flex;">
                <div class="logo-icon">⚡</div>
                Paykonet
            </a>
            <p>
                Nigeria's smartest digital wallet. Send, spend, save, and borrow
                — all in one place.
            </p>
            <div class="footer-socials" style="margin-top:18px;">
                <a href="#" class="social-btn">𝕏</a>
                <a href="#" class="social-btn">📘</a>
                <a href="#" class="social-btn">📸</a>
                <a href="#" class="social-btn">▶</a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Product</h4>
            <ul>
                <li><a href="#">Send Money</a></li>
                <li><a href="#">Airtime &amp; Data</a></li>
                <li><a href="#">Pay Bills</a></li>
                <li><a href="#">Betting Funding</a></li>
                <li><a href="#">Virtual Cards</a></li>
                <li><a href="#">Loans</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Company</h4>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Press Kit</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Legal</h4>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Cookie Policy</a></li>
                <li><a href="#">Security</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© 2025 Paykonet. All rights reserved. Licensed by CBN.</span>
        <span style="color:var(--color-text3);font-size:0.76rem;"
            >🔒 256-bit SSL Encrypted · CBN Licensed · NDIC Insured</span
        >
    </div>
</footer>

<style>
    :global(:root) {
        --color-bg: #f8fafc;
        --color-bg2: #f1f5f9;
        --color-surface: #ffffff;
        --color-surface2: #f1f5f9;
        --color-border: rgba(124, 58, 237, 0.15);
        --color-text: #0a0f1e;
        --color-text2: #475569;
        --color-text3: #94a3b8;
        --color-violet: #7c3aed;
        --color-emerald: #10b981;
        --color-card-glow: rgba(124, 58, 237, 0.08);
    }

    @media (prefers-color-scheme: dark) {
        :global(:root) {
            --color-bg: #060b18;
            --color-bg2: #0a0f1e;
            --color-surface: #0f1829;
            --color-surface2: #162035;
            --color-border: rgba(124, 58, 237, 0.25);
            --color-text: #f8fafc;
            --color-text2: #94a3b8;
            --color-text3: #475569;
            --color-card-glow: rgba(124, 58, 237, 0.12);
        }
    }

    :global(*) {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    :global(html) {
        scroll-behavior: smooth;
        scroll-padding-top: 84px;
    }
    :global(body) {
        font-family: "Inter", sans-serif;
        background: var(--color-bg);
        color: var(--color-text);
        transition:
            background 0.3s,
            color 0.3s;
    }
    :global(h1, h2, h3, h4, h5) {
        font-family: "Sora", sans-serif;
    }

    /* NAV */
    nav {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        background: rgba(248, 250, 252, 0.8);
        border-bottom: 1px solid var(--color-border);
        transition: background 0.3s;
    }
    @media (prefers-color-scheme: dark) {
        nav {
            background: rgba(6, 11, 24, 0.8);
        }
    }

    .nav-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
        height: 68px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: "Sora", sans-serif;
        font-weight: 700;
        font-size: 1.35rem;
        color: var(--color-text);
        text-decoration: none;
    }

    .logo-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #7c3aed, #10b981);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .nav-links {
        display: flex;
        align-items: center;
        gap: 32px;
        list-style: none;
    }
    .nav-links a {
        color: var(--color-text2);
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        transition: color 0.2s;
    }
    .nav-links a:hover {
        color: var(--color-violet);
    }

    @media (max-width: 960px) {
        .nav-links {
            display: none;
        }
    }

    .btn-primary {
        background: linear-gradient(135deg, #7c3aed, #6d28d9);
        color: #fff;
        padding: 10px 22px;
        border-radius: 10px;
        font-family: "Sora", sans-serif;
        font-weight: 600;
        font-size: 0.88rem;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition:
            transform 0.2s,
            box-shadow 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.35);
    }

    .btn-outline {
        background: transparent;
        color: var(--color-violet);
        padding: 10px 22px;
        border-radius: 10px;
        font-family: "Sora", sans-serif;
        font-weight: 600;
        font-size: 0.88rem;
        border: 1.5px solid var(--color-violet);
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-outline:hover {
        background: var(--color-violet);
        color: #fff;
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.3);
    }

    .hamburger {
        display: none;
        flex-direction: column;
        gap: 5px;
        cursor: pointer;
        padding: 11px;
        min-width: 44px;
        min-height: 44px;
        justify-content: center;
        border: none;
        background: none;
    }
    @media (max-width: 960px) {
        .hamburger {
            display: flex;
        }
    }
    .hamburger span {
        display: block;
        width: 22px;
        height: 2px;
        background: var(--color-text);
        border-radius: 2px;
        transition: all 0.3s;
    }

    .mobile-menu {
        position: fixed;
        top: 68px;
        max-height: calc(100dvh - 68px);
        overflow-y: auto;
        left: 0;
        right: 0;
        background: var(--color-surface);
        border-bottom: 1px solid var(--color-border);
        padding: 20px 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        z-index: 99;
    }
    .mobile-menu a {
        color: var(--color-text);
        font-size: 0.95rem;
        font-weight: 500;
        text-decoration: none;
        padding: 8px 0;
        border-bottom: 1px solid var(--color-border);
    }

    /* HERO */
    .hero {
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 100px 24px 60px;
        position: relative;
        overflow: hidden;
    }

    .hero-bg {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
    }
    .hero-blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.18;
    }
    @media (prefers-color-scheme: dark) {
        .hero-blob {
            opacity: 0.22;
        }
    }

    .hero-inner {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 60px;
        align-items: center;
    }
    @media (max-width: 900px) {
        .hero-inner {
            grid-template-columns: 1fr;
            text-align: center;
        }
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--color-card-glow);
        border: 1px solid var(--color-border);
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--color-violet);
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 20px;
    }
    .eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--color-emerald);
    }

    h1.hero-title {
        font-size: clamp(2.6rem, 5vw, 3.8rem);
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -0.03em;
        margin-bottom: 20px;
    }

    .gradient-text {
        background: linear-gradient(135deg, #7c3aed 0%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-sub {
        font-size: 1.05rem;
        line-height: 1.7;
        color: var(--color-text2);
        margin-bottom: 36px;
        max-width: 480px;
    }
    @media (max-width: 900px) {
        .hero-sub {
            max-width: 100%;
        }
    }

    .hero-cta {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }
    @media (max-width: 900px) {
        .hero-cta {
            justify-content: center;
        }
    }

    .hero-stats {
        margin-top: 48px;
        display: flex;
        gap: 36px;
        flex-wrap: wrap;
    }
    @media (max-width: 900px) {
        .hero-stats {
            justify-content: center;
        }
    }

    .stat-item h3 {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--color-text);
    }
    .stat-item p {
        font-size: 0.8rem;
        color: var(--color-text2);
        margin-top: 2px;
    }
    .stat-divider {
        width: 1px;
        background: var(--color-border);
        align-self: stretch;
    }
    @media (max-width: 480px) {
        .stat-divider {
            display: none;
        }
    }

    /* PHONE */
    .phone-wrap {
        width: 100%;
        max-width: 360px;
        margin: 0 auto;
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .phone-glow {
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: radial-gradient(
            circle,
            rgba(124, 58, 237, 0.35) 0%,
            transparent 70%
        );
        filter: blur(40px);
    }

    .phone {
        width: 240px;
        height: 490px;
        border-radius: 40px;
        background: var(--color-surface);
        border: 8px solid var(--color-surface2);
        box-shadow:
            0 40px 80px rgba(0, 0, 0, 0.25),
            0 0 0 1px var(--color-border);
        position: relative;
        overflow: hidden;
        animation: float 6s ease-in-out infinite;
        z-index: 2;
    }
    @media (prefers-color-scheme: dark) {
        .phone {
            background: #0f1829;
            border-color: #1e2a40;
            box-shadow:
                0 40px 80px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(124, 58, 237, 0.25);
        }
    }

    .phone-screen {
        width: 100%;
        height: 100%;
        background: var(--color-bg);
        padding: 16px 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    @media (prefers-color-scheme: dark) {
        .phone-screen {
            background: #060b18;
        }
    }

    .phone-notch {
        width: 70px;
        height: 22px;
        background: var(--color-surface);
        border-radius: 0 0 16px 16px;
        margin: 0 auto 8px;
    }
    @media (prefers-color-scheme: dark) {
        .phone-notch {
            background: #0f1829;
        }
    }

    .phone-balance-card {
        border-radius: 16px;
        padding: 14px;
        background: linear-gradient(135deg, #7c3aed 0%, #4f1d96 100%);
        color: #fff;
    }
    .phone-balance-card .label {
        font-size: 0.55rem;
        opacity: 0.75;
        margin-bottom: 4px;
    }
    .phone-balance-card .amount {
        font-family: "Sora", sans-serif;
        font-size: 1.3rem;
        font-weight: 700;
    }
    .phone-balance-card .acct {
        font-size: 0.5rem;
        opacity: 0.65;
        margin-top: 6px;
    }

    .phone-actions {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 6px;
    }
    .phone-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    .phone-action .icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        background: var(--color-surface2);
    }
    @media (prefers-color-scheme: dark) {
        .phone-action .icon {
            background: #162035;
        }
    }
    .phone-action .lbl {
        font-size: 0.42rem;
        color: var(--color-text2);
        text-align: center;
    }

    .phone-txn-title {
        font-family: "Sora", sans-serif;
        font-size: 0.6rem;
        font-weight: 700;
        color: var(--color-text);
        margin-top: 4px;
    }

    .phone-txn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px;
        background: var(--color-surface);
        border-radius: 10px;
    }
    @media (prefers-color-scheme: dark) {
        .phone-txn {
            background: #0f1829;
        }
    }
    .txn-icon {
        width: 26px;
        height: 26px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        flex-shrink: 0;
    }
    .txn-info {
        flex: 1;
    }
    .txn-info .txn-name {
        font-size: 0.5rem;
        font-weight: 600;
        color: var(--color-text);
    }
    .txn-info .txn-date {
        font-size: 0.4rem;
        color: var(--color-text2);
    }
    .txn-amt {
        font-family: "Sora", sans-serif;
        font-size: 0.55rem;
        font-weight: 700;
    }

    /* FLOATING CARDS */
    .float-card {
        position: absolute;
        border-radius: 14px;
        padding: 12px 14px;
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(12px);
        z-index: 3;
    }
    @media (prefers-color-scheme: dark) {
        .float-card {
            background: #0f1829;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
        }
    }
    .float-card-1 {
        top: 40px;
        left: 0;
        animation: float 6s ease-in-out 1s infinite;
    }
    .float-card-2 {
        bottom: 80px;
        right: 0;
        animation: float 6s ease-in-out 3s infinite;
    }
    .float-card-3 {
        top: 200px;
        left: 0;
        animation: float 6s ease-in-out 2s infinite;
    }

    .fc-label {
        font-size: 0.62rem;
        color: var(--color-text2);
        margin-bottom: 3px;
    }
    .fc-val {
        font-family: "Sora", sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: var(--color-text);
    }
    .fc-sub {
        font-size: 0.58rem;
        color: var(--color-emerald);
        margin-top: 2px;
        font-weight: 600;
    }
    .fc-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
        padding: 2px 8px;
        border-radius: 100px;
        font-size: 0.58rem;
        font-weight: 600;
    }

    @media (max-width: 900px) {
        .float-card-1 {
            left: 0;
            top: 20px;
        }
        .float-card-2 {
            right: 0;
            bottom: 40px;
        }
        .float-card-3 {
            display: none;
        }
    }

    /* SECTIONS */
    section {
        padding: 96px 24px;
    }
    .section-inner {
        max-width: 1200px;
        margin: 0 auto;
    }

    .section-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--color-violet);
        margin-bottom: 14px;
    }
    .section-tag::before {
        content: "";
        display: block;
        width: 20px;
        height: 2px;
        background: var(--color-violet);
        border-radius: 2px;
    }

    h2.section-title {
        font-size: clamp(1.8rem, 3.5vw, 2.8rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.15;
        margin-bottom: 16px;
        color: var(--color-text);
    }

    .section-sub {
        font-size: 1rem;
        color: var(--color-text2);
        line-height: 1.7;
        max-width: 520px;
    }

    /* LOGOS BAR */
    .logos-bar {
        background: var(--color-bg2);
        border-top: 1px solid var(--color-border);
        border-bottom: 1px solid var(--color-border);
        padding: 28px 24px;
    }
    .logos-inner {
        max-width: 1000px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
    }
    .logo-item {
        font-family: "Sora", sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--color-text3);
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    /* BENTO */
    .bento-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 60px;
    }
    @media (max-width: 900px) {
        .bento-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 580px) {
        .bento-grid {
            grid-template-columns: 1fr;
        }
    }

    .bento-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 20px;
        padding: 28px;
        transition:
            transform 0.25s,
            box-shadow 0.25s,
            border-color 0.25s;
        position: relative;
        overflow: hidden;
    }
    .bento-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 48px var(--color-card-glow);
        border-color: rgba(124, 58, 237, 0.3);
    }
    .bento-card.wide {
        grid-column: span 2;
    }
    .bento-card.accent {
        background: linear-gradient(135deg, #7c3aed 0%, #4f1d96 100%);
        border-color: transparent;
    }
    :global(.bento-card.accent *) {
        color: #fff !important;
    }

    .bento-icon {
        font-size: 1.8rem;
        margin-bottom: 14px;
    }
    .bento-title {
        font-family: "Sora", sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 8px;
    }
    .bento-sub {
        font-size: 0.87rem;
        color: var(--color-text2);
        line-height: 1.6;
    }
    .bento-card-glow {
        position: absolute;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: radial-gradient(
            circle,
            rgba(255, 255, 255, 0.1) 0%,
            transparent 70%
        );
        bottom: -60px;
        right: -60px;
        pointer-events: none;
    }

    /* VIRTUAL CARDS */
    .cards-section {
        background: var(--color-bg2);
    }
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 60px;
        align-items: center;
        margin-top: 0;
    }
    @media (max-width: 800px) {
        .cards-grid {
            grid-template-columns: 1fr;
        }
    }

    .vc-stack {
        position: relative;
        height: 220px;
    }
    .vc {
        position: absolute;
        width: 280px;
        height: 170px;
        border-radius: 18px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 20px 48px rgba(0, 0, 0, 0.2);
    }
    .vc-usd {
        background: linear-gradient(
            135deg,
            #1a1a2e 0%,
            #16213e 50%,
            #0f3460 100%
        );
        color: #fff;
        top: 0;
        left: 0;
        z-index: 2;
        transform: rotate(-5deg);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .vc-ngn {
        background: linear-gradient(135deg, #7c3aed 0%, #10b981 100%);
        color: #fff;
        top: 30px;
        left: 40px;
        z-index: 3;
        transform: rotate(2deg);
    }
    .vc-logo {
        font-family: "Sora", sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .vc-chip {
        font-size: 1.2rem;
    }
    .vc-number {
        font-size: 0.75rem;
        letter-spacing: 0.15em;
        opacity: 0.8;
    }
    .vc-name {
        font-size: 0.7rem;
        font-weight: 600;
    }
    .vc-currency {
        font-family: "Sora", sans-serif;
        font-size: 0.65rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.2);
        padding: 3px 8px;
        border-radius: 100px;
    }
    .vc-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    .vc-features {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .vc-feat {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .vc-feat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        background: var(--color-card-glow);
        border: 1px solid var(--color-border);
    }
    .vc-feat-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--color-text);
    }
    .vc-feat-sub {
        font-size: 0.82rem;
        color: var(--color-text2);
        margin-top: 2px;
    }

    /* LOAN */
    .loan-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 60px;
        align-items: center;
        margin-top: 60px;
    }
    @media (max-width: 800px) {
        .loan-grid {
            grid-template-columns: 1fr;
        }
    }

    .loan-visual {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 24px;
        padding: 32px;
    }

    .loan-progress-wrap {
        margin: 20px 0;
    }
    .loan-progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.78rem;
        color: var(--color-text2);
        margin-bottom: 8px;
    }
    .loan-bar {
        height: 8px;
        border-radius: 100px;
        background: var(--color-bg2);
        overflow: hidden;
    }
    .loan-bar-fill {
        height: 100%;
        border-radius: 100px;
        background: linear-gradient(90deg, #7c3aed, #10b981);
    }

    .loan-amounts {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 20px;
    }
    .loan-amt-card {
        background: var(--color-bg2);
        border-radius: 12px;
        padding: 14px;
        border: 1px solid var(--color-border);
    }
    .loan-amt-card .amt-label {
        font-size: 0.65rem;
        color: var(--color-text2);
        margin-bottom: 4px;
    }
    .loan-amt-card .amt-val {
        font-family: "Sora", sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: var(--color-text);
    }

    .loan-steps {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .loan-step {
        display: flex;
        gap: 16px;
    }
    .step-num {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        flex-shrink: 0;
        background: linear-gradient(135deg, #7c3aed, #10b981);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: "Sora", sans-serif;
        font-size: 0.8rem;
        font-weight: 700;
        color: #fff;
    }
    .step-title {
        font-weight: 600;
        font-size: 0.92rem;
        color: var(--color-text);
    }
    .step-sub {
        font-size: 0.82rem;
        color: var(--color-text2);
        margin-top: 2px;
    }

    /* TESTIMONIALS */
    .testimonials-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
        margin-top: 60px;
    }
    @media (max-width: 900px) {
        .testimonials-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 580px) {
        .testimonials-grid {
            grid-template-columns: 1fr;
        }
    }

    .testi-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 20px;
        padding: 24px;
        transition: transform 0.25s;
    }
    .testi-card:hover {
        transform: translateY(-3px);
    }
    .stars {
        display: flex;
        gap: 3px;
        margin-bottom: 14px;
        font-size: 0.85rem;
    }
    .testi-text {
        font-size: 0.9rem;
        line-height: 1.65;
        color: var(--color-text2);
        margin-bottom: 18px;
    }
    .testi-author {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .testi-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
        color: #fff;
    }
    .testi-name {
        font-weight: 600;
        font-size: 0.88rem;
        color: var(--color-text);
    }
    .testi-role {
        font-size: 0.75rem;
        color: var(--color-text2);
    }

    /* CTA */
    .cta-section {
        background: var(--color-bg2);
        border-top: 1px solid var(--color-border);
    }
    .cta-card {
        border-radius: 28px;
        background: linear-gradient(
            135deg,
            #7c3aed 0%,
            #4c1d95 60%,
            #065f46 100%
        );
        padding: 72px 48px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    @media (max-width: 640px) {
        .cta-card {
            padding: 48px 24px;
        }
    }
    .cta-card h2 {
        color: #fff;
        margin-bottom: 14px;
    }
    .cta-card p {
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 36px;
        font-size: 1.05rem;
    }
    .cta-btns {
        display: flex;
        gap: 14px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-white {
        background: #fff;
        color: #7c3aed;
        padding: 13px 28px;
        border-radius: 12px;
        font-family: "Sora", sans-serif;
        font-weight: 700;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition:
            transform 0.2s,
            box-shadow 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-white:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
    }

    .btn-ghost {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        padding: 13px 28px;
        border-radius: 12px;
        font-family: "Sora", sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        border: 1.5px solid rgba(255, 255, 255, 0.3);
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-ghost:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .cta-orb {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        opacity: 0.15;
        filter: blur(50px);
    }

    /* FOOTER */
    footer {
        padding: 60px 24px 40px;
        border-top: 1px solid var(--color-border);
    }
    .footer-inner {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 40px;
    }
    @media (max-width: 800px) {
        .footer-inner {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 480px) {
        .footer-inner {
            grid-template-columns: 1fr;
        }
    }

    .footer-brand p {
        font-size: 0.88rem;
        color: var(--color-text2);
        line-height: 1.65;
        margin-top: 12px;
        max-width: 240px;
    }
    .footer-col h4 {
        font-family: "Sora", sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 16px;
        letter-spacing: 0.02em;
    }
    .footer-col ul {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .footer-col ul a {
        font-size: 0.85rem;
        color: var(--color-text2);
        text-decoration: none;
        transition: color 0.2s;
    }
    .footer-col ul a:hover {
        color: var(--color-violet);
    }

    .footer-bottom {
        max-width: 1200px;
        margin: 40px auto 0;
        padding-top: 24px;
        border-top: 1px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 0.8rem;
        color: var(--color-text3);
    }

    .footer-socials {
        display: flex;
        gap: 12px;
    }
    .social-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: var(--color-surface2);
        border: 1px solid var(--color-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        text-decoration: none;
        color: var(--color-text2);
        transition: all 0.2s;
    }
    .social-btn:hover {
        background: var(--color-violet);
        color: #fff;
        border-color: transparent;
    }

    .wallet-preview {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
        align-items: center;
    }

    .hero-inner > div,
    .bento-card,
    .cards-grid > div,
    .loan-grid > div {
        min-width: 0;
    }

    @media (max-width: 580px) {
        section {
            padding: 64px 16px;
        }
        .hero {
            padding: 100px 16px 60px;
        }
        .nav-inner {
            padding: 0 16px;
            gap: 12px;
        }
        .bento-card.wide {
            grid-column: auto;
        }
        .wallet-preview {
            grid-template-columns: minmax(0, 1fr);
        }
        .bento-card,
        .loan-visual {
            padding: 20px;
        }
        h1.hero-title {
            font-size: clamp(2rem, 9vw, 2.6rem);
        }
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .stat-divider {
            display: none;
        }
        .stat-item h3 {
            font-size: 1.3rem;
        }
        .stat-item p {
            font-size: 0.72rem;
        }
        .vc-stack {
            width: 100%;
            max-width: 360px;
            margin: 0 auto;
        }
        .vc {
            width: calc(100% - 48px);
            max-width: 280px;
        }
        .vc-usd {
            left: 12px;
        }
        .vc-ngn {
            left: 32px;
        }
    }

    @media (max-width: 480px) {
        .nav-inner .btn-primary {
            display: none;
        }
    }

    /* SCROLL REVEAL */
    :global(.reveal) {
        opacity: 0;
        transform: translateY(28px);
        transition:
            opacity 0.6s ease,
            transform 0.6s ease;
    }
    :global(.reveal.visible) {
        opacity: 1;
        transform: none;
    }

    /* ANIMATIONS */
    @keyframes float {
        0%,
        100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-12px);
        }
    }
</style>
