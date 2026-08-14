<template>
  <div class="space-y-6">
    
    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-briefcase text-emerald-600 text-2xl"></i>
          Teachers Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          Manage all teachers, their information, and subject/class assignments
        </p>
      </div>

      <Button 
        label="Add New Teacher" 
        icon="pi pi-plus" 
        class="!bg-emerald-600 hover:!bg-emerald-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= TABLE HEADER BAR / SEARCH & FILTER ======= -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
      <div class="relative w-full sm:w-80">
        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
        <InputText
          v-model="filters['global'].value"
          placeholder="Search teacher code, name, or phone..."
          class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
        />
      </div>
    </div>

    <!-- ======= DATA TABLE (floating card rows, scrolls when there are many rows/columns) ======= -->
    <DataTable
      :value="teachers"
      v-model:filters="filters"
      dataKey="id"
      paginator
      :rows="10"
      :rowsPerPageOptions="[10, 20, 50]"
      scrollable
      scrollHeight="560px"
      responsiveLayout="scroll"
      class="p-datatable-sm teachers-table"
    >
        <template #empty>
          <div class="text-center py-8 text-slate-400 text-sm">
            No teachers found.
          </div>
        </template>

        <!-- No -->
        <Column header="NO" style="width: 46px">
          <template #body="{ index }">
            <span class="text-slate-400 text-sm font-semibold">{{ index + 1 }}</span>
          </template>
        </Column>

        <!-- Teacher Name (English) -->
        <Column field="name_en" header="TEACHER NAME" sortable style="min-width: 210px">
          <template #body="{ data }">
            <div class="flex items-center gap-2.5">
              <!-- <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-sm">
                {{ data.name_en.charAt(0) }}
              </div> -->
              <span class="font-semibold text-slate-800 text-sm">{{ data.name_en }}</span>
            </div>
          </template>
        </Column>

        <!-- Teacher Name (Khmer) -->
        <Column field="name_kh" header="TEACHER NAME (KH)" sortable style="min-width: 170px">
          <template #body="{ data }">
            <span v-if="data.name_kh" class="text-slate-600 text-sm font-khmer">{{ data.name_kh }}</span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Gender -->
        <Column field="gender" header="GENDER" sortable style="min-width: 90px">
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

        <!-- Faculty -->
        <Column field="department" header="FACULTY" sortable style="min-width: 160px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm font-medium">{{ data.department || '—' }}</span>
          </template>
        </Column>

        <!-- Department -->
        <Column field="department_name" header="DEPARTMENT" sortable style="min-width: 160px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm font-medium">{{ data.department_name || '—' }}</span>
          </template>
        </Column>

        <!-- Degree -->
        <Column field="degree" header="DEGREE" style="min-width: 150px">
          <template #body="{ data }">
            <span v-if="data.degree" class="text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md">
              {{ data.degree }}
            </span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Phone -->
        <Column field="phone" header="PHONE" style="min-width: 120px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm font-mono">{{ data.phone || '—' }}</span>
          </template>
        </Column>

        <!-- Email -->
        <Column field="email" header="EMAIL" style="min-width: 170px">
          <template #body="{ data }">
            <span class="text-slate-500 text-sm">{{ data.email || '—' }}</span>
          </template>
        </Column>

        <!-- Employment Type -->
        <Column field="type" header="EMPLOYMENT" sortable style="min-width: 110px">
          <template #body="{ data }">
            <span
              class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-0.5 rounded-full border"
              :class="data.type === 'Full-Time' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
            >
              {{ data.type }}
            </span>
          </template>
        </Column>

        <!-- Status -->
        <Column field="status" header="STATUS" sortable style="min-width: 100px">
          <template #body="{ data }">
            <span
              class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-0.5 rounded-full border"
              :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
            >
              <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="data.status === 'Active' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
              {{ data.status }}
            </span>
          </template>
        </Column>

        <!-- Actions -->
        <Column header="ACTIONS" class="!text-right" style="min-width: 130px">
          <template #body="{ data }">
            <div class="flex items-center justify-end gap-1.5">
              <Button
                icon="pi pi-book"
                class="!p-2 !w-8 !h-8 !rounded-lg !bg-indigo-50 !text-indigo-600 hover:!bg-indigo-100 hover:!text-indigo-700 !border !border-indigo-100"
                title="Manage Assignments"
                @click="openAssignmentsDialog(data)"
              />
              <Button
                icon="pi pi-pencil"
                class="!p-2 !w-8 !h-8 !rounded-lg !bg-blue-50 !text-blue-600 hover:!bg-blue-100 hover:!text-blue-700 !border !border-blue-100"
                title="Edit Teacher"
                @click="editTeacher(data)"
              />
              <Button
                icon="pi pi-trash"
                class="!p-2 !w-8 !h-8 !rounded-lg !bg-rose-50 !text-rose-600 hover:!bg-rose-100 hover:!text-rose-700 !border !border-rose-100"
                title="Delete Teacher"
                @click="confirmDeleteTeacher(data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>

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

        <!-- Faculty & Department -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty *</label>
            <Dropdown
              v-model="teacherForm.faculty_id"
              :options="faculties"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Select Faculty"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
              @change="teacherForm.department_id = null"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department</label>
            <Dropdown
              v-model="teacherForm.department_id"
              :options="departmentOptions"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Select Department"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
        </div>

        <!-- Degree -->
        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Degree Level</label>
          <Dropdown
            v-model="teacherForm.degree_id"
            :options="degrees"
            optionLabel="title_en"
            optionValue="id"
            placeholder="Select Degree"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
          />
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
          <Button label="Save Teacher" icon="pi pi-check" class="!bg-emerald-600 hover:!bg-emerald-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveTeacher" />
        </div>
      </template>
    </Dialog>

    <!-- ======= ASSIGNMENTS DIALOG (Subject + Class per Teacher) ======= -->
    <Dialog
      v-model:visible="assignmentsDialog"
      :header="assignmentTeacher ? `Assignments - ${assignmentTeacher.name_en}` : 'Assignments'"
      :modal="true"
      class="w-full max-w-2xl"
    >
      <div class="space-y-4 pt-2">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end bg-slate-50 p-3 rounded-xl border border-slate-200">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subject</label>
            <Dropdown
              v-model="assignmentForm.subject_id"
              :options="assignmentSubjectOptions"
              optionLabel="name_en"
              optionValue="id"
              placeholder="Select Subject"
              class="w-full !bg-white !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class</label>
            <Dropdown
              v-model="assignmentForm.class_id"
              :options="assignmentClassOptions"
              optionLabel="name"
              optionValue="id"
              placeholder="Select Class"
              class="w-full !bg-white !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <Button
            label="Add Assignment"
            icon="pi pi-plus"
            class="!bg-emerald-600 hover:!bg-emerald-700 !border-0 !rounded-xl !text-xs !font-semibold"
            @click="addAssignment"
          />
        </div>
        <p class="text-[11px] text-slate-400 -mt-2">
          Only subjects and classes within this teacher's faculty are shown, so a teacher can't be assigned to teach outside their own faculty.
        </p>

        <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 max-h-72 overflow-y-auto">
          <div
            v-for="assignment in teacherAssignments"
            :key="assignment.id"
            class="flex items-center justify-between px-4 py-2.5"
          >
            <div>
              <span class="text-xs font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded mr-2">{{ assignment.subject_code }}</span>
              <span class="text-sm font-semibold text-slate-800">{{ assignment.subject_name }}</span>
              <span class="text-xs text-slate-400 ml-2">→ {{ assignment.class_name }}</span>
            </div>
            <Button
              icon="pi pi-trash"
              class="!p-1.5 !w-7 !h-7 !rounded-lg !text-slate-400 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
              @click="removeAssignment(assignment)"
            />
          </div>
          <div v-if="teacherAssignments.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">
            No subject/class assignments yet.
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end pt-3">
          <Button label="Close" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="assignmentsDialog = false" />
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

const teachers = ref([])
const faculties = ref([])
const departments = ref([])
const degrees = ref([])
const subjects = ref([])
const classes = ref([])

const fetchTeachers = async () => {
  const { data } = await api.get('/teachers', { params: { per_page: 100 } })
  teachers.value = data.data
}

const fetchLookups = async () => {
  const [facultiesRes, departmentsRes, degreesRes, subjectsRes, classesRes] = await Promise.all([
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/departments', { params: { per_page: 200 } }),
    api.get('/degrees', { params: { per_page: 100 } }),
    api.get('/subjects', { params: { per_page: 200 } }),
    api.get('/classes', { params: { per_page: 200 } }),
  ])
  faculties.value = facultiesRes.data.data
  departments.value = departmentsRes.data.data
  degrees.value = degreesRes.data.data
  subjects.value = subjectsRes.data.data.map(s => ({ id: s.id, faculty_id: s.faculty_id, name_en: `${s.name_en} (${s.code})` }))
  classes.value = classesRes.data.data
}

// Department options are scoped to whichever faculty is currently selected
// in the form, since a department belongs to exactly one faculty.
const departmentOptions = computed(() =>
  departments.value.filter(d => d.faculty_id === teacherForm.value.faculty_id)
)

onMounted(() => {
  fetchTeachers()
  fetchLookups()
})

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
  faculty_id: null,
  department_id: null,
  degree_id: null,
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
    faculty_id: null,
    department_id: null,
    degree_id: null,
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

const saveTeacher = async () => {
  if (!teacherForm.value.code || !teacherForm.value.name_en || !teacherForm.value.faculty_id || !teacherForm.value.phone) {
    alert('Teacher code, name (English), faculty, and phone are required.')
    return
  }

  const payload = {
    code: teacherForm.value.code,
    name_en: teacherForm.value.name_en,
    name_kh: teacherForm.value.name_kh,
    gender: teacherForm.value.gender,
    faculty_id: teacherForm.value.faculty_id,
    department_id: teacherForm.value.department_id,
    degree_id: teacherForm.value.degree_id,
    phone: teacherForm.value.phone,
    email: teacherForm.value.email,
    type: teacherForm.value.type,
    status: teacherForm.value.status,
  }

  try {
    if (isEdit.value) {
      await api.put(`/teachers/${teacherForm.value.id}`, payload)
    } else {
      await api.post('/teachers', payload)
    }

    teacherDialog.value = false
    await fetchTeachers()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteTeacher = async (data) => {
  if (confirm(`Are you sure you want to delete ${data.name_en}?`)) {
    try {
      await api.delete(`/teachers/${data.id}`)
      await fetchTeachers()
    } catch (error) {
      alert(extractError(error))
    }
  }
}

// Assignments Dialog (Subject + Class per Teacher)
const assignmentsDialog = ref(false)
const assignmentTeacher = ref(null)
const teacherAssignments = ref([])
const assignmentForm = ref({ subject_id: null, class_id: null })

// Subjects/classes are scoped to the faculty of whichever teacher's
// assignment dialog is open, so a teacher can only be assigned within
// their own faculty (backend enforces this too, see TeacherAssignmentController).
const assignmentSubjectOptions = computed(() =>
  assignmentTeacher.value
    ? subjects.value.filter(s => s.faculty_id === assignmentTeacher.value.faculty_id)
    : []
)
const assignmentClassOptions = computed(() =>
  assignmentTeacher.value
    ? classes.value.filter(c => c.faculty_id === assignmentTeacher.value.faculty_id)
    : []
)

const fetchAssignments = async (teacherId) => {
  const { data } = await api.get('/teacher-assignments', { params: { teacher_profile_id: teacherId } })
  teacherAssignments.value = data.data
}

const openAssignmentsDialog = async (data) => {
  assignmentTeacher.value = data
  assignmentForm.value = { subject_id: null, class_id: null }
  assignmentsDialog.value = true
  try {
    await fetchAssignments(data.id)
  } catch (error) {
    alert(extractError(error))
  }
}

const addAssignment = async () => {
  if (!assignmentForm.value.subject_id || !assignmentForm.value.class_id) {
    alert('Please select both a subject and a class.')
    return
  }

  try {
    await api.post('/teacher-assignments', {
      teacher_profile_id: assignmentTeacher.value.id,
      subject_id: assignmentForm.value.subject_id,
      class_id: assignmentForm.value.class_id,
    })
    assignmentForm.value = { subject_id: null, class_id: null }
    await fetchAssignments(assignmentTeacher.value.id)
  } catch (error) {
    alert(extractError(error))
  }
}

const removeAssignment = async (assignment) => {
  if (!confirm(`Remove ${assignment.subject_name} → ${assignment.class_name}?`)) return

  try {
    await api.delete(`/teacher-assignments/${assignment.id}`)
    await fetchAssignments(assignmentTeacher.value.id)
  } catch (error) {
    alert(extractError(error))
  }
}
</script>

<style scoped>
/* "Floating card row" table, styled to match the reference: green column
   headers sitting directly on the page background, and each row as its
   own white rounded card with a soft shadow — spacing does the
   separating, not gridlines. */
.teachers-table :deep(.p-datatable-table) {
  border-collapse: separate;
  border-spacing: 0 0.6rem;
}

/* Strip every border/outline PrimeVue's default theme puts on the table's
   wrapper elements — otherwise a 1px frame survives around the scrollable
   header/body even after the cells themselves go borderless. */
.teachers-table :deep(.p-datatable-table-container),
.teachers-table :deep(.p-datatable-header),
.teachers-table :deep(.p-datatable-footer),
.teachers-table :deep(.p-datatable-thead),
.teachers-table :deep(.p-datatable),
.teachers-table :deep(.p-datatable-mask) {
  border: none !important;
  box-shadow: none;
  background: transparent;
}

.teachers-table :deep(.p-datatable-thead > tr > th) {
  background: #ffffff;
  border: none !important;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 1px 5px rgba(15, 23, 42, 0.05);
  color: #047857;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  line-height: 1.5rem;
  padding: 1rem 1rem;
  white-space: nowrap;
}

.teachers-table :deep(.p-datatable-thead > tr > th:first-child) {
  border-top-left-radius: 0.6rem;
  border-bottom-left-radius: 0.6rem;
}

.teachers-table :deep(.p-datatable-thead > tr > th:last-child) {
  border-top-right-radius: 0.6rem;
  border-bottom-right-radius: 0.6rem;
}

.teachers-table :deep(.p-datatable-tbody > tr > td) {
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

.teachers-table :deep(.p-datatable-tbody > tr > td:first-child) {
  border-top-left-radius: 0.6rem;
  border-bottom-left-radius: 0.6rem;
}

.teachers-table :deep(.p-datatable-tbody > tr > td:last-child) {
  border-top-right-radius: 0.6rem;
  border-bottom-right-radius: 0.6rem;
  overflow: visible;
}

.teachers-table :deep(.p-datatable-tbody > tr:hover > td) {
  background: #f8fafc;
}

.teachers-table :deep(.p-datatable-tbody > tr) {
  outline: none;
}

.teachers-table :deep(.p-paginator) {
  background: transparent;
  border: none;
  padding-top: 0.75rem;
}
</style>