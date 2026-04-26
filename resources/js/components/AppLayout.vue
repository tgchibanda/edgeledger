<template>
  <div class="flex h-screen overflow-hidden">

    <!-- ── Trial / subscription expired banner ── -->
    <div v-if="trialBanner" class="trial-banner" :class="trialBanner.type">
      <span>{{ trialBanner.message }}</span>
      <router-link to="/subscription" class="trial-banner__btn">{{ trialBanner.cta }}</router-link>
    </div>

    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 bg-card border-r border-border flex flex-col" :style="trialBanner ? 'margin-top:36px' : ''"  >
      <div class="px-5 py-5 border-b border-border">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-win flex items-center justify-center flex-shrink-0">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
              <rect x="2"  y="12" width="3" height="6" rx="1" fill="white" opacity="0.5"/>
              <rect x="7"  y="7"  width="3" height="11" rx="1" fill="white" opacity="0.75"/>
              <rect x="12" y="2"  width="3" height="16" rx="1" fill="white"/>
              <path d="M3.5 11 L8.5 6 L13.5 1" stroke="white" stroke-width="1.5" stroke-linecap="round" opacity="0.9"/>
            </svg>
          </div>
          <div>
            <div class="text-white font-bold text-base leading-none">Edge<span class="text-win">Ledger</span></div>
            <div class="text-gray-500 text-xs mt-0.5">Trading Intelligence</div>
          </div>
        </div>
      </div>

      <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <router-link v-for="item in navItems" :key="item.to" :to="item.to" custom v-slot="{ isActive, navigate }">
          <div :class="isActive ? 'nav-link-active' : 'nav-link'" @click="navigate">
            <span class="w-5 text-center text-base">{{ item.icon }}</span>
            <span class="text-sm">{{ item.label }}</span>
          </div>
        </router-link>

        <div class="border-t border-border my-3"></div>

        <router-link v-if="isSuperuser" to="/admin/users" custom v-slot="{ isActive, navigate }">
          <div :class="isActive ? 'nav-link-active' : 'nav-link'" @click="navigate">
            <span class="w-5 text-center text-base">👥</span>
            <span class="text-sm">User Management</span>
          </div>
        </router-link>
        <router-link v-if="isSuperuser" to="/admin/revenue" custom v-slot="{ isActive, navigate }">
          <div :class="isActive ? 'nav-link-active' : 'nav-link'" @click="navigate">
            <span class="w-5 text-center text-base">💰</span>
            <span class="text-sm">Revenue</span>
          </div>
        </router-link>
      </nav>

      <div class="px-3 py-4 border-t border-border">
        <div class="flex items-center gap-3 px-2">
          <div class="w-8 h-8 rounded-full bg-win/20 border border-win/30 flex items-center justify-center text-win text-xs font-bold flex-shrink-0">
            {{ initials }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm text-white font-medium truncate">{{ user && user.name }}</div>
            <div class="text-xs text-gray-500 capitalize truncate">{{ user && user.role }}</div>
          </div>
          <button @click="logout" title="Logout" class="text-gray-500 hover:text-red-400 transition-colors p-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 overflow-y-auto bg-surface">
      <slot />
    </main>
  </div>
</template>

<script>
export default {
  name: 'AppLayout',
  computed: {
    user()        { return this.$store.state.auth.user },
    isSuperuser() { return this.$store.getters['auth/isSuperuser'] },
    initials()    { return (this.user?.name || 'U').split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2) },
    navItems() {
      return [
        { to: '/',            label: 'Dashboard',        icon: '📊' },
        { to: '/scanner',     label: 'Scanner',          icon: '🤖' },
        { to: '/filter',      label: 'Pre-Trade Filter', icon: '🔍' },
        { to: '/database',    label: 'Trade Database',   icon: '🗄️' },
        { to: '/journal',     label: 'Journal',          icon: '📓' },
        { to: '/analytics',   label: 'Analytics',        icon: '📈' },
        { to: '/categories',  label: 'Categories',       icon: '🏷️' },
        { to: '/pairs',       label: 'Pairs',            icon: '💱' },
        { to: '/subscription',label: 'Subscription',     icon: '💳' },
        { to: '/referral',    label: 'Earn & Refer',     icon: '🤝' },
      ]
    },
    trialBanner() {
      const user = this.$store.state.auth.user
      if (!user) return null
      if (user.role === 'superuser') return null

      const onTrial      = user.on_trial
      const daysLeft     = user.trial_days_left ?? 0
      const hasAccess    = user.has_access
      const hasSub       = user.subscription?.status === 'active'

      if (hasSub) return null  // active subscriber — no banner

      if (onTrial && daysLeft <= 7 && daysLeft > 0) {
        return {
          type:    'warning',
          message: `⏳ Your free trial ends in ${daysLeft} day${daysLeft===1?'':'s'}.`,
          cta:     'Subscribe now',
        }
      }
      if (!hasAccess) {
        return {
          type:    'expired',
          message: '🔒 Your free trial has ended. Subscribe to continue using EdgeLedger.',
          cta:     'View plans',
        }
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
.trial-banner {
  position: fixed; top: 0; left: 0; right: 0; z-index: 200;
  height: 36px; display: flex; align-items: center; justify-content: center; gap: 16px;
  font-size: 13px; font-weight: 500;
}
.trial-banner.warning  { background: rgba(186,117,23,0.9); color: #fff; }
.trial-banner.expired  { background: rgba(226,75,74,0.95); color: #fff; }
.trial-banner__btn {
  background: rgba(255,255,255,0.2); color: #fff; text-decoration: none;
  padding: 3px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;
  transition: background 0.15s;
}
.trial-banner__btn:hover { background: rgba(255,255,255,0.3); }
</style>