<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">ការផ្តល់ Feedback ដល់សិស្ស</h1>
        <p class="text-xs text-slate-500 mt-1">ណែនាំសិស្សដែលទទួលបានពិន្ទុទាប ឬធ្លាក់ ឱ្យខិតខំរៀនបន្ថែម</p>
      </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400 uppercase">សិស្សប្រឡងសរុប</span>
        <h3 class="text-lg font-bold text-slate-800 m-0 mt-1">{{ students.length }} នាក់</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200">
        <span class="text-[11px] font-bold text-slate-400 uppercase">សិស្សជាប់ (Passed)</span>
        <h3 class="text-lg font-bold text-emerald-600 m-0 mt-1">{{ passedStudents.length }} នាក់</h3>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-rose-200 bg-rose-50/20">
        <span class="text-[11px] font-bold text-rose-500 uppercase">សិស្សធ្លាក់ត្រូវរៀនបន្ថែម (Failed)</span>
        <h3 class="text-lg font-bold text-rose-600 m-0 mt-1">{{ failedStudents.length }} នាក់</h3>
      </div>
    </div>

    <!-- Student List Table -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800 m-0">បញ្ជីសិស្ស និងលទ្ធផលប្រឡង</h3>
        <!-- Filter Toggle -->
        <div class="flex gap-2">
          <button 
            @click="filterStatus = 'all'" 
            :class="filterStatus === 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
          >
            ទាំងអស់
          </button>
          <button 
            @click="filterStatus = 'failed'" 
            :class="filterStatus === 'failed' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-600 hover:bg-rose-100'"
            class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
          >
            តែសិស្សធ្លាក់ (Failed)
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase font-bold text-[10px]">
              <th class="py-3 px-4">អត្តលេខ</th>
              <th class="py-3 px-4">ឈ្មោះសិស្ស</th>
              <th class="py-3 px-4 text-center">ពិន្ទុ</th>
              <th class="py-3 px-4 text-center">លទ្ធផល</th>
              <th class="py-3 px-4 text-center">ស្ថានភាព Feedback</th>
              <th class="py-3 px-4 text-center">សកម្មភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="student in displayedStudents" :key="student.id" class="hover:bg-slate-50/80">
              <td class="py-3 px-4 font-mono font-bold text-indigo-600">{{ student.studentId }}</td>
              <td class="py-3 px-4 font-semibold text-slate-800">{{ student.name }}</td>
              <td class="py-3 px-4 text-center font-bold text-slate-700">{{ student.score }} / 100</td>
              <td class="py-3 px-4 text-center">
                <span 
                  :class="student.score >= 50 ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
                  class="font-bold px-2.5 py-0.5 rounded-full text-[10px] border"
                >
                  {{ student.score >= 50 ? 'PASSED' : 'FAILED' }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <span v-if="student.hasFeedbackSent" class="text-emerald-600 font-semibold flex items-center justify-center gap-1">
                  <i class="pi pi-check-circle text-xs"></i> បានផ្ញើរួច
                </span>
                <span v-else-if="student.score < 50" class="text-rose-500 font-semibold flex items-center justify-center gap-1">
                  <i class="pi pi-exclamation-circle text-xs"></i> ត្រូវការ Feedback
                </span>
                <span v-else class="text-slate-400">-</span>
              </td>
              <td class="py-3 px-4 text-center">
                <!-- ប៊ូតុងផ្ញើ Feedback សម្រាប់សិស្សធ្លាក់ -->
                <button
                  v-if="student.score < 50"
                  @click="openFeedbackModal(student)"
                  class="bg-rose-500 hover:bg-rose-600 text-white font-semibold px-3 py-1.5 rounded-xl transition-colors cursor-pointer text-[11px] flex items-center gap-1.5 mx-auto shadow-xs"
                >
                  <i class="pi pi-send text-[10px]"></i>
                  <span>ផ្ញើការណែនាំរៀនបន្ថែម</span>
                </button>
                <button
                  v-else
                  @click="openFeedbackModal(student)"
                  class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold px-3 py-1.5 rounded-xl transition-colors cursor-pointer text-[11px] mx-auto"
                >
                  ផ្តល់ Feedback
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal: ផ្ញើ Feedback ទៅសិស្ស -->
    <div v-if="selectedStudent" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-lg overflow-hidden">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h3 class="text-base font-bold text-slate-800 m-0">
              ផ្ញើ Feedback ណែនាំ៖ <span class="text-indigo-600">{{ selectedStudent.name }}</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">
              ពិន្ទុទទួលបាន៖ <span class="font-bold text-rose-600">{{ selectedStudent.score }}/100</span> (ធ្លាក់)
            </p>
          </div>
          <button @click="selectedStudent = null" class="w-8 h-8 rounded-lg hover:bg-slate-200/60 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 space-y-4 text-xs">
          <!-- Template Selector -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">ជ្រើសរើសទម្រង់សារគំរូ (Template)៖</label>
            <div class="flex gap-2">
              <button 
                @click="applyTemplate('restudy')"
                class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-3 py-1.5 rounded-lg border border-indigo-200 text-[11px] cursor-pointer"
              >
                + ណែនាំឱ្យរំលឹកមេរៀនឡើងវិញ
              </button>
              <button 
                @click="applyTemplate('consult')"
                class="bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold px-3 py-1.5 rounded-lg border border-amber-200 text-[11px] cursor-pointer"
              >
                + អញ្ជើញជួបពិគ្រោះផ្ទាល់
              </button>
            </div>
          </div>

          <!-- Message Textarea -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">ខ្លឹមសារ Feedback / ការណែនាំ *</label>
            <textarea
              v-model="feedbackMessage"
              rows="5"
              placeholder="សរសេរការណែនាំពីការសិក្សា ឬការលើកទឹកចិត្តនៅទីនេះ..."
              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 focus:outline-none focus:border-indigo-500 font-sans text-slate-700 leading-relaxed"
            ></textarea>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-2">
          <button @click="selectedStudent = null" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-4 rounded-xl text-xs transition-colors cursor-pointer">
            បោះបង់
          </button>
          <button @click="sendFeedback" class="bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2 px-4 rounded-xl text-xs transition-colors cursor-pointer flex items-center gap-1.5">
            <i class="pi pi-send text-xs"></i>
            <span>ផ្ញើជូនសិស្ស</span>
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const filterStatus = ref('all') // 'all' or 'failed'
const selectedStudent = ref(null)
const feedbackMessage = ref('')

// Mock Data សិស្ស និងពិន្ទុ
const students = ref([
  { id: 1, studentId: 'STU-2026-001', name: 'សុខ មករា', score: 85, hasFeedbackSent: false },
  { id: 2, studentId: 'STU-2026-002', name: 'ចាន់ ធារី', score: 35, hasFeedbackSent: false },
  { id: 3, studentId: 'STU-2026-003', name: 'គង់ វីរៈ', score: 40, hasFeedbackSent: true },
  { id: 4, studentId: 'STU-2026-004', name: 'លី ណា', score: 72, hasFeedbackSent: false },
])

const passedStudents = computed(() => students.value.filter(s => s.score >= 50))
const failedStudents = computed(() => students.value.filter(s => s.score < 50))

const displayedStudents = computed(() => {
  if (filterStatus.value === 'failed') return failedStudents.value
  return students.value
})

function openFeedbackModal(student) {
  selectedStudent.value = student
  feedbackMessage.value = ''
  
  // ប្រសិនបើសិស្សធ្លាក់ បំពេញ Template ស្វ័យប្រវត្តិ
  if (student.score < 50) {
    applyTemplate('restudy')
  }
}

function applyTemplate(type) {
  if (!selectedStudent.value) return

  if (type === 'restudy') {
    feedbackMessage.value = `ជម្រាបសួរ ${selectedStudent.value.name},\n\nលទ្ធផលប្រឡងចុងក្រោយរបស់អ្នកទទួលបាន ${selectedStudent.value.score}/100 ពិន្ទុ (មិនទាន់ជាប់ឡើយ)។ សូមរំលឹកមេរៀនឡើងវិញ ជាពិសេសផ្នែក Vue.js State Management និងទាក់ទងមកលោកគ្រូ ប្រសិនបើមានចម្ងល់បន្ថែម។`
  } else if (type === 'consult') {
    feedbackMessage.value = `ជម្រាបសួរ ${selectedStudent.value.name},\n\nលោកគ្រូសង្កេតឃើញថាពិន្ទុប្រឡងរបស់អ្នកមិនទាន់ល្អប្រសើរឡើយ (${selectedStudent.value.score}/100)។ សូមណាត់ជួបជាមួយលោកគ្រូនៅម៉ោងទំនេរ ដើម្បីពិគ្រោះពីវិធីសាស្ត្រសិក្សាឡើងវិញ។`
  }
}

function sendFeedback() {
  if (!feedbackMessage.value.trim()) {
    alert('សូមបញ្ចូលខ្លឹមសារ Feedback!')
    return
  }
  
  // កែប្រែប្រព័ន្ធសម្គាល់ថាបានផ្ញើរួច
  selectedStudent.value.hasFeedbackSent = true
  alert(`បានផ្ញើ Feedback ទៅកាន់សិស្សឈ្មោះ "${selectedStudent.value.name}" រួចរាល់!`)
  
  selectedStudent.value = null
}
</script>