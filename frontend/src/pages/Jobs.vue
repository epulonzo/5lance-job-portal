<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../services/api';
import JobCard from '../components/JobCard.vue';
import Button from '../components/Button.vue';
import FormInput from '../components/FormInput.vue';

const route = useRoute();
const router = useRouter();

const jobs = ref([]);
const loading = ref(true);

const filters = reactive({
  keyword: '',
  location: '',
  category: '',
  type: '',
  experience: ''
});

// Categories list for dropdown
const categories = ['Development', 'Design', 'DevOps', 'Management'];
// Job types
const jobTypes = ['Full-time', 'Contract', 'Internship'];
// Experience levels
const experienceLevels = ['Junior', 'Mid-level', 'Mid-Senior', 'Senior'];

const loadJobs = async () => {
  loading.value = true;
  try {
    jobs.value = await api.getJobs();
  } catch (error) {
    console.error('Failed to load jobs:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  // Sync router query params to filters
  filters.keyword = route.query.keyword || '';
  filters.location = route.query.location || '';
  filters.category = route.query.category || '';
  filters.type = route.query.type || '';
  filters.experience = route.query.experience || '';
  loadJobs();
});

// Watch route query params changes (e.g. when category clicked on Home)
watch(() => route.query, (newQuery) => {
  filters.keyword = newQuery.keyword || '';
  filters.location = newQuery.location || '';
  filters.category = newQuery.category || '';
  filters.type = newQuery.type || '';
  filters.experience = newQuery.experience || '';
});

const filteredJobs = computed(() => {
  return jobs.value.filter(job => {
    // Keyword filter
    if (filters.keyword) {
      const query = filters.keyword.toLowerCase();
      const matchTitle = job.title.toLowerCase().includes(query);
      const matchCompany = job.company.toLowerCase().includes(query);
      const matchSkills = job.skills.some(skill => skill.toLowerCase().includes(query));
      if (!matchTitle && !matchCompany && !matchSkills) return false;
    }
    
    // Location filter
    if (filters.location) {
      const query = filters.location.toLowerCase();
      if (!job.location.toLowerCase().includes(query)) return false;
    }
    
    // Category filter
    if (filters.category && job.category !== filters.category) {
      return false;
    }
    
    // Type filter
    if (filters.type && job.type !== filters.type) {
      return false;
    }
    
    // Experience filter
    if (filters.experience && job.experience !== filters.experience) {
      return false;
    }
    
    return true;
  });
});

const clearFilters = () => {
  filters.keyword = '';
  filters.location = '';
  filters.category = '';
  filters.type = '';
  filters.experience = '';
  router.push({ name: 'Jobs' });
};
</script>

<template>
  <div class="space-y-8 py-4">
    <!-- Header -->
    <div class="text-left space-y-2">
      <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white">Find Your Opportunity</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400">Explore and filter through our active list of verified tech roles.</p>
    </div>

    <!-- Search & Filter Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
      <!-- Filter Sidebar (Desktop) -->
      <aside class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-6 space-y-6 sticky top-24">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white">Filters</h2>
          <button
            @click="clearFilters"
            class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-350 transition-colors cursor-pointer"
          >
            Reset All
          </button>
        </div>

        <hr class="border-slate-100 dark:border-slate-800" />

        <!-- Category Filter -->
        <div class="space-y-2.5 text-left">
          <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Category</label>
          <select
            v-model="filters.category"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm font-semibold transition-all"
          >
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>

        <!-- Job Type Filter -->
        <div class="space-y-2.5 text-left">
          <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Job Type</label>
          <select
            v-model="filters.type"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm font-semibold transition-all"
          >
            <option value="">All Types</option>
            <option v-for="type in jobTypes" :key="type" :value="type">{{ type }}</option>
          </select>
        </div>

        <!-- Experience Level Filter -->
        <div class="space-y-2.5 text-left">
          <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Experience Level</label>
          <select
            v-model="filters.experience"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm font-semibold transition-all"
          >
            <option value="">All Levels</option>
            <option v-for="level in experienceLevels" :key="level" :value="level">{{ level }}</option>
          </select>
        </div>
      </aside>

      <!-- Main Results Column -->
      <div class="lg:col-span-3 space-y-6">
        <!-- Search Input Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-4 shadow-sm">
          <FormInput
            v-model="filters.keyword"
            label="Search Keywords"
            placeholder="Title, company, or skills..."
          />
          <FormInput
            v-model="filters.location"
            label="Location"
            placeholder="City, state, or 'Remote'"
          />
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="space-y-4">
          <div v-for="i in 4" :key="i" class="h-[140px] rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 animate-pulse" />
        </div>

        <!-- Results -->
        <div v-else>
          <div class="flex items-center justify-between mb-4 text-sm font-semibold text-slate-500">
            <span>Showing {{ filteredJobs.length }} results</span>
          </div>

          <!-- Empty State -->
          <div
            v-if="filteredJobs.length === 0"
            class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-12 text-center space-y-4 shadow-sm"
          >
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto text-slate-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">No jobs match your criteria</h3>
            <p class="text-sm text-slate-400 max-w-sm mx-auto">Try broadening your search keywords or clearing some filters to find more opportunities.</p>
            <Button variant="outline" size="sm" @click="clearFilters">Clear All Filters</Button>
          </div>

          <!-- Jobs list -->
          <div v-else class="space-y-4">
            <JobCard v-for="job in filteredJobs" :key="job.id" :job="job" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
