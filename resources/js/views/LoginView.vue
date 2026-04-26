<template>
  <div class="el-land">

    <!-- ══ NAV ══════════════════════════════════════════════════════════ -->
    <nav class="el-nav" :class="{ scrolled: scrollY > 40 }">
      <div class="el-nav__logo">
        <div class="el-logo-icon">
          <svg width="22" height="22" viewBox="0 0 20 20" fill="none">
            <rect x="2"  y="12" width="3" height="6" rx="1" fill="white" opacity="0.5"/>
            <rect x="7"  y="7"  width="3" height="11" rx="1" fill="white" opacity="0.75"/>
            <rect x="12" y="2"  width="3" height="16" rx="1" fill="white"/>
            <path d="M3.5 11 L8.5 6 L13.5 1" stroke="white" stroke-width="1.5" stroke-linecap="round" opacity="0.9"/>
          </svg>
        </div>
        <span class="el-logo-text">Edge<span class="el-logo-accent">Ledger</span></span>
      </div>
      <div class="el-nav__links">
        <a href="#features" @click.prevent="scrollTo('features')">Features</a>
        <a href="#stats" @click.prevent="scrollTo('stats')">Performance</a>
        <a href="#how" @click.prevent="scrollTo('how')">How It Works</a>
        <a href="#about" @click.prevent="scrollTo('about')">About</a>
      </div>
      <div class="el-nav__actions">
        <button class="el-btn el-btn--ghost" @click="openModal('login')">Sign In</button>
        <button class="el-btn el-btn--gold" @click="openModal('register')">Start Free — 30 Days</button>
      </div>
      <button class="el-nav__burger" @click="mobileNav = !mobileNav">
        <span></span><span></span><span></span>
      </button>
    </nav>

    <!-- Mobile nav -->
    <div class="el-mobile-nav" :class="{ open: mobileNav }">
      <a @click="scrollTo('features'); mobileNav=false">Features</a>
      <a @click="scrollTo('stats'); mobileNav=false">Performance</a>
      <a @click="scrollTo('how'); mobileNav=false">How It Works</a>
      <a @click="scrollTo('about'); mobileNav=false">About</a>
      <button class="el-btn el-btn--gold mt-4 w-full" @click="openModal('register'); mobileNav=false">Start Free Trial</button>
      <button class="el-btn el-btn--ghost mt-2 w-full" @click="openModal('login'); mobileNav=false">Sign In</button>
    </div>

    <!-- ══ HERO ═════════════════════════════════════════════════════════ -->
    <section class="el-hero">
      <div class="el-hero__bg">
        <div class="el-hero__grid"></div>
        <div class="el-hero__glow el-hero__glow--1"></div>
        <div class="el-hero__glow el-hero__glow--2"></div>
        <div class="el-hero__particles">
          <span v-for="i in 12" :key="i" class="el-particle" :style="particleStyle(i)"></span>
        </div>
      </div>

      <div class="el-hero__content">
        <div class="el-hero__badge">
          <span class="el-hero__badge-dot"></span>
          Professional Trading Intelligence Platform
        </div>
        <h1 class="el-hero__h1">
          Your Edge.<br/>
          <span class="el-hero__h1--grad">Quantified.</span>
        </h1>
        <p class="el-hero__sub">
          Stop guessing. EdgeLedger transforms your trading history into a
          precision decision engine — revealing exactly where your edge lives,
          and alerting you when conditions align.
        </p>
        <div class="el-hero__actions">
          <button class="el-btn el-btn--gold el-btn--lg" @click="openModal('register')">
            Start Your 30-Day Free Trial
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <button class="el-btn el-btn--outline" @click="scrollTo('features')">
            Explore Features
          </button>
        </div>
        <p class="el-hero__note">No credit card required · $2/month after trial · Cancel anytime</p>

        <!-- Floating stats row -->
        <div class="el-hero__stats">
          <div class="el-hero__stat">
            <span class="el-hero__stat-n">HTF → MTF → Entry</span>
            <span class="el-hero__stat-l">Cascade Analysis</span>
          </div>
          <div class="el-hero__stat-div"></div>
          <div class="el-hero__stat">
            <span class="el-hero__stat-n">Real-time</span>
            <span class="el-hero__stat-l">Win Rate Per Setup</span>
          </div>
          <div class="el-hero__stat-div"></div>
          <div class="el-hero__stat">
            <span class="el-hero__stat-n">50%</span>
            <span class="el-hero__stat-l">Referral Commission</span>
          </div>
        </div>
      </div>

      <!-- Hero mock -->
      <div class="el-hero__mock">
        <div class="el-mock-chrome">
          <div class="el-mock-chrome__bar">
            <div class="el-mock-chrome__dots">
              <span style="background:#FF5F57"></span>
              <span style="background:#FEBC2E"></span>
              <span style="background:#28C840"></span>
            </div>
            <span class="el-mock-chrome__title">EdgeLedger — Pre-Trade Filter</span>
          </div>
          <div class="el-mock-body">
            <!-- Filter card -->
            <div class="el-mock-filter">
              <div class="el-mock-filter__row">
                <div class="el-mock-filter__field">
                  <span class="el-mock-filter__label">HTF Structure</span>
                  <span class="el-mock-filter__val el-mock-filter__val--h">Bullish BOS</span>
                </div>
                <div class="el-mock-filter__field">
                  <span class="el-mock-filter__label">MTF Trigger</span>
                  <span class="el-mock-filter__val el-mock-filter__val--m">Bullish OB ↑ filtered</span>
                </div>
                <div class="el-mock-filter__field">
                  <span class="el-mock-filter__label">Entry TF</span>
                  <span class="el-mock-filter__val el-mock-filter__val--e">M1 CHoCH</span>
                </div>
              </div>
              <!-- Win rate result -->
              <div class="el-mock-result">
                <div class="el-mock-result__wr">
                  <span class="el-mock-result__pct">71%</span>
                  <span class="el-mock-result__label">Win Rate</span>
                </div>
                <div class="el-mock-result__bar">
                  <div class="el-mock-result__fill" style="width:71%"></div>
                </div>
                <div class="el-mock-result__trades">34 trades · EURUSD · London</div>
              </div>
            </div>
            <!-- Trade cards -->
            <div class="el-mock-trades">
              <div v-for="t in mockTrades" :key="t.id" class="el-mock-trade" :class="'el-mock-trade--'+t.r">
                <div class="el-mock-trade__badge">{{ t.r.toUpperCase() }}</div>
                <div class="el-mock-trade__info">
                  <span class="el-mock-trade__name">{{ t.name }}</span>
                  <span class="el-mock-trade__pair">{{ t.pair }}</span>
                </div>
                <div class="el-mock-trade__r" :class="t.rn > 0 ? 'pos' : 'neg'">{{ t.rn > 0 ? '+' : '' }}{{ t.rn }}R</div>
              </div>
            </div>
          </div>
        </div>
        <!-- Floating cards -->
        <div class="el-float el-float--1">
          <div class="el-float__icon">📊</div>
          <div><div class="el-float__title">Analytics</div><div class="el-float__sub">Updated live</div></div>
        </div>
        <div class="el-float el-float--2">
          <div class="el-float__wr">68%</div>
          <div class="el-float__sub">Overall win rate</div>
        </div>
      </div>
    </section>

    <!-- ══ TICKER STRIP ═══════════════════════════════════════════════ -->
    <div class="el-ticker">
      <div class="el-ticker__track">
        <span v-for="item in tickerItems" :key="item" class="el-ticker__item">
          <span class="el-ticker__dot"></span>{{ item }}
        </span>
        <!-- Duplicate for seamless loop -->
        <span v-for="item in tickerItems" :key="'b'+item" class="el-ticker__item">
          <span class="el-ticker__dot"></span>{{ item }}
        </span>
      </div>
    </div>

    <!-- ══ STATS COUNTER ══════════════════════════════════════════════ -->
    <section class="el-stats" id="stats">
      <div class="el-stats__inner">
        <div class="el-stat-card" v-for="s in statCards" :key="s.label">
          <div class="el-stat-card__n">{{ s.value }}</div>
          <div class="el-stat-card__label">{{ s.label }}</div>
          <div class="el-stat-card__sub">{{ s.sub }}</div>
        </div>
      </div>
    </section>

    <!-- ══ FEATURES ═══════════════════════════════════════════════════ -->
    <section class="el-features" id="features">
      <div class="el-features__inner">
        <div class="el-section-label">Platform Features</div>
        <h2 class="el-section-h2">Built for how professionals trade</h2>
        <p class="el-section-p">Every feature is engineered around multi-timeframe methodology — not generic journaling designed for retail traders.</p>

        <div class="el-feature-grid">

          <div class="el-feat el-feat--wide">
            <div class="el-feat__icon-wrap el-feat__icon-wrap--green">🔍</div>
            <h3 class="el-feat__title">Cascade Pre-Trade Filter</h3>
            <p class="el-feat__body">Before every trade, query your entire historical database. The cascade filter mirrors how you actually think — HTF context narrows MTF options, which narrows your Entry TF. See your exact win rate for the conditions you're facing right now.</p>
            <div class="el-feat__mock-filter">
              <div class="el-feat__mock-row"><span class="htf">HTF</span><span class="el-feat__mock-arrow">→</span><span class="mtf">MTF</span><span class="el-feat__mock-arrow">→</span><span class="etf">Entry TF</span><span class="el-feat__mock-wr">71% ↑</span></div>
              <div class="el-feat__mock-row"><span class="htf">HTF</span><span class="el-feat__mock-arrow">→</span><span class="mtf">MTF</span><span class="el-feat__mock-arrow">→</span><span class="etf">Entry TF</span><span class="el-feat__mock-wr">44%</span></div>
              <div class="el-feat__mock-row"><span class="htf">HTF</span><span class="el-feat__mock-arrow">→</span><span class="mtf">MTF</span><span class="el-feat__mock-arrow">→</span><span class="etf">Entry TF</span><span class="el-feat__mock-wr neg">31% ↓</span></div>
            </div>
          </div>

          <div class="el-feat">
            <div class="el-feat__icon-wrap el-feat__icon-wrap--blue">📊</div>
            <h3 class="el-feat__title">Deep Performance Analytics</h3>
            <p class="el-feat__body">Win rate by every combination of structure, pair, and session. Expectancy per setup. Streaks. Separate statistics for disciplined vs impulsive trades so your numbers reflect your true edge.</p>
            <div class="el-feat__analytics">
              <div class="el-feat__bar" v-for="b in analyticsBars" :key="b.label">
                <span class="el-feat__bar-label">{{ b.label }}</span>
                <div class="el-feat__bar-track"><div class="el-feat__bar-fill" :style="{width: b.pct+'%', background: b.pct>55?'#1D9E75':b.pct>40?'#D4A017':'#E24B4A'}"></div></div>
                <span class="el-feat__bar-pct" :style="{color: b.pct>55?'#1D9E75':b.pct>40?'#D4A017':'#E24B4A'}">{{ b.pct }}%</span>
              </div>
            </div>
          </div>

          <div class="el-feat">
            <div class="el-feat__icon-wrap el-feat__icon-wrap--gold">📓</div>
            <h3 class="el-feat__title">Pre-Trade Journal</h3>
            <p class="el-feat__body">Document your analysis before you pull the trigger. Record your HTF bias, MTF trigger, and entry plan. Complete the entry after the trade. Promote winning setups directly to your permanent database.</p>
            <div class="el-feat__journal">
              <div class="el-feat__journal-entry">
                <div class="el-feat__journal-status el-feat__journal-status--pre">PRE-TRADE</div>
                <div class="el-feat__journal-text">Bullish BOS on H4. Waiting for M15 OB retest...</div>
              </div>
              <div class="el-feat__journal-entry">
                <div class="el-feat__journal-status el-feat__journal-status--done">COMPLETED</div>
                <div class="el-feat__journal-text">+2.8R · Promoted to database ✓</div>
              </div>
            </div>
          </div>

          <div class="el-feat el-feat--wide">
            <div class="el-feat__icon-wrap el-feat__icon-wrap--purple">🗄️</div>
            <h3 class="el-feat__title">Visual Trade Database</h3>
            <p class="el-feat__body">Every trade stored with HTF, MTF, and Entry TF chart screenshots. Browse your setups visually — thumbnails in the list, click to expand all three timeframes side by side. Navigate through your entire dataset with keyboard arrows without closing the view.</p>
            <div class="el-feat__chart-row">
              <div class="el-feat__chart-slot el-feat__chart-slot--h">
                <div class="el-feat__chart-candles">
                  <div v-for="c in chartCandles('h')" :key="c.i" class="el-feat__candle" :style="c.style"></div>
                </div>
                <div class="el-feat__chart-label">HTF</div>
              </div>
              <div class="el-feat__chart-slot el-feat__chart-slot--m">
                <div class="el-feat__chart-candles">
                  <div v-for="c in chartCandles('m')" :key="c.i" class="el-feat__candle" :style="c.style"></div>
                </div>
                <div class="el-feat__chart-label">MTF</div>
              </div>
              <div class="el-feat__chart-slot el-feat__chart-slot--e">
                <div class="el-feat__chart-candles">
                  <div v-for="c in chartCandles('e')" :key="c.i" class="el-feat__candle" :style="c.style"></div>
                </div>
                <div class="el-feat__chart-label">Entry TF</div>
              </div>
            </div>
          </div>

          <div class="el-feat">
            <div class="el-feat__icon-wrap el-feat__icon-wrap--teal">🤝</div>
            <h3 class="el-feat__title">Earn While You Trade</h3>
            <p class="el-feat__body">Share your referral code. Earn 50% commission for every trader who subscribes through your link — paid directly to your wallet. Redeem via PayPal, bank transfer, or crypto.</p>
            <div class="el-feat__referral">
              <div class="el-feat__ref-code">REF: <strong>A8F3C2D1</strong></div>
              <div class="el-feat__ref-stat"><span class="green">$24.00</span> earned this month</div>
              <div class="el-feat__ref-stat"><span class="green">12</span> active referrals</div>
            </div>
          </div>

          <div class="el-feat">
            <div class="el-feat__icon-wrap el-feat__icon-wrap--red">📈</div>
            <h3 class="el-feat__title">Expectancy Engine</h3>
            <p class="el-feat__body">Know your mathematical edge before you enter. EdgeLedger calculates expectancy per setup — the single number that tells you whether your system makes money over time when followed with discipline.</p>
            <div class="el-feat__expect">
              <div class="el-feat__expect-main">+0.87R</div>
              <div class="el-feat__expect-label">Expected value per trade</div>
              <div class="el-feat__expect-formula">= (Win% × Avg Win) − (Loss% × Avg Loss)</div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ══ HOW IT WORKS ═══════════════════════════════════════════════ -->
    <section class="el-how" id="how">
      <div class="el-how__inner">
        <div class="el-section-label">Simple Workflow</div>
        <h2 class="el-section-h2">From trade to insight in four steps</h2>

        <div class="el-steps">
          <div class="el-step" v-for="(step, i) in steps" :key="i">
            <div class="el-step__num">{{ String(i+1).padStart(2,'0') }}</div>
            <div class="el-step__icon">{{ step.icon }}</div>
            <h3 class="el-step__title">{{ step.title }}</h3>
            <p class="el-step__body">{{ step.body }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ══ TESTIMONIAL / QUOTE SLIDER ════════════════════════════════ -->
    <section class="el-quotes">
      <div class="el-quotes__glow"></div>
      <div class="el-quotes__inner">
        <div class="el-section-label">Trader Philosophy</div>
        <div class="el-quote-slider">
          <transition name="el-quote-fade" mode="out-in">
            <div class="el-quote" :key="quoteIdx">
              <div class="el-quote__mark">"</div>
              <p class="el-quote__text">{{ quotes[quoteIdx].text }}</p>
              <div class="el-quote__attr">— {{ quotes[quoteIdx].attr }}</div>
            </div>
          </transition>
          <div class="el-quote__dots">
            <button v-for="(q, i) in quotes" :key="i"
              class="el-quote__dot" :class="{ active: i === quoteIdx }"
              @click="quoteIdx = i"></button>
          </div>
        </div>
      </div>
    </section>

    <!-- ══ PRICING ════════════════════════════════════════════════════ -->
    <section class="el-pricing" id="pricing">
      <div class="el-pricing__inner">
        <div class="el-section-label">Simple Pricing</div>
        <h2 class="el-section-h2">One plan. Everything included.</h2>

        <div class="el-price-card">
          <div class="el-price-card__badge">Most Popular</div>
          <div class="el-price-card__top">
            <div>
              <div class="el-price-card__name">EdgeLedger Pro</div>
              <div class="el-price-card__tagline">Everything you need to trade with an edge</div>
            </div>
            <div class="el-price-card__price">
              <span class="el-price-card__currency">$</span>
              <span class="el-price-card__amount">2</span>
              <span class="el-price-card__per">/month</span>
            </div>
          </div>
          <div class="el-price-card__trial">
            <span class="el-price-card__trial-badge">🎉 30 Days Free</span>
            No credit card required to start
          </div>
          <ul class="el-price-card__features">
            <li v-for="f in pricingFeatures" :key="f"><span class="el-check">✓</span>{{ f }}</li>
          </ul>
          <button class="el-btn el-btn--gold el-btn--full" @click="openModal('register')">
            Start Free Trial — No Card Required
          </button>
          <p class="el-price-card__note">Cancel anytime. No questions asked.</p>
        </div>
      </div>
    </section>

    <!-- ══ ABOUT ══════════════════════════════════════════════════════ -->
    <section class="el-about" id="about">
      <div class="el-about__inner">
        <div class="el-about__content">
          <div class="el-section-label">About EdgeLedger</div>
          <h2 class="el-section-h2">Built by a trader, for traders</h2>
          <p class="el-about__body">EdgeLedger was built out of frustration with generic trading journals that treat every trade the same. Real edge comes from understanding the specific confluence of market conditions that produce consistent results — not from tracking P&L in a spreadsheet.</p>
          <p class="el-about__body">The platform is built around how ICT and Smart Money traders actually think — hierarchical structure from higher timeframes to entry, with every setup label connected to real historical outcomes. Your database becomes your most valuable trading asset.</p>
          <div class="el-about__values">
            <div class="el-about__value" v-for="v in values" :key="v.title">
              <div class="el-about__value-icon">{{ v.icon }}</div>
              <div>
                <div class="el-about__value-title">{{ v.title }}</div>
                <div class="el-about__value-body">{{ v.body }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="el-about__visual">
          <div class="el-about__card el-about__card--1">
            <div class="el-about__card-label">Trade Database</div>
            <div class="el-about__card-stat">11 trades recorded</div>
            <div class="el-about__card-wr"><span style="color:#1D9E75;font-weight:700;font-size:28px">100%</span><span style="color:#64748B;font-size:12px;margin-left:6px">Win Rate</span></div>
          </div>
          <div class="el-about__card el-about__card--2">
            <div class="el-about__card-label">Best Setup</div>
            <div class="el-about__card-setup">H4 Bullish OE → M15 OB → M1 CHoCH</div>
            <div class="el-about__card-r" style="color:#1D9E75">Avg +3.4R per trade</div>
          </div>
          <div class="el-about__card el-about__card--3">
            <div class="el-about__card-label">Expectancy</div>
            <div class="el-about__card-big" style="color:#1D9E75">+0.87R</div>
            <div class="el-about__card-sub">Per trade · All setups</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ══ FINAL CTA ══════════════════════════════════════════════════ -->
    <section class="el-cta-final">
      <div class="el-cta-final__glow"></div>
      <div class="el-cta-final__inner">
        <div class="el-cta-final__badge">Start Today</div>
        <h2 class="el-cta-final__h2">Your edge is already in your data.<br/>Let's find it.</h2>
        <p class="el-cta-final__sub">Join traders who use EdgeLedger to turn their history into a systematic edge. 30 days free — no credit card, no commitment.</p>
        <div class="el-cta-final__actions">
          <button class="el-btn el-btn--gold el-btn--lg" @click="openModal('register')">Create Free Account</button>
          <a href="mailto:tamaeproductions@gmail.com?subject=EdgeLedger Support" class="el-btn el-btn--outline">Request Support</a>
        </div>
      </div>
    </section>

    <!-- ══ FOOTER ═════════════════════════════════════════════════════ -->
    <footer class="el-footer">
      <div class="el-footer__inner">
        <div class="el-footer__brand">
          <div class="el-footer__logo">
            <div class="el-logo-icon el-logo-icon--sm">
              <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                <rect x="2" y="12" width="3" height="6" rx="1" fill="white" opacity="0.5"/>
                <rect x="7" y="7"  width="3" height="11" rx="1" fill="white" opacity="0.75"/>
                <rect x="12" y="2" width="3" height="16" rx="1" fill="white"/>
                <path d="M3.5 11 L8.5 6 L13.5 1" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>
            <span class="el-logo-text">Edge<span class="el-logo-accent">Ledger</span></span>
          </div>
          <p class="el-footer__tagline">Trading Intelligence Platform</p>
          <p class="el-footer__contact"><a href="mailto:tamaeproductions@gmail.com">tamaeproductions@gmail.com</a></p>
        </div>
        <div class="el-footer__links">
          <div class="el-footer__col">
            <div class="el-footer__col-title">Platform</div>
            <a @click.prevent="scrollTo('features')">Features</a>
            <a @click.prevent="scrollTo('how')">How It Works</a>
            <a @click.prevent="scrollTo('pricing')">Pricing</a>
          </div>
          <div class="el-footer__col">
            <div class="el-footer__col-title">Company</div>
            <a @click.prevent="scrollTo('about')">About</a>
            <a href="mailto:tamaeproductions@gmail.com?subject=EdgeLedger Support">Request Support</a>
          </div>
          <div class="el-footer__col">
            <div class="el-footer__col-title">Account</div>
            <a @click.prevent="openModal('register')">Start Free Trial</a>
            <a @click.prevent="openModal('login')">Sign In</a>
          </div>
        </div>
      </div>
      <div class="el-footer__bottom">
        <span>© {{ new Date().getFullYear() }} EdgeLedger. All rights reserved.</span>
        <span>Built for structured traders.</span>
      </div>
    </footer>

    <!-- ══ AUTH MODAL ════════════════════════════════════════════════ -->
    <transition name="el-modal-fade">
      <div v-if="showModal" class="el-modal-overlay" @click.self="showModal=false">
        <div class="el-modal">
          <button class="el-modal__close" @click="showModal=false">✕</button>
          <div class="el-modal__logo">
            <div class="el-logo-icon el-logo-icon--sm"><svg width="16" height="16" viewBox="0 0 20 20" fill="none"><rect x="2" y="12" width="3" height="6" rx="1" fill="white" opacity="0.5"/><rect x="7" y="7" width="3" height="11" rx="1" fill="white" opacity="0.75"/><rect x="12" y="2" width="3" height="16" rx="1" fill="white"/><path d="M3.5 11 L8.5 6 L13.5 1" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg></div>
            <span class="el-logo-text" style="font-size:16px">Edge<span class="el-logo-accent">Ledger</span></span>
          </div>
          <div class="el-modal__tabs">
            <button :class="['el-modal__tab', mode==='login'?'active':'']" @click="mode='login';error=''">Sign In</button>
            <button :class="['el-modal__tab', mode==='register'?'active':'']" @click="mode='register';error=''">Create Account</button>
          </div>

          <form v-if="mode==='login'" @submit.prevent="login" class="el-modal__form">
            <div class="el-field"><label class="el-label">Email</label><input v-model="form.email" type="email" class="el-input" placeholder="you@example.com" required autocomplete="username"/></div>
            <div class="el-field"><label class="el-label">Password</label><input v-model="form.password" type="password" class="el-input" placeholder="••••••••" required autocomplete="current-password"/></div>
            <div v-if="error" class="el-modal__error">{{ error }}</div>
            <button type="submit" class="el-btn el-btn--gold el-btn--full" :disabled="loading">{{ loading ? 'Signing in…' : 'Sign In' }}</button>
            <div class="el-modal__switch">No account? <button type="button" class="el-link" @click="mode='register';error=''">Create one free →</button></div>
          </form>

          <form v-if="mode==='register'" @submit.prevent="register" class="el-modal__form">
            <div class="el-modal__trial-pill">🎉 30 days free · No credit card needed</div>
            <div class="el-field"><label class="el-label">Full Name</label><input v-model="regForm.name" type="text" class="el-input" placeholder="Your name" required/></div>
            <div class="el-field"><label class="el-label">Email</label><input v-model="regForm.email" type="email" class="el-input" placeholder="you@example.com" required autocomplete="email"/></div>
            <div class="el-field"><label class="el-label">Password</label><input v-model="regForm.password" type="password" class="el-input" placeholder="Minimum 8 characters" required autocomplete="new-password"/></div>
            <div class="el-field"><label class="el-label">Confirm Password</label><input v-model="regForm.password_confirmation" type="password" class="el-input" placeholder="Repeat password" required/></div>
            <div class="el-field"><label class="el-label">Referral Code <span style="color:#4A5568;font-weight:400">(optional)</span></label><input v-model="regForm.referral_code" type="text" class="el-input" placeholder="Enter code if you have one"/></div>
            <div v-if="error" class="el-modal__error">{{ error }}</div>
            <button type="submit" class="el-btn el-btn--gold el-btn--full" :disabled="loading">{{ loading ? 'Creating account…' : 'Start 30-Day Free Trial' }}</button>
            <div class="el-modal__terms">After your free trial, EdgeLedger Pro is <strong style="color:#D4A017">$2/month</strong>. Cancel anytime.</div>
            <div class="el-modal__switch">Have an account? <button type="button" class="el-link" @click="mode='login';error=''">Sign in →</button></div>
          </form>
        </div>
      </div>
    </transition>

  </div>
</template>

<script>
export default {
  name: 'LoginView',
  data() {
    return {
      scrollY:   0,
      mobileNav: false,
      showModal: false,
      mode:      'login',
      loading:   false,
      error:     '',
      form:    { email: '', password: '' },
      regForm: { name: '', email: '', password: '', password_confirmation: '', referral_code: '' },
      quoteIdx: 0,
      quoteTimer: null,

      mockTrades: [
        { id:1, r:'win',  name:'OB + FVG Confluence', pair:'EURUSD', rn:2.8 },
        { id:2, r:'win',  name:'M1 TS Stop',          pair:'GBPUSD', rn:4.0 },
        { id:3, r:'loss', name:'Fake BOS Entry',       pair:'AUDUSD', rn:-1.0 },
        { id:4, r:'win',  name:'M15 Bullish OB',       pair:'EURUSD', rn:3.0 },
      ],

      tickerItems: [
        'HTF → MTF → Entry Cascade','Win Rate Per Setup','Pre-Trade Filter',
        'Visual Trade Database','Expectancy Engine','ICT / SMC Methodology',
        'Multi-Timeframe Analysis','50% Referral Commission','$2 / Month',
        'Journal & Database','30-Day Free Trial','Cancel Anytime',
      ],

      statCards: [
        { value:'3×',    label:'Timeframe Cascade',  sub:'HTF → MTF → Entry TF' },
        { value:'100%',  label:'Setup Specificity',  sub:'Every label tied to outcomes' },
        { value:'+0.87R',label:'Avg Expectancy',     sub:'Per documented trade' },
        { value:'50%',   label:'Referral Commission',sub:'Paid to your wallet' },
      ],

      analyticsBars: [
        { label:'Bullish BOS → OB → CHoCH', pct: 71 },
        { label:'Bearish OB → FVG → SSL',   pct: 58 },
        { label:'Bullish BOS → FVG → BPR',  pct: 44 },
        { label:'Bearish CHoCH → OB',        pct: 33 },
      ],

      steps: [
        { icon:'🗄️', title:'Record Your Trades', body:'Add every trade with HTF, MTF, and Entry TF classification. Upload chart screenshots. Mark whether you followed your rules.' },
        { icon:'🔍', title:'Filter Before You Trade', body:'Before entering, run the cascade filter. See your historical win rate for the exact market conditions you face right now.' },
        { icon:'📊', title:'Analyse Your Edge', body:'Deep analytics reveal which setups make money and which destroy it. Identify your strongest pairs, sessions, and confluences.' },
        { icon:'💰', title:'Grow Your Income', body:'Share your referral code and earn 50% of every subscription payment. Your trading knowledge becomes passive income.' },
      ],

      quotes: [
        { text: 'The goal of a successful trader is to make the best trades. Money is secondary.', attr: 'Alexander Elder' },
        { text: 'In trading, the impossible happens about twice a year.', attr: 'Henri M. Simoes' },
        { text: 'An investment in knowledge pays the best interest.', attr: 'Benjamin Franklin' },
        { text: 'The market is a device for transferring money from the impatient to the patient.', attr: 'Warren Buffett' },
        { text: 'Risk comes from not knowing what you are doing.', attr: 'Warren Buffett' },
        { text: 'Every battle is won or lost before it is ever fought.', attr: 'Sun Tzu' },
      ],

      pricingFeatures: [
        'Unlimited trade database entries',
        'HTF → MTF → Entry cascade filter',
        'Smart category suggestion engine',
        'Full win rate analytics by setup',
        'Pre-trade journal with promotion',
        'Visual 3-timeframe chart review',
        'Expectancy and streak analysis',
        'Referral programme — earn 50% commission',
        'Multi-user admin panel',
        'Priority support',
      ],

      values: [
        { icon:'🎯', title:'Precision Over Volume',  body:'One well-documented setup beats a hundred vague entries.' },
        { icon:'🔒', title:'Your Data, Your Edge',   body:'Every trade you record strengthens a database only you can access.' },
        { icon:'📐', title:'Built on Structure',     body:'HTF to entry — the same cascade a professional uses.' },
      ],
    }
  },

  mounted() {
    window.addEventListener('scroll', this.onScroll)
    this.quoteTimer = setInterval(() => {
      this.quoteIdx = (this.quoteIdx + 1) % this.quotes.length
    }, 5000)
    const ref = new URLSearchParams(window.location.search).get('ref')
    if (ref) { this.regForm.referral_code = ref; this.openModal('register') }
  },
  beforeDestroy() {
    window.removeEventListener('scroll', this.onScroll)
    clearInterval(this.quoteTimer)
  },

  methods: {
    onScroll() { this.scrollY = window.scrollY },
    scrollTo(id) {
      this.mobileNav = false
      document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
    },
    openModal(mode) { this.mode = mode; this.showModal = true; this.error = '' },

    particleStyle(i) {
      const seed = i * 137.508
      return {
        left:            ((seed * 13) % 100) + '%',
        top:             ((seed * 7)  % 100) + '%',
        animationDelay:  ((seed * 0.3) % 4)  + 's',
        animationDuration: (3 + (seed % 4)) + 's',
        width:  (2 + (i % 3)) + 'px',
        height: (2 + (i % 3)) + 'px',
      }
    },

    chartCandles(type) {
      const seeds = { h:[4,7,3,8,5,6,9,4,7,8], m:[3,5,8,4,7,9,5,8,6,7], e:[5,3,7,9,4,8,6,5,9,7] }
      return (seeds[type]||seeds.h).map((h,i) => ({
        i,
        style: {
          height:     h*4+'px',
          background: i > 5 ? '#1D9E75' : '#E24B4A',
          width:      '6px', borderRadius:'1px', margin:'0 1px',
          alignSelf:  'flex-end',
        }
      }))
    },

    async login() {
      this.loading = true; this.error = ''
      try {
        await this.$store.dispatch('auth/login', this.form)
        this.$router.push('/')
      } catch(e) { this.error = e.response?.data?.message || 'Invalid credentials.' }
      finally { this.loading = false }
    },
    async register() {
      this.loading = true; this.error = ''
      try {
        const { data } = await this.$http.post('/register', this.regForm)
        this.$store.commit('auth/SET_TOKEN', data.token)
        this.$store.commit('auth/SET_USER',  data.user)
        localStorage.setItem('el_token', data.token)
        localStorage.setItem('el_user',  JSON.stringify(data.user))
        this.$router.push('/')
      } catch(e) {
        const errs = e.response?.data?.errors
        this.error = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'Registration failed.')
      } finally { this.loading = false }
    },
  },
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap');

*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }

.el-land { font-family:'DM Sans',sans-serif; background:#06090E; color:#E2E8F0; min-height:100vh; overflow-x:hidden; }

/* ── TOKENS ─────────────────────────────────────────────────── */
:root { --gold:#D4A017; --green:#1D9E75; --red:#E24B4A; --dark:#06090E; --card:#0D1420; --border:rgba(255,255,255,.07); }

/* ── LOGO ────────────────────────────────────────────────────── */
.el-logo-icon { width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#1D9E75,#0F6E56);display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.el-logo-icon--sm { width:26px;height:26px;border-radius:6px; }
.el-logo-text { font-family:'Syne',sans-serif;font-weight:700;font-size:20px;color:#fff; }
.el-logo-accent { color:#D4A017; }

/* ── BUTTONS ──────────────────────────────────────────────────── */
.el-btn { display:inline-flex;align-items:center;gap:8px;padding:11px 24px;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;border:none;text-decoration:none;white-space:nowrap; }
.el-btn--gold { background:linear-gradient(135deg,#D4A017,#B8860B);color:#000;box-shadow:0 4px 20px rgba(212,160,23,.35); }
.el-btn--gold:hover:not(:disabled) { transform:translateY(-1px);box-shadow:0 8px 30px rgba(212,160,23,.5); }
.el-btn--gold:disabled { opacity:.6;cursor:not-allowed; }
.el-btn--ghost { background:transparent;border:1px solid rgba(255,255,255,.15);color:#94A3B8; }
.el-btn--ghost:hover { border-color:rgba(255,255,255,.3);color:#fff; }
.el-btn--outline { background:transparent;border:1px solid rgba(212,160,23,.4);color:#D4A017; }
.el-btn--outline:hover { background:rgba(212,160,23,.08);border-color:#D4A017; }
.el-btn--lg { padding:14px 32px;font-size:15px; }
.el-btn--full { width:100%;justify-content:center; }

/* ── NAV ──────────────────────────────────────────────────────── */
.el-nav { position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;gap:32px;padding:16px 48px;transition:all .3s; }
.el-nav.scrolled { background:rgba(6,9,14,.92);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:12px 48px; }
.el-nav__logo { display:flex;align-items:center;gap:10px; }
.el-nav__links { display:flex;gap:28px;margin-left:auto; }
.el-nav__links a { font-size:14px;color:#64748B;cursor:pointer;transition:color .15s;text-decoration:none; }
.el-nav__links a:hover { color:#fff; }
.el-nav__actions { display:flex;gap:10px; }
.el-nav__burger { display:none;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:4px; }
.el-nav__burger span { width:22px;height:2px;background:#94A3B8;border-radius:2px; }

.el-mobile-nav { display:none;position:fixed;top:60px;left:0;right:0;background:#0D1420;border-bottom:1px solid var(--border);padding:20px 24px;z-index:99;flex-direction:column;gap:12px; }
.el-mobile-nav.open { display:flex; }
.el-mobile-nav a { color:#94A3B8;font-size:15px;cursor:pointer;padding:8px 0;border-bottom:1px solid var(--border); }

/* ── HERO ─────────────────────────────────────────────────────── */
.el-hero { min-height:100vh;display:flex;align-items:center;gap:60px;padding:120px 48px 80px;position:relative;overflow:hidden; }
.el-hero__bg { position:absolute;inset:0;pointer-events:none; }
.el-hero__grid { position:absolute;inset:0;background-image:linear-gradient(rgba(29,158,117,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(29,158,117,.03) 1px,transparent 1px);background-size:60px 60px; }
.el-hero__glow { position:absolute;border-radius:50%;filter:blur(120px);opacity:.5; }
.el-hero__glow--1 { width:600px;height:600px;background:radial-gradient(circle,rgba(29,158,117,.15),transparent 70%);top:-100px;right:200px; }
.el-hero__glow--2 { width:400px;height:400px;background:radial-gradient(circle,rgba(212,160,23,.1),transparent 70%);bottom:0;left:0; }
.el-particle { position:absolute;background:rgba(29,158,117,.4);border-radius:50%;animation:float linear infinite; }
@keyframes float { 0%,100%{transform:translateY(0) scale(1);opacity:.4} 50%{transform:translateY(-20px) scale(1.2);opacity:.8} }

.el-hero__content { flex:1;position:relative;z-index:1;max-width:580px; }
.el-hero__badge { display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#D4A017;margin-bottom:24px;padding:7px 14px;border:1px solid rgba(212,160,23,.3);border-radius:100px;background:rgba(212,160,23,.06); }
.el-hero__badge-dot { width:6px;height:6px;background:#D4A017;border-radius:50%;animation:pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }
.el-hero__h1 { font-family:'Syne',sans-serif;font-size:76px;font-weight:800;line-height:1;letter-spacing:-3px;color:#fff;margin-bottom:24px; }
.el-hero__h1--grad { background:linear-gradient(135deg,#D4A017,#F5C842,#D4A017);-webkit-background-clip:text;-webkit-text-fill-color:transparent; }
.el-hero__sub { font-size:17px;line-height:1.75;color:#64748B;margin-bottom:36px;font-weight:300; }
.el-hero__actions { display:flex;gap:14px;margin-bottom:16px;flex-wrap:wrap; }
.el-hero__note { font-size:12px;color:#334155;margin-bottom:40px; }
.el-hero__stats { display:flex;align-items:center;gap:20px;padding:20px;background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:12px;backdrop-filter:blur(8px); }
.el-hero__stat { display:flex;flex-direction:column;gap:2px; }
.el-hero__stat-n { font-family:'Syne',sans-serif;font-size:13px;font-weight:700;color:#fff; }
.el-hero__stat-l { font-size:11px;color:#4A5568; }
.el-hero__stat-div { width:1px;height:28px;background:rgba(255,255,255,.08); }

/* ── HERO MOCK ────────────────────────────────────────────────── */
.el-hero__mock { flex:1;position:relative;z-index:1;max-width:520px; }
.el-mock-chrome { background:#0D1420;border:1px solid rgba(255,255,255,.1);border-radius:14px;overflow:hidden;box-shadow:0 40px 100px rgba(0,0,0,.7),0 0 0 1px rgba(255,255,255,.04); }
.el-mock-chrome__bar { display:flex;align-items:center;gap:6px;padding:12px 16px;background:#131C2E;border-bottom:1px solid rgba(255,255,255,.05); }
.el-mock-chrome__dots { display:flex;gap:6px; }
.el-mock-chrome__dots span { width:12px;height:12px;border-radius:50%;display:block; }
.el-mock-chrome__title { margin-left:8px;font-size:12px;color:#4A5568; }
.el-mock-body { padding:16px;display:flex;flex-direction:column;gap:12px; }

.el-mock-filter { background:#131C2E;border-radius:10px;padding:14px;border:1px solid rgba(255,255,255,.05); }
.el-mock-filter__row { display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap; }
.el-mock-filter__field { display:flex;flex-direction:column;gap:3px;flex:1;min-width:0; }
.el-mock-filter__label { font-size:9px;color:#4A5568;text-transform:uppercase;letter-spacing:.8px; }
.el-mock-filter__val { font-size:11px;font-weight:600;padding:4px 8px;border-radius:5px; }
.el-mock-filter__val--h { background:rgba(55,138,221,.15);color:#85B7EB; }
.el-mock-filter__val--m { background:rgba(127,119,221,.15);color:#AFA9EC;font-size:10px; }
.el-mock-filter__val--e { background:rgba(212,160,23,.15);color:#D4A017; }
.el-mock-result { display:flex;align-items:center;gap:12px;padding-top:10px;border-top:1px solid rgba(255,255,255,.05); }
.el-mock-result__wr { display:flex;flex-direction:column;align-items:center; }
.el-mock-result__pct { font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:#1D9E75; }
.el-mock-result__label { font-size:9px;color:#4A5568;text-transform:uppercase; }
.el-mock-result__bar { flex:1;height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden; }
.el-mock-result__fill { height:100%;background:linear-gradient(90deg,#1D9E75,#5DCAA5);border-radius:3px;transition:width 1s; }
.el-mock-result__trades { font-size:10px;color:#4A5568; }

.el-mock-trades { display:flex;flex-direction:column;gap:6px; }
.el-mock-trade { display:flex;align-items:center;gap:10px;padding:10px 12px;background:#131C2E;border-radius:8px;border-left:3px solid; }
.el-mock-trade--win  { border-color:#1D9E75; }
.el-mock-trade--loss { border-color:#E24B4A; }
.el-mock-trade__badge { font-size:9px;font-weight:700;letter-spacing:.5px;padding:2px 6px;border-radius:3px; }
.el-mock-trade--win  .el-mock-trade__badge { background:rgba(29,158,117,.2);color:#1D9E75; }
.el-mock-trade--loss .el-mock-trade__badge { background:rgba(226,75,74,.2);color:#E24B4A; }
.el-mock-trade__info { flex:1;display:flex;flex-direction:column; }
.el-mock-trade__name { font-size:11px;color:#E2E8F0;font-weight:500; }
.el-mock-trade__pair { font-size:10px;color:#4A5568;font-family:monospace; }
.el-mock-trade__r { font-family:'Syne',sans-serif;font-size:13px;font-weight:700; }
.el-mock-trade__r.pos { color:#1D9E75; }
.el-mock-trade__r.neg { color:#E24B4A; }

.el-float { position:absolute;background:rgba(13,20,32,.95);border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:12px 16px;backdrop-filter:blur(12px);display:flex;align-items:center;gap:10px;box-shadow:0 8px 32px rgba(0,0,0,.4); }
.el-float--1 { bottom:-20px;left:-20px;animation:floatAnim 4s ease-in-out infinite; }
.el-float--2 { top:-16px;right:-16px;flex-direction:column;text-align:center;animation:floatAnim 4s ease-in-out infinite .5s; }
@keyframes floatAnim { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
.el-float__icon { font-size:22px; }
.el-float__title { font-size:12px;font-weight:600;color:#fff; }
.el-float__sub { font-size:10px;color:#4A5568; }
.el-float__wr { font-family:'Syne',sans-serif;font-size:24px;font-weight:800;color:#1D9E75; }

/* ── TICKER ──────────────────────────────────────────────────── */
.el-ticker { overflow:hidden;background:#0D1420;border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:12px 0; }
.el-ticker__track { display:flex;width:max-content;animation:ticker 40s linear infinite; }
@keyframes ticker { to { transform:translateX(-50%) } }
.el-ticker__item { display:flex;align-items:center;gap:8px;padding:0 24px;font-size:12px;font-weight:500;color:#4A5568;white-space:nowrap; }
.el-ticker__dot { width:5px;height:5px;background:#D4A017;border-radius:50%;flex-shrink:0; }

/* ── STATS ───────────────────────────────────────────────────── */
.el-stats { padding:80px 48px; }
.el-stats__inner { max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:20px; }
.el-stat-card { background:#0D1420;border:1px solid var(--border);border-radius:14px;padding:32px 24px;text-align:center;transition:border-color .2s; }
.el-stat-card:hover { border-color:rgba(212,160,23,.3); }
.el-stat-card__n { font-family:'Syne',sans-serif;font-size:40px;font-weight:800;color:#D4A017;line-height:1; }
.el-stat-card__label { font-size:14px;font-weight:600;color:#fff;margin:8px 0 4px; }
.el-stat-card__sub { font-size:12px;color:#4A5568; }

/* ── SECTION SHARED ─────────────────────────────────────────── */
.el-section-label { font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:#D4A017;margin-bottom:14px;display:block; }
.el-section-h2 { font-family:'Syne',sans-serif;font-size:46px;font-weight:800;color:#fff;letter-spacing:-1.5px;line-height:1.1;margin-bottom:16px; }
.el-section-p { font-size:16px;color:#64748B;line-height:1.75;max-width:560px;margin-bottom:56px; }

/* ── FEATURES ────────────────────────────────────────────────── */
.el-features { padding:80px 48px; }
.el-features__inner { max-width:1200px;margin:0 auto; }
.el-feature-grid { display:grid;grid-template-columns:1fr 1fr;gap:20px; }
.el-feat { background:#0D1420;border:1px solid var(--border);border-radius:16px;padding:32px;transition:border-color .2s,transform .2s; }
.el-feat:hover { border-color:rgba(212,160,23,.25);transform:translateY(-2px); }
.el-feat--wide { grid-column:span 2; }
.el-feat__icon-wrap { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:16px; }
.el-feat__icon-wrap--green  { background:rgba(29,158,117,.15);border:1px solid rgba(29,158,117,.2); }
.el-feat__icon-wrap--blue   { background:rgba(55,138,221,.15);border:1px solid rgba(55,138,221,.2); }
.el-feat__icon-wrap--gold   { background:rgba(212,160,23,.15);border:1px solid rgba(212,160,23,.2); }
.el-feat__icon-wrap--purple { background:rgba(127,119,221,.15);border:1px solid rgba(127,119,221,.2); }
.el-feat__icon-wrap--teal   { background:rgba(29,158,117,.15);border:1px solid rgba(29,158,117,.2); }
.el-feat__icon-wrap--red    { background:rgba(226,75,74,.15);border:1px solid rgba(226,75,74,.2); }
.el-feat__title { font-family:'Syne',sans-serif;font-size:20px;font-weight:700;color:#fff;margin-bottom:10px; }
.el-feat__body  { font-size:14px;color:#64748B;line-height:1.7;margin-bottom:20px; }

.el-feat__mock-filter { display:flex;flex-direction:column;gap:6px;background:#131C2E;padding:14px;border-radius:10px; }
.el-feat__mock-row { display:flex;align-items:center;gap:6px;font-size:12px; }
.htf { color:#85B7EB;font-weight:600;font-size:11px; }
.mtf { color:#AFA9EC;font-weight:600;font-size:11px; }
.etf { color:#D4A017;font-weight:600;font-size:11px; }
.el-feat__mock-arrow { color:#4A5568;font-size:10px; }
.el-feat__mock-wr { margin-left:auto;font-weight:700;color:#1D9E75;font-size:12px; }
.el-feat__mock-wr.neg { color:#E24B4A; }

.el-feat__analytics { display:flex;flex-direction:column;gap:8px; }
.el-feat__bar { display:flex;align-items:center;gap:8px; }
.el-feat__bar-label { font-size:11px;color:#4A5568;width:180px;flex-shrink:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.el-feat__bar-track { flex:1;height:5px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden; }
.el-feat__bar-fill { height:100%;border-radius:3px;transition:width 1s; }
.el-feat__bar-pct { font-size:11px;font-weight:700;width:32px;text-align:right; }

.el-feat__journal { display:flex;flex-direction:column;gap:8px; }
.el-feat__journal-entry { background:#131C2E;border-radius:8px;padding:10px 12px;border-left:2px solid rgba(212,160,23,.4); }
.el-feat__journal-status { font-size:9px;font-weight:700;letter-spacing:.8px;margin-bottom:4px; }
.el-feat__journal-status--pre  { color:#4A5568; }
.el-feat__journal-status--done { color:#1D9E75; }
.el-feat__journal-text { font-size:12px;color:#94A3B8; }

.el-feat__chart-row { display:flex;gap:12px; }
.el-feat__chart-slot { flex:1;background:#131C2E;border-radius:10px;padding:12px;border:1px solid rgba(255,255,255,.05);display:flex;flex-direction:column;gap:8px; }
.el-feat__chart-slot--h { border-top:2px solid #85B7EB; }
.el-feat__chart-slot--m { border-top:2px solid #AFA9EC; }
.el-feat__chart-slot--e { border-top:2px solid #D4A017; }
.el-feat__chart-candles { display:flex;align-items:flex-end;height:60px;gap:2px; }
.el-feat__chart-label { font-size:10px;color:#4A5568;text-align:center; }
.el-feat__candle { border-radius:1px; }

.el-feat__referral { background:#131C2E;border-radius:10px;padding:14px;display:flex;flex-direction:column;gap:8px; }
.el-feat__ref-code { font-family:monospace;font-size:13px;color:#94A3B8;background:rgba(255,255,255,.04);padding:8px 12px;border-radius:6px; }
.el-feat__ref-stat { font-size:13px;color:#94A3B8; }
.green { color:#1D9E75;font-weight:700; }

.el-feat__expect { background:#131C2E;border-radius:10px;padding:16px;text-align:center; }
.el-feat__expect-main { font-family:'Syne',sans-serif;font-size:40px;font-weight:800;color:#1D9E75; }
.el-feat__expect-label { font-size:12px;color:#94A3B8;margin:4px 0; }
.el-feat__expect-formula { font-size:10px;color:#4A5568;font-family:monospace; }

/* ── HOW ─────────────────────────────────────────────────────── */
.el-how { padding:80px 48px;background:#0D1420;border-top:1px solid var(--border);border-bottom:1px solid var(--border); }
.el-how__inner { max-width:1100px;margin:0 auto; }
.el-steps { display:grid;grid-template-columns:repeat(4,1fr);gap:24px;position:relative; }
.el-steps::before { content:'';position:absolute;top:28px;left:calc(12.5% + 16px);right:calc(12.5% + 16px);height:1px;background:linear-gradient(90deg,transparent,rgba(212,160,23,.3),transparent); }
.el-step { display:flex;flex-direction:column;gap:12px;padding:28px;background:#06090E;border:1px solid var(--border);border-radius:14px; }
.el-step__num { font-family:'Syne',sans-serif;font-size:11px;font-weight:700;color:#D4A017;letter-spacing:1px; }
.el-step__icon { font-size:28px; }
.el-step__title { font-family:'Syne',sans-serif;font-size:17px;font-weight:700;color:#fff; }
.el-step__body { font-size:13px;color:#64748B;line-height:1.7; }

/* ── QUOTES ──────────────────────────────────────────────────── */
.el-quotes { padding:100px 48px;position:relative;overflow:hidden;text-align:center; }
.el-quotes__glow { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:600px;height:300px;background:radial-gradient(ellipse,rgba(212,160,23,.06),transparent 70%);pointer-events:none; }
.el-quotes__inner { max-width:800px;margin:0 auto;position:relative; }
.el-quote__mark { font-family:'Syne',sans-serif;font-size:80px;color:rgba(212,160,23,.3);line-height:.8;margin-bottom:16px; }
.el-quote__text { font-family:'Syne',sans-serif;font-size:26px;font-weight:600;color:#fff;line-height:1.5;margin-bottom:20px;letter-spacing:-.5px; }
.el-quote__attr { font-size:14px;color:#4A5568; }
.el-quote__dots { display:flex;gap:8px;justify-content:center;margin-top:28px; }
.el-quote__dot { width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.12);border:none;cursor:pointer;transition:all .2s; }
.el-quote__dot.active { background:#D4A017;width:24px;border-radius:4px; }
.el-quote-fade-enter-active,.el-quote-fade-leave-active { transition:all .5s; }
.el-quote-fade-enter,.el-quote-fade-leave-to { opacity:0;transform:translateY(12px); }

/* ── PRICING ─────────────────────────────────────────────────── */
.el-pricing { padding:80px 48px; }
.el-pricing__inner { max-width:560px;margin:0 auto;text-align:center; }
.el-price-card { background:#0D1420;border:1px solid rgba(212,160,23,.3);border-radius:20px;padding:36px;position:relative;box-shadow:0 0 60px rgba(212,160,23,.06); }
.el-price-card__badge { position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#D4A017,#B8860B);color:#000;font-size:11px;font-weight:700;padding:5px 16px;border-radius:100px;letter-spacing:.5px; }
.el-price-card__top { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;text-align:left; }
.el-price-card__name { font-family:'Syne',sans-serif;font-size:20px;font-weight:700;color:#fff; }
.el-price-card__tagline { font-size:13px;color:#4A5568;margin-top:4px; }
.el-price-card__price { display:flex;align-items:baseline;gap:2px;flex-shrink:0; }
.el-price-card__currency { font-size:20px;font-weight:700;color:#D4A017; }
.el-price-card__amount { font-family:'Syne',sans-serif;font-size:52px;font-weight:800;color:#D4A017;line-height:1; }
.el-price-card__per { font-size:14px;color:#4A5568; }
.el-price-card__trial { background:rgba(29,158,117,.08);border:1px solid rgba(29,158,117,.2);border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:10px;font-size:13px;color:#94A3B8;margin-bottom:24px;text-align:left; }
.el-price-card__trial-badge { background:rgba(29,158,117,.2);color:#1D9E75;font-size:12px;font-weight:700;padding:3px 10px;border-radius:100px;white-space:nowrap; }
.el-price-card__features { list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:28px;text-align:left; }
.el-price-card__features li { display:flex;align-items:center;gap:10px;font-size:14px;color:#94A3B8; }
.el-check { color:#1D9E75;font-weight:700;flex-shrink:0; }
.el-price-card__note { font-size:12px;color:#334155;margin-top:12px; }

/* ── ABOUT ───────────────────────────────────────────────────── */
.el-about { padding:80px 48px;background:#0D1420;border-top:1px solid var(--border); }
.el-about__inner { max-width:1100px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center; }
.el-about__body { font-size:15px;color:#64748B;line-height:1.8;margin-bottom:16px; }
.el-about__values { display:flex;flex-direction:column;gap:16px;margin-top:28px; }
.el-about__value { display:flex;align-items:flex-start;gap:14px; }
.el-about__value-icon { font-size:22px;flex-shrink:0;margin-top:2px; }
.el-about__value-title { font-size:14px;font-weight:700;color:#fff;margin-bottom:3px; }
.el-about__value-body { font-size:13px;color:#4A5568; }
.el-about__visual { display:flex;flex-direction:column;gap:16px; }
.el-about__card { background:#06090E;border:1px solid var(--border);border-radius:14px;padding:24px;transition:border-color .2s; }
.el-about__card:hover { border-color:rgba(212,160,23,.2); }
.el-about__card-label { font-size:11px;color:#4A5568;text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px; }
.el-about__card-stat,.el-about__card-setup { font-size:14px;color:#94A3B8;margin-bottom:4px; }
.el-about__card-r,.el-about__card-sub { font-size:12px;color:#4A5568; }
.el-about__card-big { font-family:'Syne',sans-serif;font-size:44px;font-weight:800; }

/* ── FINAL CTA ───────────────────────────────────────────────── */
.el-cta-final { padding:120px 48px;text-align:center;position:relative;overflow:hidden; }
.el-cta-final__glow { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:800px;height:500px;background:radial-gradient(ellipse,rgba(29,158,117,.08),transparent 70%);pointer-events:none; }
.el-cta-final__inner { max-width:720px;margin:0 auto;position:relative; }
.el-cta-final__badge { display:inline-block;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#D4A017;border:1px solid rgba(212,160,23,.3);padding:6px 16px;border-radius:100px;margin-bottom:24px; }
.el-cta-final__h2 { font-family:'Syne',sans-serif;font-size:52px;font-weight:800;color:#fff;letter-spacing:-2px;line-height:1.1;margin-bottom:16px; }
.el-cta-final__sub { font-size:16px;color:#64748B;line-height:1.7;margin-bottom:36px; }
.el-cta-final__actions { display:flex;gap:14px;justify-content:center;flex-wrap:wrap; }

/* ── FOOTER ──────────────────────────────────────────────────── */
.el-footer { background:#06090E;border-top:1px solid var(--border);padding:60px 48px 32px; }
.el-footer__inner { max-width:1100px;margin:0 auto;display:flex;gap:60px;margin-bottom:48px;flex-wrap:wrap; }
.el-footer__brand { max-width:280px; }
.el-footer__logo { display:flex;align-items:center;gap:10px;margin-bottom:12px; }
.el-footer__tagline { font-size:13px;color:#4A5568;margin-bottom:8px; }
.el-footer__contact a { font-size:13px;color:#4A5568;text-decoration:none; }
.el-footer__contact a:hover { color:#D4A017; }
.el-footer__links { display:flex;gap:48px;flex:1;flex-wrap:wrap; }
.el-footer__col { display:flex;flex-direction:column;gap:10px;min-width:120px; }
.el-footer__col-title { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#fff;margin-bottom:4px; }
.el-footer__col a { font-size:13px;color:#4A5568;cursor:pointer;text-decoration:none;transition:color .15s; }
.el-footer__col a:hover { color:#D4A017; }
.el-footer__bottom { max-width:1100px;margin:0 auto;padding-top:24px;border-top:1px solid var(--border);display:flex;justify-content:space-between;font-size:12px;color:#334155;flex-wrap:wrap;gap:8px; }

/* ── MODAL ───────────────────────────────────────────────────── */
.el-modal-overlay { position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.85);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;padding:24px; }
.el-modal { background:#0D1420;border:1px solid rgba(255,255,255,.1);border-radius:18px;padding:36px;width:100%;max-width:440px;position:relative;box-shadow:0 40px 100px rgba(0,0,0,.7); }
.el-modal__close { position:absolute;top:16px;right:16px;width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:#64748B;cursor:pointer;font-size:14px;transition:all .15s; }
.el-modal__close:hover { background:rgba(255,255,255,.1);color:#fff; }
.el-modal__logo { display:flex;align-items:center;gap:10px;margin-bottom:20px; }
.el-modal__tabs { display:flex;gap:0;margin-bottom:20px;background:#06090E;border-radius:8px;padding:3px; }
.el-modal__tab { flex:1;padding:8px;border-radius:6px;background:transparent;border:none;color:#64748B;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;cursor:pointer;transition:all .15s; }
.el-modal__tab.active { background:#131C2E;color:#fff; }
.el-modal__form { display:flex;flex-direction:column;gap:14px; }
.el-modal__trial-pill { background:rgba(29,158,117,.1);border:1px solid rgba(29,158,117,.25);border-radius:8px;padding:10px 14px;text-align:center;font-size:13px;color:#1D9E75;font-weight:600; }
.el-modal__error { background:rgba(226,75,74,.1);border:1px solid rgba(226,75,74,.3);border-radius:8px;padding:10px 14px;font-size:13px;color:#E24B4A; }
.el-modal__terms { font-size:11px;color:#334155;text-align:center;line-height:1.6; }
.el-modal__switch { font-size:13px;color:#4A5568;text-align:center; }
.el-field { display:flex;flex-direction:column;gap:5px; }
.el-label { font-size:12px;color:#64748B;font-weight:500; }
.el-input { background:#131C2E;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:10px 14px;color:#E2E8F0;font-size:14px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s; }
.el-input:focus { border-color:rgba(212,160,23,.4); }
.el-link { background:none;border:none;color:#D4A017;cursor:pointer;font-size:13px;font-family:'DM Sans',sans-serif;text-decoration:underline; }
.el-modal-fade-enter-active,.el-modal-fade-leave-active { transition:opacity .25s; }
.el-modal-fade-enter,.el-modal-fade-leave-to { opacity:0; }

/* ── RESPONSIVE ───────────────────────────────────────────────── */
@media(max-width:1024px) {
  .el-hero { flex-direction:column;padding:100px 24px 60px; }
  .el-hero__h1 { font-size:52px; }
  .el-hero__mock { width:100%; }
  .el-float { display:none; }
  .el-feature-grid,.el-about__inner { grid-template-columns:1fr; }
  .el-feat--wide { grid-column:span 1; }
  .el-stats__inner { grid-template-columns:repeat(2,1fr); }
  .el-steps { grid-template-columns:repeat(2,1fr); }
}
@media(max-width:768px) {
  .el-nav { padding:14px 20px; }
  .el-nav__links,.el-nav__actions { display:none; }
  .el-nav__burger { display:flex; }
  .el-hero { padding:90px 20px 60px; }
  .el-hero__h1 { font-size:42px;letter-spacing:-2px; }
  .el-section-h2 { font-size:34px; }
  .el-features,.el-how,.el-about,.el-pricing,.el-cta-final,.el-stats,.el-quotes { padding:60px 20px; }
  .el-steps { grid-template-columns:1fr; }
  .el-steps::before { display:none; }
  .el-stats__inner { grid-template-columns:1fr 1fr; }
  .el-cta-final__h2 { font-size:36px; }
  .el-footer__inner { flex-direction:column;gap:32px; }
  .el-footer__links { gap:28px; }
}
</style>