<template>
  <div class="not-found-section">
    <div class="container text-left not-found-text-section">
      <h1>OOOOoooopppssss ...</h1>
      <h2>I'm sorry very well</h2>
      <h3>But I can not understand what you want from me</h3>
      <h4>So you see this message</h4>
      <h5>P.S: This is a 404 error, if that tells you something ..</h5>
      <h6>P.S:P.S: I don't even know what it is</h6>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFound'
}
</script>

<style scoped>
  .not-found-section{
    width: 100%;
    height: 100%;
    position: fixed;
    background: url('../../public/Images/404.png') no-repeat center center;
    background-size: cover;
  }
  .not-found-text-section{
    margin-top: 25px;
  }
</style>
