<template>
  <div class="space-y-4 max-w-7xl mx-auto font-sans pb-10">
    <!-- 1. Header Page & Controls -->
    <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#D8E7EC] space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#002060] tracking-tight m-0">Grading & Reports</h1>
          <p class="text-xs text-slate-500 m-0 mt-1">Grade essay answers and download score reports for any class.</p>
        </div>

        <!-- Table / Chart view toggle -->
        <div class="flex items-center gap-1 bg-[#F8F8F8] p-1.5 rounded-xl border border-[#D8E7EC] self-start sm:self-auto">
          <Button
            label="Show Table"
            icon="pi pi-table"
            size="small"
            :class="viewMode === 'table' ? '!bg-[#002060] !text-white shadow-xs' : '!bg-transparent !text-slate-500 hover:!text-[#002060]'"
            class="!border-0 !rounded-lg !text-xs !font-bold !px-3 !py-1.5 transition-all"
            @click="viewMode = 'table'"
          />
          <Button
            label="Show Chart"
            icon="pi pi-chart-bar"
            size="small"
            :class="viewMode === 'chart' ? '!bg-[#002060] !text-white shadow-xs' : '!bg-transparent !text-slate-500 hover:!text-[#002060]'"
            class="!border-0 !rounded-lg !text-xs !font-bold !px-3 !py-1.5 transition-all"
            @click="viewMode = 'chart'"
          />
        </div>
      </div>

      <!-- Filter Bar: Class / Subject / Quiz -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Class</label>
          <Dropdown
            v-model="selectedClass"
            :options="classOptions"
            option-label="label"
            option-value="value"
            placeholder="All Classes"
            showClear
            size="small"
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] !rounded-xl text-xs"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Subject</label>
          <Dropdown
            v-model="selectedSubject"
            :options="subjectOptions"
            option-label="label"
            option-value="value"
            placeholder="All Subjects"
            showClear
            size="small"
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] !rounded-xl text-xs"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Quiz / Exam</label>
          <Dropdown
            v-model="selectedQuiz"
            :options="quizOptions"
            option-label="title"
            option-value="id"
            :placeholder="quizOptions.length ? 'Select a quiz' : 'No quizzes match'"
            :disabled="!quizOptions.length"
            filter
            size="small"
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] !rounded-xl text-xs"
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

watch(quizOptions, (options) => {
  if (!options.some(o => o.id === selectedQuiz.value)) {
    selectedQuiz.value = options[0]?.id ?? null
  }
})

const currentQuizTitle = computed(() => myQuizzes.value.find(q => q.id === selectedQuiz.value)?.title ?? '')
</script>