import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '../components/MainLayout.vue'

import Dashboard from '../dashboard.vue'
import Attendance from '../attendance.vue'
import Upload from '../upload.vue'
import Employee from '../employee.vue'
import Reports from '../reports.vue'

const routes = [
  {
    path: '/',
    component: MainLayout,
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', component: Dashboard },
      { path: 'attendance', component: Attendance },
      { path: 'upload', component: Upload },
      { path: 'employee', component: Employee },
      { path: 'reports', component: Reports },
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
