<template>
  <div class="space-y-6">
    <!-- 1. Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">មុខវិជ្ជារបស់ខ្ញុំ (My Subjects)</h1>
        <p class="text-xs text-slate-500 mt-1">គ្រប់គ្រងមុខវិជ្ជាដែលអ្នកកំពុងបង្រៀន និងរៀបចំកាលវិភាគមេរៀន/ឯកសារ</p>
      </div>

      <!-- Search -->
      <div class="flex items-center gap-3">
        <div class="relative">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="ស្វែងរកមុខវិជ្ជា..."
            class="pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-indigo-500 w-48 sm:w-64"
          />
        </div>
      </div>
    </div>

    <!-- 2. Subjects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="subject in filteredSubjects"
        :key="subject.id"
        class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-indigo-300 hover:shadow-md transition-all flex flex-col justify-between"
      >
        <div>
          <!-- Code Badge & Credit -->
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[11px] font-mono font-bold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100">
              {{ subject.code }}
            </span>
            <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-2 py-0.5 rounded-md">
              {{ subject.credits }} Credits
            </span>
          </div>

          <!-- Subject Title & Faculty -->
          <h3 class="text-base font-bold text-slate-800 m-0">{{ subject.name }}</h3>
          <p class="text-xs text-slate-400 mt-1">{{ subject.faculty }} • {{ subject.degree }}</p>

          <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400">ថ្នាក់ដែលកំពុងបង្រៀន៖</span>
              <span class="font-bold text-slate-700">{{ subject.assignedClasses.join(', ') }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400">មេរៀនសរុប (Chapters):</span>
              <span class="font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">{{ subject.chapters.length }} មេរៀន</span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-5 pt-3 border-t border-slate-100 flex gap-2">
          <button
            @click="openMaterialsModal(subject)"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-3 rounded-xl text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer"
          >
            <i class="pi pi-folder-open text-xs"></i>
            <span>កាលវិភាគមេរៀន & ឯកសារ</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 3. Modal: View & Manage Syllabus / Chapters -->
    <div v-if="selectedSubject" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">{{ selectedSubject.code }}</span>
              <h3 class="text-base font-bold text-slate-800 m-0">{{ selectedSubject.name }}</h3>
            </div>
            <p class="text-xs text-slate-500 mt-1">គ្រោងមេរៀន និងឯកសារបង្រៀនសម្រាប់សិស្ស</p>
          </div>
          <button @click="selectedSubject = null" class="w-8 h-8 rounded-lg hover:bg-slate-200/60 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Modal Body: Chapters List -->
        <div class="p-5 overflow-y-auto flex-1 space-y-4">
          <div class="flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">បញ្ជីមេរៀន (Course Syllabus)</h4>
            <button class="bg-slate-100 hover:bg-indigo-50 text-indigo-600 font-bold px-3 py-1.5 rounded-lg text-xs transition-colors cursor-pointer flex items-center gap-1.5">
              <i class="pi pi-plus text-[10px]"></i>
              <span>បន្ថែមមេរៀន</span>
            </button>
          </div>

          <div class="space-y-3">
            <div
              v-for="(chapter, idx) in selectedSubject.chapters"
              :key="idx"
              class="border border-slate-200 rounded-xl p-4 bg-slate-50/30 hover:border-slate-300 transition-all"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <span class="text-[11px] font-bold text-indigo-600 uppercase">មេរៀនទី {{ idx + 1 }}</span>
                  <h5 class="text-sm font-bold text-slate-800 m-0 mt-0.5">{{ chapter.title }}</h5>
                  <p class="text-xs text-slate-500 mt-1">{{ chapter.description }}</p>
                </div>
                <span class="text-[10px] bg-slate-200/70 text-slate-600 font-medium px-2 py-0.5 rounded-md shrink-0">
                  {{ chapter.duration }}
                </span>
              </div>

              <!-- Materials Attachment Link -->
              <div class="mt-3 pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2 text-indigo-600 font-semibold">
                  <i class="pi pi-file-pdf"></i>
                  <span>{{ chapter.resourceName || 'គ្មានឯកសារ' }}</span>
                </div>
                <button class="text-slate-400 hover:text-indigo-600 font-medium text-[11px] cursor-pointer">
                  Upload PDF/Slide
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-end">
          <button @click="selectedSubject = null" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-4 rounded-xl text-xs transition-colors cursor-pointer">
            បិទ
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchQuery = ref('')
const selectedSubject = ref(null)

// Mock Data មុខវិជ្ជារបស់គ្រូ
const mySubjects = ref([
  {
    id: 1,
    code: 'CS-301',
    name: 'Web Programming (Vue.js & Laravel)',
    credits: 3,
    faculty: 'មហាវិទ្យាល័យបច្ចេកវិទ្យា',
    degree: 'បរិញ្ញាបត្រ',
    assignedClasses: ['M1-Class A', 'M1-Class B'],
    chapters: [
      { title: 'ការណែនាំពី Vue.js & Options/Composition API', description: 'សិក្សាពីមូលដ្ឋានគ្រឹះ Components, Props, និង Reactive State', duration: '៣ ម៉ោង', resourceName: 'Vuejs_Basics_Slide.pdf' },
      { title: 'Vue Router & Pinia State Management', description: 'ការរៀបចំ Routing និងការគ្រប់គ្រង Global State ក្នុង App', duration: '៣ ម៉ោង', resourceName: 'Vue_Router_Guide.pdf' },
      { title: 'ការភ្ជាប់ API ជាមួយ Laravel RESTful Backend', description: 'ប្រើប្រាស់ Axios ទាញយកទិន្នន័យ និងដោះស្រាយ Authentication', duration: '៤ ម៉ោង', resourceName: 'Laravel_API_Integration.pdf' }
    ]
  },
  {
    id: 2,
    code: 'CS-204',
    name: 'Database Management Systems (MySQL)',
    credits: 3,
    faculty: 'មហាវិទ្យាល័យបច្ចេកវិទ្យា',
    degree: 'បរិញ្ញាបត្រ',
    assignedClasses: ['M2-Class B'],
    chapters: [
      { title: 'Database Design & ER Diagram', description: 'ការរចនាទម្រង់ទិន្នន័យ (Database Schema) និងទំនាក់ទំនង Table', duration: '៣ ម៉ោង', resourceName: 'ERD_Design_Note.pdf' },
      { title: 'SQL Queries (SELECT, JOIN, GROUP BY)', description: 'ការសរសេរ Query ទាញយក និងចម្រាញ់ទិន្នន័យ', duration: '៤ ម៉ោង', resourceName: 'SQL_Commands_Cheatsheet.pdf' }
    ]
  }
])

// Filter Search
const filteredSubjects = computed(() => {
  if (!searchQuery.value) return mySubjects.value
  const q = searchQuery.value.toLowerCase()
  return mySubjects.value.filter(s => 
    s.name.toLowerCase().includes(q) ||
    s.code.toLowerCase().includes(q)
  )
})

function openMaterialsModal(subject) {
  selectedSubject.value = subject
}
</script>