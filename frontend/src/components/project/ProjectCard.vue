<script setup>
import { defineProps, defineEmits } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  project: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['deleted'])
const router = useRouter()

function viewProject() {
  router.push({ name: 'project-detail', params: { id: props.project.id } })
}

function editProject() {
  router.push({ name: 'project-edit', params: { id: props.project.id } })
}

function deleteProject() {
  emit('deleted', props.project.id)
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <div class="bg-white rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow duration-300">
    <div class="flex justify-between items-start mb-3">
      <h3 class="text-xl font-bold text-gray-800">{{ project.title }}</h3>
      <div class="flex space-x-2">
        <button @click="editProject" class="p-1 text-gray-500 hover:text-gray-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
        </button>
        <button @click="deleteProject" class="p-1 text-gray-500 hover:text-red-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>
    
    <p class="text-gray-600 mb-4">{{ project.description }}</p>
    
    <!-- Project details section -->
    <div class="mb-4 p-3 bg-gray-50 rounded-md">
      <h4 class="text-sm font-medium text-gray-700 mb-2">Détails du projet</h4>
      <div class="grid grid-cols-2 gap-2 text-sm">
        <div>
          <span class="text-gray-500">ID:</span>
          <span class="ml-1 text-gray-800">{{ project.id }}</span>
        </div>
        <div>
          <span class="text-gray-500">Créé le:</span>
          <span class="ml-1 text-gray-800">{{ formatDate(project.created_at) }}</span>
        </div>
        <div>
          <span class="text-gray-500">Mis à jour le:</span>
          <span class="ml-1 text-gray-800">{{ formatDate(project.updated_at) }}</span>
        </div>
        <div v-if="project.user">
          <span class="text-gray-500">Créé par:</span>
          <span class="ml-1 text-gray-800">{{ project.user.name }}</span>
        </div>
      </div>
    </div>
    
    <div class="flex justify-between items-center">
      <div class="flex items-center space-x-1 text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <span>{{ project.tasks_count || 0 }} tâches</span>
      </div>
      
      <button 
        @click="viewProject"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
        Voir le détail
      </button>
    </div>
  </div>
</template>