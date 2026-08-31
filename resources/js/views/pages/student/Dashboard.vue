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

    <!-- ======= MY CLASSES (NEW DESIGN EXACTLY LIKE REFERENCE IMAGE) ======= -->
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
      
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="course in myClasses.slice(0, 3)"
          :key="`${course.id}-${course.classId}`"
          class="bg-white border border-slate-200/90 hover:border-[#63c7df] rounded-[24px] p-5 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between"
        >
          <div>
            <!-- Header Badge & Active Tag -->
            <div class="flex items-center justify-between mb-3">
              <span class="inline-block px-3 py-1 rounded-xl text-xs font-extrabold bg-[#002060] text-white tracking-wide">
                {{ course.code }}
              </span>
              <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-100">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Active
              </span>
            </div>

            <!-- Subject Title & Class Name -->
            <h4 class="text-base font-bold text-[#002060] m-0 leading-snug">{{ course.title }}</h4>
            <p class="text-xs text-slate-400 font-medium m-0 mt-1">{{ course.className }}</p>

            <!-- Metadata List Section with Top/Bottom Borders -->
            <div class="space-y-2 py-3.5 my-3.5 border-y border-slate-100 text-xs">
              <div class="flex items-center justify-between">
                <span class="text-slate-400 flex items-center gap-2">
                  <i class="pi pi-book text-[#e4ac14] text-sm"></i> Subject Code
                </span>
                <span class="font-semibold text-[#002060]">{{ course.code }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-slate-400 flex items-center gap-2">
                  <i class="pi pi-users text-[#e4ac14] text-sm"></i> Class Group
                </span>
                <span class="font-semibold text-[#002060]">{{ course.className }}</span>
              </div>
            </div>

            <!-- Quizzes Progress Section -->
            <div class="mt-2">
              <div class="flex justify-between items-center text-xs font-semibold mb-2">
                <span class="text-slate-400">Quizzes Completed</span>
                <span class="text-[#002060] font-bold font-mono">
                  <span class="text-emerald-600">{{ course.completedQuizzes }}</span> / {{ course.totalQuizzes }}
                </span>
              </div>
              <!-- Progress Bar -->
              <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div 
                  class="h-full bg-blue-600 rounded-full transition-all duration-500"
                  :style="{ width: course.totalQuizzes ? Math.min((course.completedQuizzes / course.totalQuizzes) * 100, 100) + '%' : '0%' }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Bottom Action Button (Open Workspace) -->
          <div class="pt-4 mt-4 border-t border-slate-100">
            <router-link
              :to="{ name: 'student.courseWorkspace', params: { classId: course.classId, subjectId: course.id } }"
              class="w-full py-2 px-3 rounded-xl bg-[#002060] hover:bg-blue-900 text-white border border-cyan-100 flex items-center justify-center gap-2 font-semibold text-xs transition-colors no-underline"
            >
              <i class="fa-solid fa-folder-open text-[#e4ac14]"></i>
              <span>Open Workspace</span>
            </router-link>
          </div>
        </div>
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

    <!-- ======= CONFIRM DIALOG ======= -->
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
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import api, { toastFromError } from '../../../api'
import StudentCalendar from '../../../components/student/StudentCalendar.vue'
import { formatDateTime as formatDate } from '../../../utils/formatDateTime'

const router = useRouter()
const confirm = useConfirm()
const toast = useToast()

function startQuiz(quiz) {
  confirm.require({
    header: 'Start this quiz?',
    message: `Duration: ${quiz.duration} minutes, ${quiz.totalQuestions} questions. Once started, the timer begins immediately and cannot be paused.`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Start Exam',
    rejectLabel: 'Cancel',
    accept: () => router.push(`/student/take-quiz?id=${quiz.id}`),
  })
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
    toast.add({ summary: 'Failed to load dashboard', ...toastFromError(error) })
  }
}

onMounted(fetchDashboard)
</script>