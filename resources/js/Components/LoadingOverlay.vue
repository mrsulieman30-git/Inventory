<template>
  <Transition name="fade">
    <div v-if="loading" class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-white/70 dark:bg-dark-bg/80 backdrop-blur-md">
      <!-- Minimalist Baidu/Apple style spinner -->
      <div class="relative w-24 h-24">
        <div class="absolute inset-0 rounded-full border-[3px] border-slate-200 dark:border-slate-700"></div>
        <div class="absolute inset-0 rounded-full border-[3px] border-primary-500 border-t-transparent animate-spin"></div>

        <!-- Central glowing core -->
        <div class="absolute inset-1/4 rounded-full bg-gradient-to-tr from-primary-400 to-action-info blur-sm animate-pulse opacity-50"></div>
      </div>

      <p class="mt-6 text-lg font-medium tracking-wide text-slate-700 dark:text-slate-200 animate-pulse">
        {{ message }}
      </p>
    </div>
  </Transition>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  loading: {
    type: Boolean,
    default: false
  },
  message: {
    type: String,
    default: 'Processing...'
  }
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
