<template>
  <div class="space-y-6">
    <!-- 1. Header & Quick Stats -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">Question Bank</h1>
        <p class="text-xs text-slate-500 mt-1">Create and organize questions by subject, ready to use in any quiz or exam.</p>
      </div>

      <Button
        label="Add Question"
        icon="pi pi-plus"
        size="small"
        class="!bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg !text-xs shadow-sm self-start sm:self-auto"
        @click="openQuestionModal()"
      />
    </div>

    <!-- 2. Filters Bar -->
    <div class="bg-white p-3 rounded-2xl border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-3 shadow-xs">
      <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
        <!-- Filter Subject -->
        <Dropdown
          v-model="selectedSubjectFilter"
          :options="subjectFilterOptions"
          option-label="label"
          option-value="value"
          placeholder="All Subjects"
          size="small"
          class="!bg-slate-50 !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700"
          @change="fetchQuestions"
        />

        <!-- Filter Question Type -->
        <Dropdown
          v-model="selectedTypeFilter"
          :options="typeFilterOptions"
          option-label="label"
          option-value="value"
          placeholder="All Types"
          size="small"
          class="!bg-slate-50 !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700"
          @change="fetchQuestions"
        />
      </div>

      <!-- Search Input -->
      <div class="relative w-full md:w-56">
        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
        <InputText
          v-model="searchQuery"
          size="small"
          placeholder="Search questions..."
          class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
        />
      </div>
    </div>

    <!-- 3. Questions List -->
    <div class="space-y-4">
      <div
        v-for="(q, index) in filteredQuestions"
        :key="q.id"
        class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-indigo-200 transition-all shadow-xs space-y-3"
      >
        <!-- Card Header: Subject Tag, Type Badge, Difficulty -->
        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2">
            <span class="text-[10px] font-mono font-bold px-2.5 py-0.5 rounded bg-indigo-50 text-indigo-600 border border-indigo-100">
              {{ q.subjectCode }}
            </span>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600">
              {{ formatType(q.type) }}
            </span>
          </div>

          <div class="flex items-center gap-2">
            <span
              :class="{
                'bg-emerald-50 text-emerald-600 border-emerald-200': q.difficulty === 'Easy',
                'bg-amber-50 text-amber-600 border-amber-200': q.difficulty === 'Medium',
                'bg-rose-50 text-rose-600 border-rose-200': q.difficulty === 'Hard'
              }"
              class="text-[10px] font-bold px-2 py-0.5 rounded border"
            >
              {{ q.difficulty }}
            </span>

            <!-- Action Buttons -->
            <Button icon="pi pi-pencil" text rounded size="small" severity="secondary" class="!w-7 !h-7 !text-slate-400 hover:!text-indigo-600" @click="openQuestionModal(q)" />
            <Button icon="pi pi-trash" text rounded size="small" severity="secondary" class="!w-7 !h-7 !text-slate-400 hover:!text-red-500" @click="deleteQuestion(q.id)" />
          </div>
        </div>

        <!-- Question Title -->
        <div class="flex items-start gap-2">
          <span class="text-xs font-bold text-slate-400 font-mono">Q{{ index + 1 }}.</span>
          <h4 class="text-sm font-semibold text-slate-800 m-0 leading-relaxed">{{ q.title }}</h4>
        </div>

        <!-- Multiple Choice / True False Options Display -->
        <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-6 pt-1">
          <div
            v-for="(opt, oIdx) in q.options"
            :key="oIdx"
            :class="opt.isCorrect ? 'bg-emerald-50/80 border-emerald-300 text-emerald-800 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-600'"
            class="p-2.5 rounded-xl border text-xs flex items-center justify-between"
          >
            <span>{{ String.fromCharCode(65 + oIdx) }}. {{ opt.text }}</span>
            <i v-if="opt.isCorrect" class="pi pi-check-circle text-emerald-600 text-xs"></i>
          </div>
        </div>

        <!-- Matching Pairs Display -->
        <div v-else-if="q.type === 'matching'" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-6 pt-1">
          <div
            v-for="(pair, pIdx) in q.matchingPairs"
            :key="pIdx"
            class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs flex items-center gap-2 text-slate-600"
          >
            <span class="font-semibold text-slate-700">{{ pair.leftText }}</span>
            <i class="pi pi-arrow-right-arrow-left text-slate-300 text-[10px]"></i>
            <span>{{ pair.rightText }}</span>
          </div>
        </div>

        <!-- Essay Answer Note Display -->
        <div v-else class="pl-6 pt-1">
          <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-500">
            <span class="font-bold text-slate-700">Grading notes / model answer:</span> {{ q.sampleAnswer || 'None provided' }}
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredQuestions.length === 0" class="text-center py-12 bg-white rounded-2xl border border-slate-200">
        <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
        <p class="text-xs text-slate-500">No questions found. Try adjusting your filters, or add a new question.</p>
      </div>
    </div>

    <QuestionEditorModal
      v-model:visible="showModal"
      :subjects="mySubjects"
      :editing-question="editingQuestion"
      @saved="fetchQuestions"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import api, { extractError } from '../../../api'
import QuestionEditorModal from '../../../components/teacher/QuestionEditorModal.vue'

const route = useRoute()

const searchQuery = ref('')
const selectedSubjectFilter = ref('')
const selectedTypeFilter = ref('')

const typeFilterOptions = [
  { label: 'All Types', value: '' },
  { label: 'Multiple Choice', value: 'multiple_choice' },
  { label: 'True / False', value: 'true_false' },
  { label: 'Matching', value: 'matching' },
  { label: 'Essay', value: 'essay' },
]

const showModal = ref(false)
const editingQuestion = ref(null)

const mySubjects = ref([])
const questions = ref([])
const loading = ref(false)

const fetchSubjects = async () => {
  const { data } = await api.get('/teacher/subjects')
  mySubjects.value = data.data
}

const subjectFilterOptions = computed(() => [
  { label: 'All Subjects', value: '' },
  ...mySubjects.value.map(s => ({ label: `${s.name} (${s.code})`, value: s.id })),
])

const fetchQuestions = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/questions', {
      params: {
        subject_id: selectedSubjectFilter.value || undefined,
        type: selectedTypeFilter.value || undefined,
      },
    })
    questions.value = data.data
  } catch (error) {
    alert(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchSubjects()
  if (route.query.subject_id) {
    selectedSubjectFilter.value = Number(route.query.subject_id)
  }
  await fetchQuestions()
})

// Filtered Questions (search is client-side; subject/type filters are re-fetched from the server)
const filteredQuestions = computed(() => {
  if (!searchQuery.value) return questions.value
  const q = searchQuery.value.toLowerCase()
  return questions.value.filter(item => item.title.toLowerCase().includes(q))
})

function formatType(type) {
  if (type === 'multiple_choice') return 'Multiple Choice'
  if (type === 'true_false') return 'True / False'
  if (type === 'matching') return 'Matching'
  if (type === 'essay') return 'Essay'
  return type
}

function openQuestionModal(q = null) {
  editingQuestion.value = q
  showModal.value = true
}

async function deleteQuestion(id) {
  if (confirm('Are you sure you want to delete this question?')) {
    try {
      await api.delete(`/teacher/questions/${id}`)
      await fetchQuestions()
    } catch (error) {
      alert(extractError(error))
    }
  }
}
</script>
