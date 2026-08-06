<template>
  <div class="space-y-6">
    <!-- 1. Header & Quick Stats -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">ធនាគារសំណួរ (Question Bank)</h1>
        <p class="text-xs text-slate-500 mt-1">គ្រប់គ្រង និងបង្កើតសំណួរតាមមុខវិជ្ជា សម្រាប់យកទៅប្រើក្នុង Quiz/ប្រឡង</p>
      </div>

      <button
        @click="openQuestionModal()"
        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition-colors flex items-center gap-2 cursor-pointer shadow-sm self-start sm:self-auto"
      >
        <i class="pi pi-plus text-xs"></i>
        <span>បង្កើតសំណួរថ្មី</span>
      </button>
    </div>

    <!-- 2. Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-3 shadow-xs">
      <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
        <!-- Filter Subject -->
        <select
          v-model="selectedSubjectFilter"
          class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-indigo-500"
        >
          <option value="">-- មុខវិជ្ជាទាំងអស់ --</option>
          <option value="CS-301">Web Programming (CS-301)</option>
          <option value="CS-204">Database Systems (CS-204)</option>
        </select>

        <!-- Filter Question Type -->
        <select
          v-model="selectedTypeFilter"
          class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-indigo-500"
        >
          <option value="">-- ប្រភេទសំណួរទាំងអស់ --</option>
          <option value="multiple_choice">ជ្រើសរើសចម្លើយ (Multiple Choice)</option>
          <option value="true_false">ខុស ឬ ត្រូវ (True/False)</option>
          <option value="essay">សំណួរពន្យល់ (Essay)</option>
        </select>
      </div>

      <!-- Search Input -->
      <div class="relative w-full md:w-64">
        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="ស្វែងរកសំណួរ..."
          class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-indigo-500"
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
            <button @click="openQuestionModal(q)" class="p-1.5 text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
              <i class="pi pi-pencil text-xs"></i>
            </button>
            <button @click="deleteQuestion(q.id)" class="p-1.5 text-slate-400 hover:text-red-500 transition-colors cursor-pointer">
              <i class="pi pi-trash text-xs"></i>
            </button>
          </div>
        </div>

        <!-- Question Title -->
        <div class="flex items-start gap-2">
          <span class="text-xs font-bold text-slate-400 font-mono">Q{{ index + 1 }}.</span>
          <h4 class="text-sm font-semibold text-slate-800 m-0 leading-relaxed">{{ q.title }}</h4>
        </div>

        <!-- Multiple Choice / True False Options Display -->
        <div v-if="q.type !== 'essay'" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-6 pt-1">
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

        <!-- Essay Answer Note Display -->
        <div v-else class="pl-6 pt-1">
          <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-500">
            <span class="font-bold text-slate-700">គោលការណ៍ពិន្ទុ/ចម្លើយគំរូ៖</span> {{ q.sampleAnswer || 'គ្មាន' }}
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredQuestions.length === 0" class="text-center py-12 bg-white rounded-2xl border border-slate-200">
        <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
        <p class="text-xs text-slate-500">ពុំមានសំណួរត្រូវបានរកឃើញឡើយ</p>
      </div>
    </div>

    <!-- 4. Modal: Create / Edit Question -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <h3 class="text-base font-bold text-slate-800 m-0">
            {{ isEditing ? 'កែប្រែសំណួរ' : 'បង្កើតសំណួរថ្មី' }}
          </h3>
          <button @click="closeModal" class="w-8 h-8 rounded-lg hover:bg-slate-200/60 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Modal Form Body -->
        <div class="p-5 overflow-y-auto flex-1 space-y-4 text-xs">
          <!-- Subject & Difficulty -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-bold text-slate-700 mb-1">មុខវិជ្ជា *</label>
              <select v-model="form.subjectCode" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500">
                <option value="CS-301">Web Programming (CS-301)</option>
                <option value="CS-204">Database Systems (CS-204)</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">កម្រិតលំបាក *</label>
              <select v-model="form.difficulty" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500">
                <option value="Easy">Easy (ងាយស្រួល)</option>
                <option value="Medium">Medium (មធ្យម)</option>
                <option value="Hard">Hard (លំបាក)</option>
              </select>
            </div>
          </div>

          <!-- Type Select -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">ប្រភេទសំណួរ *</label>
            <select v-model="form.type" @change="handleTypeChange" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500">
              <option value="multiple_choice">ជ្រើសរើសចម្លើយ (Multiple Choice)</option>
              <option value="true_false">ខុស ឬ ត្រូវ (True/False)</option>
              <option value="essay">សំណួរពន្យល់ (Essay)</option>
            </select>
          </div>

          <!-- Question Title -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">ខ្លឹមសារសំណួរ *</label>
            <textarea
              v-model="form.title"
              rows="3"
              placeholder="បញ្ចូលសំណួរនៅទីនេះ..."
              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 focus:outline-none focus:border-indigo-500"
            ></textarea>
          </div>

          <!-- Options Section (Multiple Choice) -->
          <div v-if="form.type === 'multiple_choice'" class="space-y-3 pt-2">
            <label class="block font-bold text-slate-700">ជម្រើសចម្លើយ (សូមគ្រីសយកចម្លើយដែលត្រឹមត្រូវ) *</label>
            <div v-for="(opt, idx) in form.options" :key="idx" class="flex items-center gap-2">
              <input
                type="radio"
                :name="'correct-opt'"
                :checked="opt.isCorrect"
                @change="setCorrectOption(idx)"
                class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
              />
              <input
                v-model="opt.text"
                type="text"
                :placeholder="'ចម្លើយ ' + String.fromCharCode(65 + idx)"
                class="flex-1 bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>

          <!-- Options Section (True/False) -->
          <div v-if="form.type === 'true_false'" class="space-y-2 pt-2">
            <label class="block font-bold text-slate-700">ចម្លើយត្រឹមត្រូវ *</label>
            <div class="flex gap-4">
              <label class="flex items-center gap-2 cursor-pointer bg-slate-50 border border-slate-200 px-4 py-2 rounded-xl">
                <input type="radio" value="True" v-model="form.tfCorrect" class="text-indigo-600" />
                <span class="font-bold text-emerald-600">ត្រូវ (True)</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer bg-slate-50 border border-slate-200 px-4 py-2 rounded-xl">
                <input type="radio" value="False" v-model="form.tfCorrect" class="text-indigo-600" />
                <span class="font-bold text-rose-600">ខុស (False)</span>
              </label>
            </div>
          </div>

          <!-- Essay Sample Answer -->
          <div v-if="form.type === 'essay'" class="pt-2">
            <label class="block font-bold text-slate-700 mb-1">គោលការណ៍ដាក់ពិន្ទុ / ចម្លើយគំរូ</label>
            <textarea
              v-model="form.sampleAnswer"
              rows="3"
              placeholder="បញ្ចូលគន្លឹះចម្លើយសម្រាប់កែពិន្ទុ..."
              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 focus:outline-none focus:border-indigo-500"
            ></textarea>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-2">
          <button @click="closeModal" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-4 rounded-xl text-xs transition-colors cursor-pointer">
            បោះបង់
          </button>
          <button @click="saveQuestion" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl text-xs transition-colors cursor-pointer">
            រក្សាទុក
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchQuery = ref('')
const selectedSubjectFilter = ref('')
const selectedTypeFilter = ref('')

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

// Form State
const form = ref({
  subjectCode: 'CS-301',
  difficulty: 'Medium',
  type: 'multiple_choice',
  title: '',
  options: [
    { text: '', isCorrect: true },
    { text: '', isCorrect: false },
    { text: '', isCorrect: false },
    { text: '', isCorrect: false }
  ],
  tfCorrect: 'True',
  sampleAnswer: ''
})

// Mock Questions List
const questions = ref([
  {
    id: 1,
    subjectCode: 'CS-301',
    type: 'multiple_choice',
    difficulty: 'Easy',
    title: 'តើ Directive មួយណាប្រើសម្រាប់ទាញទិន្នន័យពី Input ក្នុង Vue.js (Two-way Data Binding)?',
    options: [
      { text: 'v-model', isCorrect: true },
      { text: 'v-bind', isCorrect: false },
      { text: 'v-on', isCorrect: false },
      { text: 'v-for', isCorrect: false }
    ]
  },
  {
    id: 2,
    subjectCode: 'CS-204',
    type: 'true_false',
    difficulty: 'Medium',
    title: 'Primary Key នៅក្នុង Table អាចមានតម្លៃ NULL បាន។',
    options: [
      { text: 'True', isCorrect: false },
      { text: 'False', isCorrect: true }
    ]
  },
  {
    id: 3,
    subjectCode: 'CS-301',
    type: 'essay',
    difficulty: 'Hard',
    title: 'ចូរពន្យល់ពីភាពខុសគ្នារវាង Computed Property និង Method នៅក្នុង Vue.js?',
    sampleAnswer: 'Computed ត្រូវបាន Cache ទិន្នន័យទុករហូតដល់ Reactive Dependency ប្រែប្រួល ចំណែក Method ដំណើរការឡើងវិញគ្រប់ពេលដែលមាន Re-render។'
  }
])

// Filtered Questions
const filteredQuestions = computed(() => {
  return questions.value.filter(q => {
    const matchSubject = !selectedSubjectFilter.value || q.subjectCode === selectedSubjectFilter.value
    const matchType = !selectedTypeFilter.value || q.type === selectedTypeFilter.value
    const matchSearch = !searchQuery.value || q.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchSubject && matchType && matchSearch
  })
})

function formatType(type) {
  if (type === 'multiple_choice') return 'Multiple Choice'
  if (type === 'true_false') return 'True / False'
  if (type === 'essay') return 'Essay'
  return type
}

function openQuestionModal(q = null) {
  if (q) {
    isEditing.value = true
    editingId.value = q.id
    form.value = JSON.parse(JSON.stringify(q))
  } else {
    isEditing.value = false
    editingId.value = null
    resetForm()
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  resetForm()
}

function resetForm() {
  form.value = {
    subjectCode: 'CS-301',
    difficulty: 'Medium',
    type: 'multiple_choice',
    title: '',
    options: [
      { text: '', isCorrect: true },
      { text: '', isCorrect: false },
      { text: '', isCorrect: false },
      { text: '', isCorrect: false }
    ],
    tfCorrect: 'True',
    sampleAnswer: ''
  }
}

function setCorrectOption(idx) {
  form.value.options.forEach((opt, i) => {
    opt.isCorrect = i === idx
  })
}

function saveQuestion() {
  if (!form.value.title.trim()) {
    alert('សូមបញ្ចូលខ្លឹមសារសំណួរ!')
    return
  }

  if (isEditing.value) {
    const idx = questions.value.findIndex(q => q.id === editingId.value)
    if (idx !== -1) questions.value[idx] = { ...form.value, id: editingId.value }
  } else {
    questions.value.unshift({
      ...form.value,
      id: Date.now()
    })
  }

  closeModal()
}

function deleteQuestion(id) {
  if (confirm('តើអ្នកប្រាកដថានឹងលុបសំណួរនេះទេ?')) {
    questions.value = questions.value.filter(q => q.id !== id)
  }
}
</script>