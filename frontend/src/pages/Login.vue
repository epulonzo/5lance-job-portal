<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/authStore';
import FormInput from '../components/FormInput.vue';
import Button from '../components/Button.vue';
import logo from '../assets/logo.png';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const formErrors = ref({ email: '', password: '' });
const generalError = ref('');
const loading = ref(false);

const handleLogin = async () => {
  // Simple validation
  formErrors.value = { email: '', password: '' };
  generalError.value = '';
  
  if (!email.value) {
    formErrors.value.email = 'Email address is required.';
    return;
  }
  if (!password.value) {
    formErrors.value.password = 'Password is required.';
    return;
  }

  loading.value = true;
  try {
    const user = await authStore.login(email.value, password.value);
    window.toast?.(`Welcome back, ${user.name}!`, 'success');
    
    // Redirect to guarded route or dashboard
    const redirectPath = route.query.redirect || ((user.role === 'admin' || user.role === 'recruiter') ? '/admin' : '/dashboard');
    router.push(redirectPath);
  } catch (error) {
    generalError.value = error.message || 'Login failed. Please check your credentials.';
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
          <img :src="logo" alt="5Lance" class="inline-flex w-10 h-10 mb-2" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Log In to 5Lance</h1>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Discover top developers & premium tech opportunities.</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <!-- Email -->
          <FormInput
            v-model="email"
            label="Email Address"
            type="email"
            placeholder="e.g. jane@example.com"
            :error="formErrors.email"
            required
          />

          <!-- Password -->
          <FormInput
            v-model="password"
            label="Password"
            type="password"
            placeholder="••••••••"
            :error="formErrors.password"
            required
          />

          <!-- General Error -->
          <div v-if="generalError" class="p-3.5 bg-rose-500/10 border border-rose-500/20 text-rose-500 text-xs font-semibold rounded-xl text-left">
            {{ generalError }}
          </div>

          <!-- Submit Button -->
          <Button
            type="submit"
            variant="primary"
            class="w-full"
            :loading="loading"
          >
            Log In
          </Button>
        </form>



        <!-- Footnote link -->
        <p class="text-xs font-semibold text-slate-400 text-center">
          Don't have an account? 
          <router-link :to="{ name: 'Register' }" class="text-brand-600 hover:underline dark:text-brand-400">Join 5Lance</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
