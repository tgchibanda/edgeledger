<template>
  <div class="card hover:bg-card-hover transition-colors">
    <div class="flex items-start justify-between gap-3 flex-wrap">
      <div class="flex items-center gap-2 flex-wrap">
        <span :class="resultBadge">{{ (trade.result || '').toUpperCase() }}</span>
        <span v-if="trade.is_reference" class="badge-ref">⭐ Reference</span>
        <span v-if="!trade.is_valid" class="badge-invalid">✗ Invalid</span>
        <span class="text-sm font-semibold text-white">{{ trade.entry_technique }}</span>
      </div>
      <div class="flex items-center gap-2">
        <span v-if="trade.pair"    class="badge-neutral">{{ trade.pair.symbol }}</span>
        <span v-if="trade.session" class="badge-neutral">{{ trade.session.name }}</span>
        <span v-if="trade.trade_date" class="text-xs text-gray-600">{{ formatDate(trade.trade_date) }}</span>
      </div>
    </div>

    <div class="mt-2 flex items-center gap-2 text-xs text-gray-500 flex-wrap">
      <span :class="TF_COLORS.h4">{{ TF.h4 }}:</span><span>{{ trade.h4 && trade.h4.name }}</span>
      <span class="text-gray-600">→</span>
      <span :class="TF_COLORS.m15">{{ TF.m15 }}:</span><span>{{ trade.m15 && trade.m15.name }}</span>
      <span class="text-gray-600">→</span>
      <span :class="TF_COLORS.m1">{{ TF.m1 }}:</span><span>{{ trade.m1 && trade.m1.name }}</span>
    </div>

    <div v-if="trade.pips_result || trade.r_multiple" class="mt-2 flex gap-4 text-xs">
      <span v-if="trade.pips_result" :class="trade.pips_result > 0 ? 'text-win' : 'text-loss'">
        Pips: {{ trade.pips_result > 0 ? '+' : '' }}{{ trade.pips_result }}
      </span>
      <span v-if="trade.r_multiple" :class="trade.r_multiple > 0 ? 'text-win' : 'text-loss'">
        R: {{ trade.r_multiple > 0 ? '+' : '' }}{{ trade.r_multiple }}R
      </span>
    </div>

    <div v-if="trade.notes" class="mt-2 text-xs text-gray-500 line-clamp-2">{{ trade.notes }}</div>

    <!-- Image thumbnails -->
    <div v-if="trade.images && trade.images.length" class="mt-3">
      <div class="flex gap-2">
        <div
          v-for="img in orderedImages"
          :key="img.timeframe"
          class="flex-1 relative group cursor-pointer rounded-lg overflow-hidden border border-border hover:border-win transition-colors"
          style="height: 160px"
          @click="$emit('view-images', { images: trade.images, startAt: img.timeframe })">
          <img v-if="img.path" :src="`/api/images/${img.path}`" class="w-full h-full object-contain bg-gray-900" />
          <div v-else class="w-full h-full bg-surface flex items-center justify-center">
            <span class="text-gray-700 text-xs">No image</span>
          </div>
          <div class="absolute bottom-0 left-0 right-0 px-2 py-0.5 flex items-center justify-between" style="background:rgba(0,0,0,0.6)">
            <span class="text-xs font-bold" :class="TF_BADGE_TEXT[img.timeframe]">{{ img.timeframe === 'H4' ? TF.h4 : img.timeframe === 'M15' ? TF.m15 : TF.m1 }}</span>
            <span v-if="img.path" class="text-gray-400 text-xs opacity-0 group-hover:opacity-100 transition-opacity">🔍</span>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-3 flex gap-2 justify-end">
      <slot name="actions" />
    </div>
  </div>
</template>

<script>
import { TF, TF_COLORS } from '@/timeframes.js'
export default {
  name: 'TradeCard',
  props: { trade: { type: Object, required: true } },
  data() {
    return {
      TF,
      TF_COLORS,
      TF_BADGE_TEXT: { H4: 'text-blue-300', M15: 'text-purple-300', M1: 'text-yellow-300' },
    }
  },
  computed: {
    resultBadge() {
      if (this.trade.result === 'win')  return 'badge-win'
      if (this.trade.result === 'loss') return 'badge-loss'
      return 'badge-neutral'
    },
    orderedImages() {
      const order = ['H4', 'M15', 'M1']
      const map   = {}
      if (this.trade.images) this.trade.images.forEach(img => { map[img.timeframe] = img })
      return order.map(tf => map[tf] || { timeframe: tf, path: null })
    },
  },
  methods: {
    formatDate(d) { return d ? new Date(d).toLocaleDateString() : '' },
  },
}
</script>