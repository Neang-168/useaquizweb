<template>
  <div class="space-y-6 w-full max-w-7xl mx-auto font-sans pb-10">
    
    <!-- 1. Header Page & View Toggle -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-white rounded-2xl border border-[#D8E7EC] shadow-xs">
      <div>
        <h1 class="text-2xl font-bold text-[#002060] tracking-tight m-0 flex items-center gap-2">
          <i class="pi pi-graduation-cap text-[#E4AC40]"></i> My Classes
        </h1>
        <p class="text-xs text-slate-500 mt-1 m-0">
          <span class="font-bold text-[#002060]">{{ filteredClasses.length }}</span> of {{ myClasses.length }} class{{ myClasses.length === 1 ? '' : 'es' }}
          &middot; <span class="font-bold text-[#002060]">{{ totalStudentsAcrossClasses }}</span> students total
        </p>
      </div>

      <div class="flex items-center gap-3 self-start sm:self-auto">
        <!-- Grid / Table View Toggle -->
        <div class="flex items-center gap-1 bg-[#F8F8F8] p-1.5 rounded-xl border border-[#D8E7EC]">
          <Button
            label="Grid"
            icon="pi pi-th-large"
            size="small"
            :class="viewMode === 'grid' ? '!bg-[#002060] !text-white shadow-xs' : '!bg-transparent !text-slate-500 hover:!text-[#002060]'"
            class="!border-0 !rounded-lg !text-xs !font-bold !px-3 !py-1.5 transition-all"
            @click="viewMode = 'grid'"
          />
          <Button
            label="Table"
            icon="pi pi-list"
            size="small"
            :class="viewMode === 'table' ? '!bg-[#002060] !text-white shadow-xs' : '!bg-transparent !text-slate-500 hover:!text-[#002060]'"
            class="!border-0 !rounded-lg !text-xs !font-bold !px-3 !py-1.5 transition-all"
            @click="viewMode = 'table'"
          />
        </div>

        <span class="hidden md:inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold bg-[#002060]/5 text-[#002060] border border-[#002060]/10">
          <i class="pi pi-calendar text-[#63C7DF]"></i> Active Semester
        </span>
      </div>
    </div>

    <!-- 2. Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-[#D8E7EC] shadow-xs space-y-3">
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
              placeholder="Class, subject..."
              class="w-full !pl-9 !pr-3 !bg-[#F8F8F8] !border-[#D8E7EC] focus:!border-[#63C7DF] !rounded-xl !text-xs"
            />
          </div>
        </div>

      </div>
    </div>

    <!-- 3A. Grid Cards View (Box) -->
    <div v-if="viewMode === 'grid' && filteredClasses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="item in filteredClasses"
        :key="item.id"
        class="bg-white border border-[#D8E7EC] rounded-2xl p-5 hover:border-[#63C7DF] hover:shadow-lg transition-all duration-200 flex flex-col justify-between group"
      >
        <div>
          <!-- Header Card: Badge Shift & Academic Year -->
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-1 rounded-md bg-[#002060] text-white">
              <i class="pi pi-clock text-[9px] mr-1.5 text-white"></i>{{ item.shift }}
            </span>
            <span class="text-[11px] font-bold text-[#E4AC40] bg-[#E4AC40]/10 px-2 py-0.5 rounded-md border border-[#E4AC40]/20">
              {{ item.academicYear }}
            </span>
          </div>

          <!-- Subject Name & Code -->
          <div class="space-y-1">
            <p class="text-base font-bold text-[#002060] m-0 flex items-center gap-2 flex-wrap">
              <i class="pi pi-book text-sm text-[#E4AC40]"></i> 
              <span>{{ item.subject }}</span>
              
              <button
                v-if="item.subject_code"
                type="button"
                title="Copy subject code"
                class="inline-flex items-center gap-1 text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-[#63C7DF]/10 text-[#002060] border border-[#63C7DF]/30 hover:bg-[#63C7DF]/20 transition-colors cursor-pointer"
                @click="copySubjectCode(item.subject_code)"
              >
                {{ item.subject_code }}
                <i class="pi pi-copy text-[9px]"></i>
              </button>
            </p>
            
            <h3 class="text-sm font-bold text-slate-600 m-0 pl-5">
              {{ item.className }}
            </h3>
          </div>

          <!-- Key info grid -->
          <div class="mt-4 pt-4 border-t border-[#D8E7EC]/60 grid grid-cols-2 gap-2.5">
            <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40">
              <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Students</p>
              <p class="text-sm font-bold text-[#002060] m-0 mt-0.5 flex items-center gap-1.5">
                <i class="pi pi-users text-xs text-[#63C7DF]"></i> {{ item.totalStudents }}
              </p>
            </div>
            
            <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40">
              <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Room</p>
              <p class="text-sm font-bold text-[#002060] m-0 mt-0.5 flex items-center gap-1.5">
                <i class="pi pi-building text-xs text-[#63C7DF]"></i> {{ item.room || '—' }}
              </p>
            </div>

            <div class="bg-[#F8F8F8] rounded-xl p-2.5 border border-[#D8E7EC]/40 col-span-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase m-0">Major</p>
              <p class="text-xs font-bold text-slate-700 m-0 mt-0.5 truncate">{{ item.major || '—' }}</p>
            </div>
          </div>
        </div>

        <!-- Action Button -->
        <div class="mt-5 pt-3 border-t border-[#D8E7EC]/60">
          <Button 
            as="router-link" 
            size="small"
            :to="{ name: 'teacher.classWorkspace', params: { assignmentId: item.id } }"
            :label="`Manage ${item.subject}`" 
            icon="pi pi-cog"
            class="w-full !bg-[#002060] hover:!bg-[#001540] !border-[#002060] !text-white !rounded-xl !text-xs !font-semibold no-underline shadow-xs transition-colors"
          />
        </div>
      </div>
    </div>

    <!-- 3B. Table View -->
    <div v-else-if="viewMode === 'table' && filteredClasses.length > 0" class="bg-white border border-[#D8E7EC] rounded-2xl shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#F8F8F8] border-b border-[#D8E7EC] text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              <th class="py-3.5 px-4">Subject</th>
              <th class="py-3.5 px-4">Class</th>
              <th class="py-3.5 px-4">Major</th>
              <th class="py-3.5 px-4 text-center">Shift</th>
              <th class="py-3.5 px-4 text-center">Year</th>
              <th class="py-3.5 px-4 text-center">Room</th>
              <th class="py-3.5 px-4 text-center">Students</th>
              <th class="py-3.5 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#D8E7EC]/60 text-xs">
            <tr v-for="item in filteredClasses" :key="item.id" class="hover:bg-[#63C7DF]/5 transition-colors">
              <td class="py-3 px-4">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-[#002060]">{{ item.subject }}</span>
                  <button
                    v-if="item.subject_code"
                    type="button"
                    title="Copy subject code"
                    class="inline-flex items-center gap-1 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-[#63C7DF]/10 text-[#002060] border border-[#63C7DF]/30 hover:bg-[#63C7DF]/20 cursor-pointer"
                    @click="copySubjectCode(item.subject_code)"
                  >
                    {{ item.subject_code }}
                    <i class="pi pi-copy text-[8px]"></i>
                  </button>
                </div>
              </td>
              <td class="py-3 px-4 font-semibold text-slate-700">{{ item.className }}</td>
              <td class="py-3 px-4 text-slate-600 max-w-[180px] truncate">{{ item.major || '—' }}</td>
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#002060] text-white">
                  {{ item.shift }}
                </span>
              </td>
              <td class="py-3 px-4 text-center font-bold text-[#E4AC40]">{{ item.academicYear }}</td>
              <td class="py-3 px-4 text-center font-semibold text-slate-600">{{ item.room || '—' }}</td>
              <td class="py-3 px-4 text-center font-bold text-[#002060]">
                <i class="pi pi-users text-xs text-[#63C7DF] mr-1"></i>{{ item.totalStudents }}
              </td>
              <td class="py-3 px-4 text-right">
                <Button 
                  as="router-link" 
                  size="small"
                  :to="{ name: 'teacher.classWorkspace', params: { assignmentId: item.id } }"
                  label="Manage" 
                  icon="pi pi-cog"
                  class="!bg-[#002060] hover:!bg-[#001540] !border-[#002060] !text-white !rounded-xl !text-xs !font-semibold no-underline shadow-xs"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredClasses.length === 0" class="text-center py-16 bg-white rounded-2xl border border-[#D8E7EC]">
      <div class="w-12 h-12 rounded-full bg-[#F8F8F8] text-[#002060] flex items-center justify-center mx-auto mb-3 border border-[#D8E7EC]">
        <i class="pi pi-inbox text-xl"></i>
      </div>
      <p class="text-xs font-semibold text-slate-500 m-0">
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

const viewMode = ref('grid') // Mode: 'grid' or 'table'
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

const copySubjectCode = async (code) => {
  try {
    await navigator.clipboard.writeText(code)
  } catch (error) {
    // Clipboard API fail silently
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