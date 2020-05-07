<template>
  <div>
    <h4>
      Formula: 
      <code>{{formula || 'figures formula'}}</code>
    </h4>    
    <img :src='require("../../../public/Images/" + imageName)' :alt='imageName || "figures image"' height="100" /> 
  </div>
</template>

<script>
export default {
  name: 'FiguresInformation',
  props: ['formula', 'imageName'],
}
</script>

<style>

</style>
