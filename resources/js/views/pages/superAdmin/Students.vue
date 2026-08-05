<template>
  <div class="space-y-6">
    
    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-id-card text-blue-600 text-2xl"></i>
          Students Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          គ្រប់គ្រងទិន្នន័យសិស្ស/និស្សិត ថ្នាក់សិក្សា ទំនាក់ទំនង និងស្ថានភាពសិក្សា
        </p>
      </div>

      <Button 
        label="Add New Student" 
        icon="pi pi-plus" 
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Students</span>
          <span class="text-2xl font-bold text-slate-800">{{ students.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
          <i class="pi pi-users"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active Enrolled</span>
          <span class="text-2xl font-bold text-emerald-600">{{ activeStudentsCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
          <i class="pi pi-check-circle"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Inactive / Suspended</span>
          <span class="text-2xl font-bold text-rose-600">{{ inactiveStudentsCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
          <i class="pi pi-exclamation-triangle"></i>
        </div>
      </div>
    </div>

    <!-- ======= DATA TABLE CARD ======= -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
      
      <!-- Table Header Bar / Search & Filter -->
      <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3">
        <div class="relative w-full sm:w-80">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <InputText 
            v-model="filters['global'].value" 
            placeholder="Search ID, student name, or class..." 
            class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
          />
        </div>
        <div class="text-xs text-slate-400">
          Showing <b>{{ students.length }}</b> entries
        </div>
      </div>

      <!-- PrimeVue DataTable -->
      <DataTable 
        :value="students" 
        v-model:filters="filters"
        dataKey="id" 
        paginator 
        :rows="5" 
        :rowsPerPageOptions="[5, 10, 20]"
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <template #empty>
          <div class="text-center py-8 text-slate-400 text-sm">
            No students found.
          </div>
        </template>

        <!-- Student ID -->
        <Column field="student_id" header="STUDENT ID" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
              {{ data.student_id }}
            </span>
          </template>
        </Column>

        <!-- Student Profile & Name -->
        <Column field="name_en" header="STUDENT NAME" sortable class="!py-3.5">
          <template #body="{ data }">
            <div class="flex items-center gap-3">
              <Avatar :image="data.avatar" :label="data.name_en.charAt(0)" shape="circle" class="!bg-slate-100 !text-slate-600 font-bold" />
              <div>
                <div class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ data.name_kh }} • {{ data.gender }}</div>
              </div>
            </div>
          </template>
        </Column>

        <!-- Class & Shift -->
        <Column field="class_name" header="CLASS / SHIFT" sortable class="!py-3.5">
          <template #body="{ data }">
            <div>
              <div class="text-xs font-semibold text-slate-700 flex items-center gap-1">
                <i class="pi pi-users text-slate-400 text-[11px]"></i>{{ data.class_name }}
              </div>
              <div class="text-[11px] text-indigo-600 font-medium mt-0.5 flex items-center gap-1">
                <i class="pi pi-clock text-indigo-400 text-[11px]"></i>{{ data.shift }}
              </div>
            </div>
          </template>
        </Column>

        <!-- Contact Info -->
        <Column header="CONTACT INFO" class="!py-3.5">
          <template #body="{ data }">
            <div class="flex flex-col gap-1 text-xs">
              <span class="text-slate-700 flex items-center gap-1.5">
                <i class="pi pi-phone text-slate-400 text-[11px]"></i>{{ data.phone }}
              </span>
              <span class="text-slate-400 flex items-center gap-1.5 text-[11px]">
                <i class="pi pi-envelope text-slate-400 text-[11px]"></i>{{ data.email }}
              </span>
            </div>
          </template>
        </Column>

        <!-- Major / Department -->
        <Column field="major" header="MAJOR" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
              {{ data.major }}
            </span>
          </template>
        </Column>

        <!-- Status -->
        <Column field="status" header="STATUS" sortable class="!py-3.5">
          <template #body="{ data }">
            <span 
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
              :class="{
                'bg-emerald-50 text-emerald-700 border border-emerald-200': data.status === 'Active',
                'bg-rose-50 text-rose-700 border border-rose-200': data.status === 'Inactive',
                'bg-amber-50 text-amber-700 border border-amber-200': data.status === 'Suspended'
              }"
            >
              <span 
                class="w-1.5 h-1.5 rounded-full" 
                :class="{
                  'bg-emerald-500': data.status === 'Active',
                  'bg-rose-500': data.status === 'Inactive',
                  'bg-amber-500': data.status === 'Suspended'
                }"
              ></span>
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
                @click="editStudent(data)"
              />
              <Button 
                icon="pi pi-trash" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
                @click="confirmDeleteStudent(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog 
      v-model:visible="studentDialog" 
      :header="isEdit ? 'Edit Student Information' : 'Add New Student'" 
      :modal="true" 
      class="w-full max-w-xl"
    >
      <div class="space-y-4 pt-2">
        <!-- Student ID & Gender -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Student ID *</label>
            <InputText 
              v-model="studentForm.student_id" 
              placeholder="e.g. STU-1001" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Gender *</label>
            <Dropdown 
              v-model="studentForm.gender" 
              :options="['Male', 'Female']" 
              placeholder="Select Gender" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
        </div>

        <!-- Name English & Khmer -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name (English) *</label>
            <InputText 
              v-model="studentForm.name_en" 
              placeholder="e.g. Sok Visal" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name (Khmer)</label>
            <InputText 
              v-model="studentForm.name_kh" 
              placeholder="ឧ. សុខ វិសាល" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
        </div>

        <!-- Class Name & Shift -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class *</label>
            <Dropdown 
              v-model="studentForm.class_name" 
              :options="classOptions" 
              placeholder="Select Class" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shift *</label>
            <Dropdown 
              v-model="studentForm.shift" 
              :options="shiftOptions" 
              placeholder="Select Shift" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
        </div>

        <!-- Major & Phone -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major / Department *</label>
            <Dropdown 
              v-model="studentForm.major" 
              :options="majorOptions" 
              placeholder="Select Major" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Phone Number *</label>
            <InputText 
              v-model="studentForm.phone" 
              placeholder="012 345 678" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
        </div>

        <!-- Email & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email Address</label>
            <InputText 
              v-model="studentForm.email" 
              placeholder="student@school.edu.kh" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown 
              v-model="studentForm.status" 
              :options="['Active', 'Inactive', 'Suspended']" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="studentDialog = false" />
          <Button label="Save Student" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveStudent" />
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
import Avatar from 'primevue/avatar'

// Mock Data: Students
const students = ref([
  { id: 1, student_id: 'STU-1001', name_en: 'Sok Visal', name_kh: 'សុខ វិសាល', gender: 'Male', class_name: 'Class M1-CS', shift: 'Morning Shift', major: 'Computer Science', phone: '012 333 444', email: 'visal.sok@student.edu.kh', status: 'Active', avatar: '' },
  { id: 2, student_id: 'STU-1002', name_en: 'Chan Leakhena', name_kh: 'ចាន់ លក្ខិណា', gender: 'Female', class_name: 'Class A2-CS', shift: 'Afternoon Shift', major: 'Computer Science', phone: '098 111 222', email: 'leakhena.chan@student.edu.kh', status: 'Active', avatar: '' },
  { id: 3, student_id: 'STU-1003', name_en: 'Keo Sovann', name_kh: 'កែវ សុវណ្ណ', gender: 'Male', class_name: 'Class E3-BIT', shift: 'Evening Shift', major: 'BIT', phone: '017 555 666', email: 'sovann.keo@student.edu.kh', status: 'Suspended', avatar: '' },
  { id: 4, student_id: 'STU-1004', name_en: 'Nhem Bopha', name_kh: 'ញឹម បុប្ផា', gender: 'Female', class_name: 'Class W4-LAW', shift: 'Weekend Shift', major: 'Law', phone: '088 777 888', email: 'bopha.nhem@student.edu.kh', status: 'Inactive', avatar: '' },
])

const classOptions = ref([
  'Class M1-CS',
  'Class A2-CS',
  'Class E3-BIT',
  'Class W4-LAW'
])

const shiftOptions = ref([
  'Morning Shift',
  'Afternoon Shift',
  'Evening Shift',
  'Weekend Shift'
])

const majorOptions = ref([
  'Computer Science',
  'BIT',
  'Law',
  'Marketing'
])

// Search Filter
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// Dialog States & Form
const studentDialog = ref(false)
const isEdit = ref(false)
const studentForm = ref({
  id: null,
  student_id: '',
  name_en: '',
  name_kh: '',
  gender: 'Male',
  class_name: '',
  shift: '',
  major: '',
  phone: '',
  email: '',
  status: 'Active',
  avatar: ''
})

// Computed Properties
const activeStudentsCount = computed(() => students.value.filter(s => s.status === 'Active').length)
const inactiveStudentsCount = computed(() => students.value.filter(s => s.status !== 'Active').length)

// Actions
const openNewDialog = () => {
  studentForm.value = {
    id: null,
    student_id: 'STU-' + Math.floor(1000 + Math.random() * 9000),
    name_en: '',
    name_kh: '',
    gender: 'Male',
    class_name: '',
    shift: '',
    major: '',
    phone: '',
    email: '',
    status: 'Active',
    avatar: ''
  }
  isEdit.value = false
  studentDialog.value = true
}

const editStudent = (data) => {
  studentForm.value = { ...data }
  isEdit.value = true
  studentDialog.value = true
}

const saveStudent = () => {
  if (!studentForm.value.student_id || !studentForm.value.name_en || !studentForm.value.class_name) return

  if (isEdit.value) {
    const index = students.value.findIndex(s => s.id === studentForm.value.id)
    if (index !== -1) students.value[index] = { ...studentForm.value }
  } else {
    studentForm.value.id = Date.now()
    students.value.unshift({ ...studentForm.value })
  }

  studentDialog.value = false
}

const confirmDeleteStudent = (data) => {
  if (confirm(`Are you sure you want to delete ${data.name_en}?`)) {
    students.value = students.value.filter(s => s.id !== data.id)
  }
}
</script>