import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/authStore';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../pages/Home.vue'),
    meta: { title: 'Home - 5Lance Job Portal' }
  },
  {
    path: '/jobs',
    name: 'Jobs',
    component: () => import('../pages/Jobs.vue'),
    meta: { title: 'Find Jobs - 5Lance Job Portal' }
  },
  {
    path: '/jobs/:id',
    name: 'JobDetail',
    component: () => import('../pages/JobDetail.vue'),
    meta: { title: 'Job Details - 5Lance Job Portal' }
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../pages/Login.vue'),
    meta: { guestOnly: true, title: 'Login - 5Lance' }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../pages/Register.vue'),
    meta: { guestOnly: true, title: 'Register - 5Lance' }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('../pages/Dashboard.vue'),
    meta: { requiresAuth: true, title: 'Candidate Dashboard - 5Lance' }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('../pages/Profile.vue'),
    meta: { requiresAuth: true, title: 'Your Profile - 5Lance' }
  },
  {
    path: '/admin',
    name: 'Admin',
    component: () => import('../pages/Admin.vue'),
    meta: { requiresAuth: true, requiresAdmin: true, title: 'Recruiter Admin Panel - 5Lance' }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  }
});

// Navigation Guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = authStore.isAuthenticated;
  const isAdmin = authStore.isAdmin;
  const isRecruiter = authStore.isRecruiter;

  // Set document title
  if (to.meta.title) {
    document.title = to.meta.title;
  }

  // Guard for guest only pages (e.g. login/register)
  if (to.meta.guestOnly && isAuthenticated) {
    return next({ name: 'Dashboard' });
  }

  // Guard for pages requiring authentication
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: 'Login', query: { redirect: to.fullPath } });
  }

  // Guard for pages requiring admin/recruiter role
  if (to.meta.requiresAdmin && !isAdmin && !isRecruiter) {
    return next({ name: 'Home' });
  }

  next();
});

export default router;
