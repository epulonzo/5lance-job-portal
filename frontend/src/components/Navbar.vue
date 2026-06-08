<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/authStore';
import logo from '../assets/logo.png';

const router = useRouter();
const authStore = useAuthStore();

const isMobileMenuOpen = ref(false);
const isProfileDropdownOpen = ref(false);
const isDark = ref(false);

onMounted(() => {
  // Check theme preference
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
    isDark.value = true;
  } else {
    document.documentElement.classList.remove('dark');
    isDark.value = false;
  }
});

const toggleTheme = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.setItem('theme', 'dark');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('theme', 'light');
  }
};

const handleLogout = () => {
  authStore.logout();
  isProfileDropdownOpen.value = false;
  router.push({ name: 'Home' });
};
</script>

<template>
  <nav class="sticky top-0 z-40 w-full glass border-b border-slate-200/50 dark:border-slate-800/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center">
          <router-link :to="{ name: 'Home' }" class="flex items-center gap-2 group">
            <div class="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center shadow-lg shadow-brand-500/20 group-hover:rotate-12 transition-transform duration-300 overflow-hidden">
              <img :src="logo" alt="5Lance" class="w-7 h-7 object-contain" />
            </div>
            <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-brand-600 to-purple-600 bg-clip-text text-transparent">5Lance</span>
          </router-link>
        </div>

        <!-- Desktop Navigation Links -->
        <div class="hidden md:flex items-center gap-6">
          <router-link
            :to="{ name: 'Jobs' }"
            class="text-sm font-semibold text-slate-600 hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400 transition-colors"
            active-class="text-brand-600 dark:text-brand-400 font-bold"
          >
            Find Jobs
          </router-link>

          <!-- Divider -->
          <span class="w-px h-5 bg-slate-200 dark:bg-slate-800" />

          <!-- Theme Toggle -->
          <button
            @click="toggleTheme"
            class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-all cursor-pointer"
            aria-label="Toggle Theme"
          >
            <!-- Sun Icon -->
            <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m2.828-9.9a5 5 0 11-7.07 7.07l.707-.707m1.414-1.414l-.707-.707" />
            </svg>
            <!-- Moon Icon -->
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- Guest State -->
          <div v-if="!authStore.isAuthenticated" class="flex items-center gap-3">
            <router-link
              :to="{ name: 'Login' }"
              class="text-sm font-semibold text-slate-700 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition-colors"
            >
              Sign In
            </router-link>
            <router-link
              :to="{ name: 'Register' }"
              class="px-4.5 py-2 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition-all shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 active:scale-95"
            >
              Join
            </router-link>
          </div>

          <!-- Logged In State -->
          <div v-else class="relative">
            <button
              @click="isProfileDropdownOpen = !isProfileDropdownOpen"
              class="flex items-center gap-2 text-left focus:outline-none cursor-pointer"
            >
              <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow-md">
                {{ authStore.user?.name ? authStore.user.name.split(' ').map(n => n[0]).join('') : 'U' }}
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Profile Dropdown Menu -->
            <transition
              enter-active-class="transition duration-100 ease-out"
              enter-from-class="transform scale-95 opacity-0"
              enter-to-class="transform scale-100 opacity-100"
              leave-active-class="transition duration-75 ease-in"
              leave-from-class="transform scale-100 opacity-100"
              leave-to-class="transform scale-95 opacity-0"
            >
              <div
                v-if="isProfileDropdownOpen"
                class="absolute right-0 mt-2 w-48 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl py-1 z-50 text-left"
              >
                <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800">
                  <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Signed in as</p>
                  <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ authStore.user?.name }}</p>
                </div>

                <!-- Recruiter Pages -->
                <template v-if="authStore.isAdmin">
                  <router-link
                    :to="{ name: 'Admin' }"
                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold"
                    @click="isProfileDropdownOpen = false"
                  >
                    Recruiter Panel
                  </router-link>
                </template>

                <!-- Candidate Pages -->
                <template v-else>
                  <router-link
                    :to="{ name: 'Dashboard' }"
                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold"
                    @click="isProfileDropdownOpen = false"
                  >
                    Dashboard
                  </router-link>
                  <router-link
                    :to="{ name: 'Profile' }"
                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold"
                    @click="isProfileDropdownOpen = false"
                  >
                    My Profile
                  </router-link>
                </template>

                <hr class="border-slate-100 dark:border-slate-800 my-1" />

                <button
                  @click="handleLogout"
                  class="w-full text-left block px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 font-semibold cursor-pointer"
                >
                  Sign Out
                </button>
              </div>
            </transition>
          </div>
        </div>

        <!-- Mobile menu button -->
        <div class="flex items-center gap-2 md:hidden">
          <!-- Theme Toggle Mobile -->
          <button
            @click="toggleTheme"
            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 transition-all cursor-pointer"
            aria-label="Toggle Theme"
          >
            <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m2.828-9.9a5 5 0 11-7.07 7.07l.707-.707m1.414-1.414l-.707-.707" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none cursor-pointer"
          >
            <svg v-if="!isMobileMenuOpen" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-if="isMobileMenuOpen" class="md:hidden border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-950/95 backdrop-blur-md">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
          <router-link
            :to="{ name: 'Jobs' }"
            class="block px-3 py-2.5 rounded-xl text-base font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
            active-class="bg-brand-500/10 text-brand-600 dark:text-brand-400"
            @click="isMobileMenuOpen = false"
          >
            Find Jobs
          </router-link>

          <template v-if="authStore.isAuthenticated">
            <template v-if="authStore.isAdmin">
              <router-link
                :to="{ name: 'Admin' }"
                class="block px-3 py-2.5 rounded-xl text-base font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                active-class="bg-brand-500/10 text-brand-600 dark:text-brand-400"
                @click="isMobileMenuOpen = false"
              >
                Recruiter Panel
              </router-link>
            </template>
            <template v-else>
              <router-link
                :to="{ name: 'Dashboard' }"
                class="block px-3 py-2.5 rounded-xl text-base font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                active-class="bg-brand-500/10 text-brand-600 dark:text-brand-400"
                @click="isMobileMenuOpen = false"
              >
                Dashboard
              </router-link>
              <router-link
                :to="{ name: 'Profile' }"
                class="block px-3 py-2.5 rounded-xl text-base font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                active-class="bg-brand-500/10 text-brand-600 dark:text-brand-400"
                @click="isMobileMenuOpen = false"
              >
                My Profile
              </router-link>
            </template>
            <button
              @click="handleLogout"
              class="w-full text-left block px-3 py-2.5 rounded-xl text-base font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20"
            >
              Sign Out
            </button>
          </template>

          <div v-else class="pt-4 pb-2 border-t border-slate-100 dark:border-slate-800 flex flex-col gap-2 px-3">
            <router-link
              :to="{ name: 'Login' }"
              class="flex items-center justify-center px-4 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-850"
              @click="isMobileMenuOpen = false"
            >
              Sign In
            </router-link>
            <router-link
              :to="{ name: 'Register' }"
              class="flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 rounded-xl text-sm font-semibold text-white shadow-md"
              @click="isMobileMenuOpen = false"
            >
              Join Now
            </router-link>
          </div>
        </div>
      </div>
    </transition>
  </nav>
</template>
