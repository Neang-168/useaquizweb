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
import Users from '../views/pages/superAdmin/Users.vue'
import RolesPermissions from '../views/pages/superAdmin/RolesPermissions.vue'

import TeacherLayout from '../views/TeacherLayout.vue'
import TeachClasses from '../views/pages/teacher/Classes.vue'
import TeachSubjects from '../views/pages/teacher/Subjects.vue'
import questionBank from '../views/pages/teacher/questionBank.vue'
import Quizzes from '../views/pages/teacher/Quizzes.vue'
import ScoreReport from '../views/pages/teacher/ScoreReport.vue'
import Feedback from '../views/pages/teacher/Feedback.vue'
import QuestionBank from '../views/pages/teacher/questionBank.vue'

import StudentLayout from '../views/StudentLayout.vue'
import Mycourse from '../views/pages/student/Mycourse.vue'
import MyExam from '../views/pages/student/MyExam.vue'
import GradeHistory from '../views/pages/student/GradeHistory.vue'
import Notification from '../views/pages/student/Notification.vue'


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
        path: 'users',
        name: 'admin.users',
        component: Users,
      },
      {
        path: 'roles-permissions',
        name: 'admin.roles-permissions',
        component: RolesPermissions,
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
        path: 'classes',
        name: 'teacher.classes',
        component: TeachClasses,
      },
      {
        path: 'subjects',
        name: 'teacher.subjects',
        component: TeachSubjects,
      },
      {
        path: 'questionbank',
        name: 'teacher.questionbank',
        component: QuestionBank,
      },
      {
        path: 'quizzes',
        name: 'teacher.quizzes',
        component: Quizzes,
      },
      {
        path: 'scoreReport',
        name: 'teacher.scoreReport',
        component: ScoreReport,
      },
      {
        path: 'feedback',
        name: 'teacher.feedback',
        component: Feedback,
      },
      {
        path: 'role-profile',
        alias: 'profile',
        name: 'teacher.role-profile',
        component: RoleProfileView,
      },
    ],
  },

  //Route Student
  {
    path: '/student',
    component: StudentLayout,
    meta:{requiresAuth: true, roles: ['Student']},
    children: [
      {
        path: 'dashboard',
        name: 'student.dashboard',
        component: () => import('../views/pages/student/Dashboard.vue'),
      },
      {
        path: 'mycourses',
        name: 'student.mycourses',
        component: Mycourse,
      },
      {
        path: 'myexam',
        name: 'student.myexam',
        component: MyExam,
      },
      {
        path: 'gradeHistory',
        name: 'student.gradeHistory',
        component: GradeHistory,
      },
      {
        path: 'notification',
        name: 'student.notification',
        component: Notification,
      },
    ]
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

// ================= Navigation Guard (ការពារតាម Role) =================
router.beforeEach((to, from) => {
  const token = localStorage.getItem('auth_token')
  const userRole = localStorage.getItem('auth_role') // "Super Admin", "Admin", ឬ "Teacher"

  // ១. បើ Route ត្រូវការ Auth តែមិនទាន់ Login ត្រូវរុញទៅ Login
  if (to.meta.requiresAuth && !token) {
    return { name: 'login' }
  }

  // ២. បើ Login រួចហើយ តែព្យាយាមចូល Route ដែលខ្លួនគ្មានសិទ្ធិ (Role មិនត្រូវគ្នា)
  if (to.meta.roles && !to.meta.roles.includes(userRole)) {
    if (userRole === 'Super Admin' || userRole === 'Admin') {
      return { name: 'admin.dashboard' }
    } else if (userRole === 'Teacher') {
      return { name: 'teacher.dashboard' }
    } else if (userRole === 'Student') {
      return {name: 'student.dashboard'}
    } else {
      return { name: 'login' }
    }
  }

  // ៣. បើ Login រួចហើយ តែព្យាយាមចូលទំព័រ /login ម្ដងទៀត ត្រូវរុញទៅ Dashboard តាម Role
  if (to.name === 'login' && token) {
    if (userRole === 'Super Admin' || userRole === 'Admin') {
      return { name: 'admin.dashboard' }
    } else if (userRole === 'Teacher') {
      return { name: 'teacher.dashboard' }
    } else if( userRole === 'Student'){
      return {name: 'student.dashboard'}
    }
  }

  // បើឆែកត្រូវអស់ហើយ ឱ្យវាបន្តដំណើរទៅ Route នោះធម្មតា
  return true
})

export default router