<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../services/api';
import { useAuthStore } from '../stores/authStore';
import Button from '../components/Button.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const job = ref(null);
const loading = ref(true);
const isApplying = ref(false);
const showApplyModal = ref(false);
const hasApplied = ref(false);

// Form Fields
const coverLetter = ref('');
const submitLoading = ref(false);
const submitError = ref('');

// Computed check for profile completeness
const isProfileComplete = computed(() => {
  const user = authStore.user;
  if (!user) return false;
  if (user.role !== 'candidate') return true; // Recruiters/Admins bypass this
  return (
    user.name?.trim() &&
    user.title?.trim() &&
    user.location?.trim() &&
    user.bio?.trim() &&
    user.resumeUrl?.trim() &&
    user.skills?.length > 0
  );
});

const loadJob = async () => {
  loading.value = true;
  try {
    const data = await api.getJobById(route.params.id);
    if (!data) {
      router.push({ name: 'Jobs' });
      return;
    }
    job.value = data;
    
    // Check if user already applied
    if (authStore.isAuthenticated) {
      const apps = await api.getApplicationsByCandidate(authStore.user.email);
      hasApplied.value = apps.some(app => app.jobId === data.id);
    }
  } catch (error) {
    console.error('Failed to load job details:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadJob();
});

const handleApplyClick = () => {
  if (!authStore.isAuthenticated) {
    router.push({
      name: 'Login',
      query: { redirect: route.fullPath }
    });
  } else {
    showApplyModal.value = true;
  }
};

const handleApplySubmit = async () => {
  if (!coverLetter.value) {
    submitError.value = 'Please provide a cover letter or introductory message.';
    return;
  }
  
  submitLoading.value = true;
  submitError.value = '';
  try {
    await api.applyForJob(
      job.value.id,
      authStore.user.email,
      {
        coverLetter: coverLetter.value,
        candidateName: authStore.user.name
      }
    );
    hasApplied.value = true;
    showApplyModal.value = false;
    window.toast?.('Application submitted successfully!', 'success');
  } catch (error) {
    submitError.value = error.message || 'Failed to submit application.';
    window.toast?.(submitError.value, 'error');
  } finally {
    submitLoading.value = false;
  }
};
</script>

<template>
  <div class="py-4">
    <!-- Loading State -->
    <div v-if="loading" class="max-w-4xl mx-auto space-y-6 animate-pulse">
      <div class="h-32 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl" />
      <div class="h-96 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl" />
    </div>

    <!-- Loaded Job Detail -->
    <div v-else-if="job" class="max-w-4xl mx-auto space-y-6 text-left">
      <!-- Breadcrumb -->
      <router-link
        :to="{ name: 'Jobs' }"
        class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Jobs
      </router-link>

      <!-- Main Header Card -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-6 sm:p-8 relative overflow-hidden shadow-sm">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r" :class="job.logoBg" />
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div class="flex items-start gap-4">
            <div
              class="w-14 h-14 rounded-2xl bg-gradient-to-br flex items-center justify-center font-bold text-lg text-white shadow-inner"
              :class="job.logoBg"
            >
              {{ job.logoText }}
            </div>
            
            <div class="space-y-1">
              <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">{{ job.title }}</h1>
              <div class="flex items-center gap-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                <span>{{ job.company }}</span>
                <span class="inline-block w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-700" />
                <span>{{ job.location }}</span>
              </div>
            </div>
          </div>

          <!-- CTAs -->
          <div class="flex flex-col sm:flex-row sm:items-center gap-3 shrink-0">
            <template v-if="authStore.isAuthenticated && authStore.user.role === 'candidate'">
              <div v-if="!isProfileComplete" class="flex flex-col items-end gap-1">
                <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wide">Profile Incomplete</span>
                <router-link
                  :to="{ name: 'Profile' }"
                  class="inline-flex items-center justify-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl transition-all shadow-md shadow-amber-500/10 cursor-pointer"
                >
                  Complete Profile to Apply
                </router-link>
              </div>
              <Button
                v-else-if="hasApplied"
                variant="secondary"
                disabled
                class="w-full sm:w-auto"
              >
                Applied
              </Button>
              <Button
                v-else
                variant="primary"
                class="w-full sm:w-auto"
                @click="handleApplyClick"
              >
                Apply Now
              </Button>
            </template>
            <template v-else-if="authStore.isAuthenticated && authStore.user.role !== 'candidate'">
              <span class="text-xs font-bold text-slate-400">Recruiter View</span>
            </template>
            <template v-else>
              <Button
                variant="primary"
                class="w-full sm:w-auto"
                @click="handleApplyClick"
              >
                Log In to Apply
              </Button>
            </template>
          </div>
        </div>

        <!-- Meta list tags -->
        <div class="flex flex-wrap gap-y-2 gap-x-4 mt-6 pt-6 border-t border-slate-100 dark:border-slate-800/60 text-xs font-semibold text-slate-500">
          <div class="flex items-center gap-1.5">
            <span class="text-slate-400">Job Type:</span>
            <span class="text-slate-700 dark:text-slate-300">{{ job.type }}</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="text-slate-400">Salary:</span>
            <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ job.salary }}</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="text-slate-400">Experience:</span>
            <span class="text-slate-700 dark:text-slate-300">{{ job.experience }}</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="text-slate-400">Posted:</span>
            <span class="text-slate-700 dark:text-slate-300">{{ job.postedDate }}</span>
          </div>
        </div>
      </div>

      <!-- Main Specifications -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Details Column -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Incomplete Profile Warning Alert -->
          <div
            v-if="authStore.isAuthenticated && authStore.user.role === 'candidate' && !isProfileComplete"
            class="p-5 bg-amber-500/10 dark:bg-amber-500/5 border border-amber-500/20 rounded-2xl flex items-start gap-4 text-left"
          >
            <div class="p-2.5 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5 animate-pulse">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="space-y-1">
              <h4 class="text-sm font-extrabold text-amber-800 dark:text-amber-400">Complete Your Profile Settings First</h4>
              <p class="text-xs font-semibold text-amber-700/90 dark:text-amber-500/80 leading-relaxed">
                Before applying for jobs, you must complete your profile. Please make sure to fill in your <strong>Headline</strong>, <strong>Location</strong>, <strong>Bio</strong>, <strong>Skills</strong>, and <strong>Resume Link</strong> in your profile settings.
              </p>
              <div class="pt-2">
                <router-link
                  :to="{ name: 'Profile' }"
                  class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 dark:text-amber-400 hover:underline"
                >
                  Go to Profile Settings &rarr;
                </router-link>
              </div>
            </div>
          </div>

          <!-- Description -->
          <section class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-sm space-y-4">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Role Overview</h2>
            <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed whitespace-pre-line">{{ job.description }}</p>
          </section>

          <!-- Requirements -->
          <section class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-sm space-y-4">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Key Requirements</h2>
            <ul class="space-y-3">
              <li
                v-for="(req, idx) in job.requirements"
                :key="idx"
                class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 shrink-0 mt-2" />
                <span>{{ req }}</span>
              </li>
            </ul>
          </section>

          <!-- Benefits -->
          <section class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-sm space-y-4">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Perks & Benefits</h2>
            <ul class="space-y-3">
              <li
                v-for="(ben, idx) in job.benefits"
                :key="idx"
                class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ ben }}</span>
              </li>
            </ul>
          </section>
        </div>

        <!-- Sidebar / Tags -->
        <aside class="space-y-6">
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Required Skills</h2>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="skill in job.skills"
                :key="skill"
                class="px-3 py-1.5 text-xs rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold"
              >
                {{ skill }}
              </span>
            </div>
          </div>
        </aside>
      </div>
    </div>

    <!-- Application Modal -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showApplyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="showApplyModal = false" />
        
        <!-- Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-xl p-6 sm:p-8 relative z-10 shadow-2xl text-left space-y-6 max-h-[90vh] overflow-y-auto">
          <div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Apply for {{ job?.title }}</h3>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Submit your details to {{ job?.company }}</p>
          </div>

          <form @submit.prevent="handleApplySubmit" class="space-y-4">
            <!-- Cover Letter -->
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cover Letter, Period & Notes *</label>
              <textarea
                v-model="coverLetter"
                rows="5"
                placeholder="Introduce yourself, specify your available period, and share any related information..."
                required
                class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl transition-all outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm leading-relaxed"
              />
            </div>

            <!-- Info Note -->
            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-850 rounded-xl p-3">
              ℹ️ Your profile details (including your saved resume link and skills) will automatically be attached to this application.
            </div>

            <!-- Error Banner -->
            <div v-if="submitError" class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-500 text-xs font-semibold">
              {{ submitError }}
            </div>

            <!-- Modal CTAs -->
            <div class="flex items-center justify-end gap-3 pt-2">
              <Button
                type="button"
                variant="ghost"
                @click="showApplyModal = false"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                variant="primary"
                :loading="submitLoading"
              >
                Submit Application
              </Button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>
