<script setup>
import { ref, onMounted, reactive } from 'vue';
import { api } from '../services/api';
import FormInput from '../components/FormInput.vue';
import Button from '../components/Button.vue';

const activeTab = ref('applications'); // applications, post-job, manage-jobs

const applications = ref([]);
const jobs = ref([]);
const loading = ref(true);

// Job Form Reactive State
const jobForm = reactive({
  title: '',
  company: '',
  location: '',
  category: 'Development',
  type: 'Full-time',
  experience: 'Mid-level',
  salary: '',
  description: '',
  skillsInput: '',
  requirementsInput: '',
  benefitsInput: ''
});

const formError = ref('');
const formLoading = ref(false);

const loadAdminData = async () => {
  loading.value = true;
  try {
    applications.value = await api.getApplications();
    jobs.value = await api.getJobs();
  } catch (error) {
    console.error('Failed to load admin dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadAdminData();
});

const handleStatusChange = async (appId, newStatus) => {
  try {
    const updated = await api.updateApplicationStatus(appId, newStatus);
    applications.value = applications.value.map(app => app.id === appId ? updated : app);
    window.toast?.(`Application status updated to ${newStatus}.`, 'success');
  } catch (error) {
    window.toast?.('Failed to update status.', 'error');
  }
};

const handleDeleteJob = async (jobId) => {
  if (!confirm('Are you sure you want to delete this job listing?')) return;
  try {
    await api.deleteJob(jobId);
    jobs.value = jobs.value.filter(j => j.id !== jobId);
    window.toast?.('Job listing deleted successfully.', 'success');
  } catch (error) {
    window.toast?.('Failed to delete job listing.', 'error');
  }
};

const handleCreateJob = async () => {
  formError.value = '';
  if (!jobForm.title || !jobForm.company || !jobForm.location || !jobForm.salary || !jobForm.description) {
    formError.value = 'Please fill out all required fields.';
    return;
  }

  formLoading.value = true;
  
  // Format logo bg with random premium gradients
  const gradients = [
    'from-indigo-500 to-purple-600',
    'from-pink-500 to-rose-500',
    'from-blue-500 to-cyan-500',
    'from-amber-500 to-orange-600',
    'from-emerald-500 to-teal-600',
    'from-sky-500 to-blue-600'
  ];
  const randomGradient = gradients[Math.floor(Math.random() * gradients.length)];
  
  // Format arrays
  const skills = jobForm.skillsInput
    ? jobForm.skillsInput.split(',').map(s => s.trim()).filter(s => s.length > 0)
    : [];
  
  const requirements = jobForm.requirementsInput
    ? jobForm.requirementsInput.split('\n').map(r => r.trim()).filter(r => r.length > 0)
    : ['Experience in a similar engineering role.'];

  const benefits = jobForm.benefitsInput
    ? jobForm.benefitsInput.split('\n').map(b => b.trim()).filter(b => b.length > 0)
    : ['Competitive salary', 'Flexible work arrangements'];

  const newJobData = {
    title: jobForm.title,
    company: jobForm.company,
    location: jobForm.location,
    category: jobForm.category,
    type: jobForm.type,
    experience: jobForm.experience,
    salary: jobForm.salary,
    description: jobForm.description,
    skills,
    requirements,
    benefits,
    logoBg: randomGradient,
    logoText: jobForm.company.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase(),
    featured: Math.random() > 0.5 // Randomly feature new jobs for display demo
  };

  try {
    const createdJob = await api.createJob(newJobData);
    jobs.value.unshift(createdJob);
    window.toast?.('New job posted successfully!', 'success');
    
    // Reset Form
    jobForm.title = '';
    jobForm.company = '';
    jobForm.location = '';
    jobForm.salary = '';
    jobForm.description = '';
    jobForm.skillsInput = '';
    jobForm.requirementsInput = '';
    jobForm.benefitsInput = '';
    
    // Redirect to management tab
    activeTab.value = 'manage-jobs';
  } catch (error) {
    formError.value = error.message || 'Failed to create job.';
    window.toast?.(formError.value, 'error');
  } finally {
    formLoading.value = false;
  }
};

const getStatusBadgeClass = (status) => {
  const classes = {
    'Applied': 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
    'Interviewing': 'bg-purple-500/10 text-purple-600 dark:text-purple-400',
    'Offered': 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    'Declined': 'bg-rose-500/10 text-rose-600 dark:text-rose-400'
  };
  return classes[status] || 'bg-slate-500/10 text-slate-600';
};
</script>

<template>
  <div class="space-y-8 py-4 text-left">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 shadow-sm">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-650 to-purple-600 flex items-center justify-center text-white font-extrabold text-xl shadow-md">
          RP
        </div>
        <div class="space-y-1">
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Recruiter Workspace</h1>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Post roles, evaluate candidates, and manage your pipeline.</p>
        </div>
      </div>
    </div>

    <!-- Stats Panel -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
        <p class="text-2xl font-black text-brand-600 dark:text-brand-400">{{ jobs.length }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Active Postings</p>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
        <p class="text-2xl font-black text-purple-600 dark:text-purple-400">{{ applications.length }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Total Applicants</p>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm col-span-2">
        <p class="text-2xl font-black text-emerald-500">
          {{ applications.filter(a => a.status === 'Offered').length }} /
          {{ applications.filter(a => a.status === 'Interviewing').length }}
        </p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Offers / Interviews Pending</p>
      </div>
    </div>

    <!-- Workspace Tabs -->
    <div class="space-y-6">
      <div class="border-b border-slate-200 dark:border-slate-800/60 flex gap-6">
        <button
          @click="activeTab = 'applications'"
          class="pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer"
          :class="activeTab === 'applications' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          Evaluate Candidates
        </button>
        <button
          @click="activeTab = 'post-job'"
          class="pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer"
          :class="activeTab === 'post-job' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          Post a Job Listing
        </button>
        <button
          @click="activeTab = 'manage-jobs'"
          class="pb-3 text-sm font-bold border-b-2 transition-all cursor-pointer"
          :class="activeTab === 'manage-jobs' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          Manage Listings
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="h-24 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl animate-pulse" />
      </div>

      <!-- Content -->
      <div v-else>
        <!-- Tab 1: Evaluate Candidates -->
        <div v-if="activeTab === 'applications'" class="space-y-4">
          <!-- Empty State -->
          <div
            v-if="applications.length === 0"
            class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-12 text-center text-slate-500 shadow-sm"
          >
            <h3 class="text-base font-bold text-slate-700 dark:text-slate-350">No applications received yet</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Posted job listings will gather candidate applications here. Try logging into a Candidate profile and applying to test.</p>
          </div>

          <!-- Applications list -->
          <div v-else class="space-y-4">
            <div
              v-for="app in applications"
              :key="app.id"
              class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm space-y-4 hover:border-brand-500/20 transition-colors"
            >
              <!-- Candidate Title Header -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ app.candidateName }}
                  </h3>
                  <p class="text-xs font-semibold text-slate-500">
                    Applied for: <span class="text-brand-600 dark:text-brand-400">{{ app.jobTitle }}</span> ({{ app.company }}) &bull; Submitted: {{ app.date }}
                  </p>
                </div>
                
                <!-- Status controls -->
                <div class="flex items-center gap-3">
                  <span class="px-2.5 py-1 text-xs font-bold rounded-full" :class="getStatusBadgeClass(app.status)">
                    {{ app.status }}
                  </span>
                  
                  <select
                    :value="app.status"
                    @change="(e) => handleStatusChange(app.id, e.target.value)"
                    class="px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-lg outline-none text-xs font-bold cursor-pointer transition-all"
                  >
                    <option value="Applied">Applied</option>
                    <option value="Interviewing">Interviewing</option>
                    <option value="Offered">Offered</option>
                    <option value="Declined">Declined</option>
                  </select>
                </div>
              </div>

              <!-- Message/Cover Letter Box -->
              <div class="p-4 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-850 rounded-xl text-slate-600 dark:text-slate-350 text-sm leading-relaxed whitespace-pre-line">
                <p class="font-bold text-xs uppercase text-slate-400 dark:text-slate-500 mb-1">Cover Letter Message</p>
                {{ app.coverLetter }}
              </div>

              <!-- Resume Link Attachment -->
              <div v-if="app.resumeUrl" class="flex items-center gap-2 text-xs font-bold text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Attachment: </span>
                <a
                  :href="app.resumeUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-brand-600 dark:text-brand-400 hover:underline"
                >
                  {{ app.resumeUrl }}
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 2: Post Job Listing Form -->
        <div v-if="activeTab === 'post-job'" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-sm">
          <form @submit.prevent="handleCreateJob" class="space-y-4">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Post a New Role</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <FormInput
                v-model="jobForm.title"
                label="Job Title *"
                placeholder="e.g. Senior Frontend Developer"
                required
              />
              <FormInput
                v-model="jobForm.company"
                label="Company Name *"
                placeholder="e.g. Acme Tech Inc."
                required
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <FormInput
                v-model="jobForm.location"
                label="Location *"
                placeholder="e.g. Remote (Worldwide) or Austin, TX"
                required
              />
              <FormInput
                v-model="jobForm.salary"
                label="Salary Range *"
                placeholder="e.g. $100,000 - $120,000 / year"
                required
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- Category select -->
              <div class="flex flex-col gap-1.5 text-left">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Category</label>
                <select
                  v-model="jobForm.category"
                  class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm font-semibold transition-all"
                >
                  <option value="Development">Development</option>
                  <option value="Design">Design</option>
                  <option value="DevOps">DevOps</option>
                  <option value="Management">Management</option>
                </select>
              </div>

              <!-- Job Type select -->
              <div class="flex flex-col gap-1.5 text-left">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Job Type</label>
                <select
                  v-model="jobForm.type"
                  class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm font-semibold transition-all"
                >
                  <option value="Full-time">Full-time</option>
                  <option value="Contract">Contract</option>
                  <option value="Internship">Internship</option>
                </select>
              </div>

              <!-- Experience level select -->
              <div class="flex flex-col gap-1.5 text-left">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Experience Level</label>
                <select
                  v-model="jobForm.experience"
                  class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm font-semibold transition-all"
                >
                  <option value="Junior">Junior</option>
                  <option value="Mid-level">Mid-level</option>
                  <option value="Mid-Senior">Mid-Senior</option>
                  <option value="Senior">Senior</option>
                </select>
              </div>
            </div>

            <!-- Skills -->
            <FormInput
              v-model="jobForm.skillsInput"
              label="Keywords / Skills (Comma-separated)"
              placeholder="e.g. Vue.js, Node.js, AWS, Kubernetes"
            />

            <!-- Requirements -->
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Requirements (One per line)</label>
              <textarea
                v-model="jobForm.requirementsInput"
                rows="3"
                placeholder="e.g. 3+ years experience with Javascript&#10;Familiarity with containerization..."
                class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl transition-all outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm leading-relaxed"
              />
            </div>

            <!-- Benefits -->
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Benefits (One per line)</label>
              <textarea
                v-model="jobForm.benefitsInput"
                rows="3"
                placeholder="e.g. Full health insurance cover&#10;Flexible remote working environment..."
                class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl transition-all outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm leading-relaxed"
              />
            </div>

            <!-- Description -->
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Detailed Description *</label>
              <textarea
                v-model="jobForm.description"
                rows="5"
                placeholder="Provide a comprehensive role description detailing objectives and responsibilities..."
                required
                class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl transition-all outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm leading-relaxed"
              />
            </div>

            <!-- Error message -->
            <div v-if="formError" class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-500 text-xs font-semibold">
              {{ formError }}
            </div>

            <!-- Action -->
            <div class="flex justify-end pt-2">
              <Button
                type="submit"
                variant="primary"
                :loading="formLoading"
              >
                Publish Job Listing
              </Button>
            </div>
          </form>
        </div>

        <!-- Tab 3: Manage Jobs -->
        <div v-if="activeTab === 'manage-jobs'" class="space-y-4">
          <!-- Empty State -->
          <div
            v-if="jobs.length === 0"
            class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-12 text-center text-slate-500 shadow-sm"
          >
            <h3 class="text-base font-bold text-slate-700 dark:text-slate-350">No jobs posted</h3>
            <p class="text-xs text-slate-400 mt-1">Click the 'Post a Job' tab to publish your first opportunity.</p>
          </div>

          <!-- Jobs list table -->
          <div v-else class="overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800/60">
                <thead class="bg-slate-50 dark:bg-slate-800/40">
                  <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Job Title</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Salary</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold text-slate-700 dark:text-slate-300">
                  <tr v-for="job in jobs" :key="job.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                    <td class="px-6 py-4.5 whitespace-nowrap text-slate-900 dark:text-white font-bold">
                      <router-link :to="{ name: 'JobDetail', params: { id: job.id } }" class="hover:text-brand-600 transition-colors">
                        {{ job.title }}
                      </router-link>
                      <span v-if="job.featured" class="ml-2 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-brand-500/10 text-brand-600 dark:text-brand-450 align-middle">Featured</span>
                    </td>
                    <td class="px-6 py-4.5 whitespace-nowrap text-slate-400 text-sm font-medium">{{ job.salary }}</td>
                    <td class="px-6 py-4.5 whitespace-nowrap text-slate-400 text-sm font-medium">{{ job.type }}</td>
                    <td class="px-6 py-4.5 whitespace-nowrap">
                      <button
                        @click="() => handleDeleteJob(job.id)"
                        class="px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-500/10 hover:text-rose-500 rounded-lg cursor-pointer transition-all"
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
