<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <div class="page-header">
        <h1 class="page-title">💳 Subscription</h1>
        <p class="page-sub">Manage your EdgeLedger Pro subscription.</p>
      </div>

      <div v-if="loading" class="card text-gray-500 text-sm">Loading…</div>

      <div v-else>

        <!-- Active subscription banner -->
        <div v-if="data.is_active" class="card mb-5 border border-win/30 bg-win/5">
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-2 bg-win rounded-full"></span>
                <span class="text-win font-semibold text-sm">Active</span>
                <span v-if="data.subscription?.status === 'cancelled'" class="text-xs text-yellow-400 ml-1">(Cancels at period end)</span>
              </div>
              <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div><span class="text-gray-500">Plan:</span> <span class="text-white font-semibold">{{ data.plan?.name }}</span></div>
                <div><span class="text-gray-500">Price:</span> <span class="text-white">${{ data.plan?.price }}/month</span></div>
                <div><span class="text-gray-500">Started:</span> <span class="text-white">{{ fmt(data.subscription?.started_at) }}</span></div>
                <div><span class="text-gray-500">Current period:</span> <span class="text-white">{{ fmt(data.subscription?.current_period_start) }}</span></div>
                <div><span class="text-gray-500">Next renewal:</span>
                  <span :class="data.days_left < 5 ? 'text-yellow-400' : 'text-white'">
                    {{ fmt(data.subscription?.current_period_end) }}
                    ({{ data.days_left }}d remaining)
                  </span>
                </div>
                <div><span class="text-gray-500">Payment:</span> <span class="text-white capitalize">{{ data.subscription?.payment_method }}</span></div>
              </div>
            </div>
          </div>
          <div v-if="data.subscription?.status !== 'cancelled'" class="mt-4 pt-4 border-t border-border flex gap-3">
            <button class="btn-ghost text-sm" @click="showCancel = true">Cancel Subscription</button>
          </div>
          <div v-else class="mt-4 pt-4 border-t border-border text-sm text-yellow-400">
            Your subscription is cancelled. You have access until {{ fmt(data.subscription?.current_period_end) }}. You can resubscribe below.
          </div>
        </div>

        <!-- No active subscription -->
        <div v-if="!data.is_active" class="card mb-5">
          <h2 class="section-title">{{ data.plan?.name }}</h2>
          <div class="flex items-baseline gap-1 mb-4">
            <span class="text-4xl font-bold text-white">${{ data.plan?.price }}</span>
            <span class="text-gray-500">/month</span>
          </div>
          <ul class="space-y-2 mb-5">
            <li v-for="f in (data.plan?.features || [])" :key="f" class="flex items-center gap-2 text-sm text-gray-400">
              <span class="text-win">✓</span> {{ f }}
            </li>
          </ul>
          <div class="p-3 bg-blue-900/20 border border-blue-800/30 rounded-lg text-sm text-blue-300 mb-4">
            <strong>Manual payment:</strong> Contact <a href="mailto:tamaeproductions@gmail.com" class="underline">tamaeproductions@gmail.com</a>
            to arrange payment. Once confirmed, your subscription will be activated manually.
          </div>
          <button class="btn-primary" @click="showSubscribeForm = true">Get Started — ${{ data.plan?.price }}/month</button>
        </div>

        <!-- Subscribe form -->
        <div v-if="showSubscribeForm" class="card mb-5">
          <h2 class="section-title">Confirm Subscription</h2>
          <div class="space-y-3">
            <div>
              <label class="label">Payment Method</label>
              <select v-model="subForm.payment_method" class="select">
                <option value="paypal">PayPal</option>
                <option value="bank">Bank Transfer</option>
                <option value="crypto">Crypto</option>
                <option value="manual">Other / Manual</option>
              </select>
            </div>
            <div>
              <label class="label">Payment Reference / Transaction ID</label>
              <input v-model="subForm.payment_ref" class="input" placeholder="e.g. PayPal transaction ID" />
            </div>
            <div v-if="subError" class="text-loss text-sm">{{ subError }}</div>
            <div class="flex gap-3">
              <button class="btn-primary" :disabled="subSaving" @click="subscribe">
                {{ subSaving ? 'Submitting…' : 'Submit Payment Confirmation' }}
              </button>
              <button class="btn-ghost" @click="showSubscribeForm = false">Cancel</button>
            </div>
          </div>
        </div>

        <!-- Payment history -->
        <div class="card">
          <h2 class="section-title">Payment History</h2>
          <div v-if="!payments.length" class="text-gray-600 text-sm">No payments yet.</div>
          <table v-else class="w-full text-sm">
            <thead><tr class="border-b border-border">
              <th class="th">Date</th>
              <th class="th">Amount</th>
              <th class="th">Method</th>
              <th class="th">Status</th>
              <th class="th">Period</th>
            </tr></thead>
            <tbody class="divide-y divide-border">
              <tr v-for="p in payments" :key="p.id" class="hover:bg-card-hover">
                <td class="td text-xs font-mono">{{ fmt(p.paid_at) }}</td>
                <td class="td font-semibold text-white">${{ p.amount }}</td>
                <td class="td capitalize text-gray-400">{{ p.payment_method }}</td>
                <td class="td"><span class="text-xs px-2 py-0.5 rounded" :class="p.status==='completed'?'bg-win/20 text-win':'bg-loss/20 text-loss'">{{ p.status }}</span></td>
                <td class="td text-xs text-gray-500">{{ fmt(p.subscription?.current_period_start) }} → {{ fmt(p.subscription?.current_period_end) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

      <!-- Cancel confirm modal -->
      <div v-if="showCancel" class="fixed inset-0 bg-black/70 flex items-center justify-center z-40 p-4">
        <div class="card w-full max-w-sm">
          <h2 class="section-title">Cancel Subscription?</h2>
          <p class="text-sm text-gray-400 mb-4">
            Your access will continue until <strong class="text-white">{{ fmt(data.subscription?.current_period_end) }}</strong>.
            You will not be billed again after that date.
          </p>
          <div class="flex gap-3">
            <button class="btn-danger" :disabled="cancelling" @click="cancelSub">{{ cancelling ? 'Cancelling…' : 'Yes, Cancel' }}</button>
            <button class="btn-ghost" @click="showCancel = false">Keep Subscription</button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../components/AppLayout.vue'
export default {
  name: 'SubscriptionView',
  components: { AppLayout },
  data() {
    return {
      loading:           true,
      data:              {},
      payments:          [],
      showSubscribeForm: false,
      showCancel:        false,
      cancelling:        false,
      subForm:           { payment_method: 'paypal', payment_ref: '' },
      subSaving:         false,
      subError:          '',
    }
  },
  async created() {
    await this.load()
  },
  methods: {
    async load() {
      this.loading = true
      try {
        const [sub, hist] = await Promise.all([
          this.$http.get('/subscription'),
          this.$http.get('/subscription/history'),
        ])
        this.data     = sub.data
        this.payments = hist.data
      } catch(e) {}
      finally { this.loading = false }
    },
    async subscribe() {
      this.subSaving = true; this.subError = ''
      try {
        await this.$http.post('/subscription', this.subForm)
        this.showSubscribeForm = false
        await this.load()
      } catch(e) { this.subError = e.response?.data?.message || 'Failed.' }
      finally { this.subSaving = false }
    },
    async cancelSub() {
      this.cancelling = true
      try {
        await this.$http.post('/subscription/cancel')
        this.showCancel = false
        await this.load()
      } finally { this.cancelling = false }
    },
    fmt(d) { return d ? new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }) : '—' },
  },
}
</script>
