<template>
  <div class="space-y-6">

    <!-- ======= PAGE HEADER ======= -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-[#002060] m-0 flex items-center gap-2">
          <i class="pi pi-briefcase text-[#e4ac40] text-2xl"></i>
          Teachers Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          Manage all teachers, their information, and subject/class assignments
        </p>
      </div>

      <Button label="Add New Teacher" icon="pi pi-plus"
        class="!bg-[#002060] hover:!bg-blue-900 !border-0 !rounded-xl !py-2.5 !px-4 !text-xs !font-semibold shadow-xs"
        @click="openNewDialog" />
    </div>

    <!-- ======= TABLE CARD (header/search/filters/table, styled to match FeedbackTable) ======= -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="text-sm font-bold text-[#002060] m-0">Teachers List</h3>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <!-- Search Input -->
          <div class="relative w-full sm:w-56">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText v-model="filters['global'].value" size="small" placeholder="Search teacher code, name, or phone..."
              class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs" />
          </div>

          <!-- Reset Filters Button -->
          <Button v-if="hasActiveFilters" label="Reset Filters" icon="pi pi-filter-slash" size="small"
            class="!bg-rose-50 !border-rose-50 !text-rose-600 hover:!bg-rose-100 !rounded-lg !text-xs !font-semibold !px-3 !py-1"
            @click="clearFilters" />

          <!-- Show More / Show Less Toggle Button -->
          <Button :label="showAllColumns ? 'Fewer Column' : 'More Column'"
            :icon="showAllColumns ? 'pi pi-angle-double-left' : 'pi pi-angle-double-right'" size="small"
            class="!bg-[#002060] !border-slate-100 !text-white hover:!bg-blue-900 !rounded-lg !text-xs !font-semibold !px-3 !py-1"
            @click="showAllColumns = !showAllColumns" />
        </div>
      </div>

      <!-- ======= FILTER BAR ======= -->
      <div class="p-4 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5">

        <!-- Faculty Filter -->
        <Dropdown v-model="facultyFilter" :options="faculties" optionLabel="name_en" optionValue="id"
          placeholder="All Faculties" showClear class="w-full custom-filter-dropdown" />

        <!-- Department Filter -->
        <Dropdown v-model="departmentFilter" :options="departments" optionLabel="name_en" optionValue="id"
          placeholder="All Departments" showClear class="w-full custom-filter-dropdown" />

        <!-- Degree Filter -->
        <Dropdown v-model="degreeFilter" :options="degrees" optionLabel="title_en" optionValue="id"
          placeholder="All Degrees" showClear class="w-full custom-filter-dropdown" />

        <!-- Employment Type Filter -->
        <Dropdown v-model="typeFilter" :options="['Full-Time', 'Part-Time']" placeholder="All Employment Types"
          showClear class="w-full custom-filter-dropdown" />

        <!-- Status Filter -->
        <Dropdown v-model="statusFilter" :options="['Active', 'Inactive']" placeholder="All Statuses" showClear
          class="w-full custom-filter-dropdown" />

      </div>

      <!-- ======= DATA TABLE ======= -->
      <DataTable :value="filteredTeachers" v-model:filters="filters" dataKey="id"
        paginator :rows="rows" v-model:first="first" :rowsPerPageOptions="[10, 20, 50]"
        scrollable scrollHeight="calc(100vh - 428px)" :scrollDirection="showAllColumns ? 'both' : 'vertical'"
        responsiveLayout="scroll" :loading="loading" :tableStyle="tableMinWidthStyle" class="p-datatable-sm teachers-table">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">
            No teachers found.
          </div>
        </template>

        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ filteredTeachers.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, filteredTeachers.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ filteredTeachers.length }}</span>
          </span>
        </template>

        <!-- No -->
        <Column header="NO" style="width: 46px; padding-left: 1.25rem">
          <template #body="{ index }">
            <span class="text-slate-400 text-sm font-semibold">{{ index + 1 }}</span>
          </template>
        </Column>

        <!-- Teacher Name (English) - តែងតែបង្ហាញ -->
        <Column field="name_en" header="TEACHER NAME" sortable style="min-width: 210px">
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-sm whitespace-nowrap">{{ data.name_en }}</span>
          </template>
        </Column>

        <!-- Teacher Name (Khmer) - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="name_kh" header="TEACHER NAME (KH)" sortable style="min-width: 170px">
          <template #body="{ data }">
            <span v-if="data.name_kh" class="text-slate-600 text-sm font-khmer whitespace-nowrap">{{ data.name_kh }}</span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Gender - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="gender" header="GENDER" sortable style="min-width: 90px">
          <template #body="{ data }">
            <span v-if="data.gender"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-flex items-center gap-1.5"
              :class="data.gender === 'Male' ? 'bg-sky-50 text-sky-600 border-sky-200' : 'bg-pink-50 text-pink-600 border-pink-200'">
              <i class="text-[10px]" :class="data.gender === 'Male' ? 'pi pi-mars' : 'pi pi-venus'"></i>
              {{ data.gender }}
            </span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Faculty - តែងតែបង្ហាញ -->
        <Column field="faculty_name" header="FACULTY" sortable style="min-width: 160px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm whitespace-nowrap">{{ data.faculty_name || '—' }}</span>
          </template>
        </Column>

        <!-- Department - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="department_name" header="DEPARTMENT" sortable style="min-width: 160px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm whitespace-nowrap">{{ data.department_name || '—' }}</span>
          </template>
        </Column>

        <!-- Major - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="major_name" header="MAJOR" sortable style="min-width: 160px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm whitespace-nowrap">{{ data.major_name || '—' }}</span>
          </template>
        </Column>

        <!-- Degree - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="degree" header="DEGREE" style="min-width: 150px">
          <template #body="{ data }">
            <span v-if="data.degree"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block align-bottom bg-indigo-50 text-indigo-600 border-indigo-200">
              {{ data.degree }}
            </span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Phone - តែងតែបង្ហាញ -->
        <Column field="phone" header="PHONE" style="min-width: 120px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm font-mono whitespace-nowrap">{{ data.phone || '—' }}</span>
          </template>
        </Column>

        <!-- Email - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="email" header="EMAIL" style="min-width: 170px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm whitespace-nowrap">{{ data.email || '—' }}</span>
          </template>
        </Column>

        <!-- Employment Type - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="type" header="EMPLOYMENT" sortable style="min-width: 110px">
          <template #body="{ data }">
            <span class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block"
              :class="data.type === 'Full-Time' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-amber-50 text-amber-600 border-amber-200'">
              {{ data.type }}
            </span>
          </template>
        </Column>

        <!-- Status - តែងតែបង្ហាញ -->
        <Column field="status" header="STATUS" sortable style="min-width: 100px">
          <template #body="{ data }">
            <span class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block"
              :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'">
              {{ data.status }}
            </span>
          </template>
        </Column>

        <!-- Actions - តែងតែបង្ហាញ -->
        <Column header="ACTIONS" class="!text-right" style="min-width: 130px; padding-right: 1.25rem">
          <template #body="{ data }">
            <div class="flex items-center justify-end gap-1.5">
              <!-- <Button icon="pi pi-book"
                class="!p-2 !w-8 !h-8 !rounded-xl !bg-indigo-50 !text-indigo-600 hover:!bg-indigo-100 hover:!text-indigo-700 !border !border-indigo-100 shadow-xs"
                title="Manage Assignments" @click="openAssignmentsDialog(data)" /> -->
              <Button icon="pi pi-pencil"
                class="!p-2 !w-8 !h-8 !rounded-xl !bg-slate-100 !text-slate-600 hover:!bg-slate-200 hover:!text-slate-800 !border-0 shadow-xs"
                title="Edit Teacher" @click="editTeacher(data)" />
              <Button icon="pi pi-trash"
                class="!p-2 !w-8 !h-8 !rounded-xl !bg-rose-50 !text-rose-600 hover:!bg-rose-100 hover:!text-rose-700 !border-0 shadow-xs"
                title="Delete Teacher" @click="confirmDeleteTeacher(data)" />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog v-model:visible="teacherDialog" :header="isEdit ? 'Edit Teacher Information' : 'Add New Teacher'"
      :modal="true" class="w-full max-w-3xl">
      <div class="space-y-5 pt-2">
        <!-- ======= SECTION: PERSONAL INFORMATION ======= -->
        <div class="flex items-center gap-2 text-slate-700 font-bold text-xs uppercase tracking-wider pb-2 border-b border-slate-200">
          <i class="pi pi-user text-blue-600 text-sm"></i>
          <span>Personal Information</span>
        </div>

        <!-- Code / Full Name (English) / Full Name (Khmer) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Teacher Code *</label>
            <InputText v-model="teacherForm.code" placeholder="e.g. T-101"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name (English) *</label>
            <InputText v-model="teacherForm.name_en" placeholder="e.g. Sok Chantha"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name (Khmer)</label>
            <InputText v-model="teacherForm.name_kh" placeholder="ឧ. សុខ ចាន់ថាន"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <!-- Gender / Phone / Email -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Gender *</label>
            <Dropdown v-model="teacherForm.gender" :options="['Male', 'Female']" placeholder="Select Gender"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Phone Number *</label>
            <InputText v-model="teacherForm.phone" placeholder="012 345 678"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email Address</label>
            <InputText v-model="teacherForm.email" placeholder="teacher@school.edu.kh"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <!-- Password -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
              Password {{ isEdit ? '' : '*' }}
            </label>
            <Password v-model="teacherForm.password" toggleMask :feedback="false"
              :placeholder="isEdit ? 'Leave blank to keep current password' : 'Min 8 characters'"
              inputClass="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" class="w-full" />
          </div>
        </div>

        <!-- Date of Birth / Employment Type / Status -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Date of Birth</label>
            <DatePicker v-model="teacherForm.dob" dateFormat="yy-mm-dd" showIcon iconDisplay="input"
              placeholder="Select date" class="w-full"
              inputClass="!py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Employment Type</label>
            <Dropdown v-model="teacherForm.type" :options="['Full-Time', 'Part-Time']"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown v-model="teacherForm.status" :options="['Active', 'Inactive']"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>

        <!-- Address (last Personal Information field, full width) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="sm:col-span-3">
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Address</label>
            <Textarea v-model="teacherForm.address" rows="2" placeholder="Street, city, country"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <!-- ======= SECTION: ACADEMIC INFORMATION ======= -->
        <div class="flex items-center gap-2 text-slate-700 font-bold text-xs uppercase tracking-wider pb-2 pt-2 border-b border-slate-200">
          <i class="pi pi-graduation-cap text-emerald-600 text-sm"></i>
          <span>Academic Information</span>
        </div>

        <!-- Faculty / Department / Major -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty *</label>
            <Dropdown v-model="teacherForm.faculty_id" :options="faculties" optionLabel="name_en" optionValue="id"
              placeholder="Select Faculty" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
              @change="teacherForm.department_id = null; teacherForm.major_id = null; teacherForm.degree_id = null" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department</label>
            <Dropdown v-model="teacherForm.department_id" :options="departmentOptions" optionLabel="name_en"
              optionValue="id" placeholder="Select Department" showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major</label>
            <Dropdown v-model="teacherForm.major_id" :options="majorsForSelectedFaculty" optionLabel="name_en"
              optionValue="id" placeholder="Select Major" showClear :disabled="!teacherForm.faculty_id"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" @change="onMajorChange" />
          </div>
        </div>

        <!-- Qualification / Specialization / Hire Date -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Qualification</label>
            <InputText v-model="teacherForm.qualification" placeholder="e.g. MSc Computer Science"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Specialization</label>
            <InputText v-model="teacherForm.specialization" placeholder="e.g. Database Systems"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Hire Date</label>
            <DatePicker v-model="teacherForm.hire_date" dateFormat="yy-mm-dd" showIcon iconDisplay="input"
              placeholder="Select date" class="w-full"
              inputClass="!py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times"
            class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold"
            @click="teacherDialog = false" />
          <Button label="Save Teacher" icon="pi pi-check"
            class="!bg-emerald-600 hover:!bg-emerald-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold"
            @click="saveTeacher" />
        </div>
      </template>
    </Dialog>

    <!-- ======= ASSIGNMENTS DIALOG (Subject + Class per Teacher) ======= -->
    <Dialog v-model:visible="assignmentsDialog"
      :header="assignmentTeacher ? `Assignments - ${assignmentTeacher.name_en}` : 'Assignments'" :modal="true"
      class="w-full max-w-2xl">
      <div class="space-y-4 pt-2">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end bg-slate-50 p-3 rounded-xl border border-slate-200">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subject</label>
            <Dropdown v-model="assignmentForm.subject_id" :options="assignmentSubjectOptions" optionLabel="name_en"
              optionValue="id" placeholder="Select Subject"
              class="w-full !bg-white !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class</label>
            <Dropdown v-model="assignmentForm.class_id" :options="assignmentClassOptions" optionLabel="name"
              optionValue="id" placeholder="Select Class"
              class="w-full !bg-white !border-slate-200 !rounded-xl text-sm" />
          </div>
          <Button label="Add Assignment" icon="pi pi-plus"
            class="!bg-emerald-600 hover:!bg-emerald-700 !border-0 !rounded-xl !text-xs !font-semibold"
            @click="addAssignment" />
        </div>
        <p class="text-[11px] text-slate-400 -mt-2">
          Only subjects and classes within this teacher's faculty are shown, so a teacher can't be assigned to teach
          outside
          their own faculty.
        </p>

        <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 max-h-72 overflow-y-auto">
          <div v-for="assignment in teacherAssignments" :key="assignment.id"
            class="flex items-center justify-between px-4 py-2.5">
            <div>
              <span class="text-xs font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded mr-2">{{
                assignment.subject_code }}</span>
              <span class="text-sm font-semibold text-slate-800">{{ assignment.subject_name }}</span>
              <span class="text-xs text-slate-400 ml-2">→ {{ assignment.class_name }}</span>
            </div>
            <Button icon="pi pi-trash"
              class="!p-1.5 !w-7 !h-7 !rounded-lg !text-slate-400 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
              @click="removeAssignment(assignment)" />
          </div>
          <div v-if="teacherAssignments.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">
            No subject/class assignments yet.
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end pt-3">
          <Button label="Close" icon="pi pi-times"
            class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold"
            @click="assignmentsDialog = false" />
        </div>
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import api, { extractError } from '../../../api'

// PrimeVue Components Import
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Textarea from 'primevue/textarea'
import DatePicker from 'primevue/datepicker'
import Password from 'primevue/password'

// ======= Date helpers (DatePicker binds to Date objects; the API speaks yyyy-mm-dd strings) =======
const parseApiDate = (value) => (value ? new Date(value) : null)

const formatDateForApi = (date) => {
  if (!date) return null
  const d = new Date(date)
  const yyyy = d.getFullYear()
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const dd = String(d.getDate()).padStart(2, '0')
  return `${yyyy}-${mm}-${dd}`
}

//show column more and less
const showAllColumns = ref(false);

// Visual-only sizing for the table wrapper: with all columns shown the table
// needs enough min-width to fit every column (so it scrolls horizontally),
// but with only the "always visible" columns shown it should fit the
// container width without a horizontal scrollbar.
const tableMinWidthStyle = computed(() =>
  showAllColumns.value ? 'min-width: 1700px' : 'min-width: 100%'
)

// Pagination display state (purely visual — mirrors FeedbackTable.vue's pattern
// so the #paginatorstart slot can show "Showing X to Y of Z").
const first = ref(0)
const rows = ref(10)

const teachers = ref([])
const faculties = ref([])
const departments = ref([])
const degrees = ref([])
const majors = ref([])
const subjects = ref([])
const classes = ref([])
const loading = ref(false)

const fetchTeachers = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/teachers', { params: { per_page: 100 } })
    teachers.value = data.data
  } finally {
    loading.value = false
  }
}

const fetchLookups = async () => {
  const [facultiesRes, departmentsRes, degreesRes, majorsRes, subjectsRes, classesRes] = await Promise.all([
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/departments', { params: { per_page: 200 } }),
    api.get('/degrees', { params: { per_page: 100 } }),
    api.get('/majors', { params: { per_page: 100 } }),
    api.get('/subjects', { params: { per_page: 200 } }),
    api.get('/classes', { params: { per_page: 200 } }),
  ])
  faculties.value = facultiesRes.data.data
  departments.value = departmentsRes.data.data
  degrees.value = degreesRes.data.data
  majors.value = majorsRes.data.data
  subjects.value = subjectsRes.data.data.map(s => ({ id: s.id, faculty_id: s.faculty_id, name_en: `${s.name_en} (${s.code})` }))
  classes.value = classesRes.data.data
}

// Department options are scoped to whichever faculty is currently selected
// in the form, since a department belongs to exactly one faculty.
const departmentOptions = computed(() =>
  departments.value.filter(d => d.faculty_id === teacherForm.value.faculty_id)
)

// Major options narrow down by Faculty (via the Faculty -> Degree -> Major chain).
// Degree itself is resolved automatically from the picked Major and never shown in the UI.
const majorsForSelectedFaculty = computed(() => {
  if (!teacherForm.value.faculty_id) return []
  const degreeIds = degrees.value
    .filter(d => d.faculty_id === teacherForm.value.faculty_id)
    .map(d => d.id)
  return majors.value.filter(m => degreeIds.includes(m.degree_id))
})

const onMajorChange = () => {
  const major = majors.value.find(m => m.id === teacherForm.value.major_id)
  teacherForm.value.degree_id = major?.degree_id ?? null
}

onMounted(() => {
  fetchTeachers()
  fetchLookups()
})

// Search Filter (ប្រើ String 'contains')
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// ======= Filter Bar (Faculty / Department / Degree / Type / Status) =======
const facultyFilter = ref(null)
const departmentFilter = ref(null)
const degreeFilter = ref(null)
const typeFilter = ref(null)
const statusFilter = ref(null)

const hasActiveFilters = computed(() =>
  !!(facultyFilter.value || departmentFilter.value || degreeFilter.value || typeFilter.value || statusFilter.value)
)

const clearFilters = () => {
  facultyFilter.value = null
  departmentFilter.value = null
  degreeFilter.value = null
  typeFilter.value = null
  statusFilter.value = null
}

const filteredTeachers = computed(() => teachers.value.filter((t) => {
  if (facultyFilter.value && t.faculty_id !== facultyFilter.value) return false
  if (departmentFilter.value && t.department_id !== departmentFilter.value) return false
  if (degreeFilter.value && t.degree_id !== degreeFilter.value) return false
  if (typeFilter.value && t.type !== typeFilter.value) return false
  if (statusFilter.value && t.status !== statusFilter.value) return false
  return true
}))

// Jump back to page 1 whenever the underlying list changes shape, so the
// paginator never gets stranded past the end of a smaller filtered list.
watch(filteredTeachers, () => {
  first.value = 0
})

// Dialog States & Form
const teacherDialog = ref(false)
const isEdit = ref(false)
const teacherForm = ref({
  id: null,
  code: '',
  name_en: '',
  name_kh: '',
  gender: 'Male',
  dob: null,
  address: '',
  faculty_id: null,
  department_id: null,
  degree_id: null,
  major_id: null,
  qualification: '',
  specialization: '',
  hire_date: null,
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
    dob: null,
    address: '',
    faculty_id: null,
    department_id: null,
    degree_id: null,
    major_id: null,
    qualification: '',
    specialization: '',
    hire_date: null,
    phone: '',
    email: '',
    password: '',
    type: 'Full-Time',
    status: 'Active',
    avatar: ''
  }
  isEdit.value = false
  teacherDialog.value = true
}

const editTeacher = (data) => {
  teacherForm.value = {
    ...data,
    dob: parseApiDate(data.dob),
    hire_date: parseApiDate(data.hire_date),
    password: '',
  }
  isEdit.value = true
  teacherDialog.value = true
}

const saveTeacher = async () => {
  if (!teacherForm.value.code || !teacherForm.value.name_en || !teacherForm.value.faculty_id || !teacherForm.value.phone) {
    alert('Teacher code, name (English), faculty, and phone are required.')
    return
  }
  if (!isEdit.value && (!teacherForm.value.password || teacherForm.value.password.length < 8)) {
    alert('Password is required and must be at least 8 characters.')
    return
  }
  if (isEdit.value && teacherForm.value.password && teacherForm.value.password.length < 8) {
    alert('Password must be at least 8 characters.')
    return
  }

  const payload = {
    code: teacherForm.value.code,
    name_en: teacherForm.value.name_en,
    name_kh: teacherForm.value.name_kh,
    gender: teacherForm.value.gender,
    dob: formatDateForApi(teacherForm.value.dob),
    address: teacherForm.value.address,
    faculty_id: teacherForm.value.faculty_id,
    department_id: teacherForm.value.department_id,
    degree_id: teacherForm.value.degree_id,
    major_id: teacherForm.value.major_id,
    qualification: teacherForm.value.qualification,
    specialization: teacherForm.value.specialization,
    hire_date: formatDateForApi(teacherForm.value.hire_date),
    phone: teacherForm.value.phone,
    email: teacherForm.value.email,
    type: teacherForm.value.type,
    status: teacherForm.value.status,
  }
  if (teacherForm.value.password) {
    payload.password = teacherForm.value.password
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
/* Header two sizes smaller, body two sizes larger, than the table's base text-xs
   (same convention as FeedbackTable.vue's .feedback-table). */
.teachers-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.teachers-table :deep(.p-datatable-tbody > tr > td) {
  font-size: 14px;
}

:deep(.custom-filter-dropdown) {
  background-color: #f8fafc !important; /* slate-50 */
  border: 1px solid #e2e8f0 !important;   /* slate-200 */
  border-radius: 0.75rem !important;      /* rounded-xl */
  font-size: 0.75rem !important;          /* text-xs */
  transition: all 0.2s ease;
  height: 38px;
  display: flex;
  align-items: center;
}

/* Hover & Focus State */
:deep(.custom-filter-dropdown:hover) {
  border-color: #cbd5e1 !important;      /* slate-300 */
  background-color: #ffffff !important;
}

:deep(.custom-filter-dropdown.p-focus) {
  border-color: #3b82f6 !important;      /* blue-500 */
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
  background-color: #ffffff !important;
}

/* Inner Label Padding & Text Color */
:deep(.custom-filter-dropdown .p-dropdown-label) {
  font-size: 0.75rem !important;
  padding: 0.4rem 0.75rem !important;
  color: #334155 !important;             /* slate-700 */
}

/* Clear Icon and Arrow Dropdown */
:deep(.custom-filter-dropdown .p-dropdown-trigger),
:deep(.custom-filter-dropdown .p-dropdown-clear-icon) {
  color: #94a3b8 !important;             /* slate-400 */
  width: 2rem !important;
}
</style>