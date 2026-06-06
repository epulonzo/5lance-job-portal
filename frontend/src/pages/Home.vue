<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { api } from '../services/api';
import JobCard from '../components/JobCard.vue';
import Button from '../components/Button.vue';

const router = useRouter();

const searchKeyword = ref('');
const searchLocation = ref('');
const featuredJobs = ref([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const jobs = await api.getJobs();
    featuredJobs.value = jobs.filter(j => j.featured).slice(0, 3);
  } catch (error) {
    console.error('Failed to load featured jobs:', error);
  } finally {
    loading.value = false;
  }
});

const handleSearch = () => {
  router.push({
    name: 'Jobs',
    query: {
      keyword: searchKeyword.value,
      location: searchLocation.value
    }
  });
};

const filterCategory = (category) => {
  router.push({
    name: 'Jobs',
    query: { category }
  });
};
</script>

<template>
  <div class="space-y-16 py-4">
    <!-- Hero Section -->
    <section class="relative text-center max-w-4xl mx-auto space-y-6 pt-10 pb-6">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-brand-500/10 text-brand-600 dark:text-brand-400 mb-2">
        <span class="flex h-2 w-2 relative">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
        </span>
        New freelancing & full-time roles posted today
      </div>

      <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
        Find Your Next <br />
        <span class="bg-gradient-to-r from-brand-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">Premium Tech Role</span>
      </h1>
      
      <p class="text-lg text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">
        5Lance connects top-tier developers, designer experts, and cloud architects to leading companies and hyper-growth startups.
      </p>

      <!-- Search Bar -->
      <form
        @submit.prevent="handleSearch"
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-2 sm:p-3 rounded-2xl sm:rounded-3xl shadow-xl shadow-brand-500/5 flex flex-col sm:flex-row gap-2 max-w-3xl mx-auto"
      >
        <div class="flex-grow flex items-center gap-2 px-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchKeyword"
            type="text"
            placeholder="Job title, keywords, or skills..."
            class="w-full py-2 bg-transparent text-slate-800 dark:text-slate-100 outline-none text-sm"
          />
        </div>
        
        <div class="hidden sm:block w-px h-8 bg-slate-200 dark:bg-slate-800 align-middle self-center" />

        <div class="flex-grow flex items-center gap-2 px-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <input
            v-model="searchLocation"
            type="text"
            placeholder="City, State, or 'Remote'"
            class="w-full py-2 bg-transparent text-slate-800 dark:text-slate-100 outline-none text-sm"
          />
        </div>

        <Button
          type="submit"
          variant="primary"
          class="shrink-0"
        >
          Search Jobs
        </Button>
      </form>
    </section>

    <!-- Categories Section -->
    <section class="space-y-6 text-center">
      <div class="space-y-2">
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">Explore by Category</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Find opportunities tailored to your specialization</p>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Development -->
        <button
          @click="filterCategory('Development')"
          class="group p-6 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl hover:border-brand-500/30 hover:shadow-lg transition-all duration-300 text-left cursor-pointer"
        >
          <div class="w-10 h-10 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
            </svg>
          </div>
          <h3 class="font-bold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">Development</h3>
          <p class="text-xs text-slate-400 mt-1">Web, Mobile & Apps</p>
        </button>

        <!-- Design -->
        <button
          @click="filterCategory('Design')"
          class="group p-6 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl hover:border-brand-500/30 hover:shadow-lg transition-all duration-300 text-left cursor-pointer"
        >
          <div class="w-10 h-10 rounded-xl bg-pink-500/10 text-pink-600 dark:text-pink-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="font-bold text-slate-900 dark:text-white group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors">UI/UX Design</h3>
          <p class="text-xs text-slate-400 mt-1">Product & Brand Design</p>
        </button>

        <!-- DevOps -->
        <button
          @click="filterCategory('DevOps')"
          class="group p-6 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl hover:border-brand-500/30 hover:shadow-lg transition-all duration-300 text-left cursor-pointer"
        >
          <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <h3 class="font-bold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">DevOps & Cloud</h3>
          <p class="text-xs text-slate-400 mt-1">Infrastructure & Deploy</p>
        </button>

        <!-- Management -->
        <button
          @click="filterCategory('Management')"
          class="group p-6 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl hover:border-brand-500/30 hover:shadow-lg transition-all duration-300 text-left cursor-pointer"
        >
          <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h3 class="font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Management</h3>
          <p class="text-xs text-slate-400 mt-1">Product & Roadmaps</p>
        </button>
      </div>
    </section>

    <!-- Featured Jobs Section -->
    <section class="space-y-6">
      <div class="flex items-end justify-between">
        <div class="space-y-2">
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">Featured Opportunities</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">Hand-picked premium positions with competitive compensation</p>
        </div>
        <router-link
          :to="{ name: 'Jobs' }"
          class="hidden sm:inline-flex items-center gap-1 text-sm font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-350 transition-colors"
        >
          View all jobs
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </router-link>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="i in 3" :key="i" class="h-[210px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 animate-pulse" />
      </div>

      <!-- Jobs Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <JobCard v-for="job in featuredJobs" :key="job.id" :job="job" />
      </div>

      <div class="text-center sm:hidden">
        <Button variant="outline" size="md" @click="router.push({ name: 'Jobs' })">
          View all jobs
        </Button>
      </div>
    </section>

    <!-- Trust Stats Metrics -->
    <section class="p-8 sm:p-12 rounded-3xl bg-slate-900 text-white relative overflow-hidden shadow-2xl">
      <div class="absolute -top-24 -left-24 w-60 h-60 rounded-full bg-brand-500/10 blur-3xl" />
      <div class="absolute -bottom-24 -right-24 w-60 h-60 rounded-full bg-purple-500/10 blur-3xl" />

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center relative z-10">
        <div class="space-y-1">
          <p class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-purple-400">12,000+</p>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active Listings</p>
        </div>
        <div class="space-y-1 border-t sm:border-t-0 sm:border-x border-slate-850 pt-6 sm:pt-0">
          <p class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">500+</p>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Top Brands</p>
        </div>
        <div class="space-y-1 border-t sm:border-t-0 pt-6 sm:pt-0">
          <p class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-brand-400">99.2%</p>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Successful Match</p>
        </div>
      </div>
    </section>
  </div>
</template>
