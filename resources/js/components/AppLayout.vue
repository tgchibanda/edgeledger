<template>
  <div class="app-wrap" :class="{ 'has-banner': !!trialBanner }">

    <!-- ── Trial / Expired Banner ── -->
    <div v-if="trialBanner" class="trial-banner" :class="trialBanner.type">
      <span class="trial-banner__msg">{{ trialBanner.message }}</span>
      <router-link to="/subscription" class="trial-banner__btn">{{ trialBanner.cta }}</router-link>
    </div>

    <!-- ── Mobile top bar ── -->
    <div class="mobile-topbar">
      <div class="mobile-topbar__logo">
        <div class="logo-icon">
          <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
            <rect x="2" y="12" width="3" height="6" rx="1" fill="white" opacity="0.5"/>
            <rect x="7" y="7"  width="3" height="11" rx="1" fill="white" opacity="0.75"/>
            <rect x="12" y="2" width="3" height="16" rx="1" fill="white"/>
            <path d="M3.5 11 L8.5 6 L13.5 1" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </div>
        <span class="logo-text">Edge<span class="logo-accent">Ledger</span></span>
      </div>
      <button class="mobile-menu-btn" @click="mobileOpen = !mobileOpen" aria-label="Toggle menu">
        <svg v-if="!mobileOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <!-- ── Mobile overlay ── -->
    <div v-if="mobileOpen" class="mobile-overlay" @click="mobileOpen = false" style="pointer-events:all;opacity:1;display:block"></div>

    <div class="app-body">

      <!-- ── SIDEBAR ── -->
      <aside class="sidebar" :class="{ 'sidebar--open': mobileOpen }">
        <!-- Logo -->
        <div class="sidebar__logo">
          <div class="logo-icon">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
              <rect x="2" y="12" width="3" height="6" rx="1" fill="white" opacity="0.5"/>
              <rect x="7" y="7"  width="3" height="11" rx="1" fill="white" opacity="0.75"/>
              <rect x="12" y="2" width="3" height="16" rx="1" fill="white"/>
              <path d="M3.5 11 L8.5 6 L13.5 1" stroke="white" stroke-width="1.5" stroke-linecap="round" opacity="0.9"/>
            </svg>
          </div>
          <div>
            <div class="logo-text">Edge<span class="logo-accent">Ledger</span></div>
            <div class="logo-sub">Trading Intelligence</div>
          </div>
        </div>

        <!-- Nav -->
        <nav class="sidebar__nav">
          <!-- Free items — always accessible -->
          <router-link v-for="item in freeItems" :key="item.to"
            :to="item.to" custom v-slot="{ isActive, navigate }">
            <div :class="['nav-item', isActive ? 'nav-item--active' : '']"
              @click="navigate($event); mobileOpen=false">
              <span class="nav-item__icon">{{ item.icon }}</span>
              <span class="nav-item__label">{{ item.label }}</span>
            </div>
          </router-link>

          <!-- Locked items — require active access -->
          <div class="nav-section-label">Trading Tools</div>
          <div v-for="item in lockedItems" :key="item.to">
            <!-- Has access — show normally -->
            <router-link v-if="hasAccess" :to="item.to" custom v-slot="{ isActive, navigate }">
              <div :class="['nav-item', isActive ? 'nav-item--active' : '']"
                @click="navigate($event); mobileOpen=false">
                <span class="nav-item__icon">{{ item.icon }}</span>
                <span class="nav-item__label">{{ item.label }}</span>
              </div>
            </router-link>
            <!-- No access — show locked -->
            <router-link v-else to="/subscription" custom v-slot="{ navigate }">
              <div class="nav-item nav-item--locked"
                @click="navigate($event); mobileOpen=false"
                :title="'Subscribe to access ' + item.label">
                <span class="nav-item__icon">{{ item.icon }}</span>
                <span class="nav-item__label">{{ item.label }}</span>
                <span class="nav-item__lock">🔒</span>
              </div>
            </router-link>
          </div>

          <div class="nav-divider"></div>

          <!-- Account items -->
          <router-link v-for="item in accountItems" :key="item.to"
            :to="item.to" custom v-slot="{ isActive, navigate }">
            <div :class="['nav-item', isActive ? 'nav-item--active' : '']"
              @click="navigate($event); mobileOpen=false">
              <span class="nav-item__icon">{{ item.icon }}</span>
              <span class="nav-item__label">{{ item.label }}</span>
            </div>
          </router-link>

          <!-- Admin -->
          <template v-if="isSuperuser">
            <div class="nav-section-label">Admin</div>
            <router-link to="/admin/users" custom v-slot="{ isActive, navigate }">
              <div :class="['nav-item', isActive ? 'nav-item--active' : '']" @click="navigate($event); mobileOpen=false">
                <span class="nav-item__icon">👥</span>
                <span class="nav-item__label">User Management</span>
              </div>
            </router-link>
            <router-link to="/admin/revenue" custom v-slot="{ isActive, navigate }">
              <div :class="['nav-item', isActive ? 'nav-item--active' : '']" @click="navigate($event); mobileOpen=false">
                <span class="nav-item__icon">💰</span>
                <span class="nav-item__label">Revenue</span>
              </div>
            </router-link>
          </template>
        </nav>

        <!-- User profile at bottom -->
        <div class="sidebar__user">
          <div class="user-avatar">{{ initials }}</div>
          <div class="user-info">
            <div class="user-name">{{ user && user.name }}</div>
            <div class="user-role">
              <span v-if="user && user.on_trial" class="user-trial">Trial • {{ user.trial_days_left }}d left</span>
              <span v-else class="user-role-label">{{ user && user.role }}</span>
            </div>
          </div>
          <button @click="logout" class="logout-btn" title="Sign out" aria-label="Sign out">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </div>
      </aside>

      <!-- ── MAIN CONTENT ── -->
      <main class="main-content">
        <!-- Expired blocker (full page overlay for locked pages) -->
        <div v-if="showBlocker" class="access-blocker">
          <div class="access-blocker__card">
            <div class="access-blocker__icon">🔒</div>
            <h2 class="access-blocker__title">Subscription Required</h2>
            <p class="access-blocker__body">Your free trial has ended. Subscribe to access your trade database, filter, and journal.</p>
            <router-link to="/subscription" class="access-blocker__btn">View Subscription Plans</router-link>
            <router-link to="/" class="access-blocker__link">← Back to Dashboard</router-link>
          </div>
        </div>
        <slot v-else />
      </main>

    </div>
  </div>
</template>

<script>
export default {
  name: 'AppLayout',
  data() {
    return { mobileOpen: false }
  },
  computed: {
    user()        { return this.$store.state.auth.user },
    isSuperuser() { return this.$store.getters['auth/isSuperuser'] },
    initials()    { return (this.user?.name||'U').split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2) },

    hasAccess() {
      const u = this.user
      if (!u) return false
      if (u.role === 'superuser') return true
      if (u.on_trial) return true
      if (u.subscription?.status === 'active') return true
      return false
    },

    // Always accessible — dashboard, analytics, categories, pairs, account
    freeItems() {
      return [
        { to: '/',           label: 'Dashboard',  icon: '📊' },
        { to: '/analytics',  label: 'Analytics',  icon: '📈' },
        { to: '/categories', label: 'Categories', icon: '🏷️' },
        { to: '/pairs',      label: 'Pairs',       icon: '💱' },
      ]
    },

    // Require active trial or subscription
    lockedItems() {
      return [
        { to: '/filter',   label: 'Pre-Trade Filter', icon: '🔍' },
        { to: '/database', label: 'Trade Database',   icon: '🗄️' },
        { to: '/journal',  label: 'Journal',          icon: '📓' },
      ]
    },

    accountItems() {
      return [
        { to: '/subscription', label: 'Subscription', icon: '💳' },
        { to: '/referral',     label: 'Earn & Refer',  icon: '🤝' },
      ]
    },

    // Show full-page blocker when user is on a locked route without access
    showBlocker() {
      if (this.hasAccess) return false
      if (this.isSuperuser) return false
      const locked = ['/filter', '/database', '/journal']
      return locked.some(p => this.$route.path.startsWith(p))
    },

    trialBanner() {
      const u = this.user
      if (!u || u.role === 'superuser') return null
      const hasSub   = u.subscription?.status === 'active'
      if (hasSub) return null
      const daysLeft = u.trial_days_left ?? 0
      if (u.on_trial && daysLeft <= 7 && daysLeft > 0) {
        return { type: 'warning', message: `⏳ Trial ends in ${daysLeft} day${daysLeft===1?'':'s'}.`, cta: 'Subscribe' }
      }
      if (!this.hasAccess) {
        return { type: 'expired', message: '🔒 Trial ended. Subscribe to continue.', cta: 'Subscribe now' }
      }
      return null
    },
  },
  methods: {
    async logout() {
      await this.$store.dispatch('auth/logout')
      this.$router.push('/login')
    },
  },
}
</script>

<style scoped>
/* ── Layout ─────────────────────────────────────────────────────── */
.app-wrap {
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
  background: #0F1923;
}
.app-wrap.has-banner { padding-top: 36px; }

.app-body {
  display: flex;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

/* ── Trial Banner ────────────────────────────────────────────────── */
.trial-banner {
  position: fixed; top: 0; left: 0; right: 0; z-index: 300;
  height: 36px; display: flex; align-items: center; justify-content: center; gap: 14px;
  font-size: 13px; font-weight: 500;
}
.trial-banner.warning { background: rgba(186,117,23,.95); color: #fff; }
.trial-banner.expired { background: rgba(226,75,74,.97); color: #fff; }
.trial-banner__msg { font-size: 13px; }
.trial-banner__btn {
  background: rgba(255,255,255,.2); color: #fff; text-decoration: none;
  padding: 3px 12px; border-radius: 4px; font-size: 12px; font-weight: 700;
  transition: background .15s;
}
.trial-banner__btn:hover { background: rgba(255,255,255,.3); }

/* ── Mobile Top Bar (hidden on desktop) ──────────────────────────── */
.mobile-topbar {
  display: none;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: #1A2633;
  border-bottom: 1px solid rgba(255,255,255,.06);
  position: sticky;
  top: 0;
  z-index: 50;
  flex-shrink: 0;
}
.mobile-topbar__logo { display: flex; align-items: center; gap: 8px; }
.mobile-menu-btn {
  background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px;
  display: flex; align-items: center; justify-content: center;
}

/* ── Mobile Overlay ──────────────────────────────────────────────── */
.mobile-overlay {
  display: none;
  position: fixed; inset: 0; z-index: 89;
  background: rgba(0,0,0,.6);
  backdrop-filter: blur(2px);
}

/* ── Sidebar ─────────────────────────────────────────────────────── */
.sidebar {
  width: 240px;
  flex-shrink: 0;
  background: #1A2633;
  border-right: 1px solid rgba(255,255,255,.06);
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  transition: transform .25s ease;
}
.sidebar__logo {
  display: flex; align-items: center; gap: 12px;
  padding: 18px 16px;
  border-bottom: 1px solid rgba(255,255,255,.06);
  flex-shrink: 0;
}

/* ── Logo ────────────────────────────────────────────────────────── */
.logo-icon {
  width: 32px; height: 32px; border-radius: 8px;
  background: linear-gradient(135deg, #1D9E75, #0F6E56);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.logo-text { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 17px; color: #fff; line-height: 1.1; }
.logo-accent { color: #D4A017; }
.logo-sub { font-size: 11px; color: #4A5568; margin-top: 1px; }

/* ── Nav ─────────────────────────────────────────────────────────── */
.sidebar__nav { flex: 1; padding: 10px 8px; overflow-y: auto; }
.nav-section-label {
  font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
  color: #4A5568; padding: 12px 10px 4px;
}
.nav-divider { height: 1px; background: rgba(255,255,255,.05); margin: 8px 4px; }

.nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 10px; border-radius: 8px;
  cursor: pointer; transition: all .15s;
  margin-bottom: 1px;
  text-decoration: none;
}
.nav-item:hover { background: rgba(255,255,255,.05); }
.nav-item--active { background: rgba(29,158,117,.12) !important; }
.nav-item--active .nav-item__label { color: #1D9E75; font-weight: 600; }
.nav-item--locked { opacity: .5; cursor: pointer; }
.nav-item--locked:hover { opacity: .7; background: rgba(226,75,74,.08); }
.nav-item__icon  { width: 20px; text-align: center; font-size: 15px; flex-shrink: 0; }
.nav-item__label { font-size: 13px; color: #94A3B8; flex: 1; }
.nav-item__lock  { font-size: 11px; }

/* ── User footer ─────────────────────────────────────────────────── */
.sidebar__user {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 12px;
  border-top: 1px solid rgba(255,255,255,.06);
  flex-shrink: 0;
}
.user-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: rgba(29,158,117,.2); border: 1px solid rgba(29,158,117,.3);
  display: flex; align-items: center; justify-content: center;
  color: #1D9E75; font-size: 12px; font-weight: 700; flex-shrink: 0;
}
.user-info { flex: 1; min-width: 0; }
.user-name  { font-size: 13px; color: #fff; font-weight: 500; truncate: true; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.user-role-label { font-size: 11px; color: #4A5568; text-transform: capitalize; }
.user-trial { font-size: 11px; color: #D4A017; }
.logout-btn { background: none; border: none; color: #4A5568; cursor: pointer; padding: 4px; transition: color .15s; display: flex; }
.logout-btn:hover { color: #E24B4A; }

/* ── Main content ────────────────────────────────────────────────── */
.main-content {
  flex: 1;
  overflow-y: auto;
  background: #0F1923;
  min-width: 0;
  position: relative;
}

/* ── Access blocker ──────────────────────────────────────────────── */
.access-blocker {
  position: absolute; inset: 0; display: flex;
  align-items: center; justify-content: center;
  padding: 24px;
  background: #0F1923;
}
.access-blocker__card {
  background: #1A2633; border: 1px solid rgba(255,255,255,.08);
  border-radius: 16px; padding: 40px 32px; max-width: 440px;
  width: 100%; text-align: center;
}
.access-blocker__icon  { font-size: 40px; margin-bottom: 16px; }
.access-blocker__title { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 700; color: #fff; margin-bottom: 12px; }
.access-blocker__body  { font-size: 14px; color: #64748B; line-height: 1.7; margin-bottom: 24px; }
.access-blocker__btn {
  display: block; background: linear-gradient(135deg,#D4A017,#B8860B);
  color: #000; font-weight: 700; font-size: 14px; padding: 12px 24px;
  border-radius: 8px; text-decoration: none; margin-bottom: 12px;
  transition: opacity .2s;
}
.access-blocker__btn:hover { opacity: .88; }
.access-blocker__link { font-size: 13px; color: #4A5568; text-decoration: none; }
.access-blocker__link:hover { color: #94A3B8; }

/* ── RESPONSIVE ──────────────────────────────────────────────────── */
@media (max-width: 768px) {
  .mobile-topbar { display: flex; }
  .mobile-overlay { display: block; pointer-events: none; opacity: 0; transition: opacity .25s; }
  .mobile-overlay.visible { pointer-events: all; opacity: 1; }

  .app-body { flex-direction: column; }

  .sidebar {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 90;
    transform: translateX(-100%);
    width: 260px;
    box-shadow: 4px 0 32px rgba(0,0,0,.5);
  }
  .sidebar--open { transform: translateX(0); }

  .main-content { flex: 1; min-height: 0; }
  .app-wrap.has-banner .mobile-topbar { top: 36px; }

  /* Make pages scroll better on mobile */
  :global(.p-6) { padding: 16px !important; }
  :global(.max-w-2xl), :global(.max-w-3xl), :global(.max-w-4xl) { max-width: 100% !important; }
  :global(.grid-cols-2) { grid-template-columns: 1fr !important; }
  :global(.grid-cols-3) { grid-template-columns: 1fr !important; }
  :global(.grid-cols-4) { grid-template-columns: 1fr 1fr !important; }
}

@media (max-width: 480px) {
  .trial-banner { font-size: 11px; gap: 8px; padding: 0 8px; }
  .trial-banner__msg { display: none; }
}
</style>