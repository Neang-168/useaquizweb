import { createRouter, createWebHistory } from 'vue-router'
import { quizInProgress } from '../utils/quizLock'


import AdminLayout from '../views/AdminContentView.vue'
import LoginPageView from '../views/LoginPageView.vue' 
import RoleProfileView from '../views/RoleProfileView.vue'
import MyProfileView from '../views/MyProfileView.vue'
import Subjects from '../views/pages/superAdmin/Subjects.vue'
import Sessions from '../views/pages/superAdmin/Sessions.vue'
import Classes from '../views/pages/superAdmin/Classes.vue'
import Teachers from '../views/pages/superAdmin/Teachers.vue'
import Students from '../views/pages/superAdmin/Students.vue'
import Users from '../views/pages/superAdmin/Users.vue'
import RolesPermissions from '../views/pages/superAdmin/RolesPermissions.vue'
import Report from '../views/pages/superAdmin/Report.vue'

import TeacherLayout from '../views/TeacherLayout.vue'
import TeachClasses from '../views/pages/teacher/Classes.vue'
import ClassWorkspace from '../views/pages/teacher/ClassWorkspace.vue'
import TeacherCalendar from '../views/pages/teacher/Calendar.vue'
import TeachSubjects from '../views/pages/teacher/Subjects.vue'
import ScoreReport from '../views/pages/teacher/ScoreReport.vue'
import Feedback from '../views/pages/teacher/Feedback.vue'
import QuestionBank from '../views/pages/teacher/QuestionBank.vue'
import QuestionImportExport from '../views/pages/teacher/QuestionImportExport.vue'

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
    meta: { requiresAuth: true, roles: ['Admin'] },
    children: [
      {
        path: 'dashboard',
        name: 'admin.dashboard',
        component: () => import('../views/pages/superAdmin/Dashboard.vue'),
      },
      {
        path: 'subjects',
        name: 'admin.subjects',
        component: Subjects,
      },
      {
        path: 'sessions',
        name: 'admin.sessions',
        component: Sessions,
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
        path: 'report',
        name: 'admin.report',
        component: Report,
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
        name: 'admin.role-profile',
        component: RoleProfileView,
      },
      {
        path: 'my-profile',
        alias: 'profile',
        name: 'admin.my-profile',
        component: MyProfileView,
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
        path: 'classes/:assignmentId',
        name: 'teacher.classWorkspace',
        component: ClassWorkspace,
      },
      {
        path: 'calendar',
        name: 'teacher.calendar',
        component: TeacherCalendar,
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
        path: 'questionbank/import-export',
        name: 'teacher.questionbank.importExport',
        component: QuestionImportExport,
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
        name: 'teacher.role-profile',
        component: RoleProfileView,
      },
      {
        path: 'my-profile',
        alias: 'profile',
        name: 'teacher.my-profile',
        component: MyProfileView,
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
        path: 'courses/:classId/:subjectId',
        name: 'student.courseWorkspace',
        component: () => import('../views/pages/student/CourseWorkspace.vue'),
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
      {
        path: 'take-quiz',
        name: 'student.takeQuiz',
        component: () => import('../views/pages/student/TakeQuiz.vue'),
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
  // While a timed quiz attempt is open, block leaving the quiz page for
  // anything else (sidebar links, back button, address bar) unless the
  // student explicitly confirms — the server-side timer keeps running
  // regardless, so this is about preventing accidental abandonment, not
  // protecting the clock itself.
  if (quizInProgress.value && from.name === 'student.takeQuiz' && to.name !== 'student.takeQuiz') {
    const leave = window.confirm('Your quiz is still in progress. Leaving now will not stop the timer or save your answers. Leave anyway?')
    if (!leave) return false
    quizInProgress.value = false
  }

  const token = localStorage.getItem('auth_token')
  const userRole = localStorage.getItem('auth_role') // "Admin", "Teacher", ឬ "Student"

  // ១. បើ Route ត្រូវការ Auth តែមិនទាន់ Login ត្រូវរុញទៅ Login
  if (to.meta.requiresAuth && !token) {
    return { name: 'login' }
  }

  // ២. បើ Login រួចហើយ តែព្យាយាមចូល Route ដែលខ្លួនគ្មានសិទ្ធិ (Role មិនត្រូវគ្នា)
  if (to.meta.roles && !to.meta.roles.includes(userRole)) {
    if (userRole === 'Admin') {
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
    if (userRole === 'Admin') {
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