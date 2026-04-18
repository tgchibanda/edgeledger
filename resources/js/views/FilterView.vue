<template>
  <AppLayout>
    <div class="p-6 max-w-5xl">
      <div class="page-header">
        <h1 class="page-title">Pre-Trade Filter</h1>
        <p class="page-sub">Select your current market conditions to find matching setups.</p>
      </div>

      <div class="card mb-6">
        <h2 class="section-title">Current Market Conditions</h2>
        <CascadeFilter ref="filter" @results="onResults" @cleared="onCleared" @loading="loading=$event" />
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-500">Searching database…</div>

      <div v-else-if="searched">
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div class="card text-center">
            <div class="text-2xl font-bold text-white">{{ stats.total }}</div>
            <div class="text-xs text-gray-500 mt-1">Matching Trades</div>
          </div>
          <div class="card text-center">
            <div class="text-2xl font-bold" :class="rateClass(stats.win_rate)">{{ stats.win_rate }}%</div>
            <div class="text-xs text-gray-500 mt-1">Win Rate</div>
          </div>
          <div class="card text-center">
            <div class="text-2xl font-bold text-win">{{ stats.wins }}</div>
            <div class="text-xs text-gray-500 mt-1">Wins</div>
          </div>
          <div class="card text-center">
            <div class="text-2xl font-bold text-loss">{{ stats.losses }}</div>
            <div class="text-xs text-gray-500 mt-1">Losses</div>
          </div>
        </div>

        <div v-if="stats.expectancy_r != null" class="card mb-6">
          <div class="flex items-center gap-6 flex-wrap">
            <div>
              <div class="text-xs text-gray-500">Expectancy</div>
              <div class="text-xl font-bold" :class="stats.expectancy_r > 0 ? 'text-win' : 'text-loss'">{{ stats.expectancy_r }}R</div>
            </div>
            <div v-if="stats.avg_win_r">
              <div class="text-xs text-gray-500">Avg Win</div>
              <div class="text-xl font-bold text-win">+{{ stats.avg_win_r }}R</div>
            </div>
            <div v-if="stats.avg_loss_r">
              <div class="text-xs text-gray-500">Avg Loss</div>
              <div class="text-xl font-bold text-loss">{{ stats.avg_loss_r }}R</div>
            </div>
          </div>
        </div>

        <!-- By Entry -->
        <div class="card mb-6" v-if="stats.by_entry && stats.by_entry.length">
          <h2 class="section-title">Win Rate by Entry Technique</h2>
          <table class="w-full">
            <thead class="border-b border-border">
              <tr>
                <th class="th">Entry Technique</th>
                <th class="th text-right">Total</th>
                <th class="th text-right">Win Rate</th>
                <th class="th text-right">Avg R</th>
                <th class="th"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="e in stats.by_entry" :key="e.entry_technique" class="hover:bg-card-hover">
                <td class="td font-medium text-white">{{ e.entry_technique }}</td>
                <td class="td text-right">{{ e.total }}</td>
                <td class="td text-right font-bold" :class="rateClass(e.win_rate)">{{ e.win_rate }}%</td>
                <td class="td text-right" :class="e.avg_r > 0 ? 'text-win' : e.avg_r < 0 ? 'text-loss' : 'text-gray-500'">
                  {{ e.avg_r ? (e.avg_r > 0 ? '+' : '') + e.avg_r + 'R' : '-' }}
                </td>
                <td class="td">
                  <button @click="startJournal(e.entry_technique)" class="btn-primary btn-sm">📓 Journal This</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Matching trades -->
        <div v-if="trades.length" class="card">
          <h2 class="section-title">Historical Examples ({{ trades.length }})</h2>
          <div class="space-y-3">
            <TradeCard v-for="t in trades" :key="t.id" :trade="t" @view-images="openLightbox">
              <template #actions>
                <button @click="startJournal(t.entry_technique)" class="btn-secondary btn-sm">📓 Journal</button>
              </template>
            </TradeCard>
          </div>
        </div>

        <div v-if="!trades.length" class="card text-center py-10">
          <div class="text-4xl mb-3">🔍</div>
          <p class="text-gray-500">No matching trades found for these conditions.</p>
          <p class="text-gray-600 text-sm mt-1">Add more trades to your database to see results here.</p>
        </div>
      </div>

      <ImageLightbox
        :visible="lightbox.visible"
        :trades="trades"
        :trade-index="lightbox.tradeIndex"
        :start-at="lightbox.startAt"
        @close="lightbox.visible=false"
      />
    </div>
  </AppLayout>
</template>

<script>
import AppLayout      from '../components/AppLayout.vue'
import CascadeFilter  from '../components/CascadeFilter.vue'
import TradeCard      from '../components/TradeCard.vue'
import ImageLightbox  from '../components/ImageLightbox.vue'
export default {
  name: 'FilterView',
  components: { AppLayout, CascadeFilter, TradeCard, ImageLightbox },
  data() {
    return {
      trades: [], stats: {}, loading: false, searched: false,
      lightbox: { visible: false, tradeIndex: 0, startAt: '' },
    }
  },
  methods: {
    onResults(data) { this.trades = data.trades; this.stats = data.stats; this.searched = true },
    onCleared()     { this.trades = []; this.stats = {}; this.searched = false },
    rateClass(r)    { return r >= 60 ? 'text-win' : r >= 40 ? 'text-yellow-400' : 'text-loss' },
    startJournal(entry_technique) {
      const form = this.$refs.filter.getForm()
      this.$router.push({ name:'journal-new', query: { ...form, entry_technique } })
    },
    openLightbox({ images, startAt }) {
      const tradeIndex = this.trades.findIndex(t => t.images && t.images.some(i => images.some(img => img.id === i.id)))
      this.lightbox = { visible: true, tradeIndex: tradeIndex >= 0 ? tradeIndex : 0, startAt }
    },
  },
}
</script>