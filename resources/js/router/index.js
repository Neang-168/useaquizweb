import { createRouter, createWebHistory } from 'vue-router'


import AdminLayout from '../views/AdminContentView.vue'
import LoginPageView from '../views/LoginPageView.vue' 
import RoleProfileView from '../views/RoleProfileView.vue'
import Faculties from '../views/pages/superAdmin/Faculties.vue'
import Degrees from '../views/pages/superAdmin/Degrees.vue'
import Subjects from '../views/pages/superAdmin/Subjects.vue'
import AcademicYear from '../views/pages/superAdmin/AcademicYear.vue'
import ShiftsStages from '../views/pages/superAdmin/ShiftsStages.vue'
import Classes from '../views/pages/superAdmin/Classes.vue'
import Teachers from '../views/pages/superAdmin/Teachers.vue'
import Students from '../views/pages/superAdmin/Students.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginPageView,
  },

  // 3. Route Admin 
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: 'dashboard',
        name: 'admin.dashboard',
        component: () => import('../views/pages/superAdmin/Dashboard.vue'),
      },
      {
        path: 'faculties',
        name: 'admin.faculties',
        component: Faculties,
      },
      {
        path: 'degrees',
        name: 'admin.degrees',
        component: Degrees,
      },
      {
        path: 'subjects',
        name: 'admin.subjects',
        component: Subjects,
      },
      {
        path: 'academic-years',
        name: 'admin.academic-years',
        component: AcademicYear,
      },
      {
        path: 'shifts-stages',
        name: 'admin.shifts-stages',
        component: ShiftsStages,
      },
      {
        path: 'classes',
        name: 'admin.classes',
        component: Classes,
      },
      {
        path: 'teachers',
        name: 'admin.teachers',
        component: Teachers,
      },
      {
        path: 'students',
        name: 'admin.students',
        component: Students,
      },
      {
        path: 'role-profile',
        alias: 'profile',
        name: 'admin.role-profile',
        component: RoleProfileView,
      },
    ],
  },

  // បើក / ដំបូង ឱ្យរត់ទៅ /login
  {
    path: '/',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router