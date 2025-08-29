<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import ProjectService from '../services/ProjectService'
import ProjectForm from '../components/ProjectForm.vue'
import { ensureAuthHeader } from '../utils/auth'

const router = useRouter()

const authStore = useAuthStore()
const projects = ref([])
const loading = ref(false)
const error = ref(null)

// Load projects when component is mounted
const loadProjects = async () => {
  loading.value = true
  error.value = null
  projects.value = []
  
  try {
    // Ensure we're authenticated before loading projects
    if (!authStore.isAuthenticated) {
      await authStore.fetchUser()
    }
    
    // Double check authentication and ensure header is set
    if (authStore.isAuthenticated) {
      ensureAuthHeader()
      const fetchedProjects = await ProjectService.getProjects()
      
      // Ensure we have an array of projects
      if (Array.isArray(fetchedProjects)) {
        projects.value = fetchedProjects
      } else {
        projects.value = []
        error.value = 'Invalid project data format received'
      }
    } else {
      error.value = 'You must be logged in to view projects'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load projects'
  } finally {
    loading.value = false
  }
}

// Refresh projects list after a new project is created
const refreshProjects = () => {
  loadProjects()
}

// Handle view project details button click
const viewProjectDetails = (projectId) => {
  if (projectId) {
    router.push(`/projects/${projectId}`)
  }
}

onMounted(() => {
  loadProjects()
})
</script>

<template>
  <div class="dashboard">
    <h1>Dashboard</h1>
    
    <div class="auth-status">
      <div v-if="authStore.isAuthenticated" class="auth-info">
        <p>Logged in as: {{ authStore.user?.name || 'Unknown User' }}</p>
        <p>Email: {{ authStore.user?.email || 'No email' }}</p>
      </div>
      <div v-else class="auth-warning">
        <p>You are not logged in. Please log in to view and create projects.</p>
      </div>
    </div>
    
    <div class="dashboard-content">
      <div class="projects-section">
        <h2>Your Projects</h2>
        
        <div v-if="loading" class="loading">
          Loading projects...
        </div>
        
        <div v-else-if="error" class="error-message">
          {{ error }}
        </div>
        
        <div v-else-if="projects.length === 0" class="no-projects">
          No projects found. Create your first project!
        </div>
        
        <div v-else class="projects-list">
          <div v-for="project in projects" :key="project.id" class="project-card">
            <h3>{{ project.title }}</h3>
            <p>{{ project.description }}</p>
            <div class="project-meta">
              <span>Created: {{ new Date(project.created_at).toLocaleDateString() }}</span>
              <span v-if="project.updated_at && project.updated_at !== project.created_at">Updated: {{ new Date(project.updated_at).toLocaleDateString() }}</span>
            </div>
            <div class="project-actions">
              <button @click="viewProjectDetails(project.id)" class="action-btn view-btn">View Details</button>
            </div>
          </div>
        </div>
        
        <button @click="refreshProjects" :disabled="loading" class="refresh-btn">
        {{ loading ? 'Refreshing...' : 'Refresh Projects' }}
      </button>
    </div>
    
    <!-- Composant de débogage supprimé -->
      
      <div class="create-project-section">
        <ProjectForm @project-created="refreshProjects" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.auth-status {
  margin-bottom: 20px;
  padding: 15px;
  background-color: #f5f5f5;
  border-radius: 4px;
}

.auth-warning {
  color: #f57c00;
}

.dashboard-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
}

@media (max-width: 768px) {
  .dashboard-content {
    grid-template-columns: 1fr;
  }
}

.projects-section, .create-project-section {
  background-color: #fff;
  border-radius: 4px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.loading {
  text-align: center;
  padding: 20px;
  color: #666;
}

.error-message {
  color: #f44336;
  padding: 10px;
  background-color: #ffebee;
  border-radius: 4px;
  margin-bottom: 15px;
}

.no-projects {
  text-align: center;
  padding: 20px;
  color: #666;
  font-style: italic;
}

.projects-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.project-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  background-color: #fafafa;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s, box-shadow 0.2s;
}

.project-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.project-card h3 {
  margin-top: 0;
  color: #333;
}

.project-meta {
  margin-top: 10px;
  font-size: 0.9em;
  color: #666;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.project-actions {
  margin-top: 15px;
  display: flex;
  justify-content: flex-end;
}

.action-btn {
  padding: 6px 12px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  font-size: 0.9em;
  transition: background-color 0.2s;
}

.view-btn {
  background-color: #2196F3;
  color: white;
}

.view-btn:hover {
  background-color: #1976D2;
}

.refresh-btn {
  margin-top: 15px;
  padding: 8px 15px;
  background-color: #2196F3;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.refresh-btn:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}
</style>