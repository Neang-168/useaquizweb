<template>
  <div class="space-y-6">
    <!-- 1. Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">Grading & Reports</h1>
        <p class="text-xs text-slate-500 mt-1">Grade essay answers and download score reports for any class.</p>
      </div>
    </div>

    <!-- 2. Filter Bar -->
    <div class="bg-white p-3 rounded-2xl border border-slate-200 flex flex-col md:flex-row items-center gap-3 shadow-xs">
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Class</label>
        <Dropdown
          v-model="selectedClass"
          :options="classOptions"
          option-label="label"
          option-value="value"
          placeholder="Select a class"
          size="small"
          class="!bg-slate-50 !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700"
        />
      </div>

      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quiz / Exam</label>
        <Dropdown
          v-model="selectedQuiz"
          :options="quizzesForSelectedClass"
          option-label="title"
          option-value="id"
          placeholder="Select a quiz"
          size="small"
          class="!bg-slate-50 !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700"
        />
      </div>
    </div>

    <!-- 3. Shared Score Table -->
    <ScoreTable :quiz-id="selectedQuiz" :quiz-title="currentQuizTitle" :class-name="currentClassName" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Dropdown from 'primevue/dropdown'
import api from '../../../api'
import ScoreTable from '../../../components/teacher/ScoreTable.vue'

const myClasses = ref([])
const myQuizzes = ref([])

const selectedClass = ref(null)
const selectedQuiz = ref(null)

const classOptions = computed(() => myClasses.value.map(c => ({ label: c.className, value: c.class_id })))

const quizzesForSelectedClass = computed(() => {
  if (!selectedClass.value) return myQuizzes.value
  return myQuizzes.value.filter(q => q.class_id === selectedClass.value)
})

const fetchLookups = async () => {
  const [classesRes, quizzesRes] = await Promise.all([
    api.get('/teacher/classes'),
    api.get('/teacher/quizzes'),
  ])
  myClasses.value = classesRes.data.data
  myQuizzes.value = quizzesRes.data.data
  selectedClass.value = myClasses.value[0]?.class_id ?? null
  selectedQuiz.value = quizzesForSelectedClass.value[0]?.id ?? null
}

onMounted(fetchLookups)

watch(selectedClass, () => {
  selectedQuiz.value = quizzesForSelectedClass.value[0]?.id ?? null
})

const currentQuizTitle = computed(() => myQuizzes.value.find(q => q.id === selectedQuiz.value)?.title ?? '')
const currentClassName = computed(() => myClasses.value.find(c => c.class_id === selectedClass.value)?.className ?? '')
</script>
