<script setup>
import { ref } from 'vue'
import { useTaskStore } from '../../store/tasks'

const props = defineProps({
  task: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['statusChanged', 'deleted', 'edited'])

const taskStore = useTaskStore()
const isLoading = ref(false)
const error = ref(null)

const statusOptions = [
  { value: 'todo', label: 'À faire' },
  { value: 'in_progress', label: 'En cours' },
  { value: 'done', label: 'Terminé' }
]

const priorityLabels = {
  low: 'Basse',
  medium: 'Moyenne',
  high: 'Haute'
}

const priorityColors = {
  low: 'bg-blue-100 text-blue-800',
  medium: 'bg-yellow-100 text-yellow-800',
  high: 'bg-red-100 text-red-800'
}

const statusColors = {
  todo: 'bg-gray-100 text-gray-800',
  in_progress: 'bg-purple-100 text-purple-800',
  done: 'bg-green-100 text-green-800'
}

async function changeStatus(newStatus) {
  isLoading.value = true
  error.value = null
  
  try {
    await taskStore.updateTaskStatus(props.task.id, newStatus)
    emit('statusChanged', props.task.id, newStatus)
  } catch (err) {
    error.value = 'Erreur lors du changement de statut'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

async function deleteTask() {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) return
  
  isLoading.value = true
  error.value = null
  
  try {
    await taskStore.deleteTask(props.task.id)
    emit('deleted', props.task.id)
  } catch (err) {
    error.value = 'Erreur lors de la suppression'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

function editTask() {
  emit('edited', props.task.id)
}
</script>

<template>
  <div class="bg-white rounded-lg shadow-md p-4 mb-4 border-l-4" 
       :class="{
         'border-blue-500': task.priority === 'low',
         'border-yellow-500': task.priority === 'medium',
         'border-red-500': task.priority === 'high'
       }">
    <div v-if="isLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>
    
    <div class="flex justify-between items-start mb-2">
      <h3 class="text-lg font-semibold">{{ task.title }}</h3>
      <div class="flex space-x-2">
        <span :class="`px-2 py-1 rounded-full text-xs font-medium ${priorityColors[task.priority]}`">
          {{ priorityLabels[task.priority] }}
        </span>
        <span :class="`px-2 py-1 rounded-full text-xs font-medium ${statusColors[task.status]}`">
          {{ statusOptions.find(option => option.value === task.status)?.label }}
        </span>
      </div>
    </div>
    
    <p class="text-gray-600 mb-3">{{ task.description }}</p>
    
    <div class="flex justify-between items-center">
      <div class="text-sm text-gray-500">
        <span v-if="task.assigned_user && task.assigned_user.name">Assignée à: {{ task.assigned_user.name }}</span>
        <span v-else-if="task.assigned_to && typeof task.assigned_to === 'object' && task.assigned_to.name">Assignée à: {{ task.assigned_to.name }}</span>
        <span v-else>Non assignée</span>
      </div>
      
      <div class="flex space-x-2">
        <button @click="editTask" 
                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md text-sm transition">
          Éditer
        </button>
        
        <div class="relative inline-block text-left">
          <select 
            v-model="task.status" 
            @change="changeStatus($event.target.value)"
            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md text-sm transition appearance-none pr-8">
            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
              <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
            </svg>
          </div>
        </div>
        
        <button @click="deleteTask" 
                class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-800 rounded-md text-sm transition">
          Supprimer
        </button>
      </div>
    </div>
    
    <div v-if="error" class="mt-2 text-red-600 text-sm">{{ error }}</div>
  </div>
</template>