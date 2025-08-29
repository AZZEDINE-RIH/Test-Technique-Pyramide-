<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectStore } from '../../store/projects'
import { useAuthStore } from '../../store/auth'
import { useNotificationStore } from '../../store/notification'

const route = useRoute()
const router = useRouter()
const projectStore = useProjectStore()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const isLoading = ref(false)
const error = ref(null)

// Formulaire
const form = ref({
  title: '',
  description: ''
})

// Validation
const errors = ref({
  title: '',
  description: ''
})

const isEditing = computed(() => !!route.params.id)
const pageTitle = computed(() => isEditing.value ? 'Modifier le projet' : 'Créer un nouveau projet')

onMounted(async () => {
  if (isEditing.value) {
    await loadProject(route.params.id)
  }
})

async function loadProject(id) {
  isLoading.value = true
  try {
    const project = await projectStore.fetchProject(id)
    console.log('Project loaded:', project)
    
    if (!project) {
      console.error('Project data is null or undefined')
      error.value = 'Données du projet invalides'
      return
    }
    
    // Set form values from project data
    form.value = {
      title: project.title || '',
      description: project.description || ''
    }
    
    console.log('Form values set:', form.value)
  } catch (err) {
    error.value = 'Erreur lors du chargement du projet'
    console.error('Error loading project:', err)
  } finally {
    isLoading.value = false
  }
}

function validateForm() {
  // Reset validation errors
  errors.value = {
    title: '',
    description: ''
  }
  
  let isValid = true
  
  if (!form.value.title.trim()) {
    errors.value.title = 'Le titre du projet est requis'
    isValid = false
  } else if (form.value.title.length < 3) {
    errors.value.title = 'Le titre doit contenir au moins 3 caractères'
    isValid = false
  }
  
  if (!form.value.description.trim()) {
    errors.value.description = 'La description est requise'
    isValid = false
  }
  
  return isValid
}

async function submitForm() {
  if (!validateForm()) return
  
  isLoading.value = true
  error.value = null
  
  try {
    if (isEditing.value) {
      await projectStore.updateProject(route.params.id, form.value)
      notificationStore.success('Le projet a été modifié avec succès')
      router.push({ name: 'project-detail', params: { id: route.params.id } })
    } else {
      const newProject = await projectStore.createProject(form.value)
      notificationStore.success('Le projet a été créé avec succès')
      router.push({ name: 'dashboard' })
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Une erreur est survenue'
    notificationStore.error(error.value)
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

function cancel() {
  router.back()
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center">
          <button @click="cancel" class="mr-4 text-gray-600 hover:text-gray-900">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          <h1 class="text-2xl font-bold text-gray-900">{{ pageTitle }}</h1>
        </div>
      </div>
    </header>
    
    <!-- Main content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
        <div v-if="error" class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
          {{ error }}
        </div>
        
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre du projet</label>
            <input 
              type="text" 
              id="title" 
              v-model="form.title" 
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{'border-red-500': errors.title}"
              placeholder="Titre du projet"
            >
            <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
          </div>
          
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              id="description" 
              v-model="form.description" 
              rows="4"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{'border-red-500': errors.description}"
              placeholder="Description du projet"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4">
            <button 
              type="button" 
              @click="cancel"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
              :disabled="isLoading"
            >
              Annuler
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              :disabled="isLoading"
            >
              <span v-if="isLoading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Traitement...
              </span>
              <span v-else>{{ isEditing ? 'Mettre à jour' : 'Créer' }}</span>
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>