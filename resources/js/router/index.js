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

import TeacherLayout from '../views/TeacherLayout.vue'

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
    meta: { requiresAuth: true, roles: ['Super Admin', 'Admin'] },
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

  // Route Teacher
  {
    path: '/teacher',
    component: TeacherLayout, // ប្រើ Layout ដូចគ្នា ឬប្តូរប្រើ TeacherLayout បើមាន
    meta: { requiresAuth: true, roles: ['Teacher'] },
    children: [
      {
        path: 'dashboard',
        name: 'teacher.dashboard',
        component: () => import('../views/pages/teacher/Dashboard.vue'),
      },
      {
        path: 'role-profile',
        alias: 'profile',
        name: 'teacher.role-profile',
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

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token')
  const userRole = localStorage.getItem('auth_role') // "Super Admin", "Admin", ឬ "Teacher"

  // ១. បើ Route ត្រូវការ Auth តែមិនទាន់ Login ត្រូវរុញទៅ Login
  if (to.meta.requiresAuth && !token) {
    return next({ name: 'login' })
  }

  // ២. បើ Login រួចហើយ តែព្យាយាមចូល Route ដែលខ្លួនគ្មានសិទ្ធិ (Role មិនត្រូវគ្នា)
  if (to.meta.roles && !to.meta.roles.includes(userRole)) {
    if (userRole === 'Super Admin' || userRole === 'Admin') {
      return next({ name: 'admin.dashboard' })
    } else if (userRole === 'Teacher') {
      return next({ name: 'teacher.dashboard' })
    } else {
      return next({ name: 'login' })
    }
  }

  // ៣. បើ Login រួចហើយ តែព្យាយាមចូលទំព័រ /login ម្ដងទៀត ត្រូវរុញទៅ Dashboard តាម Role
  if (to.name === 'login' && token) {
    if (userRole === 'Super Admin' || userRole === 'Admin') {
      return next({ name: 'admin.dashboard' })
    } else if (userRole === 'Teacher') {
      return next({ name: 'teacher.dashboard' })
    }
  }

  next()
})

export default router