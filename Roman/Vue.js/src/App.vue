<template>
  <div id="app">
    <header>
      <Header />
    </header>
    <main>
      <router-view></router-view>
    </main>
    
  </div>
</template>

<script>
import Header from '@/components/Header.vue'

export default {
  name: 'app',
  components: {
    Header
  },
  watch: {
    $route(to) {
        document.title = to.meta.title || 'Figures';
    },
  }
}
</script>