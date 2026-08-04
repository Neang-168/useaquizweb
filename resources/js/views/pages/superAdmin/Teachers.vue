<template>
  <div class="space-y-6">
    
    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-briefcase text-blue-600 text-2xl"></i>
          Teachers Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          គ្រប់គ្រងព័ត៌មានលោកគ្រូ-អ្នកគ្រូ កម្រិតវប្បធម៌ ជំនាញ និងទំនាក់ទំនង
        </p>
      </div>

      <Button 
        label="Add New Teacher" 
        icon="pi pi-plus" 
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Teachers</span>
          <span class="text-2xl font-bold text-slate-800">{{ teachers.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
          <i class="pi pi-users"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Full-time Staff</span>
          <span class="text-2xl font-bold text-indigo-600">{{ fullTimeCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
          <i class="pi pi-id-card"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active Status</span>
          <span class="text-2xl font-bold text-emerald-600">{{ activeTeachersCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
          <i class="pi pi-check-circle"></i>
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
            placeholder="Search teacher code, name, or phone..." 
            class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
          />
        </div>
        <div class="text-xs text-slate-400">
          Showing <b>{{ teachers.length }}</b> entries
        </div>
      </div>

      <!-- PrimeVue DataTable -->
      <DataTable 
        :value="teachers" 
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
            No teachers found.
          </div>
        </template>

        <!-- Teacher Code -->
        <Column field="code" header="CODE" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
              {{ data.code }}
            </span>
          </template>
        </Column>

        <!-- Teacher Profile & Name -->
        <Column field="name_en" header="TEACHER NAME" sortable class="!py-3.5">
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

        <!-- Specialty / Department -->
        <Column field="department" header="SPECIALTY / DEGREE" sortable class="!py-3.5">
          <template #body="{ data }">
            <div>
              <div class="text-xs font-semibold text-slate-700">{{ data.department }}</div>
              <div class="text-[11px] text-indigo-600 font-medium mt-0.5">{{ data.degree }}</div>
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

        <!-- Employment Type -->
        <Column field="type" header="EMPLOYMENT" sortable class="!py-3.5">
          <template #body="{ data }">
            <span 
              class="text-xs font-semibold px-2.5 py-1 rounded-lg border"
              :class="data.type === 'Full-Time' ? 'bg-purple-50 text-purple-700 border-purple-100' : 'bg-amber-50 text-amber-700 border-amber-100'"
            >
              {{ data.type }}
            </span>
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
                @click="editTeacher(data)"
              />
              <Button 
                icon="pi pi-trash" 
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
                @click="confirmDeleteTeacher(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog 
      v-model:visible="teacherDialog" 
      :header="isEdit ? 'Edit Teacher Information' : 'Add New Teacher'" 
      :modal="true" 
      class="w-full max-w-xl"
    >
      <div class="space-y-4 pt-2">
        <!-- Code & Gender -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Teacher Code *</label>
            <InputText 
              v-model="teacherForm.code" 
              placeholder="e.g. T-101" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Gender *</label>
            <Dropdown 
              v-model="teacherForm.gender" 
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
              v-model="teacherForm.name_en" 
              placeholder="e.g. Sok Chantha" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name (Khmer)</label>
            <InputText 
              v-model="teacherForm.name_kh" 
              placeholder="ឧ. សុខ ចាន់ថាន" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
        </div>

        <!-- Department & Degree -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department / Subject *</label>
            <Dropdown 
              v-model="teacherForm.department" 
              :options="departmentOptions" 
              placeholder="Select Department" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Degree Level *</label>
            <Dropdown 
              v-model="teacherForm.degree" 
              :options="degreeOptions" 
              placeholder="Select Degree" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
        </div>

        <!-- Phone & Email -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Phone Number *</label>
            <InputText 
              v-model="teacherForm.phone" 
              placeholder="012 345 678" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email Address</label>
            <InputText 
              v-model="teacherForm.email" 
              placeholder="teacher@school.edu.kh" 
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm"
            />
          </div>
        </div>

        <!-- Employment Type & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Employment Type</label>
            <Dropdown 
              v-model="teacherForm.type" 
              :options="['Full-Time', 'Part-Time']" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown 
              v-model="teacherForm.status" 
              :options="['Active', 'Inactive']" 
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="teacherDialog = false" />
          <Button label="Save Teacher" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveTeacher" />
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

// Mock Data: Teachers
const teachers = ref([
  { id: 1, code: 'T-101', name_en: 'Dr. Keo Samnang', name_kh: 'បណ្ឌិត កែវ សំនៀង', gender: 'Male', department: 'Computer Science', degree: 'Ph.D. in Computer Science', phone: '012 888 999', email: 'keo.samnang@school.edu.kh', type: 'Full-Time', status: 'Active', avatar: '' },
  { id: 2, code: 'T-102', name_en: 'Sok Vanna', name_kh: 'សុខ វណ្ណា', gender: 'Female', department: 'Business Information Technology', degree: 'Master Degree', phone: '098 765 432', email: 'sok.vanna@school.edu.kh', type: 'Full-Time', status: 'Active', avatar: '' },
  { id: 3, code: 'T-103', name_en: 'Chan Dara', name_kh: 'ចាន់ ដារ៉ា', gender: 'Male', department: 'Law', degree: 'Master Degree', phone: '017 112 233', email: 'chan.dara@school.edu.kh', type: 'Part-Time', status: 'Active', avatar: '' },
  { id: 4, code: 'T-104', name_en: 'Meng Bophal', name_kh: 'ម៉េង បុប្ផា', gender: 'Female', department: 'English Literature', degree: 'Bachelor Degree', phone: '069 554 433', email: 'meng.bophal@school.edu.kh', type: 'Part-Time', status: 'Inactive', avatar: '' },
])

const departmentOptions = ref([
  'Computer Science',
  'Business Information Technology',
  'Law',
  'English Literature',
  'Mathematics'
])

const degreeOptions = ref([
  'Bachelor Degree',
  'Master Degree',
  'Ph.D. / Doctoral'
])

// Search Filter (ប្រើ String 'contains')
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// Dialog States & Form
const teacherDialog = ref(false)
const isEdit = ref(false)
const teacherForm = ref({
  id: null,
  code: '',
  name_en: '',
  name_kh: '',
  gender: 'Male',
  department: '',
  degree: '',
  phone: '',
  email: '',
  type: 'Full-Time',
  status: 'Active',
  avatar: ''
})

// Computed Properties
const activeTeachersCount = computed(() => teachers.value.filter(t => t.status === 'Active').length)
const fullTimeCount = computed(() => teachers.value.filter(t => t.type === 'Full-Time').length)

// Actions
const openNewDialog = () => {
  teacherForm.value = {
    id: null,
    code: 'T-' + Math.floor(100 + Math.random() * 900),
    name_en: '',
    name_kh: '',
    gender: 'Male',
    department: '',
    degree: '',
    phone: '',
    email: '',
    type: 'Full-Time',
    status: 'Active',
    avatar: ''
  }
  isEdit.value = false
  teacherDialog.value = true
}

const editTeacher = (data) => {
  teacherForm.value = { ...data }
  isEdit.value = true
  teacherDialog.value = true
}

const saveTeacher = () => {
  if (!teacherForm.value.code || !teacherForm.value.name_en || !teacherForm.value.department) return

  if (isEdit.value) {
    const index = teachers.value.findIndex(t => t.id === teacherForm.value.id)
    if (index !== -1) teachers.value[index] = { ...teacherForm.value }
  } else {
    teacherForm.value.id = Date.now()
    teachers.value.unshift({ ...teacherForm.value })
  }

  teacherDialog.value = false
}

const confirmDeleteTeacher = (data) => {
  if (confirm(`Are you sure you want to delete ${data.name_en}?`)) {
    teachers.value = teachers.value.filter(t => t.id !== data.id)
  }
}
</script>