<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/authStore';
import FormInput from '../components/FormInput.vue';
import Button from '../components/Button.vue';

const router = useRouter();
const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const password = ref('');
const role = ref('candidate'); // candidate, admin

const formErrors = ref({ name: '', email: '', password: '' });
const generalError = ref('');
const loading = ref(false);

const handleRegister = async () => {
  formErrors.value = { name: '', email: '', password: '' };
  generalError.value = '';
  
  if (!name.value) {
    formErrors.value.name = 'Full name is required.';
    return;
  }
  if (!email.value) {
    formErrors.value.email = 'Email address is required.';
    return;
  }
  if (!password.value) {
    formErrors.value.password = 'Password is required.';
    return;
  }
  if (password.value.length < 6) {
    formErrors.value.password = 'Password must be at least 6 characters.';
    return;
  }

  loading.value = true;
  try {
    const user = await authStore.register(name.value, email.value, password.value, role.value);
    window.toast?.(`Welcome, ${user.name}! Your account has been created.`, 'success');
    
    // Redirect
    if (user.role === 'admin') {
      router.push({ name: 'Admin' });
    } else {
      router.push({ name: 'Dashboard' });
    }
  } catch (error) {
    generalError.value = error.message || 'Registration failed. Please try again.';
    window.toast?.(generalError.value, 'error');
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-[70vh] flex items-center justify-center py-6">
    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-8 shadow-xl shadow-brand-500/5 relative overflow-hidden">
      <!-- Background glow -->
      <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-brand-500/5 blur-2xl" />
      <div class="absolute -bottom-20 -left-20 w-40 h-40 rounded-full bg-purple-500/5 blur-2xl" />

      <div class="space-y-6 relative z-10">
        <!-- Logo Header -->
        <div class="text-center space-y-2">
          <div class="inline-flex w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-purple-600 items-center justify-center shadow-md shadow-brand-500/15 mb-2">
            <span class="text-white font-extrabold text-lg">5</span>
          </div>
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Create Your Account</h1>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Join a network of professional developers and recruiters.</p>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-5 text-left">
          <!-- Role Selector Segmented Controls -->
          <div class="flex flex-col gap-1.5">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 tracking-wider uppercase">I want to join as a</span>
            <div class="flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200/50 dark:border-slate-700/50">
              <button
                type="button"
                @click="role = 'candidate'"
                class="flex-1 py-2 rounded-lg text-sm font-bold transition-all cursor-pointer"
                :class="role === 'candidate' ? 'bg-white dark:bg-slate-900 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
              >
                Candidate (Looking for Work)
              </button>
              <button
                type="button"
                @click="role = 'admin'"
                class="flex-1 py-2 rounded-lg text-sm font-bold transition-all cursor-pointer"
                :class="role === 'admin' ? 'bg-white dark:bg-slate-900 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
              >
                Recruiter (Hiring Talent)
              </button>
            </div>
          </div>

          <!-- Name -->
          <FormInput
            v-model="name"
            label="Full Name"
            placeholder="Jane Doe"
            :error="formErrors.name"
            required
          />

          <!-- Email -->
          <FormInput
            v-model="email"
            label="Email Address"
            type="email"
            placeholder="jane@example.com"
            :error="formErrors.email"
            required
          />

          <!-- Password -->
          <FormInput
            v-model="password"
            label="Password"
            type="password"
            placeholder="Minimum 6 characters"
            :error="formErrors.password"
            required
          />

          <!-- General Error -->
          <div v-if="generalError" class="p-3.5 bg-rose-500/10 border border-rose-500/20 text-rose-500 text-xs font-semibold rounded-xl">
            {{ generalError }}
          </div>

          <!-- Submit Button -->
          <Button
            type="submit"
            variant="primary"
            class="w-full"
            :loading="loading"
          >
            Create Account
          </Button>
        </form>

        <!-- Footnote link -->
        <p class="text-xs font-semibold text-slate-400 text-center">
          Already have an account? 
          <router-link :to="{ name: 'Login' }" class="text-brand-600 hover:underline dark:text-brand-400">Sign In</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
