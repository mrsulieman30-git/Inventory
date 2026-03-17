<template>
  <div class="flex h-screen overflow-hidden bg-slate-50 dark:bg-dark-bg transition-colors duration-300">

    <!-- Sidebar Navigation -->
    <aside class="w-64 glass-card m-4 flex flex-col justify-between hidden md:flex border-r border-slate-200 dark:border-dark-border z-20">
      <div>
        <div class="h-20 flex items-center justify-center border-b border-slate-100 dark:border-dark-border/50">
          <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-500 to-action-info">
            MediSphere V5
          </h1>
        </div>

        <nav class="p-4 space-y-2">
          <a href="#" class="nav-item active">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard
          </a>
          <a href="#" class="nav-item">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Virtual Racks
          </a>
          <a href="#" class="nav-item">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            POS Checkout
          </a>
          <a href="#" class="nav-item text-action-warning">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Alerts & eMAR
          </a>
        </nav>
      </div>

      <!-- Theme Toggle -->
      <div class="p-4 border-t border-slate-100 dark:border-dark-border/50">
        <button @click="toggleTheme" class="w-full flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200">
          <span>{{ isDark ? 'Dark Mode' : 'Light Mode' }}</span>
          <svg v-if="isDark" class="w-5 h-5 text-action-warning" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
          <svg v-else class="w-5 h-5 text-action-info" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path></svg>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col relative overflow-hidden bg-transparent">

      <!-- Top Header -->
      <header class="h-20 flex items-center justify-end px-8 z-10">
        <div class="flex items-center space-x-4">
          <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-action-success opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-action-success"></span>
          </span>
          <span class="text-sm font-medium">IoT Sensors Active</span>

          <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-primary-500 to-action-info flex items-center justify-center text-white font-bold ml-6 shadow-md cursor-pointer hover:scale-105 transition-transform">
            JD
          </div>
        </div>
      </header>

      <!-- Page Content with Animation -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto px-4 md:px-8 pb-8 custom-scrollbar">
        <router-view v-slot="{ Component }">
          <transition name="page" mode="out-in">
            <slot />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const isDark = ref(false);

const toggleTheme = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.theme = 'dark';
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.theme = 'light';
  }
};

onMounted(() => {
  if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true;
    document.documentElement.classList.add('dark');
  } else {
    isDark.value = false;
    document.documentElement.classList.remove('dark');
  }
});
</script>

<style scoped>
.nav-item {
  @apply flex items-center px-4 py-3 text-slate-600 dark:text-slate-400 font-medium rounded-xl transition-all duration-300 hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-dark-surface dark:hover:text-primary-500;
}

.nav-item.active {
  @apply bg-primary-500 text-white dark:bg-primary-600 shadow-md shadow-primary-500/20 hover:bg-primary-600 hover:text-white dark:hover:text-white;
}
</style>
