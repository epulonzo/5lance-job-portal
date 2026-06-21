<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/authStore';
import FormInput from '../components/FormInput.vue';
import Button from '../components/Button.vue';

const authStore = useAuthStore();

const name = ref('');
const title = ref('');
const bio = ref('');
const location = ref('');
const resumeFile = ref(null);
const skillsInput = ref(''); // Comma separated string

const loading = ref(false);
const error = ref('');

onMounted(() => {
  if (authStore.user) {
    name.value = authStore.user.name || '';
    title.value = authStore.user.title || '';
    bio.value = authStore.user.bio || '';
    location.value = authStore.user.location || '';
    skillsInput.value = authStore.user.skills ? authStore.user.skills.join(', ') : '';
  }
});

const handleResumeChange = (event) => {
  resumeFile.value = event.target.files[0] || null;
};

const handleSaveProfile = async () => {
  loading.value = true;
  error.value = '';
  
  // Format skills back into array
  const skillsArray = skillsInput.value
    ? skillsInput.value.split(',').map(s => s.trim()).filter(s => s.length > 0)
    : [];

  const profileData = {
    name: name.value,
    title: title.value,
    bio: bio.value,
    location: location.value,
    resumeFile: resumeFile.value,
    skills: skillsArray
  };

  try {
    await authStore.updateProfile(profileData);
    window.toast?.('Profile updated successfully!', 'success');
  } catch (err) {
    error.value = err.message || 'Failed to update profile.';
    window.toast?.(error.value, 'error');
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="max-w-2xl mx-auto py-4 text-left">
    <!-- Breadcrumb -->
    <router-link
      :to="{ name: 'Dashboard' }"
      class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors mb-6"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      Back to Dashboard
    </router-link>

    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-xl shadow-brand-500/5 relative overflow-hidden">
      <!-- Glow -->
      <div class="absolute -top-24 -right-24 w-48 h-48 rounded-full bg-brand-500/5 blur-3xl" />

      <div class="space-y-6 relative z-10">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Profile Settings</h1>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Update your professional profile to attract recruiter interest.</p>
        </div>

        <form @submit.prevent="handleSaveProfile" class="space-y-4">
          <!-- Full Name -->
          <FormInput
            v-model="name"
            label="Full Name"
            placeholder="Jane Doe"
            required
          />

          <!-- Title / Headline -->
          <FormInput
            v-model="title"
            label="Professional Headline"
            placeholder="e.g. Senior Frontend Engineer | Vue Expert"
          />

          <!-- Location -->
          <FormInput
            v-model="location"
            label="Location"
            placeholder="e.g. Remote / San Francisco, CA"
          />

          <!-- Resume Upload -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Upload Resume (PDF only)</label>
            <div v-if="authStore.user?.resumeUrl" class="p-3.5 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/50 dark:border-slate-850 rounded-xl flex items-center justify-between text-xs font-semibold">
              <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                📄 Current: <a :href="authStore.user.resumeUrl" target="_blank" class="text-brand-600 dark:text-brand-400 hover:underline">View Uploaded Resume (PDF)</a>
              </span>
              <span class="text-[10px] text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 dark:bg-emerald-400/10 px-2 py-0.5 rounded-full font-bold">Uploaded</span>
            </div>
            <input
              type="file"
              accept=".pdf"
              @change="handleResumeChange"
              class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl transition-all outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-500/10 file:text-brand-600 dark:file:bg-brand-400/10 dark:file:text-brand-400 hover:file:bg-brand-500/20 cursor-pointer"
            />
          </div>

          <!-- Skills (Comma Separated) -->
          <FormInput
            v-model="skillsInput"
            label="Skills (Comma-separated)"
            placeholder="e.g. Vue 3, Tailwind CSS, TypeScript, Git"
          />

          <!-- Professional Bio -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Professional Bio</label>
            <textarea
              v-model="bio"
              rows="4"
              placeholder="Tell recruiters about your background, achievements, and what projects you enjoy building..."
              class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl transition-all outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm leading-relaxed"
            />
          </div>

          <!-- Error Alert -->
          <div v-if="error" class="p-3.5 bg-rose-500/10 border border-rose-500/20 text-rose-500 text-xs font-semibold rounded-xl">
            {{ error }}
          </div>

          <!-- Save Button -->
          <div class="flex justify-end pt-2">
            <Button
              type="submit"
              variant="primary"
              :loading="loading"
              class="w-full sm:w-auto"
            >
              Save Profile Changes
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
