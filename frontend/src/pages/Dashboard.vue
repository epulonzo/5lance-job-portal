<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/authStore';
import { api } from '../services/api';
import JobCard from '../components/JobCard.vue';
import Button from '../components/Button.vue';

const authStore = useAuthStore();

const activeTab = ref('applications'); // applications, saved
const applications = ref([]);
const savedJobs = ref([]);
const loading = ref(true);

const loadDashboardData = async () => {
  loading.value = true;
  try {
    // 1. Load applications
    if (authStore.user?.email) {
      applications.value = await api.getApplicationsByCandidate(authStore.user.email);
    }
    
    // 2. Load saved jobs
    const savedIds = JSON.parse(localStorage.getItem('5lance_saved_jobs')) || [];
    const jobsData = [];
    for (const id of savedIds) {
      const job = await api.getJobById(id);
      if (job) {
        jobsData.push(job);
      }
    }
    savedJobs.value = jobsData;
  } catch (error) {
    console.error('Failed to load dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadDashboardData();
});

const getStatusClass = (status) => {
  const classes = {
    'Applied': 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
    'Interviewing': 'bg-purple-500/10 text-purple-600 dark:text-purple-400',
    'Offered': 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    'Declined': 'bg-rose-500/10 text-rose-600 dark:text-rose-400'
  };
  return classes[status] || 'bg-slate-500/10 text-slate-600';
};

const handleRemoveSaved = (jobId, e) => {
  e.preventDefault();
  let savedIds = JSON.parse(localStorage.getItem('5lance_saved_jobs')) || [];
  savedIds = savedIds.filter(id => id !== jobId);
  localStorage.setItem('5lance_saved_jobs', JSON.stringify(savedIds));
  savedJobs.value = savedJobs.value.filter(job => job.id !== jobId);
  window.toast?.('Job removed from saved list.', 'info');
};
</script>

<template>
  <div class="space-y-8 py-4 text-left">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 shadow-sm">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-500 to-purple-500 flex items-center justify-center text-white font-extrabold text-xl shadow-md">
          {{ authStore.user?.name ? authStore.user.name.split(' ').map(n => n[0]).join('') : 'U' }}
        </div>
        <div class="space-y-1">
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Welcome back, {{ authStore.user?.name }}</h1>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ authStore.user?.title || 'Professional Candidate' }} &bull; {{ authStore.user?.location || 'Remote' }}</p>
        </div>
      </div>
      <router-link :to="{ name: 'Profile' }">
        <Button variant="outline" size="sm">Edit Profile</Button>
      </router-link>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <!-- Applications Stat -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
        <p class="text-2xl font-black text-brand-600 dark:text-brand-400">{{ applications.length }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Applications Sent</p>
      </div>

      <!-- Saved Jobs Stat -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
        <p class="text-2xl font-black text-rose-500">{{ savedJobs.length }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Saved Roles</p>
      </div>
    </div>

    <!-- Tab View -->
    <div class="space-y-6">
      <!-- Tab Headers -->
      <div class="border-b border-slate-200 dark:border-slate-800/60 flex gap-6">
        <button
          @click="activeTab = 'applications'"
          class="pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer"
          :class="activeTab === 'applications' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          My Applications
        </button>
        <button
          @click="activeTab = 'saved'"
          class="pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer"
          :class="activeTab === 'saved' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          Saved Jobs
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="h-20 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl animate-pulse" />
      </div>

      <!-- Tab Contents -->
      <div v-else>
        <!-- Applications Tab -->
        <div v-if="activeTab === 'applications'" class="space-y-4">
          <!-- Empty State -->
          <div
            v-if="applications.length === 0"
            class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-10 text-center space-y-4 shadow-sm"
          >
            <h3 class="text-base font-bold text-slate-700 dark:text-slate-350">No applications sent yet</h3>
            <p class="text-xs text-slate-400 max-w-xs mx-auto">Explore available listings and submit your first application to start tracking.</p>
            <router-link :to="{ name: 'Jobs' }">
              <Button variant="primary" size="sm">Find Jobs</Button>
            </router-link>
          </div>

          <!-- Applications list -->
          <div v-else class="overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800/60">
                <thead class="bg-slate-50 dark:bg-slate-800/40">
                  <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Role</th>
                    <th scope="col" class="px-6 py-3.5 class text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Company</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date Applied</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold text-slate-700 dark:text-slate-300">
                  <tr v-for="app in applications" :key="app.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                    <td class="px-6 py-4.5 whitespace-nowrap text-slate-900 dark:text-white font-bold">
                      <router-link :to="{ name: 'JobDetail', params: { id: app.jobId } }" class="hover:text-brand-600 transition-colors">
                        {{ app.jobTitle }}
                      </router-link>
                    </td>
                    <td class="px-6 py-4.5 whitespace-nowrap">{{ app.company }}</td>
                    <td class="px-6 py-4.5 whitespace-nowrap text-slate-400 text-sm font-medium">{{ app.date }}</td>
                    <td class="px-6 py-4.5 whitespace-nowrap">
                      <span class="px-3 py-1 rounded-full text-xs font-bold inline-block" :class="getStatusClass(app.status)">
                        {{ app.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Saved Jobs Tab -->
        <div v-if="activeTab === 'saved'" class="space-y-4">
          <!-- Empty State -->
          <div
            v-if="savedJobs.length === 0"
            class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-10 text-center space-y-4 shadow-sm"
          >
            <h3 class="text-base font-bold text-slate-700 dark:text-slate-350">No saved jobs</h3>
            <p class="text-xs text-slate-400 max-w-xs mx-auto">Click the bookmark heart icon on any job card to save roles for later.</p>
            <router-link :to="{ name: 'Jobs' }">
              <Button variant="primary" size="sm">Browse Jobs</Button>
            </router-link>
          </div>

          <!-- Saved jobs card grid -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="job in savedJobs" :key="job.id" class="relative group">
              <JobCard :job="job" />
              <!-- Direct Remove button overlay -->
              <button
                @click="(e) => handleRemoveSaved(job.id, e)"
                class="absolute bottom-6 right-6 z-10 px-2.5 py-1 text-[10px] font-bold text-rose-500 hover:text-white bg-rose-500/10 hover:bg-rose-600 rounded-lg cursor-pointer transition-all"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
