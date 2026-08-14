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

    <!-- ======= TABLE HEADER BAR / SEARCH & FILTER ======= -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
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

    <!-- ======= DATA TABLE (floating card rows) ======= -->
    <DataTable
        :value="students" 
        v-model:filters="filters"
        dataKey="id" 
        paginator 
        :rows="5" 
        :rowsPerPageOptions="[5, 10, 20]"
        responsiveLayout="scroll"
        class="p-datatable-sm students-table"
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

        <!-- Student Name (English) -->
        <Column field="name_en" header="STUDENT NAME" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</span>
          </template>
        </Column>

        <!-- Student Name (Khmer) -->
        <Column field="name_kh" header="STUDENT NAME (KH)" class="!py-3.5">
          <template #body="{ data }">
            <span v-if="data.name_kh" class="text-slate-600 text-sm font-khmer">{{ data.name_kh }}</span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Gender -->
        <Column field="gender" header="GENDER" class="!py-3.5">
          <template #body="{ data }">
            <span
              v-if="data.gender"
              class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-0.5 rounded-full border"
              :class="data.gender === 'Male' ? 'bg-sky-50 text-sky-700 border-sky-200' : 'bg-pink-50 text-pink-700 border-pink-200'"
            >
              <i class="text-[10px]" :class="data.gender === 'Male' ? 'pi pi-mars' : 'pi pi-venus'"></i>
              {{ data.gender }}
            </span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Class -->
        <Column field="class_name" header="CLASS" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs font-semibold text-slate-700">{{ data.class_name }}</span>
          </template>
        </Column>

        <!-- Shift -->
        <Column field="shift" header="SHIFT" class="!py-3.5">
          <template #body="{ data }">
            <span class="text-[11px] text-indigo-600 font-medium">{{ data.shift }}</span>
          </template>
        </Column>

        <!-- Phone -->
        <Column field="phone" header="PHONE" class="!py-3.5">
          <template #body="{ data }">
            <span class="text-slate-700 text-xs">{{ data.phone }}</span>
          </template>
        </Column>

        <!-- Email -->
        <Column field="email" header="EMAIL" class="!py-3.5">
          <template #body="{ data }">
            <span class="text-slate-400 text-[11px]">{{ data.email }}</span>
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

        <!-- Generation -->
        <Column field="generation" header="GENERATION" sortable class="!py-3.5">
          <template #body="{ data }">
            <span v-if="data.generation" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 border border-purple-100">
              {{ data.generation }}
            </span>
            <span v-else class="text-slate-400 text-sm">—</span>
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
            <div class="flex items-center justify-end gap-1.5">
              <Button
                icon="pi pi-pencil"
                class="!p-2 !w-8 !h-8 !rounded-lg !bg-blue-50 !text-blue-600 hover:!bg-blue-100 hover:!text-blue-700 !border !border-blue-100"
                title="Edit Student"
                @click="editStudent(data)"
              />
              <Button
                icon="pi pi-trash"
                class="!p-2 !w-8 !h-8 !rounded-lg !bg-rose-50 !text-rose-600 hover:!bg-rose-100 hover:!text-rose-700 !border !border-rose-100"
                title="Delete Student"
                @click="confirmDeleteStudent(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>

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

        <!-- Class (Shift & Major are derived from the selected class) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class *</label>
            <Dropdown
              v-model="studentForm.class_id"
              :options="classes"
              optionLabel="name"
              optionValue="id"
              placeholder="Select Class"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shift</label>
            <InputText :model-value="selectedClassShift" disabled placeholder="Derived from class" class="w-full !py-2.5 !px-3 !bg-slate-100 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <!-- Major & Phone -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major / Department</label>
            <InputText :model-value="selectedClassMajor" disabled placeholder="Derived from class" class="w-full !py-2.5 !px-3 !bg-slate-100 !border-slate-200 !rounded-xl !text-sm" />
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

        <!-- Generation -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Generation</label>
          <Dropdown
            v-model="studentForm.promotion_id"
            :options="promotions"
            optionLabel="name_en"
            optionValue="id"
            placeholder="Select Generation"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          />
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
import { ref, computed, onMounted } from 'vue'
import api, { extractError } from '../../../api'

// PrimeVue Components Import
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'

const students = ref([])
const classes = ref([])
const promotions = ref([])

const fetchStudents = async () => {
  const { data } = await api.get('/students', { params: { per_page: 100 } })
  students.value = data.data
}

const fetchClasses = async () => {
  const { data } = await api.get('/classes', { params: { per_page: 100 } })
  classes.value = data.data
}

const fetchPromotions = async () => {
  const { data } = await api.get('/promotions', { params: { per_page: 100 } })
  promotions.value = data.data
}

onMounted(() => {
  fetchStudents()
  fetchClasses()
  fetchPromotions()
})

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
  class_id: null,
  promotion_id: null,
  phone: '',
  email: '',
  status: 'Active',
  avatar: ''
})

// Shift & Major are derived from whichever class is selected, since a
// student's enrollment inherits both from its class rather than being
// chosen independently.
const selectedClassShift = computed(() => classes.value.find(c => c.id === studentForm.value.class_id)?.shift || '')
const selectedClassMajor = computed(() => classes.value.find(c => c.id === studentForm.value.class_id)?.department || '')

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
    class_id: null,
    promotion_id: null,
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

const saveStudent = async () => {
  if (classes.value.length === 0) {
    alert('No classes exist yet. Create a Class first (Classes page) before adding students.')
    return
  }
  if (!studentForm.value.student_id || !studentForm.value.name_en || !studentForm.value.class_id || !studentForm.value.phone) {
    alert('Student ID, name (English), class, and phone are required.')
    return
  }

  const payload = {
    student_id: studentForm.value.student_id,
    name_en: studentForm.value.name_en,
    name_kh: studentForm.value.name_kh,
    gender: studentForm.value.gender,
    class_id: studentForm.value.class_id,
    promotion_id: studentForm.value.promotion_id,
    phone: studentForm.value.phone,
    email: studentForm.value.email,
    status: studentForm.value.status,
  }

  try {
    if (isEdit.value) {
      await api.put(`/students/${studentForm.value.id}`, payload)
    } else {
      await api.post('/students', payload)
    }

    studentDialog.value = false
    await fetchStudents()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteStudent = async (data) => {
  if (confirm(`Are you sure you want to delete ${data.name_en}?`)) {
    try {
      await api.delete(`/students/${data.id}`)
      await fetchStudents()
    } catch (error) {
      alert(extractError(error))
    }
  }
}
</script>

<style scoped>
/* "Floating card row" table, matching the Teachers page: header text
   sitting directly on the page background, and each row as its own
   white rounded card with a soft shadow — spacing does the separating,
   not gridlines. */
.students-table :deep(.p-datatable-table) {
  border-collapse: separate;
  border-spacing: 0 0.6rem;
}

.students-table :deep(.p-datatable-table-container),
.students-table :deep(.p-datatable-header),
.students-table :deep(.p-datatable-footer),
.students-table :deep(.p-datatable-thead),
.students-table :deep(.p-datatable),
.students-table :deep(.p-datatable-mask) {
  border: none !important;
  box-shadow: none;
  background: transparent;
}

.students-table :deep(.p-datatable-thead > tr > th) {
  background: #ffffff;
  border: none !important;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 1px 5px rgba(15, 23, 42, 0.05);
  color: #1d4ed8;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  line-height: 1.5rem;
  padding: 1rem 1rem;
  white-space: nowrap;
}

.students-table :deep(.p-datatable-thead > tr > th:first-child) {
  border-top-left-radius: 0.6rem;
  border-bottom-left-radius: 0.6rem;
}

.students-table :deep(.p-datatable-thead > tr > th:last-child) {
  border-top-right-radius: 0.6rem;
  border-bottom-right-radius: 0.6rem;
}

.students-table :deep(.p-datatable-tbody > tr > td) {
  background: #ffffff;
  border: none !important;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 1px 5px rgba(15, 23, 42, 0.05);
  line-height: 1.25rem;
  padding: 0.75rem 1rem;
  transition: background-color 0.15s ease;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.students-table :deep(.p-datatable-tbody > tr > td:first-child) {
  border-top-left-radius: 0.6rem;
  border-bottom-left-radius: 0.6rem;
}

.students-table :deep(.p-datatable-tbody > tr > td:last-child) {
  border-top-right-radius: 0.6rem;
  border-bottom-right-radius: 0.6rem;
  overflow: visible;
}

.students-table :deep(.p-datatable-tbody > tr:hover > td) {
  background: #f8fafc;
}

.students-table :deep(.p-datatable-tbody > tr) {
  outline: none;
}

.students-table :deep(.p-paginator) {
  background: transparent;
  border: none;
  padding-top: 0.75rem;
}
</style>