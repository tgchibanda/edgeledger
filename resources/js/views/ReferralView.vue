<template>
  <AppLayout>
    <div class="p-6 max-w-3xl">
      <div class="page-header">
        <h1 class="page-title">🤝 Referral Program</h1>
        <p class="page-sub">Earn 50% commission for every trader you refer who subscribes.</p>
      </div>

      <div v-if="loading" class="card text-gray-500 text-sm">Loading…</div>

      <div v-else class="space-y-5">

        <!-- Your referral code + link -->
        <div class="card">
          <h2 class="section-title">Your Referral Details</h2>
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <label class="label">Referral Code</label>
              <div class="flex gap-2 mt-1">
                <code class="flex-1 bg-surface border border-border rounded-lg px-3 py-2 text-win font-bold text-lg tracking-widest">{{ stats.referral_code }}</code>
                <button class="btn-ghost text-xs" @click="copy(stats.referral_code)">{{ copied==='code'?'Copied!':'Copy' }}</button>
              </div>
            </div>
            <div>
              <label class="label">Referral Link</label>
              <div class="flex gap-2 mt-1">
                <input readonly :value="stats.referral_url" class="input flex-1 text-xs text-gray-400" />
                <button class="btn-ghost text-xs" @click="copy(stats.referral_url)">{{ copied==='url'?'Copied!':'Copy' }}</button>
              </div>
            </div>
          </div>
          <div class="p-3 bg-surface rounded-lg border border-border text-xs text-gray-500">
            Share your code or link. When someone signs up using your code and subscribes, you earn <strong class="text-win">50%</strong> of their monthly payment — automatically.
          </div>
        </div>

        <!-- Stats row -->
        <div class="grid grid-cols-4 gap-4">
          <div class="card text-center">
            <div class="text-3xl font-bold text-white">{{ stats.total_referrals }}</div>
            <div class="text-xs text-gray-500 mt-1">Total Referrals</div>
          </div>
          <div class="card text-center">
            <div class="text-3xl font-bold text-win">{{ stats.active_referrals }}</div>
            <div class="text-xs text-gray-500 mt-1">Active Subscribers</div>
          </div>
          <div class="card text-center">
            <div class="text-3xl font-bold text-white">${{ fmt(stats.wallet?.total_earned) }}</div>
            <div class="text-xs text-gray-500 mt-1">Total Earned</div>
          </div>
          <div class="card text-center">
            <div class="text-3xl font-bold text-win">${{ fmt(stats.wallet?.available) }}</div>
            <div class="text-xs text-gray-500 mt-1">Available to Redeem</div>
          </div>
        </div>

        <!-- Wallet & Redeem -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h2 class="section-title mb-0">Wallet</h2>
            <button v-if="stats.wallet?.available > 0" class="btn-primary btn-sm" @click="showRedeem = !showRedeem">
              💸 Redeem Earnings
            </button>
          </div>

          <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-surface rounded-lg p-3 border border-border text-center">
              <div class="text-xl font-bold text-win">${{ fmt(stats.wallet?.available) }}</div>
              <div class="text-xs text-gray-500 mt-1">Available</div>
            </div>
            <div class="bg-surface rounded-lg p-3 border border-border text-center">
              <div class="text-xl font-bold text-yellow-400">${{ fmt(stats.wallet?.pending) }}</div>
              <div class="text-xs text-gray-500 mt-1">Pending</div>
            </div>
            <div class="bg-surface rounded-lg p-3 border border-border text-center">
              <div class="text-xl font-bold text-gray-400">${{ fmt(stats.wallet?.total_paid) }}</div>
              <div class="text-xs text-gray-500 mt-1">Paid Out</div>
            </div>
          </div>

          <!-- Redeem form -->
          <div v-if="showRedeem" class="border-t border-border pt-4 mt-2 space-y-3">
            <h3 class="text-sm font-semibold text-white">Request Payout</h3>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="label">Amount (min $5)</label>
                <input v-model="redeemForm.amount" type="number" step="0.01" min="5" :max="stats.wallet?.available" class="input" placeholder="5.00" />
              </div>
              <div>
                <label class="label">Payout Method</label>
                <select v-model="redeemForm.payment_method" class="select">
                  <option value="paypal">PayPal</option>
                  <option value="bank">Bank Transfer</option>
                  <option value="crypto">Crypto</option>
                </select>
              </div>
            </div>
            <div>
              <label class="label">Payment Details (PayPal email, bank details, wallet address)</label>
              <input v-model="redeemForm.payment_details" class="input" placeholder="e.g. yourname@paypal.com" />
            </div>
            <div v-if="redeemError" class="text-loss text-sm">{{ redeemError }}</div>
            <div class="flex gap-3">
              <button class="btn-primary" :disabled="redeeming" @click="redeem">{{ redeeming ? 'Submitting…' : 'Submit Payout Request' }}</button>
              <button class="btn-ghost" @click="showRedeem = false">Cancel</button>
            </div>
          </div>
        </div>

        <!-- Referred users -->
        <div class="card">
          <h2 class="section-title">Your Referrals</h2>
          <div v-if="!stats.referrals?.length" class="text-gray-600 text-sm py-3">
            No referrals yet. Share your code to start earning.
          </div>
          <table v-else class="w-full text-sm">
            <thead><tr class="border-b border-border">
              <th class="th">Trader</th>
              <th class="th">Joined</th>
              <th class="th">Status</th>
              <th class="th text-right">Earned</th>
            </tr></thead>
            <tbody class="divide-y divide-border">
              <tr v-for="r in stats.referrals" :key="r.id" class="hover:bg-card-hover">
                <td class="td text-white font-medium">{{ r.name }}</td>
                <td class="td text-gray-500 text-xs">{{ r.joined }}</td>
                <td class="td">
                  <span class="text-xs px-2 py-0.5 rounded" :class="r.is_active?'bg-win/20 text-win':'bg-gray-800 text-gray-500'">
                    {{ r.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="td text-right text-win font-semibold">${{ fmt(r.earned) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Recent earnings -->
        <div class="card">
          <h2 class="section-title">Recent Earnings</h2>
          <div v-if="!stats.recent_earnings?.length" class="text-gray-600 text-sm py-3">No earnings yet.</div>
          <div v-else class="space-y-2">
            <div v-for="(e, i) in stats.recent_earnings" :key="i"
              class="flex items-center justify-between p-3 bg-surface rounded-lg border border-border">
              <div>
                <div class="text-sm text-white">{{ e.source }}</div>
                <div class="text-xs text-gray-500">{{ e.date }}</div>
              </div>
              <div class="text-right">
                <div class="font-bold text-win">${{ fmt(e.amount) }}</div>
                <div class="text-xs capitalize" :class="e.status==='available'?'text-win':e.status==='redeemed'?'text-gray-500':'text-yellow-400'">{{ e.status }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Redemption history -->
        <div class="card" v-if="redemptions.length">
          <h2 class="section-title">Payout History</h2>
          <table class="w-full text-sm">
            <thead><tr class="border-b border-border">
              <th class="th">Date</th>
              <th class="th">Amount</th>
              <th class="th">Method</th>
              <th class="th">Status</th>
            </tr></thead>
            <tbody class="divide-y divide-border">
              <tr v-for="r in redemptions" :key="r.id" class="hover:bg-card-hover">
                <td class="td text-xs text-gray-500">{{ fmtd(r.created_at) }}</td>
                <td class="td font-bold text-white">${{ fmt(r.amount) }}</td>
                <td class="td capitalize text-gray-400">{{ r.payment_method }}</td>
                <td class="td">
                  <span class="text-xs px-2 py-0.5 rounded"
                    :class="{
                      'bg-win/20 text-win':    r.status==='paid',
                      'bg-yellow-900/30 text-yellow-400': r.status==='pending'||r.status==='approved',
                      'bg-loss/20 text-loss':  r.status==='rejected',
                    }">{{ r.status }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../components/AppLayout.vue'
export default {
  name: 'ReferralView',
  components: { AppLayout },
  data() {
    return {
      loading:     true,
      stats:       {},
      redemptions: [],
      showRedeem:  false,
      redeeming:   false,
      redeemError: '',
      copied:      '',
      redeemForm:  { amount: '', payment_method: 'paypal', payment_details: '' },
    }
  },
  async created() { await this.load() },
  methods: {
    async load() {
      this.loading = true
      try {
        const [s, r] = await Promise.all([
          this.$http.get('/referral/stats'),
          this.$http.get('/referral/redemptions'),
        ])
        this.stats       = s.data
        this.redemptions = r.data
      } catch(e) {}
      finally { this.loading = false }
    },
    async redeem() {
      this.redeeming = true; this.redeemError = ''
      try {
        await this.$http.post('/referral/redeem', this.redeemForm)
        this.showRedeem = false
        this.redeemForm = { amount: '', payment_method: 'paypal', payment_details: '' }
        await this.load()
      } catch(e) { this.redeemError = e.response?.data?.message || 'Failed.' }
      finally { this.redeeming = false }
    },
    copy(text) {
      navigator.clipboard.writeText(text)
      this.copied = text === this.stats.referral_code ? 'code' : 'url'
      setTimeout(() => { this.copied = '' }, 2000)
    },
    fmt(v) { return v != null ? Number(v).toFixed(2) : '0.00' },
    fmtd(d) { return d ? new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }) : '—' },
  },
}
</script>
