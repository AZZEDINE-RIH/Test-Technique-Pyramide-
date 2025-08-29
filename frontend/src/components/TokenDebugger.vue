<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../store/auth'
import ProjectService from '../services/ProjectService'
import { ensureAuthHeader } from '../utils/auth'

const authStore = useAuthStore()
const projects = ref([])
const loading = ref(false)
const error = ref(null)

// Server status check
const serverStatus = ref('unknown') // 'online', 'offline', 'unknown'
const checkServerStatus = async () => {
  try {
    // Simple ping to check if server is running
    await window.axios.get('/api/ping', { timeout: 3000 })
    serverStatus.value = 'online'
    return true
  } catch (err) {
    // Don't set offline status for CORS errors which might be false negatives
    if (err.code !== 'ERR_NETWORK' && !err.message?.includes('Network Error')) {
      serverStatus.value = 'offline'
      return false
    }
    // Try an alternative check with projects endpoint
    try {
      await window.axios.get('/api/projects', { timeout: 3000 })
      serverStatus.value = 'online'
      return true
    } catch (secondErr) {
      serverStatus.value = 'offline'
      return false
    }
  }
}

// Load projects function
const loadProjects = async () => {
  loading.value = true
  error.value = null
  serverStatus.value = 'unknown'
  
  try {
    // Skip server status check and try to load projects directly
    // This is more reliable than the ping endpoint which might have CORS issues
    
    // Ensure we're authenticated
    const isAuthenticated = ensureAuthHeader()
    if (!isAuthenticated) {
      error.value = 'Vous n\'êtes pas authentifié. Veuillez vous connecter.'
      loading.value = false
      return
    }
    
    // Fetch projects
    const fetchedProjects = await ProjectService.getProjects()
    
    // If we get here, the server is online
    serverStatus.value = 'online'
    
    // Ensure we have an array of projects
    if (Array.isArray(fetchedProjects)) {
      projects.value = fetchedProjects
    } else {
      projects.value = []
      error.value = 'Format de données de projets invalide reçu'
    }
  } catch (err) {
    // Set server status based on error type
    if (err.code === 'ERR_NETWORK' || err.message?.includes('Network Error')) {
      serverStatus.value = 'offline'
      error.value = 'Erreur réseau: Impossible de se connecter au serveur backend. Vérifiez que le serveur est en cours d\'exécution.'
    } else if (err.response?.status === 401) {
      serverStatus.value = 'online' // Server is online but authentication failed
      error.value = 'Erreur d\'authentification: Votre session a expiré ou vous n\'êtes pas connecté.'
    } else {
      serverStatus.value = 'online' // Server is online but returned an error
      error.value = err.response?.data?.message || 'Échec du chargement des projets: ' + (err.message || 'Erreur inconnue')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  // Load projects
  loadProjects()
})
</script>

<template>
  <div class="token-debugger">
    <div class="projects-section">
      <h3>Your Projects</h3>
      
      <div v-if="loading" class="loading">
        Loading projects...
      </div>
      
      <div v-else-if="error" class="error-message">
        {{ error }}
      </div>
      
      <div v-else-if="projects.length === 0" class="no-projects">
        No projects found.
      </div>
      
      <div v-else class="projects-list">
        <div v-for="project in projects" :key="project.id" class="project-item">
          <h4>{{ project.name }}</h4>
          <p>{{ project.description }}</p>
        </div>
      </div>
      
      <button @click="loadProjects" class="refresh-btn">Refresh Projects</button>
    </div>
  </div>
</template>

<style scoped>
.token-debugger {
  margin: 20px;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
  background-color: #f9f9f9;
}

.projects-section {
  margin-top: 20px;
}

.loading {
  color: #666;
  font-style: italic;
}

.error-message {
  color: #d9534f;
  padding: 10px;
  background-color: #f9f2f2;
  border-radius: 4px;
  margin-bottom: 15px;
}

.no-projects {
  color: #666;
  font-style: italic;
}

.projects-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 15px;
  margin-top: 15px;
}

.project-item {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 10px;
  background-color: white;
}

.project-item h4 {
  margin-top: 0;
  color: #333;
}

.refresh-btn {
  margin-top: 15px;
  padding: 8px 15px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.refresh-btn:hover {
  background-color: #45a049;
}

.server-status-online {
  color: #4CAF50;
  font-weight: bold;
}

.server-status-offline {
  color: #d9534f;
  font-weight: bold;
}

.server-status-unknown {
  color: #f0ad4e;
  font-weight: bold;
}
</style>