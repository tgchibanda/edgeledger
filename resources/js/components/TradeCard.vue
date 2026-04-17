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
        <span v-if="trade.pair" class="badge-neutral">{{ trade.pair.symbol }}</span>
        <span v-if="trade.session" class="badge-neutral">{{ trade.session.name }}</span>
        <span v-if="trade.trade_date" class="text-xs text-gray-600">{{ formatDate(trade.trade_date) }}</span>
      </div>
    </div>

    <div class="mt-2 flex items-center gap-2 text-xs text-gray-500 flex-wrap">
      <span class="text-blue-400">H4:</span><span>{{ trade.h4 && trade.h4.name }}</span>
      <span class="text-gray-600">→</span>
      <span class="text-purple-400">M15:</span><span>{{ trade.m15 && trade.m15.name }}</span>
      <span class="text-gray-600">→</span>
      <span class="text-yellow-400">M1:</span><span>{{ trade.m1 && trade.m1.name }}</span>
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

    <!-- Images -->
    <div v-if="trade.images && trade.images.length" class="mt-3 flex gap-2">
      <div v-for="img in trade.images" :key="img.id"
        class="flex items-center gap-1 px-2 py-1 bg-surface rounded text-xs text-gray-400 border border-border cursor-pointer hover:border-win"
        @click="$emit('view-image', img)">
        📷 {{ img.timeframe }}
      </div>
    </div>

    <div class="mt-3 flex gap-2 justify-end">
      <slot name="actions" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'TradeCard',
  props: { trade: { type: Object, required: true } },
  computed: {
    resultBadge() {
      if (this.trade.result === 'win')       return 'badge-win'
      if (this.trade.result === 'loss')      return 'badge-loss'
      return 'badge-neutral'
    },
  },
  methods: {
    formatDate(d) { return d ? new Date(d).toLocaleDateString() : '' },
  },
}
</script>
