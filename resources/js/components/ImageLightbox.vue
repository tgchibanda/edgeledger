<template>
  <transition name="fade">
    <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/95" @click.self="close">
      <button @click="close" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors z-10 text-lg">✕</button>

      <template v-if="trades && trades.length > 1">
        <button @click="prev" :disabled="currentIndex === 0"
          class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-colors z-10 text-xl disabled:opacity-20 disabled:cursor-not-allowed">‹</button>
        <button @click="next" :disabled="currentIndex === trades.length - 1"
          class="absolute right-16 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-colors z-10 text-xl disabled:opacity-20 disabled:cursor-not-allowed">›</button>
      </template>

      <!-- Single image mode -->
      <div v-if="!currentImages.length" class="relative max-w-5xl w-full mx-16">
        <div class="text-center mb-2 text-gray-400 text-sm font-medium">{{ timeframe }} Chart</div>
        <img :src="src" class="max-h-screen max-w-full mx-auto rounded-lg object-contain" style="max-height:80vh" />
      </div>

      <!-- Multi image mode -->
      <div v-else class="w-full flex flex-col px-16 py-10" style="height:100vh">
        <div v-if="currentTrade" class="flex items-center gap-3 mb-3 flex-shrink-0 flex-wrap">
          <span class="text-xs text-gray-500">{{ currentIndex + 1 }} / {{ trades.length }}</span>
          <span :class="currentTrade.result === 'win' ? 'badge-win' : currentTrade.result === 'loss' ? 'badge-loss' : 'badge-neutral'">
            {{ (currentTrade.result || '').toUpperCase() }}
          </span>
          <span v-if="currentTrade.is_reference" class="badge-ref">⭐ Reference</span>
          <span class="text-sm text-white font-medium">{{ currentTrade.entry_technique }}</span>
          <span class="text-xs text-gray-500">{{ currentTrade.pair && currentTrade.pair.symbol }}</span>
          <span class="text-xs text-gray-500">{{ currentTrade.session && currentTrade.session.name }}</span>
          <span class="text-xs text-gray-600 ml-auto">
            <span class="text-blue-400">{{ TF.h4 }}</span>: {{ currentTrade.h4 && currentTrade.h4.name }}
            → <span class="text-purple-400">{{ TF.m15 }}</span>: {{ currentTrade.m15 && currentTrade.m15.name }}
            → <span class="text-yellow-400">{{ TF.m1 }}</span>: {{ currentTrade.m1 && currentTrade.m1.name }}
          </span>
          <span v-if="currentTrade.pips_result" class="text-xs font-semibold" :class="currentTrade.pips_result > 0 ? 'text-win' : 'text-loss'">
            {{ currentTrade.pips_result > 0 ? '+' : '' }}{{ currentTrade.pips_result }} pips
          </span>
          <span v-if="currentTrade.r_multiple" class="text-xs font-semibold" :class="currentTrade.r_multiple > 0 ? 'text-win' : 'text-loss'">
            {{ currentTrade.r_multiple > 0 ? '+' : '' }}{{ currentTrade.r_multiple }}R
          </span>
        </div>

        <div class="flex gap-3" style="flex:1; min-height:0">
          <div v-for="img in orderedImages" :key="img.timeframe" class="flex-1 flex flex-col min-w-0">
            <div class="text-center mb-1.5 flex-shrink-0">
              <span class="text-xs font-bold px-3 py-0.5 rounded-full"
                :class="{
                  'bg-blue-900/60 text-blue-300':     img.timeframe === 'H4',
                  'bg-purple-900/60 text-purple-300': img.timeframe === 'M15',
                  'bg-yellow-900/60 text-yellow-300': img.timeframe === 'M1',
                }">
                {{ img._label || (img.timeframe === 'H4' ? TF.h4 : img.timeframe === 'M15' ? TF.m15 : TF.m1) }}
              </span>
            </div>
            <div class="flex-1 rounded-xl overflow-hidden bg-gray-900 flex items-center justify-center min-h-0 transition-all"
              :class="startAt === img.timeframe ? 'border-2 border-win' : 'border border-gray-800'">
              <img v-if="img.path" :src="`/api/images/${img.path}`" class="w-full h-full object-contain" />
              <div v-else class="text-center text-gray-600 p-4">
                <div class="text-3xl mb-2">📷</div>
                <div class="text-xs">No {{ img._label || (img.timeframe === 'H4' ? TF.h4 : img.timeframe === 'M15' ? TF.m15 : TF.m1) }} image</div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="currentTrade && currentTrade.notes" class="mt-2 flex-shrink-0 text-xs text-gray-500 truncate">{{ currentTrade.notes }}</div>
        <div class="mt-2 flex-shrink-0 text-center text-xs text-gray-700">← → arrow keys to navigate · Esc to close</div>
      </div>
    </div>
  </transition>
</template>

<script>
import { TF } from '@/timeframes.js'
export default {
  name: 'ImageLightbox',
  props: {
    visible:    { type: Boolean, default: false },
    src:        { type: String,  default: '' },
    timeframe:  { type: String,  default: '' },
    images:     { type: Array,   default: () => [] },
    startAt:    { type: String,  default: '' },
    trades:     { type: Array,   default: () => [] },
    tradeIndex: { type: Number,  default: 0 },
  },
  data() { return { TF, currentIndex: 0 } },
  watch: {
    visible(v)    { if (v) this.currentIndex = this.tradeIndex },
    tradeIndex(v) { this.currentIndex = v },
  },
  computed: {
    currentTrade()  { return this.trades.length ? this.trades[this.currentIndex] : null },
    currentImages() {
      if (this.trades.length && this.currentTrade) return this.currentTrade.images || []
      return this.images
    },
    orderedImages() {
      const order = ['H4', 'M15', 'M1']
      const map   = {}
      this.currentImages.forEach(img => { map[img.timeframe] = img })
      return order.map(tf => map[tf] || { timeframe: tf, path: null })
    },
  },
  mounted()       { window.addEventListener('keydown', this.onKey) },
  beforeDestroy() { window.removeEventListener('keydown', this.onKey) },
  methods: {
    close()  { this.$emit('close') },
    next()   { if (this.currentIndex < this.trades.length - 1) this.currentIndex++ },
    prev()   { if (this.currentIndex > 0) this.currentIndex-- },
    onKey(e) {
      if (!this.visible) return
      if (e.key === 'Escape')     this.close()
      if (e.key === 'ArrowRight') this.next()
      if (e.key === 'ArrowLeft')  this.prev()
    },
  },
}
</script>