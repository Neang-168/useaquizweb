<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h3 class="text-base font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-calendar text-amber-500"></i>
          Calendar
        </h3>
        <p class="text-xs text-slate-500 mt-1 m-0">All your quizzes and exams across every class, in one place.</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <Dropdown
          v-model="filterClassId"
          :options="classFilterOptions"
          option-label="label"
          option-value="value"
          placeholder="All Classes"
          size="small"
          class="!bg-white !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700"
        />
        <Dropdown
          v-model="filterSubjectId"
          :options="subjectFilterOptions"
          option-label="label"
          option-value="value"
          placeholder="All Subjects"
          size="small"
          class="!bg-white !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700"
        />
      </div>
    </div>

    <!-- Toolbar -->
    <div class="bg-white p-2.5 rounded-2xl border border-slate-200 flex flex-wrap items-center justify-between gap-3 shadow-xs">
      <div class="flex items-center gap-2">
        <Button label="Today" size="small" class="!bg-slate-100 hover:!bg-slate-200 !border-slate-100 !text-slate-700 !rounded-lg !text-xs !font-bold" @click="goToday" />
        <Button icon="pi pi-chevron-left" size="small" class="!w-7 !h-7 !bg-slate-100 hover:!bg-slate-200 !border-slate-100 !text-slate-500 !rounded-lg" @click="goPrev" />
        <Button icon="pi pi-chevron-right" size="small" class="!w-7 !h-7 !bg-slate-100 hover:!bg-slate-200 !border-slate-100 !text-slate-500 !rounded-lg" @click="goNext" />
        <h2 class="text-sm font-bold text-slate-800 ml-2">{{ periodLabel }}</h2>
      </div>

      <div class="bg-slate-100 p-1 rounded-xl">
        <SelectButton
          v-model="viewMode"
          :options="viewModeOptions"
          option-label="label"
          option-value="value"
          :allow-empty="false"
          class="calendar-view-toggle"
        />
      </div>
    </div>

    <CalendarGrid
      :view-mode="viewMode"
      :reference-date="referenceDate"
      :quizzes="filteredQuizzes"
      @quiz-click="selectedQuiz = $event"
    />

    <QuizDetailModal
      :quiz="selectedQuiz"
      :detail-route="detailRouteFor"
      detail-label="View Course"
      @close="selectedQuiz = null"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import SelectButton from 'primevue/selectbutton'
import api, { extractError } from '../../api'
import CalendarGrid from '../teacher/calendar/CalendarGrid.vue'
import QuizDetailModal from '../teacher/calendar/QuizDetailModal.vue'

const referenceDate = ref(new Date())
const viewMode = ref('month')
const quizzes = ref([])
const myCourses = ref([])
const filterClassId = ref('')
const filterSubjectId = ref('')
const selectedQuiz = ref(null)
const loading = ref(false)

const viewModeOptions = [
  { label: 'Month', value: 'month' },
  { label: 'Week', value: 'week' },
]

const classFilterOptions = computed(() => {
  const seen = new Map()
  myCourses.value.forEach(c => {
    if (c.classId && !seen.has(c.classId)) seen.set(c.classId, c.className)
  })
  return [{ label: 'All Classes', value: '' }, ...Array.from(seen, ([value, label]) => ({ label, value }))]
})

const subjectFilterOptions = computed(() => {
  const seen = new Map()
  myCourses.value.forEach(c => {
    if (c.id && !seen.has(c.id)) seen.set(c.id, c.title)
  })
  return [{ label: 'All Subjects', value: '' }, ...Array.from(seen, ([value, label]) => ({ label, value }))]
})

const filteredQuizzes = computed(() => {
  return quizzes.value.filter(q => {
    const matchClass = !filterClassId.value || q.class?.id === filterClassId.value
    const matchSubject = !filterSubjectId.value || q.subject?.id === filterSubjectId.value
    return matchClass && matchSubject
  })
})

const periodLabel = computed(() => {
  if (viewMode.value === 'month') {
    return referenceDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
  }

  const start = new Date(referenceDate.value)
  start.setDate(start.getDate() - start.getDay())
  const end = new Date(start)
  end.setDate(start.getDate() + 6)
  const opts = { month: 'short', day: 'numeric' }
  return `${start.toLocaleDateString('en-US', opts)} – ${end.toLocaleDateString('en-US', opts)}, ${end.getFullYear()}`
})

function detailRouteFor(quiz) {
  if (!quiz.class?.id || !quiz.subject?.id) return null
  return { name: 'student.courseWorkspace', params: { classId: quiz.class.id, subjectId: quiz.subject.id } }
}

const fetchCourses = async () => {
  const { data } = await api.get('/student/courses')
  myCourses.value = data.data
}

const fetchCalendar = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/student/calendar', {
      params: {
        month: referenceDate.value.getMonth() + 1,
        year: referenceDate.value.getFullYear(),
      },
    })
    quizzes.value = data.data
  } catch (error) {
    console.error(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await Promise.all([fetchCourses(), fetchCalendar()])
})

watch(() => `${referenceDate.value.getFullYear()}-${referenceDate.value.getMonth()}`, fetchCalendar)

function goToday() {
  referenceDate.value = new Date()
}

function goPrev() {
  const d = new Date(referenceDate.value)
  if (viewMode.value === 'month') {
    d.setDate(1)
    d.setMonth(d.getMonth() - 1)
  } else {
    d.setDate(d.getDate() - 7)
  }
  referenceDate.value = d
}

function goNext() {
  const d = new Date(referenceDate.value)
  if (viewMode.value === 'month') {
    d.setDate(1)
    d.setMonth(d.getMonth() + 1)
  } else {
    d.setDate(d.getDate() + 7)
  }
  referenceDate.value = d
}
</script>

<style scoped>
.calendar-view-toggle :deep(.p-togglebutton) {
  border: 0;
  background: transparent;
  color: #64748b;
  font-weight: 500;
  font-size: 0.75rem;
  padding: 0.3rem 0.7rem;
  border-radius: 0.5rem;
}

.calendar-view-toggle :deep(.p-togglebutton:hover) {
  color: #1e293b;
}

.calendar-view-toggle :deep(.p-togglebutton-checked) {
  background: #ffffff;
  color: #4f46e5;
  font-weight: 700;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
</style>
