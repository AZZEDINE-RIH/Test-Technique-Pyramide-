import { createRouter, createWebHistory } from 'vue-router'

// Import des vues
import Login from '../views/auth/Login.vue'
import Register from '../views/auth/Register.vue'
import Dashboard from '../views/projects/Dashboard.vue'
import ProjectDetail from '../views/projects/ProjectDetail.vue'
import ProjectForm from '../views/projects/ProjectForm.vue'
import TaskForm from '../views/tasks/TaskForm.vue'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'login',
    component: Login
  },
  {
    path: '/register',
    name: 'register',
    component: Register
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/projects/create',
    name: 'project-create',
    component: ProjectForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/projects/:id/edit',
    name: 'project-edit',
    component: ProjectForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/projects/:projectId/tasks/create',
    name: 'task-create',
    component: TaskForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/projects/:id',
    name: 'project-detail',
    component: ProjectDetail,
    meta: { requiresAuth: true }
  },
  {
    path: '/tasks/:id/edit',
    name: 'task-edit',
    component: TaskForm,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard pour vérifier l'authentification
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const token = localStorage.getItem('token')

  if (requiresAuth && !token) {
    next('/login')
  } else {
    next()
  }
})

export default router