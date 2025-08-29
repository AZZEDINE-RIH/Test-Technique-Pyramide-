<script setup>
import { ref, defineEmits } from 'vue'
import { useRouter } from 'vue-router'
import ProjectService from '../services/ProjectService'
import { useAuthStore } from '../store/auth'

const router = useRouter()
const emit = defineEmits(['project-created'])

const authStore = useAuthStore()
const title = ref('')
const description = ref('')
const loading = ref(false)
const error = ref(null)
const success = ref(false)
const createProject = async () => {
  if (!authStore.isAuthenticated) {
    error.value = 'You must be logged in to create a project'
    return
  }

  loading.value = true
  error.value = null
  success.value = false

  try {
    // Log authentication state before making the request
    console.log('ProjectForm: Creating project with auth state:', {
      isAuthenticated: authStore.isAuthenticated,
      hasToken: !!authStore.token,
      hasUser: !!authStore.user,
      authHeader: window.axios.defaults.headers.common['Authorization']
    })

    const newProject = await ProjectService.createProject({
      title: title.value,
      description: description.value
    })

    // Reset form and show success
    title.value = ''
    description.value = ''
    success.value = true

    // Emit event to notify parent component that project was created
    emit('project-created', newProject)

    // Show success message and redirect
    alert('Project created successfully!')

    // Use simple path navigation to avoid route parameter issues
    router.push('/dashboard')
  } catch (err) {
    console.error('ProjectForm: Error creating project:', err)
    error.value = err.response?.data?.message || 'Failed to create project'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="project-form">
    <h2>Create New Project</h2>

    <div v-if="!authStore.isAuthenticated" class="error-message">
      Unauthenticated. Please log in to create projects.
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-if="success" class="success-message">
      Project created successfully!
    </div>

    <form @submit.prevent="createProject">
      <div class="form-group">
        <label for="title">Project Title</label>
        <input
          id="title"
          v-model="title"
          type="text"
          required
          placeholder="Enter project title"
        >
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea
          id="description"
          v-model="description"
          placeholder="Enter project description"
        ></textarea>
      </div>

      <div class="form-actions">
        <button
          type="submit"
          :disabled="loading || !authStore.isAuthenticated"
        >
          {{ loading ? 'Creating...' : 'Create Project' }}
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.project-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

input, textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

textarea {
  min-height: 100px;
}

.form-actions {
  margin-top: 20px;
}

button {
  padding: 10px 15px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.error-message {
  color: #f44336;
  margin-bottom: 15px;
  padding: 10px;
  background-color: #ffebee;
  border-radius: 4px;
}

.success-message {
  color: #4CAF50;
  margin-bottom: 15px;
  padding: 10px;
  background-color: #e8f5e9;
  border-radius: 4px;
}
</style>
