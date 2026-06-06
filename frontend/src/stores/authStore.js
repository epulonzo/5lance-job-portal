import { defineStore } from 'pinia';
import { api } from '../services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('5lance_user')) || null,
    token: localStorage.getItem('5lance_token') || null,
    loading: false,
    error: null,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin'
  },
  
  actions: {
    async login(email, password) {
      this.loading = true;
      this.error = null;
      try {
        const data = await api.login(email, password);
        this.user = data.user;
        this.token = data.token;
        localStorage.setItem('5lance_user', JSON.stringify(data.user));
        localStorage.setItem('5lance_token', data.token);
        return data.user;
      } catch (err) {
        this.error = err.message || 'Failed to login';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    async register(name, email, password, role) {
      this.loading = true;
      this.error = null;
      try {
        const data = await api.register(name, email, password, role);
        this.user = data.user;
        this.token = data.token;
        localStorage.setItem('5lance_user', JSON.stringify(data.user));
        localStorage.setItem('5lance_token', data.token);
        return data.user;
      } catch (err) {
        this.error = err.message || 'Failed to register';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    logout() {
      this.user = null;
      this.token = null;
      this.error = null;
      localStorage.removeItem('5lance_user');
      localStorage.removeItem('5lance_token');
    },
    
    async updateProfile(profileData) {
      if (!this.user?.id) throw new Error('Not authenticated');
      this.loading = true;
      this.error = null;
      try {
        const updatedUser = await api.updateProfile(this.user.id, profileData);
        this.user = updatedUser;
        localStorage.setItem('5lance_user', JSON.stringify(updatedUser));
        return updatedUser;
      } catch (err) {
        this.error = err.message || 'Failed to update profile';
        throw err;
      } finally {
        this.loading = false;
      }
    }
  }
});
