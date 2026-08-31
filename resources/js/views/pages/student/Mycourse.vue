<template>
  <div class="min-h-screen  -m-6 p-6 font-sans space-y-6">

    <!-- ======= PAGE TITLE ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#D8E7EC] shadow-sm">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#D8E7EC]/40 flex items-center justify-center text-[#E4AC40]">
          <i class="pi pi-book text-xl text-[#002060]"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-[#002060] m-0">My Courses</h2>
          <p class="text-xs text-slate-500 m-0 mt-0.5">
            <span class="font-bold text-[#002060]">{{ filteredCourses.length }}</span> of {{ courses.length }} subject{{ courses.length === 1 ? '' : 's' }} this semester
          </p>
        </div>
      </div>
    </div>

    <!-- ======= FILTERS BAR ======= -->
    <div v-if="courses.length" class="bg-white p-4 rounded-2xl border border-[#D8E7EC] shadow-xs space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Class</label>
          <Dropdown
            v-model="classFilter"
            :options="classOptions"
            option-label="label"
            option-value="value"
            placeholder="All Classes"
            showClear
            size="small"
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] focus:!border-[#63C7DF] !rounded-xl text-xs"
          />
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Subject</label>
          <Dropdown
            v-model="subjectFilter"
            :options="subjectOptions"
            option-label="label"
            option-value="value"
            placeholder="All Subjects"
            showClear
            size="small"
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] focus:!border-[#63C7DF] !rounded-xl text-xs"
          />
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Shift</label>
          <Dropdown
            v-model="shiftFilter"
            :options="shiftOptions"
            option-label="label"
            option-value="value"
            placeholder="All Shifts"
            showClear
            size="small"
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] focus:!border-[#63C7DF] !rounded-xl text-xs"
          />
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Academic Year</label>
          <Dropdown
            v-model="academicYearFilter"
            :options="academicYearOptions"
            option-label="label"
            option-value="value"
            placeholder="All Years"
            showClear
            size="small"
            class="w-full !bg-[#F8F8F8] !border-[#D8E7EC] focus:!border-[#63C7DF] !rounded-xl text-xs"
          />
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Search</label>
          <div class="relative w-full">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText
              v-model="searchQuery"
              size="small"
              placeholder="Subject, class..."
              class="w-full !pl-9 !pr-3 !bg-[#F8F8F8] !border-[#D8E7EC] focus:!border-[#63C7DF] !rounded-xl !text-xs"
            />
          </div>
        </div>

      </div>
    </div>

    <!-- ======= LOADING STATE ======= -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-[#D8E7EC]">
      <i class="pi pi-spin pi-spinner text-3xl text-[#002060] mb-3"></i>
      <span class="text-xs font-semibold text-slate-500">Loading your subjects...</span>
    </div>

    <!-- ======= EMPTY STATE (no enrollments at all) ======= -->
    <div v-else-if="!courses.length" class="bg-white rounded-2xl border border-[#D8E7EC] p-12 text-center shadow-sm max-w-2xl mx-auto my-8">
      <div class="w-20 h-20 mx-auto rounded-full bg-[#D8E7EC]/40 flex items-center justify-center mb-4 relative">
        <i class="pi pi-folder-open text-4xl text-[#002060]"></i>
        <span class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-[#E4AC40] text-white flex items-center justify-center text-xs font-bold shadow-md">
          <i class="pi pi-exclamation text-xs"></i>
        </span>
      </div>

      <h3 class="text-base font-bold text-[#002060] m-0">No Enrolled Courses Found</h3>
      <p class="text-xs text-slate-500 max-w-sm mx-auto mt-2 leading-relaxed">
        You're not enrolled in any subjects for this semester yet. Please contact your academic advisor or administrator to get assigned to a class.
      </p>

      <div class="mt-6 flex justify-center gap-3">
        <button @click="fetchCourses" class="px-4 py-2 bg-[#D8E7EC]/50 hover:bg-[#D8E7EC] text-[#002060] font-semibold text-xs rounded-xl transition-all cursor-pointer border-0 flex items-center gap-2">
          <i class="pi pi-refresh text-xs"></i>
          <span>Refresh Data</span>
        </button>
      </div>
    </div>

    <!-- ======= NO FILTER MATCHES ======= -->
    <div v-else-if="!filteredCourses.length" class="text-center py-16 bg-white rounded-2xl border border-[#D8E7EC]">
      <div class="w-12 h-12 rounded-full bg-[#F8F8F8] text-[#002060] flex items-center justify-center mx-auto mb-3 border border-[#D8E7EC]">
        <i class="pi pi-inbox text-xl"></i>
      </div>
      <p class="text-xs font-semibold text-slate-500 m-0">No courses match your filters.</p>
    </div>

    <!-- ======= COURSES GRID ======= -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <div v-for="course in filteredCourses" :key="`${course.id}-${course.classId}`"
        class="bg-white rounded-2xl border border-[#D8E7EC] shadow-xs hover:shadow-lg hover:border-[#63C7DF] transition-all flex flex-col justify-between overflow-hidden group">

        <!-- Course Header Top -->
        <div class="p-5 space-y-4">
          <div class="flex items-center justify-between gap-2 flex-wrap">
            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-[#002060] text-white shadow-xs">
              {{ course.code }}
            </span>
            <span v-if="course.shift" class="inline-flex items-center text-[10px] font-bold px-2.5 py-1 rounded-md bg-[#002060]/5 text-[#002060] border border-[#002060]/10">
              <i class="pi pi-clock text-[9px] mr-1.5"></i>{{ course.shift }}
            </span>
          </div>

          <div>
            <h3 class="text-base font-bold text-slate-800 group-hover:text-[#002060] transition-colors m-0 leading-snug">
              {{ course.title }}
            </h3>
            <p class="text-xs text-slate-500 m-0 mt-2 flex items-center gap-1.5">
              <i class="pi pi-users text-xs text-[#E4AC40]"></i>
              <span>{{ course.className }}</span>
            </p>
            <p class="text-xs text-slate-500 m-0 mt-1 flex items-center gap-1.5">
              <i class="pi pi-user text-xs text-[#63C7DF]"></i>
              <span>Teacher: <b class="text-slate-700">{{ course.teacher }}</b></span>
            </p>
          </div>

          <!-- Class metadata -->
          <div class="grid grid-cols-2 gap-2 text-center">
            <!-- Room field not needed in the UI
            <div class="bg-[#F8F8F8] p-2 rounded-xl border border-slate-100">
              <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Room</span>
              <span class="text-xs font-bold text-[#002060]">{{ course.room || '—' }}</span>
            </div>
            -->
            <div class="bg-[#F8F8F8] p-2 rounded-xl border border-slate-100 col-span-2">
              <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Academic Year</span>
              <span class="text-xs font-bold text-[#002060]">{{ course.academicYear || '—' }}</span>
            </div>
          </div>

          <!-- Course Progress / Quizzes Info -->
          <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-center">
            <div class="bg-[#F8F8F8] p-2.5 rounded-xl border border-slate-100">
              <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Quizzes</span>
              <span class="text-sm font-bold text-[#002060]">{{ course.totalQuizzes }} Quizzes</span>
            </div>
            <div class="bg-emerald-50/60 p-2.5 rounded-xl border border-emerald-100/60">
              <span class="block text-[10px] text-emerald-600 font-bold uppercase tracking-wider">Completed</span>
              <span class="text-sm font-bold text-emerald-700">{{ course.completedQuizzes }} Quizzes</span>
            </div>
          </div>
        </div>

        <!-- Course Card Footer / Button -->
        <div class="px-5 py-3.5 bg-[#F8F8F8] border-t border-[#D8E7EC] flex items-center justify-between">
          <div class="flex items-center gap-1.5">
            <div class="w-2 h-2 rounded-full bg-[#E4AC40]"></div>
            <span class="text-xs font-bold text-slate-600">
              {{ course.totalQuizzes ? Math.round((course.completedQuizzes / course.totalQuizzes) * 100) : 0 }}% complete
            </span>
          </div>

          <router-link :to="{ name: 'student.courseWorkspace', params: { classId: course.classId, subjectId: course.id } }"
            class="px-4 py-2 bg-[#002060] hover:bg-[#001848] text-white font-semibold text-xs rounded-xl transition-all no-underline flex items-center gap-2 shadow-md shadow-[#002060]/20">
            <span>View Quizzes</span>
            <i class="pi pi-arrow-right text-xs text-[#E4AC40]"></i>
          </router-link>
        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import api, { extractError } from '../../../api'

const courses = ref([])
const loading = ref(true)

const searchQuery = ref('')
const classFilter = ref('')
const subjectFilter = ref('')
const shiftFilter = ref('')
const academicYearFilter = ref('')

async function fetchCourses() {
  loading.value = true
  try {
    const { data } = await api.get('/student/courses')
    courses.value = data.data
  } catch (error) {
    console.error(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(fetchCourses)

function uniqueOptions(field) {
  const seen = new Set()
  courses.value.forEach(c => { if (c[field]) seen.add(c[field]) })
  return Array.from(seen).sort().map(v => ({ label: v, value: v }))
}

const classOptions = computed(() => uniqueOptions('className'))
const subjectOptions = computed(() => uniqueOptions('title'))
const shiftOptions = computed(() => uniqueOptions('shift'))
const academicYearOptions = computed(() => uniqueOptions('academicYear'))

const filteredCourses = computed(() => {
  const q = searchQuery.value.toLowerCase()

  return courses.value.filter(c => {
    if (classFilter.value && c.className !== classFilter.value) return false
    if (subjectFilter.value && c.title !== subjectFilter.value) return false
    if (shiftFilter.value && c.shift !== shiftFilter.value) return false
    if (academicYearFilter.value && c.academicYear !== academicYearFilter.value) return false

    if (!q) return true

    return (
      c.title?.toLowerCase().includes(q) ||
      c.className?.toLowerCase().includes(q) ||
      c.major?.toLowerCase().includes(q) ||
      c.teacher?.toLowerCase().includes(q)
    )
  })
})
</script>
