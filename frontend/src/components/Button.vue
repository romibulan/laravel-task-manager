<template>
  <div>
    <button
      v-bind="{
        ...$attrs,
        class:
          defaultClasses +
          (loading
            ? ' bg-gray-400 cursor-not-allowed'
            : ' bg-blue-500 hover:bg-blue-600'),
      }"
      :disabled="loading"
    >
      <VueSpinner v-if="loading" :size="20" color="#fff" />
      <span><slot></slot></span>
    </button>
  </div>
</template>

<script setup>
import { VueSpinner } from "vue3-spinners";
defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
});
const defaultClasses =
  "px-4 py-2 text-white rounded-md transition-colors duration-300 inline-flex space-x-1 cursor-pointer";
</script>

<style lang="scss" scoped></style>
