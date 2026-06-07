<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Navbar from '../components/Navbar.vue';
import logo from '../assets/logo.png';

const toast = ref({
  show: false,
  message: '',
  type: 'success' // success, error, info
});

let toastTimeout = null;

const showToastHandler = (event) => {
  const { message, type = 'success' } = event.detail;
  
  // Clear existing timeout
  if (toastTimeout) {
    clearTimeout(toastTimeout);
  }
  
  toast.value = {
    show: true,
    message,
    type
  };
  
  toastTimeout = setTimeout(() => {
    toast.value.show = false;
  }, 4000);
};

onMounted(() => {
  window.addEventListener('show-toast', showToastHandler);
  
  // Expose helper globally
  window.toast = (message, type = 'success') => {
    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message, type } }));
  };
});

onUnmounted(() => {
  window.removeEventListener('show-toast', showToastHandler);
  if (toastTimeout) {
    clearTimeout(toastTimeout);
  }
});
</script>

<template>
  <div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    <!-- Navbar -->
    <Navbar />

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 relative">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800/80 transition-colors duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <!-- Col 1: Brand -->
          <div class="space-y-4 md:col-span-2">
            <div class="flex items-center gap-2">
              <img :src="logo" alt="5Lance" class="w-8 h-8" />
              <span class="text-lg font-bold tracking-tight text-slate-800 dark:text-white">5Lance</span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm">
              Discover and secure your next premium full-time or contract opportunity. Built for developers, designers, and cloud builders.
            </p>
          </div>

          <!-- Col 2: Candidates -->
          <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">For Talent</h4>
            <ul class="space-y-2 text-sm font-semibold">
              <li>
                <router-link :to="{ name: 'Jobs' }" class="text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Browse Jobs</router-link>
              </li>
              <li>
                <router-link :to="{ name: 'Dashboard' }" class="text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Candidate Panel</router-link>
              </li>
              <li>
                <router-link :to="{ name: 'Profile' }" class="text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Profile Settings</router-link>
              </li>
            </ul>
          </div>

          <!-- Col 3: Employers -->
          <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">For Recruiters</h4>
            <ul class="space-y-2 text-sm font-semibold">
              <li>
                <router-link :to="{ name: 'Admin' }" class="text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Post a Job</router-link>
              </li>
              <li>
                <router-link :to="{ name: 'Admin' }" class="text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Applications Track</router-link>
              </li>
            </ul>
          </div>
        </div>

        <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold text-slate-400">
          <span>&copy; {{ new Date().getFullYear() }} 5Lance Job Portal. All rights reserved.</span>
          <div class="flex items-center gap-4">
            <span class="hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer transition-colors">Terms of Service</span>
            <span class="hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer transition-colors">Privacy Policy</span>
          </div>
        </div>
      </div>
    </footer>

    <!-- Toast Notification Popup -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
      leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
    >
      <div
        v-if="toast.show"
        class="fixed bottom-5 right-5 z-50 flex items-center gap-3 px-4.5 py-3 rounded-2xl border shadow-xl backdrop-blur-md"
        :class="{
          'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400': toast.type === 'success',
          'bg-rose-500/10 border-rose-500/20 text-rose-600 dark:text-rose-400': toast.type === 'error',
          'bg-slate-500/10 border-slate-500/20 text-slate-600 dark:text-slate-400': toast.type === 'info'
        }"
      >
        <!-- Success icon -->
        <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <!-- Error icon -->
        <svg v-else-if="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <!-- Info icon -->
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        
        <span class="text-sm font-bold">{{ toast.message }}</span>

        <!-- Close button -->
        <button
          @click="toast.show = false"
          class="text-current opacity-70 hover:opacity-100 ml-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </transition>
  </div>
</template>
