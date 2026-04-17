<template>
  <div class="rounded-xl border-2 p-4 transition-colors" :class="value ? 'border-win bg-win/5' : 'border-loss bg-loss/5'">
    <div class="flex items-center justify-between">
      <div>
        <div class="font-semibold text-white text-sm">Followed trading rules?</div>
        <div class="text-xs mt-0.5" :class="value ? 'text-win' : 'text-loss'">
          {{ value ? '✓ Valid trade — rules followed' : '✗ Invalid trade — rules not followed' }}
        </div>
      </div>
      <div class="flex gap-2">
        <button type="button" @click="$emit('input', true)"
          class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
          :class="value === true ? 'bg-win text-white' : 'bg-surface text-gray-400 border border-border hover:border-win'">
          YES
        </button>
        <button type="button" @click="$emit('input', false)"
          class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
          :class="value === false ? 'bg-loss text-white' : 'bg-surface text-gray-400 border border-border hover:border-loss'">
          NO
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RulesToggle',
  props: { value: { type: Boolean, default: null } },
}
</script>
