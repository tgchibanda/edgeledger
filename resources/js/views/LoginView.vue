<template>
  <div class="min-h-screen bg-surface flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-win mb-4">
          <svg width="32" height="32" viewBox="0 0 20 20" fill="none">
            <rect x="2" y="12" width="3" height="6" rx="1" fill="white" opacity="0.5"/>
            <rect x="7" y="7" width="3" height="11" rx="1" fill="white" opacity="0.75"/>
            <rect x="12" y="2" width="3" height="16" rx="1" fill="white"/>
            <path d="M3.5 11 L8.5 6 L13.5 1" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">Edge<span class="text-win">Ledger</span></h1>
        <p class="text-gray-500 text-sm mt-1">Structured Trading Intelligence</p>
      </div>

      <div class="card">
        <h2 class="text-lg font-semibold text-white mb-5">Sign in to your account</h2>
        <form @submit.prevent="submit">
          <div class="form-group">
            <label class="label">Email address</label>
            <input v-model="form.email" type="email" class="input" placeholder="you@example.com" required autofocus />
          </div>
          <div class="form-group">
            <label class="label">Password</label>
            <input v-model="form.password" type="password" class="input" placeholder="••••••••" required />
          </div>
          <div v-if="error" class="mb-4 p-3 bg-red-900/30 border border-red-800/40 rounded-lg text-red-400 text-sm">
            {{ error }}
          </div>
          <button type="submit" class="btn-primary w-full" :disabled="loading">
            {{ loading ? 'Signing in…' : 'Sign in' }}
          </button>
        </form>
        <div class="mt-5 p-3 bg-surface rounded-lg border border-border text-xs">
          <div class="text-gray-500 font-medium mb-1">Demo credentials</div>
          <div class="text-gray-400">Admin: admin@edgeledger.local / password</div>
          <div class="text-gray-400">Trader: trader@edgeledger.local / password</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoginView',
  data() { return { form: { email:'', password:'' }, loading: false, error: '' } },
  methods: {
    async submit() {
      this.loading = true; this.error = ''
      try {
        await this.$store.dispatch('auth/login', this.form)
        this.$router.push('/')
      } catch(e) {
        this.error = e.response?.data?.message || 'Login failed. Check your credentials.'
      } finally { this.loading = false }
    },
  },
}
</script>
