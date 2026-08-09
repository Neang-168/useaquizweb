<template>
  <div class="space-y-6">
    <!-- 1. Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">My Classes</h1>
        <p class="text-xs text-slate-500 mt-1">The classes you teach, and the students enrolled in each one.</p>
      </div>

      <!-- Search & Filter -->
      <div class="flex items-center gap-3">
        <div class="relative">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
          <InputText
            v-model="searchQuery"
            size="small"
            placeholder="Search classes..."
            class="!pl-9 !pr-3 !bg-white !border-slate-200 !rounded-lg !text-xs w-48 sm:w-64"
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
          <p class="text-xs text-slate-500 mt-1">Major: <span class="font-semibold text-slate-700">{{ item.major }}</span></p>

          <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400">Subject taught:</span>
              <span class="font-bold text-slate-700">{{ item.subject }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400">Total students:</span>
              <span class="font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">{{ item.totalStudents }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400">Room:</span>
              <span class="font-medium text-slate-600">{{ item.room }}</span>
            </div>
          </div>
        </div>

        <!-- Action Button -->
        <div class="mt-5 pt-3 border-t border-slate-100 flex gap-2">
          <Button as="router-link" size="small"
            :to="{ name: 'teacher.classWorkspace', params: { assignmentId: item.id } }"
            label="Manage Class (Quizzes, Scores, Feedback)" icon="pi pi-cog"
            class="w-full !bg-slate-900 hover:!bg-indigo-600 !border-slate-900 !text-white !rounded-lg !text-xs no-underline"
          />
        </div>
      </div>
    </div>

    <div v-if="filteredClasses.length === 0" class="text-center py-12 bg-white rounded-2xl border border-slate-200">
      <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
      <p class="text-xs text-slate-500">You haven't been assigned to any classes yet.</p>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import api, { extractError } from '../../../api'

const searchQuery = ref('')

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

// Filter Search
const filteredClasses = computed(() => {
  if (!searchQuery.value) return myClasses.value
  const q = searchQuery.value.toLowerCase()
  return myClasses.value.filter(c =>
    c.className?.toLowerCase().includes(q) ||
    c.major?.toLowerCase().includes(q) ||
    c.subject?.toLowerCase().includes(q)
  )
})
</script>