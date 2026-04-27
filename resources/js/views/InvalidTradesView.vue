<template>
  <AppLayout>
    <div class="p-6 max-w-5xl">

      <div class="page-header">
        <div class="flex items-start justify-between gap-4 flex-wrap">
          <div>
            <h1 class="page-title">⛔ Invalid Trade Patterns</h1>
            <p class="page-sub">Record setups you must not take — and what the correct trade looks like instead.</p>
          </div>
          <button class="btn-primary flex-shrink-0" @click="openForm()">+ Add Pattern</button>
        </div>
      </div>

      <div v-if="loading" class="card text-gray-500 text-sm flex items-center gap-3">
        <div class="w-4 h-4 border-2 border-gray-600 border-t-loss rounded-full animate-spin"></div>
        Loading…
      </div>

      <div v-else-if="!trades.length" class="card text-center py-12">
        <div class="text-4xl mb-3">⛔</div>
        <div class="text-white font-semibold text-lg mb-2">No patterns recorded yet</div>
        <p class="text-gray-500 text-sm max-w-sm mx-auto mb-5">When you catch yourself almost taking a bad trade, record it here with the correct version so you learn from it.</p>
        <button class="btn-primary" @click="openForm()">Add First Pattern</button>
      </div>

      <div v-else class="space-y-5">
        <div v-for="trade in trades" :key="trade.id" class="invalid-card">

          <div class="invalid-card__header">
            <div class="flex items-center gap-3">
              <div class="invalid-badge">⛔ INVALID</div>
              <div class="font-mono font-bold text-white text-sm">{{ pairName(trade.pair_id) }}</div>
              <div class="text-xs text-gray-500">{{ fmt(trade.created_at) }}</div>
            </div>
            <div class="flex gap-2">
              <button class="btn-ghost text-xs py-1 px-3" @click="openForm(trade)">Edit</button>
              <button class="text-xs text-loss hover:underline" @click="confirmDelete(trade)">Delete</button>
            </div>
          </div>

          <div class="invalid-card__charts" style="cursor:pointer" @click="openLightbox(trade)">
            <div class="chart-slot chart-slot--invalid">
              <div class="chart-slot__label">
                <span class="chart-slot__type mtf">MTF</span>
                <span class="chart-slot__title">What you saw (wrong)</span>
              </div>
              <div v-if="imgOf(trade, 'mtf')" class="chart-slot__img-wrap">
                <img :src="imgUrl(imgOf(trade, 'mtf'))" class="chart-slot__img" />
                <div class="chart-slot__zoom">🔍</div>
              </div>
              <div v-else class="chart-slot__empty">No image</div>
            </div>

            <div class="chart-slot chart-slot--invalid">
              <div class="chart-slot__label">
                <span class="chart-slot__type etf">Entry TF</span>
                <span class="chart-slot__title">Bad entry signal</span>
              </div>
              <div v-if="imgOf(trade, 'entry')" class="chart-slot__img-wrap">
                <img :src="imgUrl(imgOf(trade, 'entry'))" class="chart-slot__img" />
                <div class="chart-slot__zoom">🔍</div>
              </div>
              <div v-else class="chart-slot__empty">No image</div>
            </div>

            <div class="chart-slot chart-slot--correct">
              <div class="chart-slot__label">
                <span class="chart-slot__type correct">✓ Correct</span>
                <span class="chart-slot__title">What to trade instead</span>
              </div>
              <div v-if="imgOf(trade, 'correct')" class="chart-slot__img-wrap">
                <img :src="imgUrl(imgOf(trade, 'correct'))" class="chart-slot__img" />
                <div class="chart-slot__zoom">🔍</div>
              </div>
              <div v-else class="chart-slot__empty">No image</div>
            </div>
          </div>

          <div v-if="trade.notes || trade.lesson" class="invalid-card__notes">
            <div v-if="trade.notes" class="note-row">
              <span class="note-label note-label--bad">❌ What went wrong</span>
              <span class="note-text">{{ trade.notes }}</span>
            </div>
            <div v-if="trade.lesson" class="note-row">
              <span class="note-label note-label--good">✓ Lesson</span>
              <span class="note-text">{{ trade.lesson }}</span>
            </div>
          </div>

        </div>
      </div>

    </div>

    <!-- ImageLightbox — same as trade database, keyboard nav built in -->
    <ImageLightbox
      :visible="lightbox.show"
      :trades="lightboxTrades"
      :trade-index="lightbox.index"
      @close="lightbox.show = false"
    />

    <!-- Add/Edit form modal -->
    <div v-if="form.show" class="modal-overlay" @click.self="closeForm">
      <div class="modal-panel">
        <div class="flex items-center justify-between mb-5">
          <h2 class="section-title mb-0">{{ form.id ? 'Edit Pattern' : 'Add Invalid Trade Pattern' }}</h2>
          <button class="w-8 h-8 rounded-full bg-surface border border-border text-gray-400 hover:text-white flex items-center justify-center" @click="closeForm">✕</button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="label">Pair *</label>
            <select v-model="form.pair_id" class="select" required>
              <option value="">Select pair</option>
              <option v-for="p in pairs" :key="p.id" :value="p.id">{{ p.symbol }}</option>
            </select>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div class="upload-slot upload-slot--invalid">
              <div class="upload-slot__label">
                <span class="chart-slot__type mtf">MTF</span> — Wrong setup
              </div>
              <div class="upload-slot__zone" :class="{ 'has-file': form.mtf_preview }"
                @click="$refs.mtfInput.click()" @dragover.prevent @drop.prevent="onDrop($event,'mtf')">
                <input ref="mtfInput" type="file" accept="image/*" class="hidden" @change="onFile($event,'mtf')" />
                <img v-if="form.mtf_preview" :src="form.mtf_preview" class="upload-slot__preview" />
                <div v-else-if="form.id && existingImg(form,'mtf')" class="upload-slot__existing">
                  <img :src="imgUrl(existingImg(form,'mtf'))" class="upload-slot__preview" />
                  <span class="upload-slot__current">Current</span>
                </div>
                <div v-else class="upload-slot__inner">
                  <span class="text-xl">📷</span>
                  <span class="text-xs text-gray-500">Drop or click</span>
                </div>
                <button v-if="form.mtf_preview" type="button" class="upload-slot__remove" @click.stop="clearFile('mtf')">✕</button>
              </div>
            </div>

            <div class="upload-slot upload-slot--invalid">
              <div class="upload-slot__label">
                <span class="chart-slot__type etf">Entry TF</span> — Bad entry
              </div>
              <div class="upload-slot__zone" :class="{ 'has-file': form.entry_preview }"
                @click="$refs.entryInput.click()" @dragover.prevent @drop.prevent="onDrop($event,'entry')">
                <input ref="entryInput" type="file" accept="image/*" class="hidden" @change="onFile($event,'entry')" />
                <img v-if="form.entry_preview" :src="form.entry_preview" class="upload-slot__preview" />
                <div v-else-if="form.id && existingImg(form,'entry')" class="upload-slot__existing">
                  <img :src="imgUrl(existingImg(form,'entry'))" class="upload-slot__preview" />
                  <span class="upload-slot__current">Current</span>
                </div>
                <div v-else class="upload-slot__inner">
                  <span class="text-xl">📷</span>
                  <span class="text-xs text-gray-500">Drop or click</span>
                </div>
                <button v-if="form.entry_preview" type="button" class="upload-slot__remove" @click.stop="clearFile('entry')">✕</button>
              </div>
            </div>

            <div class="upload-slot upload-slot--correct">
              <div class="upload-slot__label">
                <span class="chart-slot__type correct">✓ Correct</span> — What to take
              </div>
              <div class="upload-slot__zone" :class="{ 'has-file': form.correct_preview }"
                @click="$refs.correctInput.click()" @dragover.prevent @drop.prevent="onDrop($event,'correct')">
                <input ref="correctInput" type="file" accept="image/*" class="hidden" @change="onFile($event,'correct')" />
                <img v-if="form.correct_preview" :src="form.correct_preview" class="upload-slot__preview" />
                <div v-else-if="form.id && existingImg(form,'correct')" class="upload-slot__existing">
                  <img :src="imgUrl(existingImg(form,'correct'))" class="upload-slot__preview" />
                  <span class="upload-slot__current">Current</span>
                </div>
                <div v-else class="upload-slot__inner">
                  <span class="text-xl">📷</span>
                  <span class="text-xs text-gray-500">Drop or click</span>
                </div>
                <button v-if="form.correct_preview" type="button" class="upload-slot__remove" @click.stop="clearFile('correct')">✕</button>
              </div>
            </div>
          </div>

          <div>
            <label class="label">What was wrong with this setup? <span class="text-gray-600">(optional)</span></label>
            <textarea v-model="form.notes" class="textarea" rows="2" placeholder="e.g. Took an OB in a bearish structure — HTF was clearly bearish"></textarea>
          </div>

          <div>
            <label class="label">Lesson — what to do instead <span class="text-gray-600">(optional)</span></label>
            <textarea v-model="form.lesson" class="textarea" rows="2" placeholder="e.g. Only take bullish OBs when HTF shows a clean bullish BOS above EQ"></textarea>
          </div>

          <div v-if="form.error" class="p-3 bg-red-900/20 border border-red-800/30 rounded-lg text-red-400 text-sm">{{ form.error }}</div>

          <div class="flex gap-3 pt-1">
            <button class="btn-primary" :disabled="form.saving" @click="saveForm">
              {{ form.saving ? 'Saving…' : form.id ? 'Update Pattern' : 'Save Pattern' }}
            </button>
            <button class="btn-ghost" @click="closeForm">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete confirm -->
    <div v-if="deleteTarget" class="fixed inset-0 bg-black/70 flex items-center justify-center z-40 p-4">
      <div class="card w-full max-w-sm">
        <h2 class="section-title">Delete pattern?</h2>
        <p class="text-sm text-gray-400 mb-4">This will permanently delete this invalid trade pattern and all its images.</p>
        <div class="flex gap-3">
          <button class="btn-danger" :disabled="deleting" @click="deleteTrade">{{ deleting ? 'Deleting…' : 'Delete' }}</button>
          <button class="btn-ghost" @click="deleteTarget = null">Cancel</button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<script>
import AppLayout     from '../components/AppLayout.vue'
import ImageLightbox from '../components/ImageLightbox.vue'

// Map invalid trade image type → timeframe slot used by ImageLightbox
// H4 = Correct trade | M15 = MTF wrong | M1 = Entry TF bad
const TYPE_TO_TF = { correct: 'H4', mtf: 'M15', entry: 'M1' }

export default {
  name: 'InvalidTradesView',
  components: { AppLayout, ImageLightbox },

  data() {
    return {
      loading: true,
      trades:  [],
      pairs:   [],

      lightbox: {
        show:  false,
        index: 0,
      },

      form: {
        show:  false,
        saving: false,
        error:  '',
        id:     null,
        pair_id: '',
        notes:   '',
        lesson:  '',
        mtf_file:    null, entry_file:   null, correct_file:    null,
        mtf_preview: null, entry_preview: null, correct_preview: null,
        images: [],
      },

      deleteTarget: null,
      deleting:     false,
    }
  },

  computed: {
    // Shape trades for ImageLightbox — maps type to timeframe slot
    lightboxTrades() {
      return this.trades.map(t => ({
        id:              t.id,
        entry_technique: '⛔ Invalid Pattern',
        pair:            t.pair || { symbol: this.pairName(t.pair_id) },
        session:         null,
        result:          null,
        pips_result:     null,
        r_multiple:      null,
        is_reference:    false,
        h4:              { name: 'Correct setup' },
        m15:             { name: t.notes  || '' },
        m1:              { name: t.lesson || '' },
        notes:           t.notes,
        images: (t.images || []).map(img => ({
          ...img,
          timeframe: TYPE_TO_TF[img.type] || img.type,
          _label: img.type === 'correct' ? '✓ Correct Trade'
                : img.type === 'mtf'     ? 'MTF — Wrong'
                : img.type === 'entry'   ? 'Entry TF — Bad'
                : img.type,
        })),
      }))
    },
  },

  async mounted() {
    await Promise.all([this.load(), this.loadPairs()])
  },

  methods: {
    async load() {
      this.loading = true
      try {
        const { data } = await this.$http.get('/invalid-trades')
        this.trades = data
      } catch(e) {}
      finally { this.loading = false }
    },

    async loadPairs() {
      try {
        const { data } = await this.$http.get('/pairs')
        this.pairs = data
      } catch(e) {}
    },

    pairName(pairId) {
      return this.pairs.find(p => p.id === pairId)?.symbol || '—'
    },

    imgOf(trade, type) {
      return trade.images?.find(i => i.type === type) || null
    },

    imgUrl(img) {
      return `/api/images/${img.path}`
    },

    existingImg(form, type) {
      return form.images?.find(i => i.type === type) || null
    },

    openLightbox(trade) {
      const idx = this.trades.findIndex(t => t.id === trade.id)
      this.lightbox = { show: true, index: Math.max(0, idx) }
    },

    openForm(trade = null) {
      this.form = {
        show:    true,
        saving:  false,
        error:   '',
        id:      trade?.id || null,
        pair_id: trade?.pair_id || '',
        notes:   trade?.notes  || '',
        lesson:  trade?.lesson || '',
        mtf_file:    null, entry_file:    null, correct_file:    null,
        mtf_preview: null, entry_preview: null, correct_preview: null,
        images: trade?.images || [],
      }
    },

    closeForm() { this.form.show = false },

    onFile(e, slot) {
      const f = e.target.files[0]
      if (f) this.setFile(slot, f)
    },

    onDrop(e, slot) {
      const f = e.dataTransfer.files[0]
      if (f && f.type.startsWith('image/')) this.setFile(slot, f)
    },

    setFile(slot, file) {
      this.form[`${slot}_file`]    = file
      this.form[`${slot}_preview`] = URL.createObjectURL(file)
    },

    clearFile(slot) {
      this.form[`${slot}_file`]    = null
      this.form[`${slot}_preview`] = null
      const ref = this.$refs[`${slot}Input`]
      if (ref) ref.value = ''
    },

    async saveForm() {
      if (!this.form.pair_id) { this.form.error = 'Please select a pair.'; return }
      this.form.saving = true
      this.form.error  = ''
      try {
        const fd = new FormData()
        fd.append('pair_id', this.form.pair_id)
        if (this.form.notes)        fd.append('notes',         this.form.notes)
        if (this.form.lesson)       fd.append('lesson',        this.form.lesson)
        if (this.form.mtf_file)     fd.append('mtf_image',     this.form.mtf_file)
        if (this.form.entry_file)   fd.append('entry_image',   this.form.entry_file)
        if (this.form.correct_file) fd.append('correct_image', this.form.correct_file)

        const opts = { headers: { 'Content-Type': 'multipart/form-data' } }

        if (this.form.id) {
          fd.append('_method', 'PUT')
          await this.$http.post(`/invalid-trades/${this.form.id}`, fd, opts)
        } else {
          await this.$http.post('/invalid-trades', fd, opts)
        }

        this.closeForm()
        await this.load()
      } catch(e) {
        this.form.error = e.response?.data?.message || 'Save failed.'
      } finally { this.form.saving = false }
    },

    confirmDelete(trade) { this.deleteTarget = trade },

    async deleteTrade() {
      if (!this.deleteTarget) return
      this.deleting = true
      try {
        await this.$http.delete(`/invalid-trades/${this.deleteTarget.id}`)
        this.deleteTarget = null
        await this.load()
      } catch(e) { alert('Delete failed.') }
      finally { this.deleting = false }
    },

    fmt(d) {
      if (!d) return ''
      return new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' })
    },
  },
}
</script>

<style scoped>
.invalid-card {
  background: #1A2633;
  border: 1px solid rgba(226,75,74,.2);
  border-radius: 14px;
  overflow: hidden;
}
.invalid-card__header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 18px;
  background: rgba(226,75,74,.05);
  border-bottom: 1px solid rgba(226,75,74,.12);
  flex-wrap: wrap; gap: 8px;
}
.invalid-badge {
  font-size: 11px; font-weight: 700; letter-spacing: .8px;
  color: #E24B4A; background: rgba(226,75,74,.15);
  border: 1px solid rgba(226,75,74,.3);
  padding: 3px 10px; border-radius: 100px;
}
.invalid-card__charts {
  display: grid; grid-template-columns: repeat(3,1fr);
  gap: 1px; background: rgba(255,255,255,.04);
}
.chart-slot { background: #0F1923; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.chart-slot--correct { background: rgba(29,158,117,.03); }
.chart-slot__label { display: flex; align-items: center; gap: 6px; }
.chart-slot__title { font-size: 11px; color: #4A5568; }
.chart-slot__type { font-size: 10px; font-weight: 700; letter-spacing: .5px; padding: 2px 7px; border-radius: 4px; }
.chart-slot__type.mtf     { background: rgba(127,119,221,.2); color: #AFA9EC; }
.chart-slot__type.etf     { background: rgba(212,160,23,.15); color: #D4A017; }
.chart-slot__type.correct { background: rgba(29,158,117,.15); color: #1D9E75; }
.chart-slot__img-wrap {
  position: relative; cursor: pointer; border-radius: 8px;
  overflow: hidden; height: 140px; background: #131C2E;
}
.chart-slot__img { width: 100%; height: 100%; object-fit: contain; }
.chart-slot__zoom {
  position: absolute; inset: 0; background: rgba(0,0,0,.4);
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; opacity: 0; transition: opacity .2s;
}
.invalid-card__charts:hover .chart-slot__zoom { opacity: 1; }
.chart-slot__empty {
  height: 140px; display: flex; align-items: center; justify-content: center;
  color: #334155; font-size: 12px; background: rgba(255,255,255,.01);
  border-radius: 8px; border: 1px dashed rgba(255,255,255,.06);
}
.invalid-card__notes {
  padding: 14px 18px; display: flex; flex-direction: column; gap: 8px;
  border-top: 1px solid rgba(255,255,255,.05);
}
.note-row { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
.note-label { font-size: 10px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; white-space: nowrap; }
.note-label--bad  { background: rgba(226,75,74,.15); color: #E24B4A; }
.note-label--good { background: rgba(29,158,117,.15); color: #1D9E75; }
.note-text { font-size: 13px; color: #94A3B8; line-height: 1.6; flex: 1; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; z-index: 50;
  background: rgba(0,0,0,.75); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center; padding: 16px;
}
.modal-panel {
  background: #1A2633; border: 1px solid rgba(255,255,255,.1);
  border-radius: 16px; padding: 28px;
  width: 100%; max-width: 640px; max-height: 92vh; overflow-y: auto;
}

/* Upload slots */
.upload-slot { display: flex; flex-direction: column; gap: 6px; }
.upload-slot__label { font-size: 11px; color: #64748B; display: flex; align-items: center; gap: 5px; }
.upload-slot__zone {
  position: relative; border: 2px dashed rgba(255,255,255,.1);
  border-radius: 10px; height: 120px; cursor: pointer; transition: all .2s;
  display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.upload-slot--invalid .upload-slot__zone:hover,
.upload-slot--invalid .upload-slot__zone.has-file { border-color: rgba(226,75,74,.4); }
.upload-slot--correct .upload-slot__zone:hover,
.upload-slot--correct .upload-slot__zone.has-file { border-color: rgba(29,158,117,.4); }
.upload-slot__inner { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.upload-slot__preview { width: 100%; height: 100%; object-fit: contain; }
.upload-slot__existing { position: relative; width: 100%; height: 100%; }
.upload-slot__current {
  position: absolute; bottom: 4px; right: 4px; font-size: 9px; font-weight: 700;
  background: rgba(0,0,0,.7); color: #64748B; padding: 2px 6px; border-radius: 3px;
}
.upload-slot__remove {
  position: absolute; top: 5px; right: 5px; width: 22px; height: 22px;
  background: rgba(226,75,74,.85); border: none; border-radius: 50%;
  color: #fff; font-size: 10px; cursor: pointer;
  display: flex; align-items: center; justify-content: center; z-index: 2;
}

@media(max-width: 640px) {
  .invalid-card__charts { grid-template-columns: 1fr; }
  .modal-panel { padding: 18px 14px; }
}
</style>