<template>
  <div class="h-full flex flex-col space-y-6 max-w-7xl mx-auto font-sans">
    
    <!-- 1. Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 bg-white rounded-2xl border border-[#D8E7EC] shadow-xs">
      <div>
        <h1 class="text-2xl font-bold text-[#002060] tracking-tight m-0">Question Bank</h1>
        <p class="text-xs text-slate-500 mt-1 m-0">Create, organize, and manage questions by subject for your quizzes and exams.</p>
      </div>

      <div class="flex items-center gap-2 self-start sm:self-auto">
        <Button
          label="Import / Export"
          icon="pi pi-file-import"
          size="small"
          class="!bg-[#002060]/5 hover:!bg-[#002060]/10 !border-[#002060]/10 !text-[#002060] !rounded-xl !text-xs !font-semibold"
          @click="router.push({ name: 'teacher.questionbank.importExport' })"
        />
        <Button
          label="Add Question"
          icon="pi pi-plus"
          size="small"
          class="!bg-[#002060] hover:!bg-[#001540] !border-[#002060] !text-white !rounded-xl !text-xs !font-bold shadow-xs"
          @click="openQuestionModal()"
        />
      </div>
    </div>

    <!-- Success banner after redirect from Import -->
    <div v-if="importedCount" class="flex items-center gap-2 bg-[#63C7DF]/15 border border-[#63C7DF]/30 text-[#002060] text-xs font-bold rounded-2xl px-4 py-3 shadow-xs">
      <i class="pi pi-check-circle text-[#63C7DF]"></i>
      {{ importedCount }} question{{ importedCount === 1 ? '' : 's' }} imported successfully.
    </div>

    <!-- 2. Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-[#D8E7EC] shadow-xs space-y-2.5">
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Class</label>
          <Dropdown
            v-model="selectedClassFilter"
            :options="classOptions"
            option-label="label"
            option-value="value"
            placeholder="All Classes"
            showClear
            size="small"
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] !rounded-xl text-xs"
            @change="onClassFilterChange"
          />
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Subject</label>
          <Dropdown
            v-model="selectedSubjectFilter"
            :options="subjectFilterOptions"
            option-label="label"
            option-value="value"
            placeholder="All Subjects"
            showClear
            size="small"
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] !rounded-xl text-xs"
            @change="fetchQuestions"
          />
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Type</label>
          <Dropdown
            v-model="selectedTypeFilter"
            :options="typeFilterOptions"
            option-label="label"
            option-value="value"
            placeholder="All Types"
            showClear
            size="small"
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] !rounded-xl text-xs"
            @change="fetchQuestions"
          />
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Search</label>
          <div class="relative w-full">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText
              v-model="searchQuery"
              size="small"
              placeholder="Search questions..."
              class="w-full !pl-9 !pr-3 !bg-[#F8F8F8] !border-[#D8E7EC] !rounded-xl !text-xs"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Questions List -->
    <div class="flex-1 min-h-0 overflow-y-auto pr-1 pb-10">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="(q, index) in filteredQuestions"
          :key="q.id"
          class="bg-white border border-[#D8E7EC] rounded-2xl p-5 hover:border-[#63C7DF] transition-all shadow-xs space-y-3 flex flex-col justify-between"
        >
          <div class="space-y-3">
            <!-- Card Header: Subject Tag, Type Badge, Difficulty -->
            <div class="flex items-center justify-between gap-2 border-b border-[#D8E7EC] pb-3">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-[#002060]/10 text-[#002060] border border-[#002060]/20">
                  {{ q.subjectCode }}
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#F8F8F8] text-slate-600 border border-[#D8E7EC]">
                  {{ formatType(q.type) }}
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#E4AC40]/15 text-[#002060] border border-[#E4AC40]/30">
                  {{ q.points }} pt{{ q.points === 1 ? '' : 's' }}
                </span>
              </div>

              <div class="flex items-center gap-1">
                <span
                  :class="{
                    'bg-emerald-50 text-emerald-700 border-emerald-200': q.difficulty === 'Easy',
                    'bg-[#E4AC40]/15 text-[#002060] border-[#E4AC40]/30': q.difficulty === 'Medium',
                    'bg-[#D71818]/10 text-[#D71818] border-[#D71818]/20': q.difficulty === 'Hard'
                  }"
                  class="text-[10px] font-bold px-2 py-0.5 rounded-md border"
                >
                  {{ q.difficulty }}
                </span>

                <!-- Action Buttons -->
                <Button icon="pi pi-pencil" text rounded size="small" severity="secondary" class="!w-7 !h-7 !text-slate-400 hover:!text-[#002060]" @click="openQuestionModal(q)" />
                <Button icon="pi pi-trash" text rounded size="small" severity="secondary" class="!w-7 !h-7 !text-slate-400 hover:!text-[#D71818]" @click="deleteQuestion(q.id)" />
              </div>
            </div>

            <!-- Question Title -->
            <div class="flex items-start gap-2">
              <span class="text-xs font-bold text-slate-400 font-mono">Q{{ index + 1 }}.</span>
              <h4 v-if="q.title" class="text-sm font-semibold text-[#002060] m-0 leading-relaxed">{{ q.title }}</h4>
              <span v-else class="text-sm italic text-slate-400">(image question)</span>
            </div>

            <!-- Question Image -->
            <div v-if="q.imageUrl" class="ml-6 inline-block rounded-xl border border-[#D8E7EC] bg-[#F8F8F8] overflow-hidden question-image-preview">
              <Image :src="q.imageUrl" :alt="q.imageAlt || ''" preview image-class="max-h-40 object-contain block" />
            </div>

            <!-- Multiple Choice / True False Options Display -->
            <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="grid grid-cols-1 gap-2 pl-6 pt-1">
              <div
                v-for="(opt, oIdx) in q.options"
                :key="oIdx"
                :class="opt.isCorrect ? 'bg-[#63C7DF]/15 border-[#63C7DF]/50 text-[#002060] font-bold' : 'bg-[#F8F8F8] border-[#D8E7EC] text-slate-600'"
                class="p-2.5 rounded-xl border text-xs flex items-center gap-2"
              >
                <Image v-if="opt.imageUrl" :src="opt.imageUrl" alt="" preview image-class="w-8 h-8 rounded-lg object-cover border border-[#D8E7EC] shrink-0 cursor-pointer" />
                <span class="flex-1">{{ String.fromCharCode(65 + oIdx) }}. {{ opt.text }}</span>
                <i v-if="opt.isCorrect" class="pi pi-check-circle text-[#002060] text-xs shrink-0"></i>
              </div>
            </div>

            <!-- Matching Pairs Display -->
            <div v-else-if="q.type === 'matching'" class="grid grid-cols-1 gap-2 pl-6 pt-1">
              <div
                v-for="(pair, pIdx) in q.matchingPairs"
                :key="pIdx"
                class="p-2.5 rounded-xl border border-[#D8E7EC] bg-[#F8F8F8] text-xs flex items-center gap-2 text-slate-700"
              >
                <Image v-if="pair.leftImageUrl" :src="pair.leftImageUrl" alt="" preview image-class="w-8 h-8 rounded-lg object-cover border border-[#D8E7EC] shrink-0 cursor-pointer" />
                <span class="font-bold text-[#002060]">{{ pair.leftText }}</span>
                <i class="pi pi-arrow-right-arrow-left text-[#63C7DF] text-[10px] shrink-0"></i>
                <Image v-if="pair.rightImageUrl" :src="pair.rightImageUrl" alt="" preview image-class="w-8 h-8 rounded-lg object-cover border border-[#D8E7EC] shrink-0 cursor-pointer" />
                <span class="font-medium">{{ pair.rightText }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredQuestions.length === 0" class="md:col-span-2 lg:col-span-3 text-center py-16 bg-white rounded-2xl border border-[#D8E7EC]">
          <i class="pi pi-inbox text-4xl text-slate-300 mb-3 block"></i>
          <p class="text-xs font-semibold text-slate-500 m-0">No questions found.</p>
          <p class="text-[11px] text-slate-400 mt-1 m-0">Try adjusting your filters, or add a new question to your bank.</p>
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
