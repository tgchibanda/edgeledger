<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <div class="page-header">
        <h1 class="page-title">💳 Subscription</h1>
        <p class="page-sub">Manage your EdgeLedger Pro subscription.</p>
      </div>

      <div v-if="loading" class="card text-gray-500 text-sm flex items-center gap-3">
        <div class="w-4 h-4 border-2 border-gray-600 border-t-win rounded-full animate-spin"></div>
        Loading…
      </div>

      <div v-else class="space-y-5">

        <!-- ── ACTIVE SUBSCRIPTION ── -->
        <div v-if="data.is_active" class="card border border-win/30" style="background:rgba(29,158,117,0.04)">
          <div class="flex items-center gap-2 mb-4">
            <span class="w-2.5 h-2.5 bg-win rounded-full"></span>
            <span class="text-win font-bold text-sm tracking-wide">ACTIVE</span>
            <span v-if="data.subscription?.status === 'cancelled'" class="text-xs text-yellow-400 ml-1">· Cancels at period end</span>
          </div>
          <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm mb-4">
            <div><span class="text-gray-500">Plan</span><div class="text-white font-semibold mt-0.5">{{ data.plan?.name }}</div></div>
            <div><span class="text-gray-500">Price</span><div class="text-white mt-0.5">${{ data.plan?.price }}/month</div></div>
            <div><span class="text-gray-500">Started</span><div class="text-white mt-0.5">{{ fmt(data.subscription?.started_at) }}</div></div>
            <div><span class="text-gray-500">Current period</span><div class="text-white mt-0.5">{{ fmt(data.subscription?.current_period_start) }}</div></div>
            <div>
              <span class="text-gray-500">Next renewal</span>
              <div class="mt-0.5" :class="data.days_left < 5 ? 'text-yellow-400 font-semibold' : 'text-white'">
                {{ fmt(data.subscription?.current_period_end) }}
                <span class="text-gray-500 text-xs font-normal">({{ data.days_left }} day{{ data.days_left !== 1 ? 's' : '' }} left)</span>
              </div>
            </div>
            <div><span class="text-gray-500">Payment method</span><div class="text-white capitalize mt-0.5">{{ data.subscription?.payment_method }}</div></div>
          </div>
          <div v-if="data.days_left <= 7 && data.days_left > 0" class="p-3 rounded-lg bg-yellow-900/20 border border-yellow-800/30 text-yellow-300 text-sm mb-4">
            ⚠️ Your subscription expires soon. Renew now to avoid losing access.
            <button class="underline ml-1 font-semibold" @click="showSubscribeForm = true">Pay for next month →</button>
          </div>
          <div v-if="data.subscription?.status !== 'cancelled'" class="pt-4 border-t border-border flex gap-3">
            <button class="btn-ghost text-sm" @click="showCancel = true">Cancel Subscription</button>
            <button class="btn-primary text-sm" @click="showSubscribeForm = true">Renew / Pay Next Month</button>
          </div>
          <div v-else class="pt-4 border-t border-border text-sm text-yellow-400">
            Cancelled · Access until {{ fmt(data.subscription?.current_period_end) }} ·
            <button class="underline text-win ml-1" @click="showSubscribeForm = true">Resubscribe</button>
          </div>
        </div>

        <!-- ── PENDING SUBMISSION ── -->
        <div v-if="hasPending" class="card border border-yellow-800/40" style="background:rgba(186,117,23,0.06)">
          <div class="flex items-center gap-2 mb-2">
            <span class="w-2.5 h-2.5 bg-yellow-400 rounded-full animate-pulse"></span>
            <span class="text-yellow-400 font-bold text-sm tracking-wide">PAYMENT PENDING REVIEW</span>
          </div>
          <p class="text-sm text-gray-400">We've received your payment submission and are reviewing it. Your subscription will be activated within 24 hours.</p>
        </div>

        <!-- ── NO SUBSCRIPTION — show plan ── -->
        <div v-if="!data.is_active && !hasPending" class="card">
          <h2 class="section-title">{{ data.plan?.name || 'EdgeLedger Pro' }}</h2>
          <div class="flex items-baseline gap-1 mb-4">
            <span class="text-4xl font-bold text-white">${{ data.plan?.price || '2' }}</span>
            <span class="text-gray-500">/month</span>
          </div>
          <ul class="space-y-2 mb-5">
            <li v-for="f in (data.plan?.features || [])" :key="f" class="flex items-center gap-2 text-sm text-gray-400">
              <span class="text-win">✓</span> {{ f }}
            </li>
          </ul>
          <button class="btn-primary" @click="showSubscribeForm = true">
            Subscribe — ${{ data.plan?.price || '2' }}/month
          </button>
        </div>

        <!-- ── PAYMENT FORM ── -->
        <div v-if="showSubscribeForm" class="card space-y-4">
          <h2 class="section-title">{{ data.is_active ? 'Renew Subscription' : 'Subscribe to EdgeLedger Pro' }}</h2>

          <!-- Step 1: choose method -->
          <div>
            <label class="label">Payment Method</label>
            <div class="grid grid-cols-3 gap-3 mt-1">
              <button v-for="m in paymentMethods" :key="m.value"
                type="button"
                class="pay-method-btn"
                :class="{ active: subForm.payment_method === m.value }"
                @click="subForm.payment_method = m.value">
                <span class="text-xl">{{ m.icon }}</span>
                <span class="text-xs font-semibold">{{ m.label }}</span>
              </button>
            </div>
          </div>

          <!-- Step 2: instructions -->
          <transition name="slide-down">

            <!-- PayPal instructions -->
            <div v-if="subForm.payment_method === 'paypal'" class="instruction-box instruction-box--blue">
              <div class="instruction-box__title">📧 PayPal Instructions</div>
              <ol class="instruction-list">
                <li>Open your PayPal app or go to <strong>paypal.com</strong></li>
                <li>Send <strong>${{ data.plan?.price || '2.00' }} USD</strong> to:</li>
              </ol>
              <div class="paypal-email" @click="copyEmail">
                <span>tamaeproductions@gmail.com</span>
                <button class="copy-btn">{{ emailCopied ? '✓ Copied' : 'Copy' }}</button>
              </div>
              <ol class="instruction-list" start="3">
                <li>In the PayPal note write: <strong>EdgeLedger subscription – {{ user && user.email }}</strong></li>
                <li>Take a screenshot of the confirmed payment</li>
                <li>Upload the screenshot below and submit</li>
              </ol>
            </div>

            <!-- Bank Transfer instructions -->
            <div v-else-if="subForm.payment_method === 'bank'" class="instruction-box instruction-box--green">
              <div class="instruction-box__title">🏦 Bank Transfer Instructions</div>
              <p class="text-sm text-gray-400 mb-2">Please contact us for bank details before transferring:</p>
              <a href="mailto:tamaeproductions@gmail.com?subject=EdgeLedger Bank Transfer Details" class="instruction-link">
                tamaeproductions@gmail.com
              </a>
              <p class="text-xs text-gray-500 mt-2">Include your EdgeLedger email ({{ user && user.email }}) in the transfer reference. Upload proof of payment below after transferring.</p>
            </div>

            <!-- Crypto instructions -->
            <div v-else-if="subForm.payment_method === 'crypto'" class="instruction-box instruction-box--purple">
              <div class="instruction-box__title">₿ Crypto Payment Instructions</div>
              <p class="text-sm text-gray-400 mb-2">Contact us for our wallet address before sending:</p>
              <a href="mailto:tamaeproductions@gmail.com?subject=EdgeLedger Crypto Payment" class="instruction-link">
                tamaeproductions@gmail.com
              </a>
              <p class="text-xs text-gray-500 mt-2">Specify which coin you'll use so we can provide the correct address. Upload proof of payment below after sending.</p>
            </div>

            <!-- Other / Manual instructions -->
            <div v-else-if="subForm.payment_method === 'manual'" class="instruction-box instruction-box--gold">
              <div class="instruction-box__title">💬 Other Payment</div>
              <p class="text-sm text-gray-400 mb-3">For other payment methods, please contact us directly:</p>
              <a :href="`mailto:tamaeproductions@gmail.com?subject=EdgeLedger Subscription Payment&body=Hi, I would like to subscribe to EdgeLedger Pro. My account email is: ${user && user.email}`"
                class="instruction-link">
                tamaeproductions@gmail.com
              </a>
              <p class="text-xs text-gray-500 mt-2">Message us on email or WhatsApp and we'll arrange payment directly. Once payment is confirmed your account will be activated immediately.</p>
            </div>

          </transition>

          <!-- Step 3: Reference + Screenshot -->
          <div v-if="subForm.payment_method && subForm.payment_method !== 'manual'" class="space-y-3">
            <div>
              <label class="label">Transaction / Reference ID <span class="text-gray-600">(optional but recommended)</span></label>
              <input v-model="subForm.payment_ref" class="input" placeholder="e.g. PayPal transaction ID, bank reference" />
            </div>
            <div>
              <label class="label">Proof of Payment Screenshot *</label>
              <div class="screenshot-drop"
                :class="{ 'screenshot-drop--has': screenshotPreview }"
                @click="$refs.screenshotInput.click()"
                @dragover.prevent
                @drop.prevent="onDropScreenshot">
                <input ref="screenshotInput" type="file" accept="image/*" class="hidden" @change="onScreenshotSelect" />
                <img v-if="screenshotPreview" :src="screenshotPreview" class="screenshot-preview" />
                <div v-else class="screenshot-drop__inner">
                  <div class="text-2xl mb-1">📷</div>
                  <div class="text-sm text-gray-400">Click or drag your payment screenshot here</div>
                  <div class="text-xs text-gray-600 mt-1">PNG, JPG up to 5MB</div>
                </div>
                <button v-if="screenshotPreview" type="button" class="screenshot-remove" @click.stop="removeScreenshot">✕</button>
              </div>
            </div>
          </div>

          <div v-if="subError" class="p-3 bg-red-900/20 border border-red-800/30 rounded-lg text-red-400 text-sm">{{ subError }}</div>

          <div v-if="subSuccess" class="p-3 bg-win/10 border border-win/30 rounded-lg text-win text-sm">
            ✓ {{ subSuccess }}
          </div>

          <div class="flex gap-3 pt-2">
            <button class="btn-primary" :disabled="subSaving || !canSubmit" @click="subscribe">
              <span v-if="subSaving" class="flex items-center gap-2">
                <span class="w-4 h-4 border-2 border-black/30 border-t-black rounded-full animate-spin"></span>
                Submitting…
              </span>
              <span v-else>{{ subForm.payment_method === 'manual' ? 'I\'ve Contacted You' : 'Submit Payment Proof' }}</span>
            </button>
            <button class="btn-ghost" @click="showSubscribeForm = false; subSuccess = ''">Cancel</button>
          </div>
        </div>

        <!-- ── PAYMENT HISTORY ── -->
        <div class="card">
          <h2 class="section-title">Payment History</h2>
          <div v-if="!payments.length" class="text-gray-600 text-sm py-3">No payments yet.</div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-border">
                  <th class="th">Date</th>
                  <th class="th">Amount</th>
                  <th class="th">Method</th>
                  <th class="th">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border">
                <tr v-for="p in payments" :key="p.id" class="hover:bg-card-hover">
                  <td class="td text-xs font-mono text-gray-400">{{ fmt(p.paid_at) }}</td>
                  <td class="td font-semibold text-white">${{ p.amount }}</td>
                  <td class="td capitalize text-gray-400">{{ p.payment_method }}</td>
                  <td class="td">
                    <span class="text-xs px-2 py-0.5 rounded font-semibold"
                      :class="{
                        'bg-win/20 text-win':            p.status === 'completed',
                        'bg-yellow-900/30 text-yellow-400': p.status === 'pending',
                        'bg-loss/20 text-loss':           p.status === 'failed',
                      }">{{ p.status }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- ── CANCEL MODAL ── -->
      <div v-if="showCancel" class="fixed inset-0 bg-black/70 flex items-center justify-center z-40 p-4">
        <div class="card w-full max-w-sm">
          <h2 class="section-title">Cancel Subscription?</h2>
          <p class="text-sm text-gray-400 mb-4">
            Your access continues until <strong class="text-white">{{ fmt(data.subscription?.current_period_end) }}</strong>.
            You will not be charged again.
          </p>
          <div class="flex gap-3">
            <button class="btn-danger" :disabled="cancelling" @click="cancelSub">
              {{ cancelling ? 'Cancelling…' : 'Yes, Cancel' }}
            </button>
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
      subSaving:         false,
      subError:          '',
      subSuccess:        '',
      emailCopied:       false,
      screenshotFile:    null,
      screenshotPreview: null,
      subForm: {
        payment_method: '',
        payment_ref:    '',
      },
      paymentMethods: [
        { value: 'paypal',  label: 'PayPal',   icon: '🅿️' },
        { value: 'bank',    label: 'Bank',      icon: '🏦' },
        { value: 'crypto',  label: 'Crypto',    icon: '₿' },
        { value: 'manual',  label: 'Other',     icon: '💬' },
      ],
    }
  },
  computed: {
    user()       { return this.$store.state.auth.user },
    hasPending() {
      const sub = this.data.subscription
      return sub && sub.status === 'pending'
    },
    canSubmit() {
      if (!this.subForm.payment_method) return false
      if (this.subForm.payment_method === 'manual') return true
      return !!this.screenshotFile
    },
  },
  async created() { await this.load() },
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

    onScreenshotSelect(e) {
      const f = e.target.files[0]
      if (f) this.setScreenshot(f)
    },
    onDropScreenshot(e) {
      const f = e.dataTransfer.files[0]
      if (f && f.type.startsWith('image/')) this.setScreenshot(f)
    },
    setScreenshot(file) {
      this.screenshotFile    = file
      this.screenshotPreview = URL.createObjectURL(file)
    },
    removeScreenshot() {
      this.screenshotFile    = null
      this.screenshotPreview = null
      if (this.$refs.screenshotInput) this.$refs.screenshotInput.value = ''
    },

    copyEmail() {
      navigator.clipboard.writeText('tamaeproductions@gmail.com')
      this.emailCopied = true
      setTimeout(() => { this.emailCopied = false }, 2000)
    },

    async subscribe() {
      this.subSaving = true
      this.subError  = ''
      this.subSuccess = ''
      try {
        const fd = new FormData()
        fd.append('payment_method', this.subForm.payment_method)
        if (this.subForm.payment_ref) fd.append('payment_ref', this.subForm.payment_ref)
        if (this.screenshotFile)      fd.append('screenshot', this.screenshotFile)

        const { data } = await this.$http.post('/subscription', fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })

        this.subSuccess = data.message || 'Payment submitted successfully. We will activate your account within 24 hours.'
        this.showSubscribeForm = false
        this.removeScreenshot()
        this.subForm = { payment_method: '', payment_ref: '' }
        await this.load()
      } catch(e) {
        this.subError = e.response?.data?.message || 'Submission failed. Please try again.'
      } finally { this.subSaving = false }
    },

    async cancelSub() {
      this.cancelling = true
      try {
        await this.$http.post('/subscription/cancel')
        this.showCancel = false
        await this.load()
      } catch(e) { alert('Failed to cancel.') }
      finally { this.cancelling = false }
    },

    fmt(d) {
      if (!d) return '—'
      return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
    },
  },
}
</script>

<style scoped>
.pay-method-btn {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 6px; padding: 14px 8px; border-radius: 10px; cursor: pointer; transition: all .15s;
  background: #1A2633; border: 1px solid rgba(255,255,255,.08); color: #64748B;
}
.pay-method-btn:hover { border-color: rgba(255,255,255,.2); color: #94A3B8; }
.pay-method-btn.active { background: rgba(29,158,117,.12); border-color: #1D9E75; color: #1D9E75; }

.instruction-box {
  border-radius: 10px; padding: 16px; border: 1px solid;
}
.instruction-box--blue   { background: rgba(55,138,221,.07); border-color: rgba(55,138,221,.25); }
.instruction-box--green  { background: rgba(29,158,117,.07); border-color: rgba(29,158,117,.25); }
.instruction-box--purple { background: rgba(127,119,221,.07); border-color: rgba(127,119,221,.25); }
.instruction-box--gold   { background: rgba(212,160,23,.07); border-color: rgba(212,160,23,.25); }
.instruction-box__title  { font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 10px; }

.instruction-list {
  padding-left: 18px; display: flex; flex-direction: column; gap: 6px;
  font-size: 13px; color: #94A3B8; margin-bottom: 10px;
}
.instruction-list li { line-height: 1.5; }

.paypal-email {
  display: flex; align-items: center; justify-content: space-between; gap: 10px;
  background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
  border-radius: 8px; padding: 10px 14px; margin: 8px 0;
  font-family: monospace; font-size: 14px; color: #E2E8F0; cursor: pointer;
}
.copy-btn {
  background: rgba(29,158,117,.2); border: none; color: #1D9E75;
  padding: 3px 10px; border-radius: 5px; font-size: 11px; font-weight: 700; cursor: pointer; white-space: nowrap;
}
.instruction-link {
  display: inline-block; color: #1D9E75; font-weight: 600; font-size: 14px; text-decoration: none;
  border-bottom: 1px dashed rgba(29,158,117,.4); padding-bottom: 1px;
}

.screenshot-drop {
  border: 2px dashed rgba(255,255,255,.1); border-radius: 12px;
  padding: 32px 16px; text-align: center; cursor: pointer; transition: all .2s;
  background: rgba(255,255,255,.01); position: relative; min-height: 120px;
  display: flex; align-items: center; justify-content: center;
}
.screenshot-drop:hover, .screenshot-drop--has { border-color: rgba(29,158,117,.4); }
.screenshot-drop__inner { display: flex; flex-direction: column; align-items: center; }
.screenshot-preview { max-height: 180px; max-width: 100%; object-fit: contain; border-radius: 8px; }
.screenshot-remove {
  position: absolute; top: 8px; right: 8px; width: 26px; height: 26px;
  background: rgba(226,75,74,.8); border: none; border-radius: 50%;
  color: #fff; font-size: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center;
}

.slide-down-enter-active { transition: all .25s ease; }
.slide-down-enter { opacity: 0; transform: translateY(-8px); }

@media(max-width: 640px) {
  .grid-cols-2 { grid-template-columns: 1fr; }
}
</style>