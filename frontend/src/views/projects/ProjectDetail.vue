<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectStore } from '../../store/projects'
import { useTaskStore } from '../../store/tasks'
import TaskCard from '../../components/task/TaskCard.vue'

const route = useRoute()
const router = useRouter()
const projectStore = useProjectStore()
const taskStore = useTaskStore()

const isLoading = ref(false)
const error = ref(null)
const showConfirmDelete = ref(false)
const taskToDelete = ref(null)

onMounted(async () => {
  await loadProject()
})

async function loadProject() {
  isLoading.value = true
  error.value = null

  try {
    await projectStore.fetchProject(route.params.id)
    console.log('Projet chargé:', projectStore.currentProject)
    
    // Récupérer les tâches du projet
    await taskStore.fetchTasks(route.params.id)
    console.log('Tâches chargées:', taskStore.tasks)
  } catch (err) {
    error.value = 'Erreur lors du chargement du projet'
    console.error('Erreur de chargement:', err)
  } finally {
    isLoading.value = false
  }
}

function goBack() {
  router.push('/dashboard')
}

function editProject() {
  router.push({ name: 'project-edit', params: { id: route.params.id } })
}

function createTask() {
  router.push({ name: 'task-create', params: { projectId: route.params.id } })
}

function editTask(taskId) {
  router.push({ name: 'task-edit', params: { id: taskId } })
}

function confirmDeleteTask(taskId) {
  taskToDelete.value = taskId
  showConfirmDelete.value = true
}

async function deleteTask() {
  if (!taskToDelete.value) return

  isLoading.value = true
  error.value = null

  try {
    await taskStore.deleteTask(taskToDelete.value)
    showConfirmDelete.value = false
    taskToDelete.value = null
  } catch (err) {
    error.value = 'Erreur lors de la suppression de la tâche'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

async function handleStatusChanged(taskId, newStatus) {
  // La mise à jour est déjà gérée dans le composant TaskCard
  console.log(`Statut de la tâche ${taskId} changé à ${newStatus}`)
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center">
          <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          <h1 class="text-2xl font-bold text-gray-900">Détail du projet</h1>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Loading state -->
      <div v-if="isLoading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
      </div>

      <!-- Error message -->
      <div v-else-if="error" class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
        {{ error }}
      </div>

      <!-- Project details -->
      <div v-else-if="projectStore.currentProject" class="px-4 sm:px-0">
        <div class="bg-white shadow rounded-lg mb-6 p-6">
          <div class="flex justify-between items-start mb-4">
            <h2 class="text-2xl font-bold text-gray-800">{{ projectStore.currentProject.name }}</h2>
            <button
              @click="editProject"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md text-sm transition"
            >
              Modifier le projet
            </button>
          </div>
          <p class="text-gray-600 mb-4">{{ projectStore.currentProject.description }}</p>
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-500">
              Créé le {{ projectStore.currentProject.created_at ? new Date(projectStore.currentProject.created_at).toLocaleDateString() : 'Date non disponible' }}
            </div>
          </div>
        </div>

        <!-- Tasks section -->
        <div class="mb-6 flex justify-between items-center">
          <h3 class="text-xl font-semibold text-gray-800">Tâches</h3>
          <button
            @click="createTask"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Nouvelle tâche
          </button>
        </div>

        <!-- Tasks list -->
        <div v-if="taskStore.tasks && taskStore.tasks.length > 0" class="space-y-4">
          <TaskCard
            v-for="task in taskStore.tasks"
            :key="task.id"
            :task="task"
            @status-changed="handleStatusChanged"
            @deleted="confirmDeleteTask"
            @edited="editTask"
          />
          <pre v-if="false" class="bg-gray-100 p-2 text-xs">{{ taskStore.tasks }}</pre>
        </div>

        <!-- Empty tasks state -->
        <div v-else class="py-12 flex flex-col items-center justify-center bg-white rounded-lg shadow">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-1">Aucune tâche</h3>
          <p class="text-gray-500 mb-4">Commencez par créer votre première tâche</p>
          <button
            @click="createTask"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Créer une tâche
          </button>
        </div>
      </div>

      <!-- Project not found -->
      <div v-else class="px-4 sm:px-0 py-12 flex flex-col items-center justify-center bg-white rounded-lg shadow">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-1">Projet non trouvé</h3>
        <p class="text-gray-500 mb-4">Le projet que vous recherchez n'existe pas ou a été supprimé</p>
        <button
          @click="goBack"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          Retour au tableau de bord
        </button>
      </div>
    </main>

    <!-- Delete confirmation modal -->
    <div v-if="showConfirmDelete" class="fixed inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showConfirmDelete = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Supprimer la tâche
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Êtes-vous sûr de vouloir supprimer cette tâche ? Cette action est irréversible.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
              :disabled="isLoading"
              @click="deleteTask"
            >
              <span v-if="isLoading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Suppression...
              </span>
              <span v-else>Supprimer</span>
            </button>
            <button
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              @click="showConfirmDelete = false"
              :disabled="isLoading"
            >
              Annuler
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
