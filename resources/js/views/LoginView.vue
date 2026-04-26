<template>
  <div class="el-landing">

    <!-- ── NAV ── -->
    <nav class="el-nav">
      <div class="el-nav__logo">
        <span class="el-logo-mark">EL</span>
        <span class="el-logo-text">EdgeLedger</span>
      </div>
      <div class="el-nav__actions">
        <button class="el-nav__cta el-nav__cta--ghost" @click="openModal('login')">Sign In</button>
        <button class="el-nav__cta el-nav__cta--primary" @click="openModal('register')">Start Free Trial</button>
      </div>
    </nav>

    <!-- ── HERO ── -->
    <section class="el-hero">
      <div class="el-hero__grid-bg"></div>
      <div class="el-hero__glow"></div>
      <div class="el-hero__content">
        <div class="el-hero__eyebrow">Smart Money · ICT · Multi-Timeframe</div>
        <h1 class="el-hero__title">
          Your edge.<br/>
          <span class="el-hero__title--accent">Measured.</span>
        </h1>
        <p class="el-hero__sub">
          EdgeLedger turns your trading history into a precision decision engine.
          Filter setups by market structure, track win rates by timeframe cascade,
          and let the bot watch the charts while you focus on execution.
        </p>
        <div class="el-hero__actions">
          <a href="mailto:tamaeproductions@gmail.com?subject=EdgeLedger Support Request" class="el-btn-primary">
            Support Request
          </a>
          <button class="el-btn-ghost" @click="scrollTo('features')">See How It Works</button>
        </div>
        <div class="el-hero__stats">
          <div class="el-stat"><span class="el-stat__n">HTF → MTF → Entry</span><span class="el-stat__l">Cascade Filter</span></div>
          <div class="el-stat__sep"></div>
          <div class="el-stat"><span class="el-stat__n">Real-time</span><span class="el-stat__l">Structure Scanner</span></div>
          <div class="el-stat__sep"></div>
          <div class="el-stat"><span class="el-stat__n">Win Rate</span><span class="el-stat__l">Per Setup Combination</span></div>
        </div>
      </div>
      <!-- Hero mock dashboard -->
      <div class="el-hero__mock">
        <div class="el-mock-window">
          <div class="el-mock-bar">
            <span class="el-mock-dot" style="background:#ff5f57"></span>
            <span class="el-mock-dot" style="background:#febc2e"></span>
            <span class="el-mock-dot" style="background:#28c840"></span>
            <span class="el-mock-title">EdgeLedger — Trade Database</span>
          </div>
          <div class="el-mock-content">
            <!-- Mock trade cards -->
            <div class="el-mock-card el-mock-card--win">
              <div class="el-mock-card__header">
                <span class="el-badge el-badge--win">WIN</span>
                <span class="el-mock-card__title">OB + FVG Confluence</span>
                <span class="el-mock-card__pair">EURUSD</span>
              </div>
              <div class="el-mock-card__tfs">
                <span class="el-tf el-tf--h4">HTF: Bullish BOS</span>
                <span class="el-tf__arrow">→</span>
                <span class="el-tf el-tf--m15">MTF: Bullish OB</span>
                <span class="el-tf__arrow">→</span>
                <span class="el-tf el-tf--m1">Entry: M1 CHoCH</span>
              </div>
              <div class="el-mock-card__charts">
                <div class="el-mock-chart el-mock-chart--bull"></div>
                <div class="el-mock-chart el-mock-chart--bull2"></div>
                <div class="el-mock-chart el-mock-chart--entry"></div>
              </div>
              <div class="el-mock-card__footer">
                <span class="el-mock-r el-mock-r--pos">+2.8R</span>
                <span class="el-mock-pips el-mock-r--pos">+34 pips</span>
              </div>
            </div>
            <div class="el-mock-card el-mock-card--loss">
              <div class="el-mock-card__header">
                <span class="el-badge el-badge--loss">LOSS</span>
                <span class="el-mock-card__title">Bearish FVG Fill</span>
                <span class="el-mock-card__pair">GBPJPY</span>
              </div>
              <div class="el-mock-card__tfs">
                <span class="el-tf el-tf--h4">HTF: Bearish OB</span>
                <span class="el-tf__arrow">→</span>
                <span class="el-tf el-tf--m15">MTF: Bearish FVG</span>
                <span class="el-tf__arrow">→</span>
                <span class="el-tf el-tf--m1">Entry: SSL Sweep</span>
              </div>
              <div class="el-mock-card__footer">
                <span class="el-mock-r el-mock-r--neg">-1.0R</span>
                <span class="el-mock-pips el-mock-r--neg">-12 pips</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── HOW IT WORKS ── -->
    <section class="el-section" id="features">
      <div class="el-section__inner">
        <div class="el-section__label">The System</div>
        <h2 class="el-section__title">Built for structured traders</h2>
        <p class="el-section__sub">Every feature is designed around how ICT and SMC traders actually think about the market — not generic journaling.</p>

        <div class="el-features">

          <div class="el-feature">
            <div class="el-feature__icon">🔍</div>
            <h3 class="el-feature__title">Pre-Trade Filter</h3>
            <p class="el-feature__body">Before entering any trade, query your own historical database. See your win rate for the exact combination of HTF structure, MTF trigger, and entry pattern you are facing right now. The cascade filter narrows suggestions based on your actual trade history.</p>
            <div class="el-feature__mock">
              <div class="el-filter-mock">
                <div class="el-filter-row">
                  <span class="el-filter-label">HTF Structure</span>
                  <span class="el-filter-val el-filter-val--h4">Bullish BOS</span>
                </div>
                <div class="el-filter-row">
                  <span class="el-filter-label">MTF Trigger</span>
                  <span class="el-filter-val el-filter-val--m15">Bullish OB ↑ filtered by HTF</span>
                </div>
                <div class="el-filter-row">
                  <span class="el-filter-label">Entry TF</span>
                  <span class="el-filter-val el-filter-val--m1">M1 CHoCH</span>
                </div>
                <div class="el-filter-result">
                  <span class="el-filter-wr">71%</span>
                  <span class="el-filter-label">win rate · 34 trades</span>
                </div>
              </div>
            </div>
          </div>

          <div class="el-feature">
            <div class="el-feature__icon">🤖</div>
            <h3 class="el-feature__title">Live Structure Scanner</h3>
            <p class="el-feature__body">Identify your HTF bias, set it in EdgeLedger, and walk away. The Python bot polls your broker's live data every 15 minutes, detects MTF structure patterns, checks your win rate threshold, and fires a Telegram alert the moment conditions align.</p>
            <div class="el-feature__mock">
              <div class="el-alert-mock">
                <div class="el-alert-header">
                  <span class="el-alert-dot"></span>
                  <span>EdgeLedger Alert 🟢</span>
                </div>
                <div class="el-alert-row"><span>Pair</span><strong>EURUSD</strong></div>
                <div class="el-alert-row"><span>HTF bias</span><strong>Bullish BOS</strong></div>
                <div class="el-alert-row"><span>MTF match</span><strong style="color:#1D9E75">Bullish OB</strong></div>
                <div class="el-alert-row"><span>Win rate</span><strong style="color:#1D9E75">71% (34 trades)</strong></div>
                <div class="el-alert-footer">Check your Entry TF for confirmation</div>
              </div>
            </div>
          </div>

          <div class="el-feature">
            <div class="el-feature__icon">📊</div>
            <h3 class="el-feature__title">Deep Analytics</h3>
            <p class="el-feature__body">Win rate by every combination. Expectancy per setup. Win and loss streaks. Average R by pair and session. Separate statistics for trades where you followed your rules versus impulse trades — so your numbers reflect your actual edge, not your mistakes.</p>
            <div class="el-feature__mock">
              <div class="el-analytics-mock">
                <div class="el-analytics-row el-analytics-row--header">
                  <span>HTF</span><span>MTF</span><span>Win Rate</span>
                </div>
                <div class="el-analytics-row">
                  <span class="el-tf--h4">Bullish BOS</span>
                  <span class="el-tf--m15">Bullish OB</span>
                  <span><span class="el-wr el-wr--high">71%</span></span>
                </div>
                <div class="el-analytics-row">
                  <span class="el-tf--h4">Bullish BOS</span>
                  <span class="el-tf--m15">FVG Bullish</span>
                  <span><span class="el-wr el-wr--mid">58%</span></span>
                </div>
                <div class="el-analytics-row">
                  <span class="el-tf--h4">Bearish OB</span>
                  <span class="el-tf--m15">Bearish CHoCH</span>
                  <span><span class="el-wr el-wr--low">38%</span></span>
                </div>
              </div>
            </div>
          </div>

          <div class="el-feature">
            <div class="el-feature__icon">🧠</div>
            <h3 class="el-feature__title">ML Training Dataset</h3>
            <p class="el-feature__body">Every trade you add, every clean chart screenshot you upload becomes a labelled training example. Over time, EdgeLedger builds a personal machine learning model that recognises your exact setups — not generic patterns, but your version of Bullish BOS on your specific pairs.</p>
            <div class="el-feature__mock">
              <div class="el-ml-mock">
                <div class="el-ml-bar">
                  <div class="el-ml-bar__label">HTF clean images</div>
                  <div class="el-ml-bar__track"><div class="el-ml-bar__fill" style="width:68%"></div></div>
                  <div class="el-ml-bar__count">68 / 100</div>
                </div>
                <div class="el-ml-bar">
                  <div class="el-ml-bar__label">MTF clean images</div>
                  <div class="el-ml-bar__track"><div class="el-ml-bar__fill" style="width:54%"></div></div>
                  <div class="el-ml-bar__count">54 / 100</div>
                </div>
                <div class="el-ml-status">
                  <span class="el-ml-ready">✓ Ready to train first model</span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ── WHY ── -->
    <section class="el-why">
      <div class="el-why__inner">
        <div class="el-section__label">Why EdgeLedger</div>
        <h2 class="el-section__title">No other journal thinks like you do</h2>
        <div class="el-compare">
          <div class="el-compare__col el-compare__col--them">
            <div class="el-compare__head">Generic Journals</div>
            <div class="el-compare__item el-compare__item--no">No cascade filter — flat category lists</div>
            <div class="el-compare__item el-compare__item--no">No pre-trade win rate lookup</div>
            <div class="el-compare__item el-compare__item--no">No MTF scanner or live alerts</div>
            <div class="el-compare__item el-compare__item--no">No separation of rule-following vs impulse trades</div>
            <div class="el-compare__item el-compare__item--no">No ML training from your own screenshots</div>
            <div class="el-compare__item el-compare__item--no">Generic for all trading styles</div>
          </div>
          <div class="el-compare__col el-compare__col--us">
            <div class="el-compare__head el-compare__head--us">EdgeLedger</div>
            <div class="el-compare__item el-compare__item--yes">HTF → MTF → Entry cascade filter</div>
            <div class="el-compare__item el-compare__item--yes">Real-time win rate before every trade</div>
            <div class="el-compare__item el-compare__item--yes">Live Telegram alerts when structure matches</div>
            <div class="el-compare__item el-compare__item--yes">Clean stats that reflect your actual edge</div>
            <div class="el-compare__item el-compare__item--yes">Personal ML model trained on your charts</div>
            <div class="el-compare__item el-compare__item--yes">Built for ICT / SMC methodology</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── CTA ── -->
    <section class="el-cta">
      <div class="el-cta__glow"></div>
      <h2 class="el-cta__title">Start trading smarter today</h2>
      <p class="el-cta__sub">Create your free account and get <strong style="color:#1D9E75">30 days free</strong>. No credit card required.</p>
      <button class="el-btn-primary el-btn-primary--lg" @click="openModal('register')">
        Get Started Free — 30 Day Trial
      </button>
      <div class="el-cta__note">Already have an account? <button class="el-link" @click="openModal('login')">Sign in here</button></div>
    </section>

    <!-- ── FOOTER ── -->
    <footer class="el-footer">
      <span class="el-footer__logo">EL</span>
      <span class="el-footer__text">EdgeLedger Trading Intelligence · Built for structured traders</span>
    </footer>

    <!-- ── AUTH MODAL ── -->
    <transition name="el-modal-fade">
      <div v-if="showModal" class="el-modal-overlay" @click.self="showModal = false">
        <div class="el-modal">
          <button class="el-modal__close" @click="showModal = false">✕</button>
          <div class="el-modal__logo">
            <span class="el-logo-mark">EL</span>
            <span class="el-logo-text">EdgeLedger</span>
          </div>

          <!-- Tabs -->
          <div class="el-modal__tabs">
            <button :class="['el-modal__tab', mode==='login'?'active':'']" @click="mode='login'; error=''">Sign In</button>
            <button :class="['el-modal__tab', mode==='register'?'active':'']" @click="mode='register'; error=''">Create Account</button>
          </div>

          <!-- LOGIN -->
          <form v-if="mode==='login'" @submit.prevent="login" class="el-modal__form">
            <div class="el-field">
              <label class="el-label">Email</label>
              <input v-model="form.email" type="email" class="el-input" placeholder="you@example.com" required autocomplete="username" />
            </div>
            <div class="el-field">
              <label class="el-label">Password</label>
              <input v-model="form.password" type="password" class="el-input" placeholder="••••••••" required autocomplete="current-password" />
            </div>
            <div v-if="error" class="el-modal__error">{{ error }}</div>
            <button type="submit" class="el-modal__btn" :disabled="loading">
              {{ loading ? 'Signing in…' : 'Sign In' }}
            </button>
            <div class="el-modal__switch">
              No account yet? <button type="button" class="el-link" @click="mode='register'; error=''">Create one free</button>
            </div>
          </form>

          <!-- REGISTER -->
          <form v-if="mode==='register'" @submit.prevent="register" class="el-modal__form">
            <div class="el-modal__trial-badge">
              🎉 30 days free · No credit card required
            </div>
            <div class="el-field">
              <label class="el-label">Full Name</label>
              <input v-model="regForm.name" type="text" class="el-input" placeholder="Your name" required />
            </div>
            <div class="el-field">
              <label class="el-label">Email</label>
              <input v-model="regForm.email" type="email" class="el-input" placeholder="you@example.com" required autocomplete="email" />
            </div>
            <div class="el-field">
              <label class="el-label">Password</label>
              <input v-model="regForm.password" type="password" class="el-input" placeholder="Min 8 characters" required autocomplete="new-password" />
            </div>
            <div class="el-field">
              <label class="el-label">Confirm Password</label>
              <input v-model="regForm.password_confirmation" type="password" class="el-input" placeholder="Repeat password" required autocomplete="new-password" />
            </div>
            <div class="el-field">
              <label class="el-label">Referral Code <span style="color:#4A5568;font-weight:400">(optional)</span></label>
              <input v-model="regForm.referral_code" type="text" class="el-input" placeholder="Enter code if you have one" autocomplete="off" />
            </div>
            <div v-if="error" class="el-modal__error">{{ error }}</div>
            <button type="submit" class="el-modal__btn" :disabled="loading">
              {{ loading ? 'Creating account…' : 'Start 30-Day Free Trial' }}
            </button>
            <div class="el-modal__terms">
              By registering you agree to our terms. After your free trial, EdgeLedger Pro is just
              <strong style="color:#1D9E75">$2/month</strong> — cancel anytime.
            </div>
            <div class="el-modal__switch">
              Already have an account? <button type="button" class="el-link" @click="mode='login'; error=''">Sign in</button>
            </div>
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
      showModal: false,
      mode:      'login',  // 'login' | 'register'
      loading:   false,
      error:     '',
      form: { email: '', password: '' },
      regForm: { name: '', email: '', password: '', password_confirmation: '', referral_code: '' },
    }
  },
  mounted() {
    // Auto-open register modal if ?ref= is in URL (referral link)
    const ref = new URLSearchParams(window.location.search).get('ref')
    if (ref) {
      this.regForm.referral_code = ref
      this.openModal('register')
    }
  },
  methods: {
    scrollTo(id) {
      document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
    },
    openModal(mode) {
      this.mode      = mode
      this.showModal = true
      this.error     = ''
    },
    async login() {
      this.loading = true
      this.error   = ''
      try {
        await this.$store.dispatch('auth/login', this.form)
        this.$router.push('/')
      } catch(e) {
        this.error = e.response?.data?.message || 'Invalid credentials.'
      } finally { this.loading = false }
    },
    async register() {
      this.loading = true
      this.error   = ''
      try {
        const { data } = await this.$http.post('/register', this.regForm)
        // Store token and user just like login
        this.$store.commit('auth/SET_TOKEN', data.token)
        this.$store.commit('auth/SET_USER',  data.user)
        localStorage.setItem('el_token', data.token)
        localStorage.setItem('el_user',  JSON.stringify(data.user))
        this.$router.push('/')
      } catch(e) {
        const errs = e.response?.data?.errors
        if (errs) {
          this.error = Object.values(errs).flat().join(' ')
        } else {
          this.error = e.response?.data?.message || 'Registration failed.'
        }
      } finally { this.loading = false }
    },
  },
}
</script>

<style scoped>
/* ── Fonts ── */
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.el-landing {
  font-family: 'DM Sans', sans-serif;
  background: #080D13;
  color: #E2E8F0;
  min-height: 100vh;
  overflow-x: hidden;
}

/* ── NAV ── */
.el-nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 48px;
  background: rgba(8,13,19,0.85);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.el-nav__logo { display: flex; align-items: center; gap: 10px; }
.el-logo-mark {
  width: 34px; height: 34px; border-radius: 8px;
  background: linear-gradient(135deg, #1D9E75, #0F6E56);
  display: flex; align-items: center; justify-content: center;
  font-family: 'Syne', sans-serif; font-weight: 800; font-size: 13px; color: #fff;
}
.el-logo-text { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 18px; color: #fff; letter-spacing: -0.3px; }
.el-nav__cta { padding:8px 18px; border-radius:8px; font-family:'DM Sans',sans-serif; font-size:14px; font-weight:500; cursor:pointer; transition:all 0.2s; }
.el-nav__cta--ghost { background:transparent; border:1px solid rgba(255,255,255,.15); color:#94A3B8; }
.el-nav__cta--ghost:hover { border-color:rgba(255,255,255,.3); color:#fff; }
.el-nav__cta--primary { background:linear-gradient(135deg,#1D9E75,#0F6E56); color:#fff; border:none; box-shadow:0 2px 12px rgba(29,158,117,.3); }
.el-nav__cta--primary:hover { transform:translateY(-1px); }

/* ── HERO ── */
.el-hero {
  min-height: 100vh; display: flex; align-items: center;
  padding: 120px 48px 80px; gap: 60px;
  position: relative; overflow: hidden;
}
.el-hero__grid-bg {
  position: absolute; inset: 0; z-index: 0;
  background-image:
    linear-gradient(rgba(29,158,117,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(29,158,117,0.04) 1px, transparent 1px);
  background-size: 60px 60px;
}
.el-hero__glow {
  position: absolute; top: 10%; left: 30%; width: 600px; height: 600px;
  background: radial-gradient(ellipse, rgba(29,158,117,0.12) 0%, transparent 70%);
  pointer-events: none; z-index: 0;
}
.el-hero__content { flex: 1; position: relative; z-index: 1; max-width: 560px; }
.el-hero__eyebrow {
  display: inline-block; font-size: 12px; font-weight: 500; letter-spacing: 2px;
  text-transform: uppercase; color: #1D9E75; margin-bottom: 20px;
  padding: 6px 14px; border: 1px solid rgba(29,158,117,0.3); border-radius: 100px;
}
.el-hero__title {
  font-family: 'Syne', sans-serif; font-size: 72px; font-weight: 800;
  line-height: 1.0; letter-spacing: -2px; color: #fff; margin-bottom: 24px;
}
.el-hero__title--accent {
  background: linear-gradient(135deg, #1D9E75, #5DCAA5);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.el-hero__sub { font-size: 17px; line-height: 1.7; color: #94A3B8; margin-bottom: 36px; font-weight: 300; }
.el-hero__actions { display: flex; gap: 14px; margin-bottom: 48px; flex-wrap: wrap; }

.el-btn-primary {
  display: inline-block; padding: 14px 28px; border-radius: 10px; text-decoration: none;
  background: linear-gradient(135deg, #1D9E75, #0F6E56); color: #fff;
  font-family: 'DM Sans', sans-serif; font-size: 15px; font-weight: 500;
  border: none; cursor: pointer; transition: all 0.2s; letter-spacing: 0.2px;
  box-shadow: 0 4px 24px rgba(29,158,117,0.35);
}
.el-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 32px rgba(29,158,117,0.45); }
.el-btn-primary--lg { padding: 18px 36px; font-size: 16px; }

.el-btn-ghost {
  display: inline-block; padding: 14px 28px; border-radius: 10px;
  background: transparent; color: #CBD5E1; border: 1px solid rgba(255,255,255,0.12);
  font-family: 'DM Sans', sans-serif; font-size: 15px; font-weight: 500; cursor: pointer; transition: all 0.2s;
}
.el-btn-ghost:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.2); }

.el-hero__stats { display: flex; align-items: center; gap: 24px; }
.el-stat { display: flex; flex-direction: column; }
.el-stat__n { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; color: #fff; }
.el-stat__l { font-size: 12px; color: #64748B; margin-top: 2px; }
.el-stat__sep { width: 1px; height: 32px; background: rgba(255,255,255,0.1); }

/* ── HERO MOCK ── */
.el-hero__mock { flex: 1; position: relative; z-index: 1; max-width: 520px; }
.el-mock-window {
  background: #0F1923; border: 1px solid rgba(255,255,255,0.1); border-radius: 14px;
  overflow: hidden; box-shadow: 0 32px 80px rgba(0,0,0,0.6);
}
.el-mock-bar {
  display: flex; align-items: center; gap: 6px; padding: 12px 16px;
  background: #1A2633; border-bottom: 1px solid rgba(255,255,255,0.06);
}
.el-mock-dot { width: 12px; height: 12px; border-radius: 50%; }
.el-mock-title { margin-left: 10px; font-size: 12px; color: #4A5568; }
.el-mock-content { padding: 16px; display: flex; flex-direction: column; gap: 10px; }
.el-mock-card {
  background: #1A2633; border-radius: 10px; padding: 14px;
  border: 1px solid rgba(255,255,255,0.06);
}
.el-mock-card--win { border-left: 3px solid #1D9E75; }
.el-mock-card--loss { border-left: 3px solid #E24B4A; }
.el-mock-card__header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.el-mock-card__title { font-size: 13px; font-weight: 600; color: #E2E8F0; }
.el-mock-card__pair { font-size: 11px; color: #64748B; margin-left: auto; font-family: 'Syne', monospace; }
.el-badge { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 4px; letter-spacing: 0.5px; }
.el-badge--win { background: rgba(29,158,117,0.2); color: #1D9E75; }
.el-badge--loss { background: rgba(226,75,74,0.2); color: #E24B4A; }
.el-mock-card__tfs { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; margin-bottom: 8px; }
.el-tf { font-size: 10px; padding: 2px 6px; border-radius: 4px; }
.el-tf--h4  { background: rgba(55,138,221,0.15); color: #85B7EB; }
.el-tf--m15 { background: rgba(127,119,221,0.15); color: #AFA9EC; }
.el-tf--m1  { background: rgba(186,117,23,0.15); color: #EF9F27; }
.el-tf__arrow { font-size: 10px; color: #4A5568; }
.el-mock-card__charts { display: flex; gap: 6px; margin-bottom: 8px; }
.el-mock-chart {
  flex: 1; height: 48px; border-radius: 6px; background: #243447;
  overflow: hidden; position: relative;
}
.el-mock-chart::after {
  content: ''; position: absolute; inset: 0;
  background: repeating-linear-gradient(90deg, transparent, transparent 3px, rgba(29,158,117,0.15) 3px, rgba(29,158,117,0.15) 4px);
}
.el-mock-chart--bull::before {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 60%;
  background: linear-gradient(to top, rgba(29,158,117,0.25), transparent);
  clip-path: polygon(0 100%, 10% 80%, 20% 85%, 30% 65%, 40% 70%, 50% 45%, 60% 30%, 70% 20%, 80% 10%, 90% 15%, 100% 5%, 100% 100%);
}
.el-mock-chart--bull2::before {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 60%;
  background: linear-gradient(to top, rgba(29,158,117,0.2), transparent);
  clip-path: polygon(0 100%, 15% 70%, 30% 75%, 45% 50%, 60% 35%, 75% 15%, 90% 20%, 100% 5%, 100% 100%);
}
.el-mock-chart--entry::before {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 50%;
  background: linear-gradient(to top, rgba(29,158,117,0.3), transparent);
  clip-path: polygon(0 100%, 20% 90%, 40% 80%, 50% 30%, 60% 25%, 80% 10%, 100% 5%, 100% 100%);
}
.el-mock-card__footer { display: flex; gap: 12px; align-items: center; }
.el-mock-r { font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 700; }
.el-mock-pips { font-size: 12px; }
.el-mock-r--pos { color: #1D9E75; }
.el-mock-r--neg { color: #E24B4A; }

/* ── SECTIONS ── */
.el-section { padding: 100px 48px; }
.el-section__inner { max-width: 1200px; margin: 0 auto; }
.el-section__label {
  font-size: 12px; font-weight: 500; letter-spacing: 2px; text-transform: uppercase;
  color: #1D9E75; margin-bottom: 16px;
}
.el-section__title {
  font-family: 'Syne', sans-serif; font-size: 48px; font-weight: 800;
  color: #fff; letter-spacing: -1.5px; margin-bottom: 16px; line-height: 1.1;
}
.el-section__sub { font-size: 17px; color: #64748B; max-width: 600px; line-height: 1.7; margin-bottom: 64px; }

/* ── FEATURES ── */
.el-features { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.el-feature {
  background: #0F1923; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 16px; padding: 32px; transition: border-color 0.2s;
}
.el-feature:hover { border-color: rgba(29,158,117,0.3); }
.el-feature__icon { font-size: 28px; margin-bottom: 16px; }
.el-feature__title { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 12px; }
.el-feature__body { font-size: 14px; line-height: 1.7; color: #64748B; margin-bottom: 24px; }

/* ── Filter mock ── */
.el-filter-mock { background: #1A2633; border-radius: 10px; padding: 16px; border: 1px solid rgba(255,255,255,0.06); }
.el-filter-row { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
.el-filter-row:last-of-type { border-bottom: none; }
.el-filter-label { font-size: 12px; color: #4A5568; }
.el-filter-val { font-size: 12px; font-weight: 600; padding: 2px 8px; border-radius: 4px; }
.el-filter-val--h4  { background: rgba(55,138,221,0.15); color: #85B7EB; }
.el-filter-val--m15 { background: rgba(127,119,221,0.15); color: #AFA9EC; font-size: 11px; }
.el-filter-val--m1  { background: rgba(186,117,23,0.15); color: #EF9F27; }
.el-filter-result { display: flex; align-items: center; gap: 10px; padding-top: 12px; margin-top: 4px; border-top: 1px solid rgba(29,158,117,0.2); }
.el-filter-wr { font-family: 'Syne', sans-serif; font-size: 32px; font-weight: 800; color: #1D9E75; }

/* ── Alert mock ── */
.el-alert-mock { background: #1A2633; border-radius: 10px; padding: 14px; border: 1px solid rgba(29,158,117,0.2); font-size: 13px; }
.el-alert-header { display: flex; align-items: center; gap: 8px; font-weight: 700; color: #fff; margin-bottom: 10px; font-size: 14px; }
.el-alert-dot { width: 8px; height: 8px; border-radius: 50%; background: #1D9E75; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
.el-alert-row { display: flex; justify-content: space-between; padding: 4px 0; color: #64748B; border-bottom: 1px solid rgba(255,255,255,0.04); }
.el-alert-row:last-of-type { border-bottom: none; }
.el-alert-row strong { color: #E2E8F0; }
.el-alert-footer { font-size: 11px; color: #4A5568; margin-top: 8px; font-style: italic; }

/* ── Analytics mock ── */
.el-analytics-mock { background: #1A2633; border-radius: 10px; overflow: hidden; border: 1px solid rgba(255,255,255,0.06); }
.el-analytics-row { display: grid; grid-template-columns: 1fr 1fr 80px; padding: 8px 12px; gap: 8px; font-size: 12px; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.04); }
.el-analytics-row--header { background: #243447; color: #4A5568; font-weight: 600; font-size: 11px; }
.el-wr { font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
.el-wr--high { background: rgba(29,158,117,0.2); color: #1D9E75; }
.el-wr--mid  { background: rgba(186,117,23,0.2); color: #EF9F27; }
.el-wr--low  { background: rgba(226,75,74,0.2); color: #E24B4A; }

/* ── ML mock ── */
.el-ml-mock { background: #1A2633; border-radius: 10px; padding: 16px; border: 1px solid rgba(255,255,255,0.06); }
.el-ml-bar { margin-bottom: 12px; }
.el-ml-bar__label { font-size: 11px; color: #64748B; margin-bottom: 6px; }
.el-ml-bar__track { height: 6px; background: #243447; border-radius: 3px; overflow: hidden; }
.el-ml-bar__fill { height: 100%; background: linear-gradient(90deg, #1D9E75, #5DCAA5); border-radius: 3px; }
.el-ml-bar__count { font-size: 11px; color: #4A5568; margin-top: 4px; text-align: right; }
.el-ml-status { padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.06); }
.el-ml-ready { font-size: 12px; color: #1D9E75; font-weight: 600; }

/* ── WHY ── */
.el-why { background: #0A1018; padding: 100px 48px; border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05); }
.el-why__inner { max-width: 1000px; margin: 0 auto; }
.el-compare { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 48px; }
.el-compare__col { background: #0F1923; border-radius: 14px; overflow: hidden; border: 1px solid rgba(255,255,255,0.07); }
.el-compare__col--us { border-color: rgba(29,158,117,0.3); }
.el-compare__head { padding: 16px 20px; background: #1A2633; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 15px; color: #64748B; border-bottom: 1px solid rgba(255,255,255,0.06); }
.el-compare__head--us { color: #1D9E75; background: rgba(29,158,117,0.1); }
.el-compare__item { padding: 12px 20px; font-size: 14px; border-bottom: 1px solid rgba(255,255,255,0.04); display: flex; align-items: center; gap: 10px; }
.el-compare__item:last-child { border-bottom: none; }
.el-compare__item--no  { color: #4A5568; }
.el-compare__item--no::before  { content: '✕'; color: #E24B4A; font-weight: 700; font-size: 12px; flex-shrink: 0; }
.el-compare__item--yes { color: #CBD5E1; }
.el-compare__item--yes::before { content: '✓'; color: #1D9E75; font-weight: 700; font-size: 12px; flex-shrink: 0; }

/* ── CTA ── */
.el-cta {
  padding: 120px 48px; text-align: center; position: relative; overflow: hidden;
}
.el-cta__glow {
  position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
  width: 800px; height: 400px;
  background: radial-gradient(ellipse, rgba(29,158,117,0.1) 0%, transparent 70%);
  pointer-events: none;
}
.el-cta__title { font-family: 'Syne', sans-serif; font-size: 52px; font-weight: 800; color: #fff; letter-spacing: -1.5px; margin-bottom: 16px; position: relative; }
.el-cta__sub { font-size: 17px; color: #64748B; margin-bottom: 36px; position: relative; }
.el-cta__note { margin-top: 20px; font-size: 14px; color: #4A5568; position: relative; }
.el-link { background: none; border: none; color: #1D9E75; cursor: pointer; font-size: 14px; font-family: 'DM Sans', sans-serif; text-decoration: underline; }

/* ── FOOTER ── */
.el-footer {
  padding: 28px 48px; display: flex; align-items: center; gap: 14px;
  border-top: 1px solid rgba(255,255,255,0.06);
}
.el-footer__logo {
  width: 28px; height: 28px; border-radius: 6px;
  background: linear-gradient(135deg, #1D9E75, #0F6E56);
  display: flex; align-items: center; justify-content: center;
  font-family: 'Syne', sans-serif; font-weight: 800; font-size: 11px; color: #fff;
}
.el-footer__text { font-size: 13px; color: #334155; }

/* ── MODAL ── */
.el-modal-overlay {
  position: fixed; inset: 0; z-index: 200;
  background: rgba(0,0,0,0.8); backdrop-filter: blur(6px);
  display: flex; align-items: center; justify-content: center; padding: 24px;
}
.el-modal {
  background: #0F1923; border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px; padding: 40px; width: 100%; max-width: 420px;
  position: relative; box-shadow: 0 32px 80px rgba(0,0,0,0.6);
}
.el-modal__close {
  position: absolute; top: 16px; right: 16px; width: 32px; height: 32px;
  border-radius: 50%; background: rgba(255,255,255,0.07); border: none;
  color: #64748B; cursor: pointer; font-size: 14px; transition: background 0.2s;
}
.el-modal__close:hover { background: rgba(255,255,255,0.12); }
.el-modal__logo { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; }
.el-modal__title  { font-family:'Syne',sans-serif; font-size:22px; font-weight:700; color:#fff; margin-bottom:16px; }
.el-modal__tabs   { display:flex; gap:0; margin-bottom:20px; background:#0F1923; border-radius:8px; padding:3px; }
.el-modal__tab    { flex:1; padding:8px; border-radius:6px; background:transparent; border:none; color:#64748B; font-family:'DM Sans',sans-serif; font-size:14px; font-weight:500; cursor:pointer; transition:all .15s; }
.el-modal__tab.active { background:#1A2633; color:#fff; }
.el-modal__trial-badge { background:rgba(29,158,117,.1); border:1px solid rgba(29,158,117,.3); border-radius:8px; padding:10px 14px; text-align:center; font-size:13px; color:#1D9E75; font-weight:600; margin-bottom:4px; }
.el-modal__switch { font-size:13px; color:#4A5568; text-align:center; }
.el-modal__terms  { font-size:11px; color:#334155; text-align:center; line-height:1.6; }
.el-modal__access { font-size:13px; color:#4A5568; text-align:center; }
.el-modal__access a { color:#1D9E75; text-decoration:none; }
.el-modal__access a:hover { text-decoration:underline; }.el-modal__form { display: flex; flex-direction: column; gap: 16px; }
.el-field { display: flex; flex-direction: column; gap: 6px; }
.el-label { font-size: 13px; color: #64748B; font-weight: 500; }
.el-input {
  background: #1A2633; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;
  padding: 11px 14px; color: #E2E8F0; font-size: 14px; font-family: 'DM Sans', sans-serif;
  outline: none; transition: border-color 0.2s;
}
.el-input:focus { border-color: #1D9E75; }
.el-modal__error { background: rgba(226,75,74,0.1); border: 1px solid rgba(226,75,74,0.3); border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #E24B4A; }
.el-modal__btn {
  background: linear-gradient(135deg, #1D9E75, #0F6E56); color: #fff; border: none;
  padding: 13px; border-radius: 10px; font-family: 'DM Sans', sans-serif;
  font-size: 15px; font-weight: 500; cursor: pointer; transition: all 0.2s;
  box-shadow: 0 4px 16px rgba(29,158,117,0.3);
}
.el-modal__btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(29,158,117,0.4); }
.el-modal__btn:disabled { opacity: 0.6; cursor: not-allowed; }
.el-modal__access { font-size: 13px; color: #4A5568; text-align: center; }
.el-modal__access a { color: #1D9E75; text-decoration: none; }
.el-modal__access a:hover { text-decoration: underline; }

/* ── Modal transition ── */
.el-modal-fade-enter-active, .el-modal-fade-leave-active { transition: opacity 0.2s; }
.el-modal-fade-enter, .el-modal-fade-leave-to { opacity: 0; }

/* ── Responsive ── */
@media (max-width: 900px) {
  .el-hero { flex-direction: column; padding: 100px 24px 60px; }
  .el-hero__title { font-size: 48px; }
  .el-hero__mock { width: 100%; }
  .el-features { grid-template-columns: 1fr; }
  .el-compare { grid-template-columns: 1fr; }
  .el-section, .el-why, .el-cta { padding: 60px 24px; }
  .el-nav { padding: 16px 24px; }
  .el-section__title, .el-cta__title { font-size: 36px; }
}
</style>