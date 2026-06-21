import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Automatically inject JWT token from localStorage
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('5lance_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => {
  return Promise.reject(error);
});

// Extract backend error messages to display user-friendly errors in UI
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.data && error.response.data.error) {
      error.message = error.response.data.error;
    }
    return Promise.reject(error);
  }
);

// Helper for relative time formatting
const formatRelativeTime = (timestamp) => {
  if (!timestamp) return 'Just now';
  const now = new Date();
  const past = new Date(timestamp);
  const diffMs = now - past;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays === 1) return 'Yesterday';
  return `${diffDays} days ago`;
};

// Database model mapping helpers
const mapJobFromBackend = (job) => {
  if (!job) return null;

  let desc = job.description;
  let skills = null;
  let requirements = null;
  let benefits = null;
  let location = 'Remote';
  let type = 'Contract';
  let experience = 'Mid-level';

  try {
    const parsed = JSON.parse(job.description);
    if (parsed && typeof parsed === 'object') {
      desc = parsed.description || job.description;
      skills = parsed.skills;
      requirements = parsed.requirements;
      benefits = parsed.benefits;
      location = parsed.location || 'Remote';
      type = parsed.type || 'Contract';
      experience = parsed.experience || 'Mid-level';
    }
  } catch (e) {
    // Normal plain text description
  }

  const clientName = job.client_name || 'Client';
  const logoText = clientName.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();

  return {
    id: String(job.job_id),
    title: job.title,
    company: clientName,
    logoBg: job.logoBg || 'from-indigo-500 to-purple-600',
    logoColor: 'text-white',
    logoText: logoText,
    location: location,
    type: type,
    experience: experience,
    category: job.category,
    salary: job.budget ? `RM ${parseFloat(job.budget).toLocaleString()}` : 'Negotiable',
    postedDate: formatRelativeTime(job.created_at),
    featured: Boolean(job.featured || false),
    description: desc,
    requirements: requirements || [
      "Must deliver project by the specified deadline.",
      "High quality, testable code.",
      "Good communication throughout the project."
    ],
    benefits: benefits || [
      "Flexible hours",
      "Professional reference",
      "Potential for ongoing work"
    ],
    skills: skills || (job.category === 'Design' ? ['Figma', 'UI Design', 'UX Design'] : ['JavaScript', 'Web Development']),
    deadline: job.deadline,
    budget: job.budget,
    client_id: job.client_id,
    status: job.status
  };
};

const mapAppFromBackend = (app) => {
  if (!app) return null;

  const statusMapping = {
    'pending': 'Applied',
    'accepted': 'Interviewing',
    'rejected': 'Declined'
  };

  const apiBase = import.meta.env.VITE_API_URL 
    ? import.meta.env.VITE_API_URL.replace(/\/api\/?$/, '') 
    : 'http://localhost:8000';

  return {
    id: String(app.app_id),
    jobId: String(app.job_id),
    jobTitle: app.job_title || 'Freelance Role',
    company: app.client_name || 'Client',
    candidateEmail: app.freelancer_email || 'freelancer@example.com',
    candidateName: app.freelancer_name || 'Freelancer',
    freelancerName: app.freelancer_name || 'Freelancer',
    status: statusMapping[app.status] || 'Applied',
    date: app.applied_at ? new Date(app.applied_at).toLocaleDateString() : 'Just now',
    cover_letter: app.cover_letter,
    coverLetter: app.cover_letter, // Align naming conventions
    proposed_rate: app.proposed_rate,
    freelancer_id: app.freelancer_id,
    resumeUrl: app.resume_path ? `${apiBase}${app.resume_path}` : null
  };
};

const mapUserFromBackend = (user) => {
  if (!user) return null;
  let frontendRole = user.role;
  if (user.role === 'client') {
    frontendRole = 'admin';
  } else if (user.role === 'freelancer') {
    frontendRole = 'candidate';
  }
  
  const apiBase = import.meta.env.VITE_API_URL 
    ? import.meta.env.VITE_API_URL.replace(/\/api\/?$/, '') 
    : 'http://localhost:8000';

  let resumeUrl = user.resume_url || '';
  if (resumeUrl && resumeUrl.startsWith('/uploads/')) {
    resumeUrl = `${apiBase}${resumeUrl}`;
  }

  return {
    id: String(user.user_id),
    user_id: user.user_id,
    name: user.name,
    email: user.email,
    role: frontendRole,
    title: user.title || '',
    location: user.location || '',
    resumeUrl: resumeUrl,
    bio: user.bio || '',
    skills: user.skills ? user.skills.split(',').map(s => s.trim()) : [],
    created_at: user.created_at
  };
};

export const api = {
  // --- Jobs ---
  async getJobs() {
    const userJson = localStorage.getItem('5lance_user');
    const user = userJson ? JSON.parse(userJson) : null;
    
    let params = {};
    if (user && (user.role === 'admin' || user.role === 'client')) {
      params.status = 'all';
    }
    
    const response = await apiClient.get('/jobs', { params });
    return response.data.map(mapJobFromBackend);
  },

  async getJobById(id) {
    const response = await apiClient.get(`/jobs/${id}`);
    return mapJobFromBackend(response.data);
  },

  async createJob(jobData) {
    const descriptionJson = JSON.stringify({
      description: jobData.description,
      skills: jobData.skills || [],
      requirements: jobData.requirements || [],
      benefits: jobData.benefits || [],
      location: jobData.location || 'Remote',
      type: jobData.type || 'Contract',
      experience: jobData.experience || 'Mid-level'
    });

    const payload = {
      title: jobData.title,
      description: descriptionJson,
      category: jobData.category,
      budget: parseFloat(jobData.salary) || parseFloat(jobData.budget) || null,
      deadline: jobData.deadline || null,
      status: jobData.status || 'open'
    };
    
    const response = await apiClient.post('/jobs', payload);
    return mapJobFromBackend(response.data);
  },

  async updateJob(id, updatedData) {
    const descriptionJson = JSON.stringify({
      description: updatedData.description,
      skills: updatedData.skills || [],
      requirements: updatedData.requirements || [],
      benefits: updatedData.benefits || [],
      location: updatedData.location || 'Remote',
      type: updatedData.type || 'Contract',
      experience: updatedData.experience || 'Mid-level'
    });

    const payload = {
      title: updatedData.title,
      description: descriptionJson,
      category: updatedData.category,
      budget: parseFloat(updatedData.salary) || parseFloat(updatedData.budget) || null,
      deadline: updatedData.deadline || null,
      status: updatedData.status || 'open'
    };

    const response = await apiClient.put(`/jobs/${id}`, payload);
    return mapJobFromBackend(response.data);
  },

  async deleteJob(id) {
    await apiClient.delete(`/jobs/${id}`);
    return true;
  },

  // --- Applications ---
  async getApplications() {
    const response = await apiClient.get('/applications');
    return response.data.map(mapAppFromBackend);
  },

  async getApplicationsByCandidate(email) {
    const response = await apiClient.get('/applications');
    return response.data.map(mapAppFromBackend);
  },

  async applyForJob(jobId, candidateEmail, applicationData) {
    const formData = new FormData();
    formData.append('cover_letter', applicationData.coverLetter || applicationData.cover_letter || '');
    
    const rate = parseFloat(applicationData.rate) || parseFloat(applicationData.proposed_rate) || null;
    if (rate !== null) {
      formData.append('proposed_rate', String(rate));
    }
    
    if (applicationData.resumeFile) {
      formData.append('resume', applicationData.resumeFile);
    }

    const response = await apiClient.post(`/jobs/${jobId}/applications`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    return mapAppFromBackend(response.data);
  },

  async updateApplicationStatus(id, status) {
    const statusMapping = {
      'Applied': 'pending',
      'Interviewing': 'accepted',
      'Offered': 'accepted',
      'Declined': 'rejected'
    };
    const backendStatus = statusMapping[status] || 'pending';
    const response = await apiClient.put(`/applications/${id}`, { status: backendStatus });
    return mapAppFromBackend(response.data);
  },

  // --- Auth & Profile ---
  async login(email, password) {
    const response = await apiClient.post('/auth/login', { email, password });
    return {
      user: mapUserFromBackend(response.data.user),
      token: response.data.token
    };
  },

  async register(name, email, password, role) {
    let backendRole = role;
    if (role === 'candidate') {
      backendRole = 'freelancer';
    } else if (role === 'admin') {
      backendRole = 'client';
    }
    const response = await apiClient.post('/auth/register', { name, email, password, role: backendRole });
    return {
      user: mapUserFromBackend(response.data.user),
      token: response.data.token
    };
  },

  async updateProfile(id, profileData) {
    let skillsStr = profileData.skills;
    if (Array.isArray(skillsStr)) {
      skillsStr = skillsStr.join(', ');
    }
    
    const formData = new FormData();
    formData.append('name', profileData.name || '');
    if (profileData.title !== undefined) formData.append('title', profileData.title || '');
    if (profileData.location !== undefined) formData.append('location', profileData.location || '');
    if (profileData.bio !== undefined) formData.append('bio', profileData.bio || '');
    if (skillsStr !== undefined) formData.append('skills', skillsStr || '');
    
    if (profileData.resumeFile) {
      formData.append('resume', profileData.resumeFile);
    }
    
    const response = await apiClient.post(`/users/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    return mapUserFromBackend(response.data);
  },

  async getUserProfile(id) {
    const response = await apiClient.get(`/users/${id}`);
    return mapUserFromBackend(response.data);
  }
};
