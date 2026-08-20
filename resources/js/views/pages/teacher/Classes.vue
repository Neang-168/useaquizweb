<template>
  <div class="space-y-6">
    <!-- 1. Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">My Classes</h1>
        <p class="text-xs text-slate-500 mt-1">
          {{ filteredClasses.length }} of {{ myClasses.length }} class{{ myClasses.length === 1 ? '' : 'es' }}
          &middot; {{ totalStudentsAcrossClasses }} students total
        </p>
      </div>
    </div>

    <!-- 2. Filters Bar -->
    <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs space-y-2.5">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5">
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Class</label>
          <Dropdown
            v-model="classFilter"
            :options="classOptions"
            option-label="label"
            option-value="value"
            placeholder="All Classes"
            showClear
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg text-xs"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Subject</label>
          <Dropdown
            v-model="subjectFilter"
            :options="subjectOptions"
            option-label="label"
            option-value="value"
            placeholder="All Subjects"
            showClear
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg text-xs"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Shift</label>
          <Dropdown
            v-model="shiftFilter"
            :options="shiftOptions"
            option-label="label"
            option-value="value"
            placeholder="All Shifts"
            showClear
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg text-xs"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Academic Year</label>
          <Dropdown
            v-model="academicYearFilter"
            :options="academicYearOptions"
            option-label="label"
            option-value="value"
            placeholder="All Years"
            showClear
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg text-xs"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Search</label>
          <div class="relative w-full">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText
              v-model="searchQuery"
              size="small"
              placeholder="Class, subject, or major..."
              class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Classes Grid Card -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="item in filteredClasses"
        :key="item.id"
        class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-indigo-300 hover:shadow-md transition-all flex flex-col justify-between"
      >
        <div>
          <!-- Header Card: Badge Shift & Academic Year -->
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100">
              <i class="pi pi-clock text-[9px] mr-1"></i>{{ item.shift }}
            </span>
            <span class="text-[10px] font-semibold text-slate-400">{{ item.academicYear }}</span>
          </div>

          <!-- Subject is the primary identity of the card; class is secondary -->
          <p class="text-base font-bold text-indigo-600 m-0 flex items-center gap-1.5 flex-wrap">
            <i class="pi pi-book text-xs"></i> {{ item.subject }}
            <button
              v-if="item.subject_code"
              type="button"
              title="Copy subject code — use it in the subject_code column when bulk-importing questions"
              class="inline-flex items-center gap-1 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-100 cursor-pointer"
              @click="copySubjectCode(item.subject_code)"
            >
              {{ item.subject_code }}
              <i class="pi pi-copy text-[9px]"></i>
            </button>
          </p>
          <h3 class="text-sm font-semibold text-slate-500 mt-1 mb-0">{{ item.className }}</h3>

          <!-- Key info grid: the numbers a teacher scans for first -->
          <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2.5">
            <div class="bg-slate-50 rounded-xl px-3 py-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Students</p>
              <p class="text-sm font-bold text-slate-800 m-0 mt-0.5">{{ item.totalStudents }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl px-3 py-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Room</p>
              <p class="text-sm font-bold text-slate-800 m-0 mt-0.5">{{ item.room || '—' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl px-3 py-2 col-span-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Major</p>
              <p class="text-sm font-semibold text-slate-700 m-0 mt-0.5 truncate">{{ item.major || '—' }}</p>
            </div>
          </div>
        </div>

        <!-- Action Button -->
        <div class="mt-5 pt-3 border-t border-slate-100 flex gap-2">
          <Button as="router-link" size="small"
            :to="{ name: 'teacher.classWorkspace', params: { assignmentId: item.id } }"
            :label="`Manage ${item.subject}`" icon="pi pi-cog"
            class="w-full !bg-slate-900 hover:!bg-indigo-600 !border-slate-900 !text-white !rounded-lg !text-xs no-underline"
          />
        </div>
      </div>
    </div>

    <div v-if="filteredClasses.length === 0" class="text-center py-12 bg-white rounded-2xl border border-slate-200">
      <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
      <p class="text-xs text-slate-500">
        {{ myClasses.length === 0 ? "You haven't been assigned to any classes yet." : 'No classes match your filters.' }}
      </p>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import api, { extractError } from '../../../api'

const searchQuery = ref('')
const classFilter = ref('')
const subjectFilter = ref('')
const shiftFilter = ref('')
const academicYearFilter = ref('')

const myClasses = ref([])
const loading = ref(false)

const fetchMyClasses = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/classes')
    myClasses.value = data.data
  } catch (error) {
    alert(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(fetchMyClasses)

// Lets a teacher grab the exact subject_code needed for the Question Bank
// import spreadsheet without retyping it from memory.
const copySubjectCode = async (code) => {
  try {
    await navigator.clipboard.writeText(code)
  } catch (error) {
    // Clipboard API can be unavailable (e.g. non-secure context); fail silently.
  }
}

function uniqueOptions(field) {
  const seen = new Set()
  myClasses.value.forEach(c => { if (c[field]) seen.add(c[field]) })
  return Array.from(seen).sort().map(v => ({ label: v, value: v }))
}

const classOptions = computed(() => uniqueOptions('className'))
const subjectOptions = computed(() => uniqueOptions('subject'))
const shiftOptions = computed(() => uniqueOptions('shift'))
const academicYearOptions = computed(() => uniqueOptions('academicYear'))

const totalStudentsAcrossClasses = computed(() =>
  myClasses.value.reduce((sum, c) => sum + (c.totalStudents || 0), 0)
)

const filteredClasses = computed(() => {
  const q = searchQuery.value.toLowerCase()

  return myClasses.value.filter(c => {
    if (classFilter.value && c.className !== classFilter.value) return false
    if (subjectFilter.value && c.subject !== subjectFilter.value) return false
    if (shiftFilter.value && c.shift !== shiftFilter.value) return false
    if (academicYearFilter.value && c.academicYear !== academicYearFilter.value) return false

    if (!q) return true

    return (
      c.className?.toLowerCase().includes(q) ||
      c.major?.toLowerCase().includes(q) ||
      c.subject?.toLowerCase().includes(q)
    )
  })
})
</script>
