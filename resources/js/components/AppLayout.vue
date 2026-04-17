<template>
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 bg-card border-r border-border flex flex-col">
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
        { to: '/',           label: 'Dashboard',        icon: '📊' },
        { to: '/filter',     label: 'Pre-Trade Filter',  icon: '🔍' },
        { to: '/database',   label: 'Trade Database',    icon: '🗄️' },
        { to: '/journal',    label: 'Journal',           icon: '📓' },
        { to: '/analytics',  label: 'Analytics',         icon: '📈' },
        { to: '/categories', label: 'Categories',        icon: '🏷️' },
        { to: '/pairs',      label: 'Pairs',             icon: '💱' },
      ]
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
