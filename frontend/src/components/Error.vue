<template>
  <div>
    <span class="text-red-500 text-sm" v-if="error" v-html="error"></span>
  </div>
</template>

<script setup>
const props = defineProps({
  error: {
    type: String,
  },
});
</script>

<style lang="scss" scoped></style>
