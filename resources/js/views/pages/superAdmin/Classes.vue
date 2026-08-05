<template>
  <div class="space-y-6">
    
    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-users text-blue-600 text-2xl"></i>
          Classes Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          គ្រប់គ្រងថ្នាក់សិក្សា កំណត់បន្ទប់សិក្សា វេនសិក្សា និងចំនួនសិស្ស/និស្សិតតាមថ្នាក់នីមួយៗ
        </p>
      </div>

      <Button 
        label="Add New Class" 
        icon="pi pi-plus" 
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Classes</span>
          <span class="text-2xl font-bold text-slate-800">{{ classes.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
          <i class="pi pi-users"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Students Enrolled</span>
          <span class="text-2xl font-bold text-indigo-600">{{ totalStudents }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
          <i class="pi pi-id-card"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active Classes</span>
          <span class="text-2xl font-bold text-emerald-600">{{ activeClassesCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
          <i class="pi pi-check-circle"></i>
        </div>
      </div>
    </div>

    <!-- ======= MAIN CONTENT CARD ======= -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
      
      <!-- Toolbar Bar: Search & View Mode Switcher -->
      <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3">
        <div class="relative w-full sm:w-80">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <InputText 
            v-model="filters['global'].value" 
            placeholder="Search class code or name..." 
            class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
          />
        </div>

        <div class="flex items-center justify-between w-full sm:w-auto gap-4">
          <span class="text-xs text-slate-400">
            Showing <b>{{ filteredClasses.length }}</b> entries
          </span>

          <!-- View Mode Switcher Buttons -->
          <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200">
            <button 
              @click="viewMode = 'grid'"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
              :class="viewMode === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
            >
              <i class="pi pi-th-large"></i> Cards
            </button>
            <button 
              @click="viewMode = 'list'"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
              :class="viewMode === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
            >
              <i class="pi pi-list"></i> List
            </button>
          </div>
        </div>
      </div>

      <!-- ======= 1. LIST VIEW (DataTable) ======= -->
      <DataTable 
        v-if="viewMode === 'list'"
        :value="filteredClasses" 
        dataKey="id" 
        paginator 
        :rows="5" 
        :rowsPerPageOptions="[5, 10, 20]"
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <template #empty>
          <div class="text-center py-8 text-slate-400 text-sm">
            No classes found.
          </div>
        </template>

        <!-- Class Code -->
        <Column field="code" header="CODE" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
              {{ data.code }}
            </span>
          </template>
        </Column>

        <!-- Class Name -->
        <Column field="name" header="CLASS NAME" sortable class="!py-3.5">
          <template #body="{ data }">
            <div>
              <div class="font-semibold text-slate-800 text-sm">{{ data.name }}</div>
              <div class="text-xs text-slate-400 mt-0.5">{{ data.department }}</div>
            </div>
          </template>
        </Column>

        <!-- Stage & Shift -->
        <Column field="stage" header="STAGE / SHIFT" sortable class="!py-3.5">
          <template #body="{ data }">
            <div class="flex flex-col gap-1">
              <span class="text-xs font-medium text-slate-700">
                <i class="pi pi-step-forward text-slate-400 mr-1"></i>{{ data.stage }}
              </span>
              <span class="text-[11px] text-slate-500">
                <i class="pi pi-clock text-slate-400 mr-1"></i>{{ data.shift }}
              </span>
            </div>
          </template>
        </Column>

        <!-- Room -->
        <Column field="room" header="ROOM" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-lg">
              <i class="pi pi-building mr-1 text-indigo-400"></i>{{ data.room }}
            </span>
          </template>
        </Column>

        <!-- Enrolled / Capacity -->
        <Column field="students_count" header="STUDENTS" sortable class="!py-3.5">
          <template #body="{ data }">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-slate-700">
                {{ data.students_count }} / {{ data.capacity }}
              </span>
              <div class="w-16 bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div 
                  class="h-full rounded-full" 
                  :class="data.students_count >= data.capacity ? 'bg-rose-500' : 'bg-blue-500'"
                  :style="{ width: Math.min((data.students_count / data.capacity) * 100, 100) + '%' }"
                ></div>
              </div>
            </div>
          </template>
        </Column>

        <!-- Status -->
        <Column field="status" header="STATUS" sortable class="!py-3.5">
          <template #body="{ data }">
            <span 
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
              :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="data.status === 'Active' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
              {{ data.status }}
            </span>
          </template>
        </Column>

        <!-- Actions -->
        <Column header="ACTIONS" class="!text-right !py-3.5">
          <template #body="{ data }">
            <div class="flex items-center justify-end gap-2">
              <Button 
                icon="pi pi-pencil" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-blue-600 hover:!bg-slate-100 !border-0"
                @click="editClass(data)"
              />
              <Button 
                icon="pi pi-trash" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
                @click="confirmDeleteClass(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>

      <!-- ======= 2. CARD VIEW ======= -->
      <div v-else class="p-6">
        <div v-if="filteredClasses.length === 0" class="text-center py-8 text-slate-400 text-sm">
          No classes found.
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div 
            v-for="cls in filteredClasses" 
            :key="cls.id"
            class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between"
          >
            <!-- Card Top Bar -->
            <div>
              <div class="flex items-start justify-between gap-2 mb-3">
                <div>
                  <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                    {{ cls.code }}
                  </span>
                  <h3 class="font-bold text-slate-800 text-base mt-2 mb-0">{{ cls.name }}</h3>
                  <p class="text-xs text-slate-400 m-0">{{ cls.department }}</p>
                </div>

                <span 
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium shrink-0"
                  :class="cls.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="cls.status === 'Active' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                  {{ cls.status }}
                </span>
              </div>

              <!-- Card Information Rows -->
              <div class="space-y-2 py-3 border-y border-slate-100 my-3 text-xs text-slate-600">
                <div class="flex items-center justify-between">
                  <span class="text-slate-400 flex items-center gap-1.5">
                    <i class="pi pi-step-forward text-slate-400"></i> Stage
                  </span>
                  <span class="font-medium text-slate-700">{{ cls.stage }}</span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-slate-400 flex items-center gap-1.5">
                    <i class="pi pi-clock text-slate-400"></i> Shift
                  </span>
                  <span class="font-medium text-slate-700">{{ cls.shift }}</span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-slate-400 flex items-center gap-1.5">
                    <i class="pi pi-building text-slate-400"></i> Room
                  </span>
                  <span class="font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                    {{ cls.room }}
                  </span>
                </div>
              </div>

              <!-- Students Progress Bar -->
              <div class="mt-3">
                <div class="flex justify-between text-xs font-semibold mb-1.5">
                  <span class="text-slate-500">Students Enrolled</span>
                  <span class="text-slate-800">{{ cls.students_count }} / {{ cls.capacity }}</span>
                </div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                  <div 
                    class="h-full rounded-full transition-all duration-300" 
                    :class="cls.students_count >= cls.capacity ? 'bg-rose-500' : 'bg-blue-600'"
                    :style="{ width: Math.min((cls.students_count / cls.capacity) * 100, 100) + '%' }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Card Bottom Actions -->
            <div class="flex items-center justify-end gap-2 pt-4 mt-4 border-t border-slate-100">
              <Button 
                label="Edit" 
                icon="pi pi-pencil" 
                class="!py-1.5 !px-3 !text-xs !bg-slate-50 hover:!bg-slate-100 !text-slate-600 !border-slate-200 !rounded-xl"
                @click="editClass(cls)"
              />
              <Button 
                label="Delete" 
                icon="pi pi-trash" 
                class="!py-1.5 !px-3 !text-xs !bg-rose-50 hover:!bg-rose-100 !text-rose-600 !border-rose-100 !rounded-xl"
                @click="confirmDeleteClass(cls)"
              />
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog 
      v-model:visible="classDialog" 
      :header="isEdit ? 'Edit Class' : 'Create New Class'" 
      :modal="true" 
      class="w-full max-w-lg"
    >
      <div class="space-y-4 pt-2">
        <!-- Class Code & Name -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Code *</label>
            <InputText 
              v-model="classForm.code" 
              placeholder="e.g. M1-CS" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class Name *</label>
            <InputText 
              v-model="classForm.name" 
              placeholder="e.g. Class M1-CS (Year 1 Morning)" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
        </div>

        <!-- Department / Major -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department / Major *</label>
          <Dropdown 
            v-model="classForm.department" 
            :options="departmentOptions" 
            placeholder="Select Department" 
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          />
        </div>

        <!-- Stage & Shift -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Stage / Level *</label>
            <Dropdown 
              v-model="classForm.stage" 
              :options="stageOptions" 
              placeholder="Select Stage" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shift *</label>
            <Dropdown 
              v-model="classForm.shift" 
              :options="shiftOptions" 
              placeholder="Select Shift" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
        </div>

        <!-- Room & Capacity -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Room *</label>
            <InputText 
              v-model="classForm.room" 
              placeholder="e.g. Room 301" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Capacity *</label>
            <InputText 
              v-model="classForm.capacity" 
              type="number" 
              placeholder="35" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
        </div>

        <!-- Status -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
          <Dropdown 
            v-model="classForm.status" 
            :options="['Active', 'Inactive']" 
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="classDialog = false" />
          <Button label="Save Class" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveClass" />
        </div>
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// PrimeVue Components Import
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'

// View Mode State: 'grid' or 'list'
const viewMode = ref('grid')

// Mock Data: Classes
const classes = ref([
  { id: 1, code: 'M1-CS', name: 'Class M1-CS', department: 'Computer Science', stage: 'Year 1 (Foundation)', shift: 'Morning Shift', room: 'Room 301', students_count: 28, capacity: 35, status: 'Active' },
  { id: 2, code: 'A2-CS', name: 'Class A2-CS', department: 'Computer Science', stage: 'Year 2', shift: 'Afternoon Shift', room: 'Lab 2', students_count: 32, capacity: 35, status: 'Active' },
  { id: 3, code: 'E3-BIT', name: 'Class E3-BIT', department: 'Business Information Technology', stage: 'Year 3', shift: 'Evening Shift', room: 'Room 405', students_count: 24, capacity: 30, status: 'Active' },
  { id: 4, code: 'W4-LAW', name: 'Class W4-LAW', department: 'Law', stage: 'Year 4', shift: 'Weekend Shift', room: 'Room 102', students_count: 15, capacity: 40, status: 'Inactive' },
])

const departmentOptions = ref([
  'Computer Science',
  'Business Information Technology',
  'Law',
  'Marketing'
])

const stageOptions = ref([
  'Year 1 (Foundation)',
  'Year 2',
  'Year 3',
  'Year 4'
])

const shiftOptions = ref([
  'Morning Shift',
  'Afternoon Shift',
  'Evening Shift',
  'Weekend Shift'
])

// Search Filter
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// Computed Filter for Grid View Search Integration
const filteredClasses = computed(() => {
  const query = filters.value.global.value?.toLowerCase().trim()
  if (!query) return classes.value
  
  return classes.value.filter(c => 
    c.code.toLowerCase().includes(query) ||
    c.name.toLowerCase().includes(query) ||
    c.department.toLowerCase().includes(query) ||
    c.room.toLowerCase().includes(query)
  )
})

// Dialog States & Form
const classDialog = ref(false)
const isEdit = ref(false)
const classForm = ref({
  id: null,
  code: '',
  name: '',
  department: '',
  stage: '',
  shift: '',
  room: '',
  capacity: 35,
  students_count: 0,
  status: 'Active'
})

// Computed Properties
const activeClassesCount = computed(() => classes.value.filter(c => c.status === 'Active').length)
const totalStudents = computed(() => classes.value.reduce((sum, c) => sum + Number(c.students_count || 0), 0))

// Actions
const openNewDialog = () => {
  classForm.value = {
    id: null,
    code: '',
    name: '',
    department: '',
    stage: '',
    shift: '',
    room: '',
    capacity: 35,
    students_count: 0,
    status: 'Active'
  }
  isEdit.value = false
  classDialog.value = true
}

const editClass = (data) => {
  classForm.value = { ...data }
  isEdit.value = true
  classDialog.value = true
}

const saveClass = () => {
  if (!classForm.value.code || !classForm.value.name || !classForm.value.department) return

  if (isEdit.value) {
    const index = classes.value.findIndex(c => c.id === classForm.value.id)
    if (index !== -1) classes.value[index] = { ...classForm.value }
  } else {
    classForm.value.id = Date.now()
    classes.value.unshift({ ...classForm.value })
  }

  classDialog.value = false
}

const confirmDeleteClass = (data) => {
  if (confirm(`Are you sure you want to delete ${data.name}?`)) {
    classes.value = classes.value.filter(c => c.id !== data.id)
  }
}
</script>