<template>
  <div class="space-y-6">
    <!-- 1. Header Page & Export Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">កែពិន្ទុ & របាយការណ៍ (Grading & Reports)</h1>
        <p class="text-xs text-slate-500 mt-1">ពិនិត្យកែពិន្ទុ Essay និងទាញយករាយការណ៍ពិន្ទុសិស្សតាមថ្នាក់</p>
      </div>

      <button
        @click="exportReport"
        class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition-colors flex items-center gap-2 cursor-pointer shadow-sm self-start sm:self-auto"
      >
        <i class="pi pi-file-excel text-xs"></i>
        <span>Export ជា Excel</span>
      </button>
    </div>

    <!-- 2. Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-3 shadow-xs">
      <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
        <!-- Select Class -->
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">ថ្នាក់រៀន</label>
          <select
            v-model="selectedClass"
            class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-indigo-500"
          >
            <option value="M1-Class A">M1-Class A</option>
            <option value="M2-Class B">M2-Class B</option>
          </select>
        </div>

        <!-- Select Quiz / Exam -->
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">ការប្រឡង (Quiz / Exam)</label>
          <select
            v-model="selectedQuiz"
            class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-indigo-500"
          >
            <option value="Quiz 1: Vue.js Basics">Quiz 1: Vue.js Basics</option>
            <option value="Midterm Exam: Database Systems">Midterm Exam: Database Systems</option>
          </select>
        </div>
      </div>

      <!-- Search Student -->
      <div class="relative w-full md:w-64 self-end">
        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="ស្វែងរកឈ្មោះ ឬ ID សិស្ស..."
          class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-indigo-500"
        />
      </div>
    </div>

    <!-- 3. Overview Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400">សិស្សប្រឡងសរុប</span>
        <h3 class="text-lg font-bold text-slate-800 m-0 mt-1">{{ filteredStudents.length }} នាក់</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400">ពិន្ទុមធ្យមភាគ</span>
        <h3 class="text-lg font-bold text-indigo-600 m-0 mt-1">{{ averageScore }} / 100</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400">ជាប់ (Passed)</span>
        <h3 class="text-lg font-bold text-emerald-600 m-0 mt-1">{{ passedCount }} នាក់</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400">ធ្លាក់ (Failed)</span>
        <h3 class="text-lg font-bold text-rose-600 m-0 mt-1">{{ failedCount }} នាក់</h3>
      </div>
    </div>

    <!-- 4. Score Table -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800 m-0">បញ្ជីពិន្ទុសិស្ស - {{ selectedQuiz }}</h3>
        <span class="text-xs text-slate-400">ថ្នាក់៖ {{ selectedClass }}</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
              <th class="py-3 px-4">#</th>
              <th class="py-3 px-4">អត្តលេខ</th>
              <th class="py-3 px-4">ឈ្មោះសិស្ស</th>
              <th class="py-3 px-4">ថ្ងៃប្រឡង</th>
              <th class="py-3 px-4 text-center">ពិន្ទុ MCQ</th>
              <th class="py-3 px-4 text-center">ពិន្ទុ Essay</th>
              <th class="py-3 px-4 text-center">ពិន្ទុសរុប</th>
              <th class="py-3 px-4 text-center">លទ្ធផល</th>
              <th class="py-3 px-4 text-center">សកម្មភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(student, index) in filteredStudents" :key="student.id" class="hover:bg-slate-50/80">
              <td class="py-3 px-4 font-mono text-slate-400">{{ index + 1 }}</td>
              <td class="py-3 px-4 font-mono font-bold text-indigo-600">{{ student.studentId }}</td>
              <td class="py-3 px-4 font-semibold text-slate-800">{{ student.name }}</td>
              <td class="py-3 px-4 text-slate-500">{{ student.submittedAt }}</td>
              <td class="py-3 px-4 text-center font-medium text-slate-700">{{ student.mcqScore }}/50</td>
              <td class="py-3 px-4 text-center">
                <span v-if="student.essayNeedsGrade" class="bg-amber-50 text-amber-600 font-bold px-2 py-0.5 rounded text-[10px] border border-amber-200">
                  រង់ចាំកែ
                </span>
                <span v-else class="font-medium text-slate-700">{{ student.essayScore }}/50</span>
              </td>
              <td class="py-3 px-4 text-center font-bold text-indigo-600 text-sm">
                {{ student.mcqScore + student.essayScore }}
              </td>
              <td class="py-3 px-4 text-center">
                <span
                  :class="(student.mcqScore + student.essayScore) >= 50 ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
                  class="font-bold px-2.5 py-0.5 rounded-full text-[10px] border"
                >
                  {{ (student.mcqScore + student.essayScore) >= 50 ? 'PASSED' : 'FAILED' }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <button
                  @click="openGradingModal(student)"
                  class="bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white font-semibold px-2.5 py-1 rounded-lg transition-colors cursor-pointer text-[11px]"
                >
                  កែពិន្ទុ Essay
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 5. Modal: Grade Essay Question -->
    <div v-if="selectedStudentForGrading" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h3 class="text-base font-bold text-slate-800 m-0">កែពិន្ទុ Essay - {{ selectedStudentForGrading.name }}</h3>
            <p class="text-xs text-slate-400 mt-0.5">អត្តលេខ៖ {{ selectedStudentForGrading.studentId }}</p>
          </div>
          <button @click="selectedStudentForGrading = null" class="w-8 h-8 rounded-lg hover:bg-slate-200/60 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 overflow-y-auto flex-1 space-y-4 text-xs">
          <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-2">
            <span class="font-bold text-indigo-600">សំណួរ Essay៖</span>
            <p class="font-semibold text-slate-800 m-0">ចូរពន្យល់ពីភាពខុសគ្នារវាង Computed Property និង Method នៅក្នុង Vue.js?</p>
          </div>

          <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-2">
            <span class="font-bold text-slate-500">ចម្លើយរបស់សិស្ស៖</span>
            <p class="text-slate-700 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
              Computed Property គឺត្រូវ បាន cache ទុកទិន្នន័យ វានឹងដំណើរការតែពេលណា dependency ប្រែប្រួល។ ចំណែក Method វិញគឺរត់ឡើងវិញរាល់ពេល Re-render។
            </p>
          </div>

          <!-- Input Score -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">បញ្ចូលពិន្ទុ Essay (អតិបរមា 50) *</label>
            <input
              v-model.number="inputEssayScore"
              type="number"
              max="50"
              min="0"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 focus:outline-none focus:border-indigo-500 font-bold text-indigo-600"
            />
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-2">
          <button @click="selectedStudentForGrading = null" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-4 rounded-xl text-xs transition-colors cursor-pointer">
            បោះបង់
          </button>
          <button @click="saveEssayScore" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl text-xs transition-colors cursor-pointer">
            រក្សាទុកពិន្ទុ
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const selectedClass = ref('M1-Class A')
const selectedQuiz = ref('Quiz 1: Vue.js Basics')
const searchQuery = ref('')

const selectedStudentForGrading = ref(null)
const inputEssayScore = ref(0)

// Mock Students Score Data
const students = ref([
  { id: 1, studentId: 'STU-2026-001', name: 'សុខ មករា', submittedAt: '2026-08-01 09:30 AM', mcqScore: 45, essayScore: 40, essayNeedsGrade: false },
  { id: 2, studentId: 'STU-2026-002', name: 'ចាន់ ធារី', submittedAt: '2026-08-01 09:42 AM', mcqScore: 50, essayScore: 0, essayNeedsGrade: true },
  { id: 3, studentId: 'STU-2026-003', name: 'គង់ វីរៈ', submittedAt: '2026-08-01 09:28 AM', mcqScore: 20, essayScore: 15, essayNeedsGrade: false },
])

const filteredStudents = computed(() => {
  if (!searchQuery.value) return students.value
  const q = searchQuery.value.toLowerCase()
  return students.value.filter(s => s.name.toLowerCase().includes(q) || s.studentId.toLowerCase().includes(q))
})

const averageScore = computed(() => {
  if (students.value.length === 0) return 0
  const total = students.value.reduce((acc, s) => acc + (s.mcqScore + s.essayScore), 0)
  return Math.round(total / students.value.length)
})

const passedCount = computed(() => {
  return students.value.filter(s => (s.mcqScore + s.essayScore) >= 50).length
})

const failedCount = computed(() => {
  return students.value.filter(s => (s.mcqScore + s.essayScore) < 50).length
})

function openGradingModal(student) {
  selectedStudentForGrading.value = student
  inputEssayScore.value = student.essayScore
}

function saveEssayScore() {
  if (selectedStudentForGrading.value) {
    selectedStudentForGrading.value.essayScore = inputEssayScore.value
    selectedStudentForGrading.value.essayNeedsGrade = false
    selectedStudentForGrading.value = null
  }
}

function exportReport() {
  alert('កំពុងទាញយករាយការណ៍ជា Excel...')
}
</script>