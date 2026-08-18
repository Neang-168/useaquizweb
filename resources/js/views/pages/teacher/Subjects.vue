<template>
  <div class="space-y-6">
    <!-- 1. Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">My Subjects</h1>
        <p class="text-xs text-slate-500 mt-1">The subjects you teach, and the classes they're taught in.</p>
      </div>

      <!-- Search -->
      <div class="flex items-center gap-3">
        <div class="relative">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
          <InputText
            v-model="searchQuery"
            size="small"
            placeholder="Search subjects..."
            class="!pl-9 !pr-3 !bg-white !border-slate-200 !rounded-lg !text-xs w-48 sm:w-64"
          />
        </div>
      </div>
    </div>

    <!-- 2. Subjects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="subject in filteredSubjects"
        :key="subject.id"
        class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-indigo-300 hover:shadow-md transition-all"
      >
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

        <div class="mt-4 pt-3 border-t border-slate-100">
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400">Taught in:</span>
            <span class="font-bold text-slate-700">{{ subject.assignedClasses.join(', ') || '—' }}</span>
          </div>
        </div>
      </div>

      <div v-if="filteredSubjects.length === 0" class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-200">
        <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
        <p class="text-xs text-slate-500">No subjects match your search.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import InputText from 'primevue/inputtext'
import api, { extractError } from '../../../api'

const searchQuery = ref('')

const mySubjects = ref([])
const loading = ref(false)

const fetchMySubjects = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/subjects')
    mySubjects.value = data.data
  } catch (error) {
    alert(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(fetchMySubjects)

// Filter Search
const filteredSubjects = computed(() => {
  if (!searchQuery.value) return mySubjects.value
  const q = searchQuery.value.toLowerCase()
  return mySubjects.value.filter(s =>
    s.name?.toLowerCase().includes(q) ||
    s.code?.toLowerCase().includes(q)
  )
})
</script>
