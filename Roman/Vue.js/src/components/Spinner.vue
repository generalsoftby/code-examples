<template>
  <div class="spinner-section">
    <b-spinner variant="primary" label="Loading..."></b-spinner>
  </div>
</template>

<script>
export default {
  name: 'Spinner',
}
</script>

<style scoped>
  .spinner-section{
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    position: fixed;
  }
</style>
