<template>
  <div class="space-y-6">
    
    <!-- ======= WELCOME BANNER ======= -->
    <div class="bg-[#002060] rounded-2xl p-6 text-white shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold m-0">Hi, {{ studentName }}! 👋</h2>
        <p class="text-blue-100 text-sm mt-1 m-0">Welcome to the online exam system. Check your schedule and quizzes below:</p>
      </div>
      <router-link to="/student/myexam" class="px-4 py-2.5 bg-white text-blue-600 hover:bg-blue-50 font-semibold text-sm rounded-xl transition-all shadow-sm no-underline shrink-0">
        Take an Exam
      </router-link>
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Active Quizzes -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-hourglass"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Quizzes To Do</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">{{ stats.todoCount }}</h3>
        </div>
      </div>

      <!-- Completed Quizzes -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-check-circle"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Completed</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">{{ stats.completedCount }}</h3>
        </div>
      </div>

      <!-- Average Score -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-chart-bar"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Average Score</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">{{ stats.averageScore }}%</h3>
        </div>
      </div>

      <!-- Enrolled Courses -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
          <i class="pi pi-book"></i>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Enrolled Subjects</p>
          <h3 class="text-2xl font-extrabold text-slate-800 m-0 mt-0.5">{{ stats.enrolledSubjectsCount }}</h3>
        </div>
      </div>
    </div>

    <!-- ======= MY CLASSES ======= -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-book text-purple-500"></i>
          My Classes
        </h3>
        <router-link to="/student/mycourses" class="text-xs font-bold text-blue-600 hover:underline no-underline">
          View All
        </router-link>
      </div>

      <p v-if="!myClasses.length" class="text-sm text-slate-400 m-0">You're not enrolled in any classes yet.</p>
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <router-link
          v-for="course in myClasses.slice(0, 3)"
          :key="`${course.id}-${course.classId}`"
          :to="{ name: 'student.courseWorkspace', params: { classId: course.classId, subjectId: course.id } }"
          class="p-4 rounded-xl border border-slate-100 bg-slate-50 hover:border-[#63C7DF] hover:bg-white transition-all no-underline block"
        >
          <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-[#002060] text-white mb-2">{{ course.code }}</span>
          <h4 class="text-sm font-bold text-slate-800 m-0">{{ course.title }}</h4>
          <p class="text-xs text-slate-400 m-0 mt-1">{{ course.className }}</p>
          <p class="text-[11px] text-slate-500 m-0 mt-2">
            <span class="font-semibold text-emerald-600">{{ course.completedQuizzes }}</span> / {{ course.totalQuizzes }} quizzes done
          </p>
        </router-link>
      </div>
    </div>

    <!-- ======= NEWLY PUBLISHED QUIZZES ======= -->
    <div v-if="stats.recentQuizzes.length" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-sparkles text-emerald-500"></i>
          Newly Published Quizzes
        </h3>
        <router-link to="/student/myexam" class="text-xs font-bold text-blue-600 hover:underline no-underline">
          View All
        </router-link>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="quiz in stats.recentQuizzes" :key="quiz.id" class="p-4 rounded-xl border border-slate-100 bg-slate-50 flex flex-col justify-between gap-3">
          <div>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 mb-2">
              <i class="pi pi-sparkles text-[9px]"></i> New
            </span>
            <h4 class="text-sm font-bold text-slate-800 m-0">{{ quiz.title }}</h4>
            <p class="text-xs text-slate-400 m-0 mt-1">
              <span v-if="quiz.subject" class="font-semibold text-slate-500">{{ quiz.subject }}</span>
              <span v-if="quiz.className"> • {{ quiz.className }}</span>
            </p>
            <p class="text-[11px] text-slate-500 m-0 mt-2 flex items-center gap-3">
              <span><i class="pi pi-clock text-xs"></i> {{ quiz.duration }} min</span>
              <span><i class="pi pi-list text-xs"></i> {{ quiz.totalQuestions }} q's</span>
            </p>
          </div>
          <router-link
            v-if="quiz.isUpcoming"
            :to="{ name: 'student.courseWorkspace', params: { classId: quiz.classId, subjectId: quiz.subjectId } }"
            class="px-3 py-2 bg-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition-all no-underline text-center"
          >
            Opens {{ formatDate(quiz.startAt) }}
          </router-link>
          <button
            v-else-if="quiz.isClosed"
            disabled
            type="button"
            title="This quiz's availability window has closed"
            class="px-3 py-2 bg-slate-100 text-slate-400 font-semibold text-xs rounded-xl cursor-not-allowed border border-slate-200 flex items-center justify-center gap-1.5"
          >
            <i class="pi pi-calendar-times text-xs"></i> Closed
          </button>
          <button
            v-else-if="quiz.attemptsExhausted"
            disabled
            type="button"
            title="You have used all of your attempts for this quiz"
            class="px-3 py-2 bg-slate-100 text-slate-400 font-semibold text-xs rounded-xl cursor-not-allowed border border-slate-200 flex items-center justify-center gap-1.5"
          >
            <i class="pi pi-lock text-xs"></i> No Attempts Left
          </button>
          <button
            v-else
            type="button"
            @click="startQuiz(quiz)"
            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition-all border-0 cursor-pointer text-center"
          >
            Start Exam
          </button>
        </div>
      </div>
    </div>

    <!-- ======= CALENDAR ======= -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
      <StudentCalendar />
    </div>

    <!-- ======= MAIN CONTENT GRID ======= -->
    <!-- <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 m-0 flex items-center gap-2">
              <i class="pi pi-calendar text-amber-500"></i>
              Schedule
            </h3>
            <router-link to="/student/myexam" class="text-xs font-bold text-blue-600 hover:underline no-underline">
              View All
            </router-link>
          </div>

          <p v-if="!stats.dueSoon.length" class="text-sm text-slate-400 m-0">No quizzes to take right now</p>

          <ol v-else class="relative border-l-2 border-slate-100 ml-2 space-y-6">
            <li v-for="quiz in stats.dueSoon" :key="quiz.id" class="ml-5 relative">
              <span class="absolute -left-[27px] top-0.5 w-3.5 h-3.5 rounded-full bg-blue-600 border-2 border-white shadow"></span>

              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div>
                  <p class="text-[11px] font-bold text-blue-600 m-0 flex items-center gap-1.5">
                    <i class="pi pi-clock text-[10px]"></i>
                    {{ quiz.endAt ? `Due ${formatDate(quiz.endAt)}` : 'No fixed deadline' }}
                  </p>
                  <router-link
                    v-if="quiz.classId && quiz.subjectId"
                    :to="{ name: 'student.courseWorkspace', params: { classId: quiz.classId, subjectId: quiz.subjectId } }"
                    class="text-sm font-bold text-slate-800 no-underline hover:text-blue-600"
                  >
                    {{ quiz.title }}
                  </router-link>
                  <h4 v-else class="text-sm font-bold text-slate-800 m-0">{{ quiz.title }}</h4>
                  <p class="text-xs text-slate-400 m-0 mt-1 flex items-center gap-3 flex-wrap">
                    <span v-if="quiz.subject" class="font-semibold text-slate-500">{{ quiz.subject }}</span>
                    <span><i class="pi pi-clock text-xs"></i> {{ quiz.duration }} min</span>
                    <span><i class="pi pi-list text-xs"></i> {{ quiz.totalQuestions }} questions</span>
                  </p>
                </div>
                <router-link :to="`/student/take-quiz?id=${quiz.id}`" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition-all no-underline text-center shrink-0">
                  Start Exam
                </router-link>
              </div>
            </li>
          </ol>
        </div>
      </div>

      <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
          <h3 class="text-base font-bold text-slate-800 m-0 mb-4 flex items-center gap-2">
            <i class="pi pi-bell text-blue-500"></i>
            Feedback From Teachers
          </h3>
          <div class="space-y-3">
            <p v-if="!stats.announcements.length" class="text-xs text-slate-400 m-0">No feedback yet</p>
            <div v-for="note in stats.announcements" :key="note.id" class="p-3 rounded-xl bg-slate-50 border border-slate-100">
              <p class="text-xs font-bold text-slate-700 m-0">{{ note.teacherName || 'Teacher' }}</p>
              <p class="text-[11px] text-slate-500 m-0 mt-1">{{ note.message }}</p>
              <span class="text-[10px] text-slate-400 mt-2 block">{{ note.sentAt }}</span>
            </div>
          </div>
        </div>
      </div>

    </div> -->

    <ConfirmDialog
      v-model="showStartConfirm"
      title="Start this quiz?"
      :message="pendingQuiz ? `Duration: ${pendingQuiz.duration} minutes, ${pendingQuiz.totalQuestions} questions. Once started, the timer begins immediately and cannot be paused.` : ''"
      confirm-text="Start Exam"
      cancel-text="Cancel"
      @confirm="confirmStartQuiz"
      @cancel="showStartConfirm = false"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api, { extractError } from '../../../api'
import StudentCalendar from '../../../components/student/StudentCalendar.vue'
import ConfirmDialog from '../../../components/ConfirmDialog.vue'
import { formatDateTime as formatDate } from '../../../utils/formatDateTime'

const router = useRouter()
const pendingQuiz = ref(null)
const showStartConfirm = ref(false)

function startQuiz(quiz) {
  pendingQuiz.value = quiz
  showStartConfirm.value = true
}

function confirmStartQuiz() {
  showStartConfirm.value = false
  const quiz = pendingQuiz.value
  pendingQuiz.value = null
  if (quiz) {
    router.push(`/student/take-quiz?id=${quiz.id}`)
  }
}

const authUser = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('auth_user')) || {}
  } catch (e) {
    return {}
  }
})

const studentName = computed(() => {
  return authUser.value.first_name || authUser.value.username || 'Student'
})

const stats = ref({
  todoCount: 0,
  completedCount: 0,
  averageScore: 0,
  enrolledSubjectsCount: 0,
  dueSoon: [],
  recentQuizzes: [],
  announcements: [],
})

const myClasses = ref([])

async function fetchDashboard() {
  try {
    const [dashboardRes, coursesRes] = await Promise.all([
      api.get('/student/dashboard'),
      api.get('/student/courses'),
    ])
    stats.value = dashboardRes.data
    myClasses.value = coursesRes.data.data
  } catch (error) {
    console.error(extractError(error))
  }
}

onMounted(fetchDashboard)
</script>