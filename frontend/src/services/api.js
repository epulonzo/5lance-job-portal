import { dummyJobs } from '../data/dummyJobs';

// Helper for simulated network delay
const delay = (ms = 500) => new Promise(resolve => setTimeout(resolve, ms));

// Storage Keys
const JOBS_KEY = '5lance_jobs';
const APPLICATIONS_KEY = '5lance_applications';
const USERS_KEY = '5lance_users';

// Initialize data if not present
const initStorage = () => {
  if (!localStorage.getItem(JOBS_KEY)) {
    localStorage.setItem(JOBS_KEY, JSON.stringify(dummyJobs));
  }
  if (!localStorage.getItem(APPLICATIONS_KEY)) {
    localStorage.setItem(APPLICATIONS_KEY, JSON.stringify([]));
  }
  if (!localStorage.getItem(USERS_KEY)) {
    // Seed an admin/employer user
    const defaultUsers = [
      {
        id: 'user-admin',
        name: 'Alex Mercer',
        email: 'admin@5lance.com',
        password: 'password123',
        role: 'admin',
        title: 'Lead Recruiter',
        bio: 'Hiring top tech talent across the globe for Apex Group.',
        skills: ['Hiring', 'Technical Recruiting', 'Sourcing'],
        location: 'New York, NY'
      },
      {
        id: 'user-candidate',
        name: 'Jane Doe',
        email: 'jane@example.com',
        password: 'password123',
        role: 'candidate',
        title: 'Frontend Developer',
        bio: 'Passionate Vue.js developer looking for my next challenge.',
        skills: ['Vue 3', 'Tailwind CSS', 'JavaScript', 'Git'],
        location: 'Remote'
      }
    ];
    localStorage.setItem(USERS_KEY, JSON.stringify(defaultUsers));
  }
};

initStorage();

export const api = {
  // --- Jobs ---
  async getJobs() {
    await delay();
    return JSON.parse(localStorage.getItem(JOBS_KEY)) || [];
  },

  async getJobById(id) {
    await delay();
    const jobs = JSON.parse(localStorage.getItem(JOBS_KEY)) || [];
    return jobs.find(job => job.id === id) || null;
  },

  async createJob(jobData) {
    await delay();
    const jobs = JSON.parse(localStorage.getItem(JOBS_KEY)) || [];
    const newJob = {
      ...jobData,
      id: `job-${Date.now()}`,
      postedDate: 'Just now',
      featured: jobData.featured || false
    };
    jobs.unshift(newJob);
    localStorage.setItem(JOBS_KEY, JSON.stringify(jobs));
    return newJob;
  },

  async updateJob(id, updatedData) {
    await delay();
    let jobs = JSON.parse(localStorage.getItem(JOBS_KEY)) || [];
    let updatedJob = null;
    jobs = jobs.map(job => {
      if (job.id === id) {
        updatedJob = { ...job, ...updatedData };
        return updatedJob;
      }
      return job;
    });
    localStorage.setItem(JOBS_KEY, JSON.stringify(jobs));
    return updatedJob;
  },

  async deleteJob(id) {
    await delay();
    let jobs = JSON.parse(localStorage.getItem(JOBS_KEY)) || [];
    jobs = jobs.filter(job => job.id !== id);
    localStorage.setItem(JOBS_KEY, JSON.stringify(jobs));
    return true;
  },

  // --- Applications ---
  async getApplications() {
    await delay();
    return JSON.parse(localStorage.getItem(APPLICATIONS_KEY)) || [];
  },

  async getApplicationsByCandidate(email) {
    await delay();
    const apps = JSON.parse(localStorage.getItem(APPLICATIONS_KEY)) || [];
    return apps.filter(app => app.candidateEmail === email);
  },

  async applyForJob(jobId, candidateEmail, applicationData) {
    await delay();
    const apps = JSON.parse(localStorage.getItem(APPLICATIONS_KEY)) || [];
    const jobs = JSON.parse(localStorage.getItem(JOBS_KEY)) || [];
    const job = jobs.find(j => j.id === jobId);

    // Prevent duplicate applications
    const exists = apps.some(app => app.jobId === jobId && app.candidateEmail === candidateEmail);
    if (exists) {
      throw new Error("You have already applied for this job.");
    }

    const newApp = {
      id: `app-${Date.now()}`,
      jobId,
      jobTitle: job ? job.title : 'Unknown Role',
      company: job ? job.company : 'Unknown Company',
      candidateEmail,
      status: 'Applied', // Applied, Interviewing, Offered, Declined
      date: new Date().toLocaleDateString(),
      ...applicationData
    };
    apps.unshift(newApp);
    localStorage.setItem(APPLICATIONS_KEY, JSON.stringify(apps));
    return newApp;
  },

  async updateApplicationStatus(id, status) {
    await delay();
    let apps = JSON.parse(localStorage.getItem(APPLICATIONS_KEY)) || [];
    let updatedApp = null;
    apps = apps.map(app => {
      if (app.id === id) {
        updatedApp = { ...app, status };
        return updatedApp;
      }
      return app;
    });
    localStorage.setItem(APPLICATIONS_KEY, JSON.stringify(apps));
    return updatedApp;
  },

  // --- Auth & Profile ---
  async login(email, password) {
    await delay();
    const users = JSON.parse(localStorage.getItem(USERS_KEY)) || [];
    const user = users.find(u => u.email === email && u.password === password);
    if (!user) {
      throw new Error('Invalid email or password.');
    }
    // Return copy of user without password
    const { password: _, ...safeUser } = user;
    return { user: safeUser, token: `mock-jwt-token-${Date.now()}` };
  },

  async register(name, email, password, role) {
    await delay();
    const users = JSON.parse(localStorage.getItem(USERS_KEY)) || [];
    const exists = users.some(u => u.email === email);
    if (exists) {
      throw new Error('A user with this email already exists.');
    }
    const newUser = {
      id: `user-${Date.now()}`,
      name,
      email,
      password,
      role, // candidate or admin
      title: role === 'admin' ? 'Recruiter' : 'Professional',
      bio: '',
      skills: [],
      location: ''
    };
    users.push(newUser);
    localStorage.setItem(USERS_KEY, JSON.stringify(users));
    const { password: _, ...safeUser } = newUser;
    return { user: safeUser, token: `mock-jwt-token-${Date.now()}` };
  },

  async updateProfile(id, profileData) {
    await delay();
    let users = JSON.parse(localStorage.getItem(USERS_KEY)) || [];
    let updatedUser = null;
    users = users.map(user => {
      if (user.id === id) {
        updatedUser = { ...user, ...profileData };
        return updatedUser;
      }
      return user;
    });
    localStorage.setItem(USERS_KEY, JSON.stringify(users));
    if (updatedUser) {
      const { password: _, ...safeUser } = updatedUser;
      return safeUser;
    }
    throw new Error('User not found.');
  }
};
