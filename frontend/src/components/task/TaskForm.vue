<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTaskStore } from '../../store/tasks'
import { useNotificationStore } from '../../store/notification'
import axios from 'axios'
import { ensureAuthHeader } from '../../utils/auth'

const props = defineProps({
  projectId: {
    type: [Number, String],
    default: null
  },
  taskId: {
    type: [Number, String],
    default: null
  }
})

const route = useRoute()
const router = useRouter()
const taskStore = useTaskStore()
const notificationStore = useNotificationStore()

const isLoading = ref(false)
const error = ref(null)
const users = ref([])

// Formulaire
const form = ref({
  title: '',
  description: '',
  status: 'todo',
  priority: 'medium',
  assigned_to: null
})

// Validation
const errors = ref({
  title: '',
  description: ''
})

const isEditing = computed(() => !!props.taskId || !!route.params.id)
const pageTitle = computed(() => isEditing.value ? 'Modifier la tâche' : 'Créer une nouvelle tâche')

// Options pour les sélecteurs
const statusOptions = [
  { value: 'todo', label: 'À faire' },
  { value: 'in_progress', label: 'En cours' },
  { value: 'done', label: 'Terminé' }
]

const priorityOptions = [
  { value: 'low', label: 'Basse' },
  { value: 'medium', label: 'Moyenne' },
  { value: 'high', label: 'Haute' }
]

onMounted(async () => {
  // Récupérer la liste des utilisateurs pour l'assignation
  try {
    // Utiliser la fonction utilitaire pour s'assurer que l'en-tête d'autorisation est défini
    ensureAuthHeader()
    
    const projectId = props.projectId || route.params.projectId
    console.log('TaskForm: Récupération des utilisateurs pour le projet', projectId)
    
    if (!projectId) {
      throw new Error('ID du projet non défini')
    }
    
    // Utiliser axios au lieu de fetch pour bénéficier des configurations globales
    const response = await axios.get(`/api/projects/${projectId}/users`)
    console.log('TaskForm: Utilisateurs récupérés avec succès', response.data)
    
    if (Array.isArray(response.data)) {
      users.value = response.data
    } else {
      console.warn('Format de réponse inattendu pour les utilisateurs:', response.data)
      users.value = []
    }
  } catch (err) {
    console.error('Erreur lors de la récupération des utilisateurs:', err)
    console.error('Détails de la réponse:', err.response?.data)
    error.value = 'Erreur lors de la récupération des utilisateurs: ' + (err.response?.data?.message || err.message || 'Erreur inconnue')
    notificationStore.error(error.value)
  }
  
  // Si on est en mode édition, récupérer les données de la tâche
  if (isEditing.value) {
    const taskId = props.taskId || route.params.id
    await loadTask(taskId)
  }
})

async function loadTask(id) {
  isLoading.value = true
  try {
    const task = await taskStore.fetchTask(id)
    form.value = {
      title: task.title,
      description: task.description,
      status: task.status,
      priority: task.priority,
      assigned_to: task.assigned_to?.id || null
    }
  } catch (err) {
    error.value = 'Erreur lors du chargement de la tâche'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

function validateForm() {
  let isValid = true
  errors.value = {
    title: '',
    description: ''
  }
  
  if (!form.value.title.trim()) {
    errors.value.title = 'Le titre est requis'
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
    // Assurons-nous que assigned_to est null si non sélectionné
    if (form.value.assigned_to === '' || form.value.assigned_to === undefined) {
      form.value.assigned_to = null
    }
    
    console.log('Données du formulaire à soumettre:', form.value)
    
    if (isEditing.value) {
      // Mode édition
      const taskId = props.taskId || route.params.id
      console.log('Mise à jour de la tâche:', taskId, form.value)
      await taskStore.updateTask(taskId, form.value)
      notificationStore.success('La tâche a été modifiée avec succès')
      router.push({ name: 'project-detail', params: { id: route.params.projectId || props.projectId } })
    } else {
      // Mode création
      const projectId = props.projectId || route.params.projectId
      console.log('Création de la tâche pour le projet:', projectId, form.value)
      await taskStore.createTask(projectId, form.value)
      notificationStore.success('La tâche a été créée avec succès')
      router.push({ name: 'project-detail', params: { id: projectId } })
    }
  } catch (err) {
    console.error('Erreur lors de la soumission:', err)
    console.error('Réponse d\'erreur:', err.response?.data)
    
    if (err.response?.data?.errors) {
      // Afficher les erreurs de validation spécifiques
      const validationErrors = err.response.data.errors
      let errorMessage = 'Veuillez corriger les erreurs suivantes:\n'
      
      Object.keys(validationErrors).forEach(field => {
        errorMessage += `- ${validationErrors[field].join('\n- ')}\n`
      })
      
      error.value = errorMessage
    } else {
      error.value = err.response?.data?.message || 'Une erreur est survenue'
    }
    
    notificationStore.error(error.value)
  } finally {
    isLoading.value = false
  }
}

function cancel() {
  router.back()
}
</script>

<template>
  <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ pageTitle }}</h2>
    
    <div v-if="error" class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
      {{ error }}
    </div>
    
    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
        <input 
          type="text" 
          id="title" 
          v-model="form.title" 
          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          :class="{'border-red-500': errors.title}"
          placeholder="Titre de la tâche"
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
          placeholder="Description détaillée de la tâche"
        ></textarea>
        <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
          <select 
            id="status" 
            v-model="form.status"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
        
        <div>
          <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
          <select 
            id="priority" 
            v-model="form.priority"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option v-for="option in priorityOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
      </div>
      
      <div>
        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assignée à</label>
        <select 
          id="assigned_to" 
          v-model="form.assigned_to"
          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option :value="null">Non assignée</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
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
</template>