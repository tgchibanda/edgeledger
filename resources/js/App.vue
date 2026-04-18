<template><div id="app"><router-view /></div></template>
<script>
export default {
  name: 'App',
  created() {
    const token = localStorage.getItem('el_token')
    const user  = localStorage.getItem('el_user')
    if (token && user) {
      this.$store.commit('auth/SET_TOKEN', token)
      this.$store.commit('auth/SET_USER', JSON.parse(user))
    }
  }
}
</script>
