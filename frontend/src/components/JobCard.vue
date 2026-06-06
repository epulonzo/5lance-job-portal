<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  job: {
    type: Object,
    required: true
  }
});

const isSaved = ref(false);

onMounted(() => {
  const savedJobs = JSON.parse(localStorage.getItem('5lance_saved_jobs')) || [];
  isSaved.value = savedJobs.includes(props.job.id);
});

const toggleSave = (e) => {
  e.preventDefault();
  e.stopPropagation();
  let savedJobs = JSON.parse(localStorage.getItem('5lance_saved_jobs')) || [];
  if (savedJobs.includes(props.job.id)) {
    savedJobs = savedJobs.filter(id => id !== props.job.id);
    isSaved.value = false;
  } else {
    savedJobs.push(props.job.id);
    isSaved.value = true;
  }
  localStorage.setItem('5lance_saved_jobs', JSON.stringify(savedJobs));
};
</script>

<template>
  <router-link
    :to="{ name: 'JobDetail', params: { id: job.id } }"
    class="block group bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-6 hover:shadow-xl hover:shadow-brand-500/5 hover:border-brand-500/30 transition-all duration-300 relative overflow-hidden"
  >
    <!-- Accent Line -->
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r opacity-0 group-hover:opacity-100 transition-opacity duration-300" :class="job.logoBg" />

    <div class="flex items-start justify-between gap-4">
      <div class="flex gap-4">
        <!-- Logo Avatar -->
        <div
          class="w-12 h-12 rounded-xl bg-gradient-to-br flex items-center justify-center font-bold text-sm shrink-0 shadow-inner"
          :class="[job.logoBg, job.logoColor || 'text-white']"
        >
          {{ job.logoText }}
        </div>
        
        <div>
          <!-- Company & Date -->
          <div class="flex items-center gap-2 mb-1.5 flex-wrap">
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ job.company }}</span>
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700" />
            <span class="text-xs text-slate-400 font-medium">{{ job.postedDate }}</span>
            <span v-if="job.featured" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-brand-500/10 text-brand-600 dark:text-brand-400">Featured</span>
          </div>

          <!-- Job Title -->
          <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors duration-200">
            {{ job.title }}
          </h3>
        </div>
      </div>

      <!-- Save Button -->
      <button
        @click="toggleSave"
        class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 transition-all cursor-pointer"
        aria-label="Save Job"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 transition-transform duration-200"
          :class="{ 'fill-rose-500 text-rose-500 scale-110': isSaved }"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
          />
        </svg>
      </button>
    </div>

    <!-- Details/Metrics -->
    <div class="flex flex-wrap items-center gap-y-2 gap-x-4 mt-4 text-xs font-semibold text-slate-500 dark:text-slate-400">
      <!-- Location -->
      <div class="flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>{{ job.location }}</span>
      </div>

      <!-- Type -->
      <div class="flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
        <span>{{ job.type }}</span>
      </div>

      <!-- Salary -->
      <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V5" />
        </svg>
        <span>{{ job.salary }}</span>
      </div>
    </div>

    <!-- Skills/Tags -->
    <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/60">
      <span
        v-for="skill in job.skills"
        :key="skill"
        class="px-2.5 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium"
      >
        {{ skill }}
      </span>
    </div>
  </router-link>
</template>
