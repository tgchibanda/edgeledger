/**
 * EdgeLedger — Timeframe Label Configuration
 *
 * Change these three labels to match any trading methodology.
 * The database columns (h4_category_id, m15_category_id, m1_category_id)
 * never change — only what the user sees changes here.
 *
 * Examples:
 *   ICT/SMC:       HTF / MTF / Entry TF
 *   Swing trading: Weekly / Daily / 4H
 *   Scalping:      1H / 15M / 5M
 *   Default SMC:   H4 / M15 / M1
 */

export const TF = {
  h4:  'HTF',
  m15: 'MTF',
  m1:  'Entry TF',
}

// Short colour classes used in templates
export const TF_COLORS = {
  h4:  'text-blue-400',
  m15: 'text-purple-400',
  m1:  'text-yellow-400',
}

export const TF_BADGE_COLORS = {
  h4:  'bg-blue-900/60 text-blue-300',
  m15: 'bg-purple-900/60 text-purple-300',
  m1:  'bg-yellow-900/60 text-yellow-300',
}