export default {
  content: ['./resources/**/*.blade.php', './resources/**/*.js', './resources/**/*.vue'],
  theme: { extend: {} },
  plugins: [],
  theme: {
    extend: {
      colors: {
        win: '#1D9E75',
        loss: '#E24B4A',
        neutral: '#6B7E8F',
        surface: '#0F1923',
        card: '#1A2633',
      }
    }
  }
}