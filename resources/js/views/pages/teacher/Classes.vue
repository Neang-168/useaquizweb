<template>
  <div class="space-y-6">
    <!-- 1. Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">ថ្នាក់បង្រៀនរបស់ខ្ញុំ (My Classes)</h1>
        <p class="text-xs text-slate-500 mt-1">បញ្ជីថ្នាក់រៀន និងសិស្សដែលអ្នកត្រូវទទួលខុសត្រូវបង្រៀន</p>
      </div>

      <!-- Search & Filter -->
      <div class="flex items-center gap-3">
        <div class="relative">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="ស្វែងរកថ្នាក់..."
            class="pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-indigo-500 w-48 sm:w-64"
          />
        </div>
      </div>
    </div>

    <!-- 2. Classes Grid Card -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="item in filteredClasses"
        :key="item.id"
        class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-indigo-300 hover:shadow-md transition-all flex flex-col justify-between"
      >
        <div>
          <!-- Header Card: Badge Shift & Degree -->
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100">
              {{ item.shift }}
            </span>
            <span class="text-xs text-slate-400 font-medium">{{ item.academicYear }}</span>
          </div>

          <!-- Class Title & Subject -->
          <h3 class="text-base font-bold text-slate-800 m-0">{{ item.className }}</h3>
          <p class="text-xs text-slate-500 mt-1">ជំនាញ៖ <span class="font-semibold text-slate-700">{{ item.major }}</span></p>

          <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400">មុខវិជ្ជាបង្រៀន៖</span>
              <span class="font-bold text-slate-700">{{ item.subject }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400">ចំនួនសិស្សសរុប៖</span>
              <span class="font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">{{ item.totalStudents }} នាក់</span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400">បន្ទប់សិក្សា៖</span>
              <span class="font-medium text-slate-600">{{ item.room }}</span>
            </div>
          </div>
        </div>

        <!-- Action Button -->
        <div class="mt-5 pt-3 border-t border-slate-100 flex gap-2">
          <button
            @click="openStudentList(item)"
            class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-semibold py-2 px-3 rounded-xl text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer"
          >
            <i class="pi pi-users text-xs"></i>
            <span>មើលបញ្ជីឈ្មោះសិស្ស</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 3. Modal: Student List inside Class -->
    <div v-if="selectedClass" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h3 class="text-base font-bold text-slate-800 m-0">
              បញ្ជីឈ្មោះសិស្ស - {{ selectedClass.className }}
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
              មុខវិជ្ជា៖ {{ selectedClass.subject }} • សរុប {{ selectedClass.students.length }} នាក់
            </p>
          </div>
          <button @click="selectedClass = null" class="w-8 h-8 rounded-lg hover:bg-slate-200/60 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Modal Body: Student Table -->
        <div class="p-5 overflow-y-auto flex-1">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-200 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                <th class="py-2.5 px-3">#</th>
                <th class="py-2.5 px-3">អត្តលេខ (ID)</th>
                <th class="py-2.5 px-3">ឈ្មោះសិស្ស</th>
                <th class="py-2.5 px-3">ភេទ</th>
                <th class="py-2.5 px-3">អុីម៉ែល</th>
                <th class="py-2.5 px-3 text-center">ស្ថានភាព</th>
              </tr>
            </thead>
            <tbody class="text-xs divide-y divide-slate-100">
              <tr v-for="(student, index) in selectedClass.students" :key="student.id" class="hover:bg-slate-50/80">
                <td class="py-3 px-3 text-slate-400 font-mono">{{ index + 1 }}</td>
                <td class="py-3 px-3 font-mono font-bold text-indigo-600">{{ student.student_id }}</td>
                <td class="py-3 px-3 font-semibold text-slate-800">{{ student.name }}</td>
                <td class="py-3 px-3 text-slate-500">{{ student.gender }}</td>
                <td class="py-3 px-3 text-slate-500">{{ student.email }}</td>
                <td class="py-3 px-3 text-center">
                  <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 font-semibold px-2 py-0.5 rounded-md text-[10px]">
                    សិក្សា
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-end">
          <button @click="selectedClass = null" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-4 rounded-xl text-xs transition-colors cursor-pointer">
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
const selectedClass = ref(null)

// Mock Data ថ្នាក់បង្រៀនរបស់គ្រូ (ពេលភ្ជាប់ API អ្នកអាចទាញពី Laravel Backend តាម axios)
const myClasses = ref([
  {
    id: 1,
    className: 'M1-Class A',
    major: 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
    subject: 'Web Programming',
    shift: 'វេនព្រឹក',
    academicYear: '2025-2026',
    room: 'Lab 302',
    totalStudents: 3,
    students: [
      { id: 1, student_id: 'STU-2026-001', name: 'សុខ មករា', gender: 'ប្រុស', email: 'makara@example.com' },
      { id: 2, student_id: 'STU-2026-002', name: 'ចាន់ ធារី', gender: 'ស្រី', email: 'theary@example.com' },
      { id: 3, student_id: 'STU-2026-003', name: 'គង់ វីរៈ', gender: 'ប្រុស', email: 'virak@example.com' },
    ]
  },
  {
    id: 2,
    className: 'M2-Class B',
    major: 'បច្ចេកវិទ្យាព័ត៌មាន',
    subject: 'Database Systems',
    shift: 'វេនល្ងាច',
    academicYear: '2025-2026',
    room: 'Room 405',
    totalStudents: 2,
    students: [
      { id: 4, student_id: 'STU-2026-010', name: 'លី ហេង', gender: 'ប្រុស', email: 'heng@example.com' },
      { id: 5, student_id: 'STU-2026-011', name: 'កែវ ណារី', gender: 'ស្រី', email: 'nary@example.com' },
    ]
  }
])

// Filter Search
const filteredClasses = computed(() => {
  if (!searchQuery.value) return myClasses.value
  const q = searchQuery.value.toLowerCase()
  return myClasses.value.filter(c => 
    c.className.toLowerCase().includes(q) ||
    c.major.toLowerCase().includes(q) ||
    c.subject.toLowerCase().includes(q)
  )
})

function openStudentList(item) {
  selectedClass.value = item
}
</script>