<template>
  <div class="space-y-3">
    <!-- 1. Header Page -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 class="text-base font-bold text-slate-800 m-0">Grading & Reports</h1>
          <p class="text-[11px] text-slate-500 m-0 mt-0.5">Grade essay answers and download score reports for any class.</p>
        </div>

        <!-- Table / Chart view toggle -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg self-start sm:self-auto">
          <Button
            label="Show Table"
            icon="pi pi-table"
            size="small"
            :class="viewMode === 'table' ? '!bg-white !text-indigo-600 shadow-xs' : '!bg-transparent !text-slate-500 hover:!text-slate-700'"
            class="!border-0 !rounded-md !text-[11px] !font-semibold !px-2.5 !py-1"
            @click="viewMode = 'table'"
          />
          <Button
            label="Show Chart"
            icon="pi pi-chart-bar"
            size="small"
            :class="viewMode === 'chart' ? '!bg-white !text-indigo-600 shadow-xs' : '!bg-transparent !text-slate-500 hover:!text-slate-700'"
            class="!border-0 !rounded-md !text-[11px] !font-semibold !px-2.5 !py-1"
            @click="viewMode = 'chart'"
          />
        </div>
      </div>

      <!-- Filter Bar: Class / Subject / Quiz, each narrowing the ones after it -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Class</label>
          <Dropdown
            v-model="selectedClass"
            :options="classOptions"
            option-label="label"
            option-value="value"
            placeholder="All Classes"
            showClear
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg text-xs"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Subject</label>
          <Dropdown
            v-model="selectedSubject"
            :options="subjectOptions"
            option-label="label"
            option-value="value"
            placeholder="All Subjects"
            showClear
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg text-xs"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quiz / Exam</label>
          <Dropdown
            v-model="selectedQuiz"
            :options="quizOptions"
            option-label="title"
            option-value="id"
            :placeholder="quizOptions.length ? 'Select a quiz' : 'No quizzes match'"
            :disabled="!quizOptions.length"
            filter
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg text-xs"
          />
        </div>
      </div>
    </div>

    <!-- 2. Shared Score Table -->
    <ScoreTable :quiz-id="selectedQuiz" :quiz-title="currentQuizTitle" :view-mode="viewMode" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'
import api from '../../../api'
import ScoreTable from '../../../components/teacher/ScoreTable.vue'

const myClasses = ref([])
const myQuizzes = ref([])

const selectedClass = ref(null)
const selectedSubject = ref(null)
const selectedQuiz = ref(null)
const viewMode = ref('table')

const classOptions = computed(() => myClasses.value.map(c => ({ label: c.className, value: c.class_id })))

// Subject options are built from whichever subjects already appear among
// this teacher's own quizzes — no separate lookup endpoint needed.
const subjectOptions = computed(() => {
  const seen = new Map()
  myQuizzes.value.forEach(q => {
    if (q.subject_id && !seen.has(q.subject_id)) seen.set(q.subject_id, q.subjectName)
  })
  return Array.from(seen, ([value, label]) => ({ label, value }))
})

const quizOptions = computed(() => myQuizzes.value.filter(q => {
  if (selectedClass.value && q.class_id !== selectedClass.value) return false
  if (selectedSubject.value && q.subject_id !== selectedSubject.value) return false
  return true
}))

const fetchLookups = async () => {
  const [classesRes, quizzesRes] = await Promise.all([
    api.get('/teacher/classes'),
    api.get('/teacher/quizzes'),
  ])
  myClasses.value = classesRes.data.data
  myQuizzes.value = quizzesRes.data.data
  selectedQuiz.value = quizOptions.value[0]?.id ?? null
}

onMounted(fetchLookups)

// Keep the selected quiz valid whenever the Class/Subject filters narrow
// (or widen) the list, falling back to the first quiz that still matches.
watch(quizOptions, (options) => {
  if (!options.some(o => o.id === selectedQuiz.value)) {
    selectedQuiz.value = options[0]?.id ?? null
  }
})

const currentQuizTitle = computed(() => myQuizzes.value.find(q => q.id === selectedQuiz.value)?.title ?? '')
</script>
