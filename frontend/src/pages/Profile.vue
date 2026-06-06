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
const resumeUrl = ref('');
const skillsInput = ref(''); // Comma separated string

const loading = ref(false);
const error = ref('');

onMounted(() => {
  if (authStore.user) {
    name.value = authStore.user.name || '';
    title.value = authStore.user.title || '';
    bio.value = authStore.user.bio || '';
    location.value = authStore.user.location || '';
    resumeUrl.value = authStore.user.resumeUrl || '';
    skillsInput.value = authStore.user.skills ? authStore.user.skills.join(', ') : '';
  }
});

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
    resumeUrl: resumeUrl.value,
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

          <!-- Resume Link -->
          <FormInput
            v-model="resumeUrl"
            label="Resume Link (PDF/Docs Link)"
            placeholder="https://myportfolio.com/my-resume.pdf"
            type="url"
          />

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
