<template>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="border-b border-border">
        <tr>
          <th class="th">{{ TF.h4 }}</th>
          <th class="th">{{ TF.m15 }}</th>
          <th class="th">{{ TF.m1 }}</th>
          <th class="th">Entry</th>
          <th class="th">Pair</th>
          <th class="th text-right">Total</th>
          <th class="th text-right">Wins</th>
          <th class="th text-right">Losses</th>
          <th class="th text-right">Win Rate</th>
          <th v-if="showR" class="th text-right">Avg Win R</th>
          <th v-if="showR" class="th text-right">Avg Loss R</th>
          <th v-if="showR" class="th text-right">Expectancy</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border">
        <tr v-if="!rows.length">
          <td colspan="12" class="td text-center text-gray-600 py-8">No data yet. Add trades to your database to see analytics.</td>
        </tr>
        <tr v-for="(row, i) in rows" :key="i" class="hover:bg-card-hover transition-colors">
          <td class="td text-blue-400">{{ row.h4 }}</td>
          <td class="td text-purple-400">{{ row.m15 }}</td>
          <td class="td text-yellow-400">{{ row.m1 }}</td>
          <td class="td font-medium text-white">{{ row.entry_technique }}</td>
          <td class="td">{{ row.pair || '-' }}</td>
          <td class="td text-right">{{ row.total }}</td>
          <td class="td text-right text-win">{{ row.wins }}</td>
          <td class="td text-right text-loss">{{ row.losses }}</td>
          <td class="td text-right">
            <span class="font-bold text-base" :class="rateClass(row.win_rate)">{{ row.win_rate }}%</span>
            <div class="w-16 h-1.5 bg-surface rounded-full mt-1 ml-auto">
              <div class="h-full rounded-full" :class="rateClass(row.win_rate).replace('text-','bg-')" :style="{width: row.win_rate+'%'}"></div>
            </div>
          </td>
          <td v-if="showR" class="td text-right" :class="row.avg_win_r > 0 ? 'text-win' : 'text-gray-500'">
            {{ row.avg_win_r != null ? '+'+row.avg_win_r+'R' : '-' }}
          </td>
          <td v-if="showR" class="td text-right" :class="row.avg_loss_r < 0 ? 'text-loss' : 'text-gray-500'">
            {{ row.avg_loss_r != null ? row.avg_loss_r+'R' : '-' }}
          </td>
          <td v-if="showR" class="td text-right font-semibold" :class="row.expectancy_r > 0 ? 'text-win' : row.expectancy_r < 0 ? 'text-loss' : 'text-gray-500'">
            {{ row.expectancy_r != null ? row.expectancy_r+'R' : '-' }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { TF } from '@/timeframes.js'
export default {
  name: 'WinRateTable',
  props: {
    rows:  { type: Array,   default: () => [] },
    showR: { type: Boolean, default: true },
  },
  data() { return { TF } },
  methods: {
    rateClass(r) {
      if (r >= 60) return 'text-win'
      if (r >= 40) return 'text-yellow-400'
      return 'text-loss'
    },
  },
}
</script>