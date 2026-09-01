<template>
  <div class="space-y-6 w-full pb-10">

    <!-- 1. Header Section (Single Class Header) -->
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs flex flex-col md:flex-row md:items-start justify-between gap-4">
      <div class="flex items-start gap-4">
        <div class="w-14 h-14 rounded-2xl bg-[#002060] text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-md">
          {{ classData.code }}
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-extrabold text-slate-800 m-0">{{ classData.name }}</h1>
            <span
              class="px-2 py-0.5 text-[10px] font-extrabold rounded-md border"
              :class="classData.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'"
            >
              {{ classData.status || '—' }}
            </span>
          </div>
          <p class="text-xs text-slate-400 m-0 mt-1">
            Major: <span class="font-semibold text-slate-600">{{ classData.subject || '—' }}</span>
            <span class="mx-1.5 text-slate-300">|</span>
            Faculty: <span class="font-semibold text-slate-600">{{ classData.faculty || '—' }}</span>
            <span class="mx-1.5 text-slate-300">|</span>
            Shift: <span class="font-semibold text-slate-600">{{ classData.shift || '—' }}</span>
            <span class="mx-1.5 text-slate-300">|</span>
            {{ classData.academic_year || '—' }} · {{ classData.semester || '—' }} · {{ classData.term || '—' }}
          </p>
          <!-- <p class="text-xs text-slate-400 m-0 mt-1">
            Teacher{{ teacherNames.length > 1 ? 's' : '' }}:
            <span v-if="teacherNames.length" class="font-semibold text-slate-600">{{ teacherNames.join(', ') }}</span>
            <span v-else class="italic text-slate-400">No teacher assigned to this class yet</span>
          </p> -->
          <div v-if="subjects.length" class="flex flex-wrap gap-1.5 mt-2">
            <span
              v-for="(row, i) in subjects" :key="i"
              class="px-2 py-0.5 bg-slate-50 text-slate-500 text-[10px] font-semibold rounded-md border border-slate-100"
              title="Subject — Teacher"
            >
              {{ row.subject || 'Subject' }}<span v-if="row.teacher"> — {{ row.teacher }}</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Subject / Teacher / Quiz Filters -->
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex flex-wrap items-center gap-2.5">
      <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1">Filter results:</span>

      <Dropdown
        v-model="filterSubjectId" :options="subjectOptions" optionLabel="name" optionValue="id"
        placeholder="All Subjects" showClear class="w-full sm:w-52 custom-filter-dropdown"
      />

      <Dropdown
        v-model="filterTeacher" :options="teacherOptions"
        placeholder="All Teachers" showClear class="w-full sm:w-52 custom-filter-dropdown"
      />

      <Dropdown
        v-model="filterQuizId" :options="quizOptions" optionLabel="title" optionValue="id"
        placeholder="All Quizzes" showClear class="w-full sm:w-60 custom-filter-dropdown"
      />

      <Button
        v-if="hasActiveFilter" icon="pi pi-filter-slash" label="Reset"
        class="!bg-rose-50 !text-rose-600 hover:!bg-rose-100 !border-0 !rounded-lg !py-1.5 !px-3 !text-xs !font-medium transition-all cursor-pointer whitespace-nowrap"
        @click="clearFilters"
      />

      <div class="ml-auto flex items-center gap-2">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Export:</span>
        <Button
          icon="pi pi-file" label="CSV" :loading="exporting"
          class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-lg !py-1.5 !px-3 !text-xs !font-medium transition-all cursor-pointer"
          @click="handleExport('csv')"
        />
        <Button
          icon="pi pi-file-excel" label="Excel" :loading="exporting"
          class="!bg-emerald-50 !text-emerald-600 hover:!bg-emerald-100 !border-0 !rounded-lg !py-1.5 !px-3 !text-xs !font-medium transition-all cursor-pointer"
          @click="handleExport('xlsx')"
        />
      </div>
    </div>

    <!-- 3. KPI Summary Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider m-0">Total Students</p>
          <h2 class="text-2xl font-black text-slate-800 font-mono m-0 mt-1">{{ classData.total_students }} / {{ classData.capacity || '—' }}</h2>
        </div>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
          <i class="pi pi-users text-base"></i>
        </div>
      </div>

      <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider m-0">Total Quizzes<span v-if="hasActiveFilter" class="normal-case font-medium text-slate-300"> (filtered)</span></p>
          <h2 class="text-2xl font-black text-purple-700 font-mono m-0 mt-1">{{ displayTotalQuizzes }}</h2>
        </div>
        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
          <i class="pi pi-file text-base"></i>
        </div>
      </div>

      <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider m-0">Passed / Failed<span v-if="hasActiveFilter" class="normal-case font-medium text-slate-300"> (filtered)</span></p>
          <h2 class="text-2xl font-black text-emerald-600 font-mono m-0 mt-1">
            {{ displayStats.passed }} <span class="text-slate-400 text-sm font-normal">/</span> <span class="text-rose-600">{{ displayStats.failed }}</span>
          </h2>
        </div>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
          <i class="pi pi-check-circle text-base"></i>
        </div>
      </div>

      <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider m-0">Average Score<span v-if="hasActiveFilter" class="normal-case font-medium text-slate-300"> (filtered)</span></p>
          <h2 class="text-2xl font-black text-amber-600 font-mono m-0 mt-1">{{ displayStats.avg_score }}/100</h2>
        </div>
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
          <i class="pi pi-star text-base"></i>
        </div>
      </div>
    </div>

    <!-- 4. Student List Table inside Class -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-5">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4">
        <div>
          <h3 class="text-sm font-bold text-slate-800 m-0">Class Roster</h3>
          <p class="text-xs text-slate-400 m-0 mt-0.5">All students enrolled in class {{ classData.code }}</p>
        </div>
        <div class="relative w-full lg:w-56">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input
            v-model="studentSearch"
            type="text"
            placeholder="Search by name, username, or student ID..."
            class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 focus:outline-none focus:border-blue-500"
          />
        </div>
      </div>

      <div v-if="loading" class="py-10 text-center text-xs text-slate-400">Loading roster…</div>

      <div v-else-if="!studentsList.length" class="py-10 text-center text-xs text-slate-400">
        No students are assigned to this class yet.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
              <th class="py-3 px-3">Username</th>
              <th class="py-3 px-3">Name</th>
              <th class="py-3 px-3">Name (KH)</th>
              <th class="py-3 px-3">Gender</th>
              <th class="py-3 px-3">Phone</th>
              <th class="py-3 px-3 text-center">Quiz Completed</th>
              <th class="py-3 px-3 text-center">Total Score</th>
              <th class="py-3 px-3 text-center">%</th>
              <th class="py-3 px-3 text-center">Result</th>
              <th class="py-3 px-3 text-right">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <tr v-for="std in displayedStudents" :key="std.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3 px-3 font-mono font-bold text-slate-500">{{ std.username || '—' }}</td>
              <td class="py-3 px-3 font-semibold text-slate-800">{{ std.name }}</td>
              <td class="py-3 px-3 text-slate-600 font-khmer">{{ std.name_kh || '—' }}</td>
              <td class="py-3 px-3 text-slate-500">{{ std.gender || '—' }}</td>
              <td class="py-3 px-3 text-slate-500">{{ std.phone || '—' }}</td>
              <td class="py-3 px-3 text-center font-mono">{{ std.effective.completed }}/{{ std.effective.total }}</td>
              <td class="py-3 px-3 text-center font-mono text-slate-500">{{ std.effective.points }}/{{ std.effective.totalPoints }}</td>
              <td class="py-3 px-3 text-center font-mono font-extrabold text-blue-600">{{ std.effective.score !== null ? std.effective.score + '%' : '—' }}</td>
              <td class="py-3 px-3 text-center">
                <span
                  v-if="std.effective.score !== null"
                  class="px-2.5 py-0.5 rounded-md text-[10px] font-bold"
                  :class="std.effective.score >= 50 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'"
                >
                  {{ std.effective.score >= 50 ? 'Passed' : 'Failed' }}
                </span>
                <span v-else class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-50 text-slate-400 border border-slate-100">
                  No Data
                </span>
              </td>
              <td class="py-3 px-3 text-right">
                <button
                  @click="openStudentDetail(std)"
                  class="!w-7 !h-7 !rounded-lg !bg-slate-100 hover:!bg-slate-200 !text-slate-500 inline-flex items-center justify-center transition-colors cursor-pointer"
                  title="View full details"
                >
                  <i class="pi pi-eye text-xs"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- STUDENT DETAIL MODAL -->
    <Dialog v-model:visible="showDetailDialog" modal header="Student Details" :style="{ width: '680px' }" class="rounded-2xl">
      <div v-if="selectedStudent" class="space-y-5 pt-2">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-[#002060] text-white flex items-center justify-center font-bold shrink-0">
            {{ (selectedStudent.name || '?').charAt(0) }}
          </div>
          <div>
            <h3 class="text-sm font-extrabold text-slate-800 m-0">{{ selectedStudent.name }}</h3>
            <p class="text-xs text-slate-400 m-0 mt-0.5 font-khmer" v-if="selectedStudent.name_kh">{{ selectedStudent.name_kh }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-xs">
          <div><span class="text-slate-400 block">Username</span><span class="font-semibold text-slate-700">{{ selectedStudent.username || '—' }}</span></div>
          <div><span class="text-slate-400 block">Student ID</span><span class="font-semibold text-slate-700">{{ selectedStudent.student_id || '—' }}</span></div>
          <div><span class="text-slate-400 block">Gender</span><span class="font-semibold text-slate-700">{{ selectedStudent.gender || '—' }}</span></div>
          <div><span class="text-slate-400 block">Date of Birth</span><span class="font-semibold text-slate-700">{{ selectedStudent.dob || '—' }}</span></div>
          <div><span class="text-slate-400 block">Phone</span><span class="font-semibold text-slate-700">{{ selectedStudent.phone || '—' }}</span></div>
          <div class="col-span-2"><span class="text-slate-400 block">Address</span><span class="font-semibold text-slate-700">{{ selectedStudent.address || '—' }}</span></div>

          <div><span class="text-slate-400 block">Class Status</span><span class="font-semibold text-slate-700">{{ selectedStudent.class_status || '—' }}</span></div>
          <div><span class="text-slate-400 block">Account Status</span><span class="font-semibold text-slate-700">{{ selectedStudent.account_status || '—' }}</span></div>
          <div><span class="text-slate-400 block">Admission Date</span><span class="font-semibold text-slate-700">{{ selectedStudent.admission_date || '—' }}</span></div>
          <div><span class="text-slate-400 block">Generation</span><span class="font-semibold text-slate-700">{{ selectedStudent.generation || '—' }}</span></div>

          <div><span class="text-slate-400 block">Faculty</span><span class="font-semibold text-slate-700">{{ selectedStudent.faculty || '—' }}</span></div>
          <div><span class="text-slate-400 block">Department</span><span class="font-semibold text-slate-700">{{ selectedStudent.department || '—' }}</span></div>
          <div><span class="text-slate-400 block">Major</span><span class="font-semibold text-slate-700">{{ selectedStudent.major || '—' }}</span></div>
          <div><span class="text-slate-400 block">Stage</span><span class="font-semibold text-slate-700">{{ selectedStudent.stage || '—' }}</span></div>

          <div><span class="text-slate-400 block">Academic Year</span><span class="font-semibold text-slate-700">{{ selectedStudent.academic_year || '—' }}</span></div>
          <div><span class="text-slate-400 block">Semester</span><span class="font-semibold text-slate-700">{{ selectedStudent.semester || '—' }}</span></div>
          <div><span class="text-slate-400 block">Term</span><span class="font-semibold text-slate-700">{{ selectedStudent.term || '—' }}</span></div>
          <div><span class="text-slate-400 block">Shift</span><span class="font-semibold text-slate-700">{{ selectedStudent.shift || '—' }}</span></div>
        </div>

        <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-4 text-xs">
          <div><span class="text-slate-400 block">Quizzes Completed</span><span class="font-bold text-slate-700">{{ selectedStudent.quiz_completed }}/{{ selectedStudent.total_quizzes }}</span></div>
          <div><span class="text-slate-400 block">Average Score</span><span class="font-bold text-blue-600">{{ selectedStudent.score ?? 'No Data' }}</span></div>
        </div>

        <div class="pt-3 border-t border-slate-100">
          <h4 class="text-xs font-bold text-slate-700 m-0 mb-2">Quiz Results Breakdown</h4>
          <div v-if="!selectedStudent.quiz_results?.length" class="text-xs text-slate-400 py-3 text-center bg-slate-50 rounded-lg">
            This student hasn't submitted any quiz in this class yet.
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                  <th class="py-2 px-2">Quiz</th>
                  <th class="py-2 px-2">Subject</th>
                  <th class="py-2 px-2">Teacher</th>
                  <th class="py-2 px-2 text-center">Score</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs">
                <tr v-for="r in selectedStudent.quiz_results" :key="r.quiz_id">
                  <td class="py-2 px-2 font-semibold text-slate-800">{{ r.quiz_title || '—' }}</td>
                  <td class="py-2 px-2 text-slate-500">{{ r.subject || '—' }}</td>
                  <td class="py-2 px-2 text-slate-500">{{ r.teacher || '—' }}</td>
                  <td class="py-2 px-2 text-center font-mono font-bold" :class="r.score >= 50 ? 'text-emerald-600' : 'text-rose-600'">
                    {{ r.score ?? '—' }} <span class="text-slate-400 font-normal">({{ r.points }}/{{ r.total_points }})</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import api, { toastFromError } from '../../../api'

const props = defineProps({
  id: { type: [String, Number], required: true },
})

const toast = useToast()

const classData = ref({
  id: null,
  code: '',
  name: '',
  subject: '',
  faculty: '',
  shift: '',
  academic_year: '',
  semester: '',
  term: '',
  status: '',
  capacity: null,
  total_students: 0,
  total_quizzes: 0,
})

const studentsList = ref([])
const quizList = ref([])
const subjects = ref([])
const loading = ref(false)

const fetchRoster = async () => {
  loading.value = true
  try {
    const { data } = await api.get(`/classes/${props.id}/roster`)
    const cls = data.class

    classData.value = {
      id: cls.id,
      code: cls.code,
      name: cls.name,
      subject: cls.major_name || '',
      faculty: cls.faculty_name || '',
      shift: cls.shift || '',
      academic_year: cls.academic_year || '',
      semester: cls.semester || '',
      term: cls.term || '',
      status: cls.status || '',
      capacity: cls.capacity,
      total_students: cls.students_count,
      total_quizzes: cls.total_quizzes,
    }
    studentsList.value = data.students
    quizList.value = data.quizzes
    subjects.value = data.subjects
  } catch (error) {
    toast.add({ summary: 'Failed to load class roster', ...toastFromError(error) })
  } finally {
    loading.value = false
  }
}

onMounted(fetchRoster)

const studentSearch = ref('')
const showDetailDialog = ref(false)
const selectedStudent = ref(null)

const filterSubjectId = ref(null)
const filterTeacher = ref(null)
const filterQuizId = ref(null)

const clearFilters = () => {
  filterSubjectId.value = null
  filterTeacher.value = null
  filterQuizId.value = null
}

// Subject dropdown: one entry per real subject taught in this class.
const subjectOptions = computed(() => {
  const seen = new Map()
  subjects.value.forEach(row => {
    if (row.subject_id != null && !seen.has(row.subject_id)) seen.set(row.subject_id, row.subject)
  })
  return [...seen].map(([id, name]) => ({ id, name }))
})

// Teacher dropdown narrows to only the teacher(s) who teach the selected
// subject, so picking a subject first makes finding the right teacher easier.
const teacherOptions = computed(() => {
  const relevant = filterSubjectId.value
    ? subjects.value.filter(row => row.subject_id === filterSubjectId.value)
    : subjects.value
  return [...new Set(relevant.map(row => row.teacher).filter(Boolean))]
})

// Quiz dropdown narrows to whichever subject/teacher is selected, since a
// quiz belongs to exactly one subject.
const quizOptions = computed(() => quizList.value.filter(q =>
  (!filterSubjectId.value || q.subject_id === filterSubjectId.value) &&
  (!filterTeacher.value || q.teacher === filterTeacher.value)
))

const quizzesMatchingFilter = computed(() => quizOptions.value.filter(q =>
  !filterQuizId.value || q.id === filterQuizId.value
))

// Selecting a subject can invalidate an already-picked teacher/quiz that
// doesn't belong to it, so drop those rather than showing a stale filter.
watch(filterSubjectId, () => {
  if (filterTeacher.value && !teacherOptions.value.includes(filterTeacher.value)) {
    filterTeacher.value = null
  }
})

watch([filterSubjectId, filterTeacher], () => {
  if (filterQuizId.value && !quizOptions.value.some(q => q.id === filterQuizId.value)) {
    filterQuizId.value = null
  }
})

const hasActiveFilter = computed(() => Boolean(filterSubjectId.value || filterTeacher.value || filterQuizId.value))

// A student's completed/total/points/score, scoped to whichever quizzes
// match the current Subject/Teacher/Quiz filters (all quizzes when none set).
const effectiveStats = (std) => {
  const matchingIds = new Set((hasActiveFilter.value ? quizzesMatchingFilter.value : quizList.value).map(q => q.id))
  const results = (std.quiz_results || []).filter(r => matchingIds.has(r.quiz_id))
  const points = results.reduce((sum, r) => sum + r.points, 0)
  const totalPoints = results.reduce((sum, r) => sum + r.total_points, 0)

  return {
    completed: results.length,
    total: matchingIds.size,
    points,
    totalPoints,
    score: totalPoints > 0 ? Math.round((points / totalPoints) * 100) : null,
  }
}

const displayTotalQuizzes = computed(() => hasActiveFilter.value ? quizzesMatchingFilter.value.length : classData.value.total_quizzes)

const displayStats = computed(() => {
  const results = studentsList.value.map(effectiveStats)
  const scored = results.filter(r => r.score !== null)
  const passed = scored.filter(r => r.score >= 50).length

  return {
    passed,
    failed: scored.length - passed,
    avg_score: scored.length ? Math.round(scored.reduce((sum, r) => sum + r.score, 0) / scored.length) : 0,
  }
})

const filteredStudents = computed(() => {
  if (!studentSearch.value.trim()) return studentsList.value
  const term = studentSearch.value.toLowerCase()
  return studentsList.value.filter(s =>
    s.name?.toLowerCase().includes(term) ||
    s.name_kh?.toLowerCase().includes(term) ||
    s.username?.toLowerCase().includes(term) ||
    s.student_id?.toLowerCase().includes(term)
  )
})

// Pre-computes each row's filter-scoped stats once, instead of recalculating
// on every template access.
const displayedStudents = computed(() => filteredStudents.value.map(std => ({ ...std, effective: effectiveStats(std) })))

const teacherNames = computed(() => [...new Set(subjects.value.map(row => row.teacher).filter(Boolean))])

// Actions
const openStudentDetail = (std) => {
  selectedStudent.value = std
  showDetailDialog.value = true
}

const exporting = ref(false)

// Exports whatever the Subject/Teacher/Quiz filters above are currently
// scoped to (the whole class when none are set) — no extra dialog/picker.
const handleExport = async (format) => {
  exporting.value = true
  try {
    const response = await api.get(`/classes/${props.id}/export`, {
      params: {
        subject_id: filterSubjectId.value || undefined,
        teacher: filterTeacher.value || undefined,
        quiz_id: filterQuizId.value || undefined,
        format,
      },
      responseType: 'blob',
    })
    const disposition = response.headers['content-disposition'] || ''
    const filename = disposition.match(/filename="?([^"]+)"?/)?.[1] || `${classData.value.code}-results.${format}`
    const blobUrl = URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href = blobUrl
    link.download = filename
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(blobUrl)
    toast.add({ severity: 'success', summary: 'Results exported', detail: `Downloaded as .${format}.`, life: 3000 })
  } catch (error) {
    toast.add({ summary: 'Failed to export results', ...toastFromError(error) })
  } finally {
    exporting.value = false
  }
}
</script>

<style scoped>
:deep(.custom-filter-dropdown) {
  background-color: #f8fafc !important;
  border: 1px solid #e2e8f0 !important;
  border-radius: 0.75rem !important;
  font-size: 0.75rem !important;
  transition: all 0.2s ease;
  height: 38px;
  display: flex;
  align-items: center;
}

:deep(.custom-filter-dropdown:hover) {
  border-color: #cbd5e1 !important;
  background-color: #ffffff !important;
}

:deep(.custom-filter-dropdown.p-focus) {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
  background-color: #ffffff !important;
}

:deep(.custom-filter-dropdown .p-dropdown-label) {
  font-size: 0.75rem !important;
  padding: 0.4rem 0.75rem !important;
  color: #334155 !important;
}

:deep(.custom-filter-dropdown .p-dropdown-trigger),
:deep(.custom-filter-dropdown .p-dropdown-clear-icon) {
  color: #94a3b8 !important;
  width: 2rem !important;
}
</style>
