<template>
  <div class="h-full flex flex-col space-y-6">
    <!-- 1. Header & Quick Stats -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">Question Bank</h1>
        <p class="text-xs text-slate-500 mt-1">Create and organize questions by subject, ready to use in any quiz or exam.</p>
      </div>

      <div class="flex items-center gap-2 self-start sm:self-auto">
        <Button
          label="Import / Export"
          icon="pi pi-file-import"
          size="small"
          class="!bg-slate-100 hover:!bg-slate-200 !border-slate-100 !text-slate-700 !rounded-lg !text-xs"
          @click="router.push({ name: 'teacher.questionbank.importExport' })"
        />
        <Button
          label="Add Question"
          icon="pi pi-plus"
          size="small"
          class="!bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg !text-xs shadow-sm"
          @click="openQuestionModal()"
        />
      </div>
    </div>

    <!-- Success banner after a redirect back from Import -->
    <div v-if="importedCount" class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-xl px-4 py-3">
      <i class="pi pi-check-circle"></i>
      {{ importedCount }} question{{ importedCount === 1 ? '' : 's' }} imported successfully.
    </div>

    <!-- 2. Filters Bar -->
    <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs space-y-2.5">
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Class</label>
          <Dropdown
            v-model="selectedClassFilter"
            :options="classOptions"
            option-label="label"
            option-value="value"
            placeholder="All Classes"
            showClear
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg text-xs"
            @change="onClassFilterChange"
          />
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Subject</label>
          <Dropdown
            v-model="selectedSubjectFilter"
            :options="subjectFilterOptions"
            option-label="label"
            option-value="value"
            placeholder="All Subjects"
            showClear
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg text-xs"
            @change="fetchQuestions"
          />
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Type</label>
          <Dropdown
            v-model="selectedTypeFilter"
            :options="typeFilterOptions"
            option-label="label"
            option-value="value"
            placeholder="All Types"
            showClear
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg text-xs"
            @change="fetchQuestions"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Search</label>
          <div class="relative w-full">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText
              v-model="searchQuery"
              size="small"
              placeholder="Search questions..."
              class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Questions List -->
    <div class="flex-1 min-h-0 overflow-y-auto pr-1">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="(q, index) in filteredQuestions"
        :key="q.id"
        class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-indigo-200 transition-all shadow-xs space-y-3 flex flex-col"
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
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-violet-50 text-violet-600 border border-violet-100">
              {{ q.points }} pt{{ q.points === 1 ? '' : 's' }}
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
          <h4 v-if="q.title" class="text-sm font-semibold text-slate-800 m-0 leading-relaxed">{{ q.title }}</h4>
          <span v-else class="text-sm italic text-slate-400">(image question)</span>
        </div>

        <!-- Question Image -->
        <div v-if="q.imageUrl" class="ml-6 inline-block rounded-xl border border-slate-200 bg-slate-50 overflow-hidden question-image-preview">
          <Image :src="q.imageUrl" :alt="q.imageAlt || ''" preview image-class="max-h-40 object-contain block" />
        </div>

        <!-- Multiple Choice / True False Options Display -->
        <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="grid grid-cols-1 gap-2 pl-6 pt-1">
          <div
            v-for="(opt, oIdx) in q.options"
            :key="oIdx"
            :class="opt.isCorrect ? 'bg-emerald-50/80 border-emerald-300 text-emerald-800 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-600'"
            class="p-2.5 rounded-xl border text-xs flex items-center gap-2"
          >
            <Image v-if="opt.imageUrl" :src="opt.imageUrl" alt="" preview image-class="w-8 h-8 rounded object-cover border border-slate-200 shrink-0 cursor-pointer" />
            <span class="flex-1">{{ String.fromCharCode(65 + oIdx) }}. {{ opt.text }}</span>
            <i v-if="opt.isCorrect" class="pi pi-check-circle text-emerald-600 text-xs shrink-0"></i>
          </div>
        </div>

        <!-- Matching Pairs Display -->
        <div v-else-if="q.type === 'matching'" class="grid grid-cols-1 gap-2 pl-6 pt-1">
          <div
            v-for="(pair, pIdx) in q.matchingPairs"
            :key="pIdx"
            class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs flex items-center gap-2 text-slate-600"
          >
            <Image v-if="pair.leftImageUrl" :src="pair.leftImageUrl" alt="" preview image-class="w-8 h-8 rounded object-cover border border-slate-200 shrink-0 cursor-pointer" />
            <span class="font-semibold text-slate-700">{{ pair.leftText }}</span>
            <i class="pi pi-arrow-right-arrow-left text-slate-300 text-[10px] shrink-0"></i>
            <Image v-if="pair.rightImageUrl" :src="pair.rightImageUrl" alt="" preview image-class="w-8 h-8 rounded object-cover border border-slate-200 shrink-0 cursor-pointer" />
            <span>{{ pair.rightText }}</span>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredQuestions.length === 0" class="md:col-span-2 lg:col-span-3 text-center py-12 bg-white rounded-2xl border border-slate-200">
        <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
        <p class="text-xs text-slate-500">No questions found. Try adjusting your filters, or add a new question.</p>
      </div>
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
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import Image from 'primevue/image'
import api, { extractError } from '../../../api'
import QuestionEditorModal from '../../../components/teacher/QuestionEditorModal.vue'

const route = useRoute()
const router = useRouter()

const searchQuery = ref('')
const selectedClassFilter = ref('')
const selectedSubjectFilter = ref('')
const selectedTypeFilter = ref('')
const importedCount = ref(route.query.imported ? Number(route.query.imported) : 0)

const typeFilterOptions = [
  { label: 'All Types', value: '' },
  { label: 'Multiple Choice', value: 'multiple_choice' },
  { label: 'True / False', value: 'true_false' },
  { label: 'Matching', value: 'matching' },
]

const showModal = ref(false)
const editingQuestion = ref(null)

const mySubjects = ref([])
const myClasses = ref([])
const questions = ref([])
const loading = ref(false)

const fetchSubjects = async () => {
  const { data } = await api.get('/teacher/subjects')
  mySubjects.value = data.data
}

const fetchClasses = async () => {
  const { data } = await api.get('/teacher/classes')
  myClasses.value = data.data
}

const classOptions = computed(() => {
  const seen = new Map()
  myClasses.value.forEach(c => {
    if (c.class_id && !seen.has(c.class_id)) seen.set(c.class_id, c.className)
  })
  return Array.from(seen, ([value, label]) => ({ label, value }))
})

// Subject options narrow to whichever subjects are taught in the selected class
const subjectFilterOptions = computed(() => {
  let subjects = mySubjects.value
  if (selectedClassFilter.value) {
    const allowedIds = new Set(
      myClasses.value.filter(c => c.class_id === selectedClassFilter.value).map(c => c.subject_id)
    )
    subjects = subjects.filter(s => allowedIds.has(s.id))
  }
  return [
    { label: 'All Subjects', value: '' },
    ...subjects.map(s => ({ label: `${s.name} (${s.code})`, value: s.id })),
  ]
})

function onClassFilterChange() {
  if (!subjectFilterOptions.value.some(o => o.value === selectedSubjectFilter.value)) {
    selectedSubjectFilter.value = ''
  }
  fetchQuestions()
}

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
  await Promise.all([fetchSubjects(), fetchClasses()])
  if (route.query.subject_id) {
    selectedSubjectFilter.value = Number(route.query.subject_id)
  }
  await fetchQuestions()

  if (route.query.imported) {
    router.replace({ query: { ...route.query, imported: undefined } })
  }
})

// Filtered Questions (search is client-side; subject/type filters are re-fetched from the server)
const filteredQuestions = computed(() => {
  if (!searchQuery.value) return questions.value
  const q = searchQuery.value.toLowerCase()
  return questions.value.filter(item => (item.title || '').toLowerCase().includes(q))
})

function formatType(type) {
  if (type === 'multiple_choice') return 'Multiple Choice'
  if (type === 'true_false') return 'True / False'
  if (type === 'matching') return 'Matching'
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
