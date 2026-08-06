<template>
  <div class="space-y-6">
    <!-- 1. Header & Create Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">គ្រប់គ្រង Quiz & ការប្រឡង</h1>
        <p class="text-xs text-slate-500 mt-1">បង្កើត បើក/បិទ និងកំណត់លក្ខខណ្ឌការប្រឡងសម្រាប់សិស្ស</p>
      </div>

      <button
        @click="openQuizModal()"
        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition-colors flex items-center gap-2 cursor-pointer shadow-sm self-start sm:self-auto"
      >
        <i class="pi pi-plus text-xs"></i>
        <span>បង្កើត Quiz ថ្មី</span>
      </button>
    </div>

    <!-- 2. Status Filter Tabs & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-3 shadow-xs">
      <!-- Tabs Filter -->
      <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl w-full md:w-auto overflow-x-auto">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="activeTab = tab.value"
          :class="activeTab === tab.value ? 'bg-white text-indigo-600 font-bold shadow-xs' : 'text-slate-500 font-medium hover:text-slate-800'"
          class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer whitespace-nowrap"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Search & Subject Filter -->
      <div class="flex items-center gap-3 w-full md:w-auto">
        <select
          v-model="selectedSubjectFilter"
          class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-indigo-500"
        >
          <option value="">-- មុខវិជ្ជាទាំងអស់ --</option>
          <option value="CS-301">Web Programming (CS-301)</option>
          <option value="CS-204">Database Systems (CS-204)</option>
        </select>

        <div class="relative w-full md:w-56">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="ស្វែងរក Quiz..."
            class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-indigo-500"
          />
        </div>
      </div>
    </div>

    <!-- 3. Quiz Cards List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="quiz in filteredQuizzes"
        :key="quiz.id"
        class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-indigo-200 hover:shadow-md transition-all flex flex-col justify-between"
      >
        <div>
          <!-- Header Card: Status & Subject -->
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[10px] font-mono font-bold px-2.5 py-0.5 rounded bg-indigo-50 text-indigo-600 border border-indigo-100">
              {{ quiz.subjectCode }}
            </span>
            <span
              :class="{
                'bg-emerald-50 text-emerald-600 border-emerald-200': quiz.status === 'Active',
                'bg-amber-50 text-amber-600 border-amber-200': quiz.status === 'Upcoming',
                'bg-slate-100 text-slate-500 border-slate-200': quiz.status === 'Completed'
              }"
              class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border"
            >
              {{ quiz.status }}
            </span>
          </div>

          <!-- Quiz Title & Class -->
          <h3 class="text-base font-bold text-slate-800 m-0">{{ quiz.title }}</h3>
          <p class="text-xs text-slate-400 mt-1">ថ្នាក់៖ <span class="font-semibold text-slate-600">{{ quiz.className }}</span></p>

          <!-- Details Info -->
          <div class="mt-4 pt-3 border-t border-slate-100 space-y-2 text-xs">
            <div class="flex items-center justify-between text-slate-500">
              <span class="flex items-center gap-1.5"><i class="pi pi-clock text-[11px]"></i> រយៈពេល៖</span>
              <span class="font-bold text-slate-700">{{ quiz.duration }} នាទី</span>
            </div>
            <div class="flex items-center justify-between text-slate-500">
              <span class="flex items-center gap-1.5"><i class="pi pi-list text-[11px]"></i> ចំនួនសំណួរ៖</span>
              <span class="font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ quiz.totalQuestions }} សំណួរ</span>
            </div>
            <div class="flex items-center justify-between text-slate-500">
              <span class="flex items-center gap-1.5"><i class="pi pi-calendar text-[11px]"></i> កាលបរិច្ឆេទ៖</span>
              <span class="font-medium text-slate-700">{{ quiz.startDate }}</span>
            </div>
            <div class="flex items-center justify-between text-slate-500">
              <span class="flex items-center gap-1.5"><i class="pi pi-users text-[11px]"></i> ប្រឡងរួច៖</span>
              <span class="font-bold text-emerald-600">{{ quiz.submittedCount }}/{{ quiz.totalStudents }} នាក់</span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
          <!-- Toggle Status Button -->
          <button
            @click="toggleQuizStatus(quiz)"
            :class="quiz.status === 'Active' ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer"
          >
            {{ quiz.status === 'Active' ? 'បិទ Quiz' : 'បើក Quiz' }}
          </button>

          <div class="flex items-center gap-1">
            <button @click="openQuizModal(quiz)" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer" title="កែប្រែ">
              <i class="pi pi-pencil text-xs"></i>
            </button>
            <button @click="deleteQuiz(quiz.id)" class="p-2 text-slate-400 hover:text-rose-500 transition-colors cursor-pointer" title="លុប">
              <i class="pi pi-trash text-xs"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Modal: Create / Edit Quiz -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <h3 class="text-base font-bold text-slate-800 m-0">
            {{ isEditing ? 'កែប្រែ Quiz' : 'បង្កើត Quiz / ការប្រឡងថ្មី' }}
          </h3>
          <button @click="closeModal" class="w-8 h-8 rounded-lg hover:bg-slate-200/60 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Modal Form Body -->
        <div class="p-5 overflow-y-auto flex-1 space-y-4 text-xs">
          <!-- Quiz Title -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">ចំណងជើង Quiz / Exam *</label>
            <input
              v-model="form.title"
              type="text"
              placeholder="ឧ. Midterm Exam - Vue.js Basics"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500"
            />
          </div>

          <!-- Subject & Target Class -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-bold text-slate-700 mb-1">មុខវិជ្ជា *</label>
              <select v-model="form.subjectCode" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500">
                <option value="CS-301">Web Programming (CS-301)</option>
                <option value="CS-204">Database Systems (CS-204)</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">សម្រាប់ថ្នាក់ *</label>
              <select v-model="form.className" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500">
                <option value="M1-Class A">M1-Class A</option>
                <option value="M2-Class B">M2-Class B</option>
              </select>
            </div>
          </div>

          <!-- Duration & Total Score -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-bold text-slate-700 mb-1">រយៈពេលប្រឡង (នាទី) *</label>
              <input
                v-model="form.duration"
                type="number"
                placeholder="45"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">កាលបរិច្ឆេទប្រឡង *</label>
              <input
                v-model="form.startDate"
                type="date"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>

          <!-- Question Bank Selection -->
          <div class="pt-2 border-t border-slate-100">
            <label class="block font-bold text-slate-700 mb-1">ជ្រើសរើសចំនួនសំណួរពី Question Bank *</label>
            <div class="bg-indigo-50/60 p-3 rounded-xl border border-indigo-100 flex items-center justify-between">
              <span class="text-slate-600 font-medium">សំណួរដែលមានក្នុង Question Bank មុខវិជ្ជានេះ៖</span>
              <span class="font-bold text-indigo-600">10 សំណួរ</span>
            </div>
            <div class="mt-2 flex items-center gap-2">
              <input
                v-model="form.totalQuestions"
                type="number"
                placeholder="បញ្ចូលចំនួនសំណួរដែលត្រូវស្រង់យក"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-2">
          <button @click="closeModal" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-4 rounded-xl text-xs transition-colors cursor-pointer">
            បោះបង់
          </button>
          <button @click="saveQuiz" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl text-xs transition-colors cursor-pointer">
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
const activeTab = ref('All')

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const tabs = [
  { label: 'ទាំងអស់', value: 'All' },
  { label: 'កំពុងប្រឡង (Active)', value: 'Active' },
  { label: 'ជិតដល់ពេល (Upcoming)', value: 'Upcoming' },
  { label: 'បានបញ្ចប់ (Completed)', value: 'Completed' }
]

const form = ref({
  title: '',
  subjectCode: 'CS-301',
  className: 'M1-Class A',
  duration: 45,
  startDate: '',
  totalQuestions: 10,
  status: 'Upcoming'
})

// Mock Quizzes List
const quizzes = ref([
  {
    id: 1,
    title: 'Quiz 1: Vue.js Basics & Directives',
    subjectCode: 'CS-301',
    className: 'M1-Class A',
    duration: 30,
    startDate: '2026-08-10',
    totalQuestions: 10,
    submittedCount: 15,
    totalStudents: 20,
    status: 'Active'
  },
  {
    id: 2,
    title: 'Midterm Exam: Database Systems',
    subjectCode: 'CS-204',
    className: 'M2-Class B',
    duration: 60,
    startDate: '2026-08-15',
    totalQuestions: 20,
    submittedCount: 0,
    totalStudents: 25,
    status: 'Upcoming'
  },
  {
    id: 3,
    title: 'Quiz 2: Vue Router & Pinia',
    subjectCode: 'CS-301',
    className: 'M1-Class A',
    duration: 25,
    startDate: '2026-07-28',
    totalQuestions: 8,
    submittedCount: 20,
    totalStudents: 20,
    status: 'Completed'
  }
])

// Filtered Quizzes
const filteredQuizzes = computed(() => {
  return quizzes.value.filter(q => {
    const matchTab = activeTab.value === 'All' || q.status === activeTab.value
    const matchSubject = !selectedSubjectFilter.value || q.subjectCode === selectedSubjectFilter.value
    const matchSearch = !searchQuery.value || q.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchTab && matchSubject && matchSearch
  })
})

function toggleQuizStatus(quiz) {
  quiz.status = quiz.status === 'Active' ? 'Completed' : 'Active'
}

function openQuizModal(quiz = null) {
  if (quiz) {
    isEditing.value = true
    editingId.value = quiz.id
    form.value = JSON.parse(JSON.stringify(quiz))
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
    title: '',
    subjectCode: 'CS-301',
    className: 'M1-Class A',
    duration: 45,
    startDate: '',
    totalQuestions: 10,
    status: 'Upcoming'
  }
}

function saveQuiz() {
  if (!form.value.title.trim()) {
    alert('សូមបញ្ចូលចំណងជើង Quiz!')
    return
  }

  if (isEditing.value) {
    const idx = quizzes.value.findIndex(q => q.id === editingId.value)
    if (idx !== -1) quizzes.value[idx] = { ...quizzes.value[idx], ...form.value }
  } else {
    quizzes.value.unshift({
      ...form.value,
      id: Date.now(),
      submittedCount: 0,
      totalStudents: 20
    })
  }

  closeModal()
}

function deleteQuiz(id) {
  if (confirm('តើអ្នកប្រាកដថានឹងលុប Quiz នេះទេ?')) {
    quizzes.value = quizzes.value.filter(q => q.id !== id)
  }
}
</script>