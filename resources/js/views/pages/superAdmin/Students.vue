<template>
  <div class="space-y-6">

    <!-- ======= PAGE HEADER ======= -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-id-card text-blue-600 text-2xl"></i>
          Students Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          Manage Student and enrollment.
        </p>
      </div>

      <Button label="Add New Student" icon="pi pi-plus"
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm cursor-pointer"
        @click="openNewDialog" />
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

    <!-- ======= TABLE HEADER BAR / SEARCH & SHOW MORE/LESS TOGGLE ======= -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
      <!-- Search Input -->
      <div class="relative w-full sm:w-80">
        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
        <InputText v-model="filters['global'].value" placeholder="Search ID, student name, or class..."
          class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white" />
      </div>

      <div class="flex items-center justify-between w-full sm:w-auto gap-4">
        <!-- <div class="text-xs text-slate-400 shrink-0">
          Showing <b>{{ filteredStudents.length }}</b> entries
        </div> -->

        <!-- Show More / Show Less Button -->
        <Button :icon="showAllColumns ? 'pi pi-angle-double-left' : 'pi pi-angle-double-right'"
          class="!bg-blue-600 hover:!bg-blue-700 !text-white !border !border-slate-200 !rounded-xl !py-2 !px-3.5 !text-xs !font-semibold transition-all shadow-xs cursor-pointer"
          @click="showAllColumns = !showAllColumns" />

      </div>
    </div>

    <!-- ======= FILTER BAR ======= -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
      <!-- Header/Title Section សម្រាប់ Filter Bar -->
      <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-2 text-slate-700 font-semibold text-xs uppercase tracking-wider">
          <i class="pi pi-filter text-blue-600 text-sm"></i>
          <span>Filter Options</span>
        </div>

        <!-- ប៊ូតុង Clear Filters -->
        <Button v-if="hasActiveFilters" label="Reset Filters" icon="pi pi-filter-slash"
          class="!bg-rose-50 !text-rose-600 hover:!bg-rose-100 !border-0 !rounded-lg !py-1.5 !px-3 !text-xs !font-medium transition-all cursor-pointer"
          @click="clearFilters" />
      </div>

      <!-- Dropdowns Grid Container -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5">

        <!-- Faculty Filter -->
        <Dropdown v-model="facultyFilter" :options="faculties" optionLabel="name_en" optionValue="id"
          placeholder="All Faculties" showClear class="w-full custom-filter-dropdown" />

        <!-- Major Filter -->
        <Dropdown v-model="majorFilter" :options="majorOptions" optionLabel="name" optionValue="id"
          placeholder="All Majors" showClear class="w-full custom-filter-dropdown" />

        <!-- Class Filter -->
        <Dropdown v-model="classFilter" :options="classes" optionLabel="name" optionValue="id" placeholder="All Classes"
          showClear class="w-full custom-filter-dropdown" />

        <!-- Generation Filter -->
        <Dropdown v-model="promotionFilter" :options="promotions" optionLabel="name_en" optionValue="id"
          placeholder="All Generations" showClear class="w-full custom-filter-dropdown" />

        <!-- Status Filter -->
        <Dropdown v-model="statusFilter" :options="['Active', 'Inactive', 'Suspended']" placeholder="All Statuses"
          showClear class="w-full custom-filter-dropdown" />

      </div>
    </div>

    <!-- ======= DATA TABLE ======= -->
    <DataTable :value="filteredStudents" v-model:filters="filters"
      :globalFilterFields="['student_id', 'name_en', 'name_kh', 'class_name', 'phone', 'email', 'major']" dataKey="id"
      paginator :rows="5" :rowsPerPageOptions="[5, 10, 20]" responsiveLayout="scroll"
      class="p-datatable-sm custom-app-table">
      <template #empty>
        <div class="text-center py-6 text-slate-400 text-sm">
          No students found.
        </div>
      </template>

      <!-- Student ID -->
      <Column field="student_id" header="STUDENT ID" sortable>
        <template #body="{ data }">
          <span
            class="font-mono text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
            {{ data.student_id }}
          </span>
        </template>
      </Column>

      <!-- Student Name (English) -->
      <Column field="name_en" header="STUDENT NAME" sortable>
        <template #body="{ data }">
          <span class="font-semibold text-slate-800 text-xs">{{ data.name_en }}</span>
        </template>
      </Column>

      <!-- Student Name (Khmer) -->
      <Column v-if="showAllColumns" field="name_kh" header="STUDENT NAME (KH)">
        <template #body="{ data }">
          <span v-if="data.name_kh" class="text-slate-600 text-xs font-khmer">{{ data.name_kh }}</span>
          <span v-else class="text-slate-400 text-xs">—</span>
        </template>
      </Column>

      <!-- Gender -->
      <Column v-if="showAllColumns" field="gender" header="GENDER">
        <template #body="{ data }">
          <span v-if="data.gender"
            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full border"
            :class="data.gender === 'Male' ? 'bg-sky-50 text-sky-700 border-sky-200' : 'bg-pink-50 text-pink-700 border-pink-200'">
            <i class="text-[9px]" :class="data.gender === 'Male' ? 'pi pi-mars' : 'pi pi-venus'"></i>
            {{ data.gender }}
          </span>
          <span v-else class="text-slate-400 text-xs">—</span>
        </template>
      </Column>

      <!-- Class -->
      <Column field="class_name" header="CLASS" sortable>
        <template #body="{ data }">
          <span class="text-xs font-semibold text-slate-700">{{ data.class_name }}</span>
        </template>
      </Column>

      <!-- Shift -->
      <Column v-if="showAllColumns" field="shift" header="SHIFT">
        <template #body="{ data }">
          <span class="text-[11px] text-indigo-600 font-medium">{{ data.shift }}</span>
        </template>
      </Column>

      <!-- Phone -->
      <Column field="phone" header="PHONE">
        <template #body="{ data }">
          <span class="text-slate-700 text-xs">{{ data.phone }}</span>
        </template>
      </Column>

      <!-- Email -->
      <Column v-if="showAllColumns" field="email" header="EMAIL">
        <template #body="{ data }">
          <span class="text-slate-700 text-[11px]">{{ data.email }}</span>
        </template>
      </Column>

      <!-- Faculty -->
      <Column v-if="showAllColumns" field="faculty_name" header="FACULTY" sortable>
        <template #body="{ data }">
          <span class="text-[11px] text-slate-600 font-medium">{{ data.faculty_name || '—' }}</span>
        </template>
      </Column>

      <!-- Department -->
      <Column v-if="showAllColumns" field="department_name" header="DEPARTMENT" sortable>
        <template #body="{ data }">
          <span class="text-[11px] text-slate-600 font-medium">{{ data.department_name || '—' }}</span>
        </template>
      </Column>

      <!-- Major / Department -->
      <Column v-if="showAllColumns" field="major" header="MAJOR" sortable>
        <template #body="{ data }">
          <span
            class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
            {{ data.major }}
          </span>
        </template>
      </Column>

      <!-- Generation -->
      <Column v-if="showAllColumns" field="generation" header="GENERATION" sortable>
        <template #body="{ data }">
          <span v-if="data.generation"
            class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-100">
            {{ data.generation }}
          </span>
          <span v-else class="text-slate-400 text-xs">—</span>
        </template>
      </Column>

      <!-- Academic Year -->
      <Column v-if="showAllColumns" field="academic_year_name" header="ACADEMIC YEAR" sortable>
        <template #body="{ data }">
          <span class="text-[11px] text-slate-600 font-medium">{{ data.academic_year_name || '—' }}</span>
        </template>
      </Column>

      <!-- Stage -->
      <Column v-if="showAllColumns" field="stage_name" header="STAGE" sortable>
        <template #body="{ data }">
          <span class="text-[11px] text-slate-600 font-medium">{{ data.stage_name || '—' }}</span>
        </template>
      </Column>

      <!-- Semester -->
      <Column v-if="showAllColumns" field="semester_name" header="SEMESTER" sortable>
        <template #body="{ data }">
          <span class="text-[11px] text-slate-600 font-medium">{{ data.semester_name || '—' }}</span>
        </template>
      </Column>

      <!-- Term -->
      <Column v-if="showAllColumns" field="term_name" header="TERM" sortable>
        <template #body="{ data }">
          <span class="text-[11px] text-slate-600 font-medium">{{ data.term_name || '—' }}</span>
        </template>
      </Column>

      <!-- Status -->
      <Column field="status" header="STATUS" sortable>
        <template #body="{ data }">
          <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium" :class="{
            'bg-emerald-50 text-emerald-700 border border-emerald-200': data.status === 'Active',
            'bg-rose-50 text-rose-700 border border-rose-200': data.status === 'Inactive',
            'bg-amber-50 text-amber-700 border border-amber-200': data.status === 'Suspended'
          }">
            <span class="w-1.5 h-1.5 rounded-full" :class="{
              'bg-emerald-500': data.status === 'Active',
              'bg-rose-500': data.status === 'Inactive',
              'bg-amber-500': data.status === 'Suspended'
            }"></span>
            {{ data.status }}
          </span>
        </template>
      </Column>

      <!-- Actions -->
      <Column header="ACTIONS" class="!text-right">
        <template #body="{ data }">
          <div class="flex items-center justify-end gap-1">
            <Button icon="pi pi-sitemap"
              class="!p-1.5 !w-7 !h-7 !rounded-lg !bg-emerald-50 !text-emerald-600 hover:!bg-emerald-100 hover:!text-emerald-700 !border !border-emerald-100 cursor-pointer text-xs"
              title="Assign Class" @click="openAssignDialog(data)" />
            <Button icon="pi pi-pencil"
              class="!p-1.5 !w-7 !h-7 !rounded-lg !bg-blue-50 !text-blue-600 hover:!bg-blue-100 hover:!text-blue-700 !border !border-blue-100 cursor-pointer text-xs"
              title="Edit Student" @click="editStudent(data)" />
            <Button icon="pi pi-trash"
              class="!p-1.5 !w-7 !h-7 !rounded-lg !bg-rose-50 !text-rose-600 hover:!bg-rose-100 hover:!text-rose-700 !border !border-rose-100 cursor-pointer text-xs"
              title="Delete Student" @click="confirmDeleteStudent(data)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog v-model:visible="studentDialog" :header="isEdit ? 'Edit Student Information' : 'Add New Student'"
      :modal="true" class="w-full max-w-3xl">
      <div class="space-y-5 pt-2">
        <!-- ======= SECTION: PERSONAL INFORMATION ======= -->
        <div class="flex items-center gap-2 text-slate-700 font-bold text-xs uppercase tracking-wider pb-2 border-b border-slate-200">
          <i class="pi pi-user text-blue-600 text-sm"></i>
          <span>Personal Information</span>
        </div>

        <!-- Student ID / Full Name (English) / Full Name (Khmer) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Student ID *</label>
            <InputText v-model="studentForm.student_id" placeholder="e.g. STU-1001"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name (English) *</label>
            <InputText v-model="studentForm.name_en" placeholder="e.g. Sok Visal"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name (Khmer)</label>
            <InputText v-model="studentForm.name_kh" placeholder="ឧ. សុខ វិសាល"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm font-khmer" />
          </div>
        </div>

        <!-- Gender / Phone / Email -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Gender *</label>
            <Dropdown v-model="studentForm.gender" :options="['Male', 'Female']" placeholder="Select Gender"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Phone Number *</label>
            <InputText v-model="studentForm.phone" placeholder="012 345 678"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email Address</label>
            <InputText v-model="studentForm.email" placeholder="student@school.edu.kh"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <!-- Date of Birth / Status -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Date of Birth</label>
            <DatePicker v-model="studentForm.dob" dateFormat="yy-mm-dd" showIcon iconDisplay="input"
              placeholder="Select date" class="w-full"
              inputClass="!py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown v-model="studentForm.status" :options="['Active', 'Inactive', 'Suspended']"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>

        <!-- Address (last Personal Information field, full width) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="sm:col-span-3">
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Address</label>
            <Textarea v-model="studentForm.address" rows="2" placeholder="Street, city, country"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <!-- ======= SECTION: ACADEMIC INFORMATION ======= -->
        <div class="flex items-center gap-2 text-slate-700 font-bold text-xs uppercase tracking-wider pb-2 pt-2 border-b border-slate-200">
          <i class="pi pi-graduation-cap text-emerald-600 text-sm"></i>
          <span>Academic Information</span>
        </div>

        <!-- Class / Faculty / Department -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class *</label>
            <Dropdown v-model="studentForm.class_id" :options="classes" optionLabel="name" optionValue="id"
              placeholder="Select Class" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty</label>
            <Dropdown v-model="studentForm.faculty_id" :options="faculties" optionLabel="name_en" optionValue="id"
              placeholder="Select Faculty" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department</label>
            <Dropdown v-model="studentForm.department_id" :options="departments" optionLabel="name_en" optionValue="id"
              placeholder="Select Department" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>

        <!-- Major / Promotion / Academic Year -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major</label>
            <Dropdown v-model="studentForm.major_id" :options="majors" optionLabel="name_en" optionValue="id"
              placeholder="Select Major" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Promotion</label>
            <Dropdown v-model="studentForm.promotion_id" :options="promotions" optionLabel="name_en" optionValue="id"
              placeholder="Select Promotion" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Academic Year</label>
            <Dropdown v-model="studentForm.academic_year_id" :options="academicYears" optionLabel="name_en" optionValue="id"
              placeholder="Select Academic Year" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>

        <!-- Stage / Semester / Term -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Stage</label>
            <Dropdown v-model="studentForm.stage_id" :options="stages" optionLabel="name_en" optionValue="id"
              placeholder="Select Stage" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Semester</label>
            <Dropdown v-model="studentForm.semester_id" :options="semesters" optionLabel="name_en" optionValue="id"
              placeholder="Select Semester" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Term</label>
            <Dropdown v-model="studentForm.term_id" :options="terms" optionLabel="name_en" optionValue="id"
              placeholder="Select Term" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>

        <!-- Shift -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shift</label>
            <Dropdown v-model="studentForm.shift_id" :options="shifts" optionLabel="name_en" optionValue="id"
              placeholder="Select Shift" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times"
            class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold cursor-pointer"
            @click="studentDialog = false" />
          <Button label="Save Student" icon="pi pi-check"
            class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold cursor-pointer"
            @click="saveStudent" />
        </div>
      </template>
    </Dialog>

    <!-- ======= ASSIGN CLASS DIALOG ======= -->
    <Dialog v-model:visible="assignDialog" header="Assign Class" :modal="true" class="w-full max-w-md">
      <div class="space-y-4 pt-2">
        <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-xl p-3">
          <div
            class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold shrink-0">
            {{ assignForm.name_en?.charAt(0) || '?' }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-800 truncate m-0">{{ assignForm.name_en }}</p>
            <p class="text-[11px] text-slate-500 m-0">{{ assignForm.student_id }}</p>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class *</label>
          <Dropdown v-model="assignForm.class_id" :options="classes" optionLabel="name" optionValue="id"
            placeholder="Select Class" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
        </div>

        <!-- Subjects for the selected class are automatic: whichever subjects a
             teacher is assigned to teach for this class (see Teachers > Assignments). -->
        <div v-if="assignForm.class_id">
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subjects</label>
          <div class="flex flex-wrap gap-1.5 bg-slate-50 border border-slate-200 rounded-xl p-2.5 min-h-[2.5rem]">
            <span v-for="subject in assignClassSubjects" :key="subject"
              class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100">
              {{ subject }}
            </span>
            <span v-if="assignClassSubjects.length === 0" class="text-[11px] text-slate-400">
              No subjects assigned to this class yet.
            </span>
          </div>
          <p class="text-[10px] text-slate-400 mt-1">
            Subjects follow the class automatically. Assign teachers to subjects for this class on the Teachers page.
          </p>
        </div>

        <!-- <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Generation</label>
            <Dropdown v-model="assignForm.promotion_id" :options="promotions" optionLabel="name_en" optionValue="id"
              placeholder="Select Generation" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown v-model="assignForm.status" :options="['Active', 'Inactive', 'Suspended']"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div> -->
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times"
            class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold cursor-pointer"
            @click="assignDialog = false" />
          <Button label="Assign" icon="pi pi-check"
            class="!bg-emerald-600 hover:!bg-emerald-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold cursor-pointer"
            @click="saveAssign" />
        </div>
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
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

const showAllColumns = ref(false);

const students = ref([])
const classes = ref([])
const promotions = ref([])
const faculties = ref([])
const departments = ref([])
const majors = ref([])
const academicYears = ref([])
const stages = ref([])
const semesters = ref([])
const terms = ref([])
const shifts = ref([])

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

const fetchLookups = async () => {
  const [facultiesRes, departmentsRes, majorsRes, academicYearsRes, stagesRes, semestersRes, termsRes, shiftsRes] = await Promise.all([
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/departments', { params: { per_page: 100 } }),
    api.get('/majors', { params: { per_page: 100 } }),
    api.get('/academic-years', { params: { per_page: 100 } }),
    api.get('/stages', { params: { per_page: 100 } }),
    api.get('/semesters', { params: { per_page: 100 } }),
    api.get('/terms', { params: { per_page: 100 } }),
    api.get('/shifts', { params: { per_page: 100 } }),
  ])
  faculties.value = facultiesRes.data.data
  departments.value = departmentsRes.data.data
  majors.value = majorsRes.data.data
  academicYears.value = academicYearsRes.data.data
  stages.value = stagesRes.data.data
  semesters.value = semestersRes.data.data
  terms.value = termsRes.data.data
  shifts.value = shiftsRes.data.data
}

onMounted(() => {
  fetchStudents()
  fetchClasses()
  fetchPromotions()
  fetchLookups()
})

// Search Filter
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// ======= Filter Bar (Faculty / Major / Class / Generation / Status) =======
const facultyFilter = ref(null)
const majorFilter = ref(null)
const classFilter = ref(null)
const promotionFilter = ref(null)
const statusFilter = ref(null)

// Majors don't have their own fetch on this page; build the option list
// from whatever majors already appear among enrolled students.
const majorOptions = computed(() => {
  const seen = new Map()
  students.value.forEach((s) => {
    if (s.major_id && !seen.has(s.major_id)) seen.set(s.major_id, s.major)
  })
  return Array.from(seen, ([id, name]) => ({ id, name }))
})

const classFacultyId = (classId) => classes.value.find((c) => c.id === classId)?.faculty_id

const hasActiveFilters = computed(() =>
  !!(facultyFilter.value || majorFilter.value || classFilter.value || promotionFilter.value || statusFilter.value)
)

const clearFilters = () => {
  facultyFilter.value = null
  majorFilter.value = null
  classFilter.value = null
  promotionFilter.value = null
  statusFilter.value = null
}

const filteredStudents = computed(() => students.value.filter((s) => {
  if (facultyFilter.value && classFacultyId(s.class_id) !== facultyFilter.value) return false
  if (majorFilter.value && s.major_id !== majorFilter.value) return false
  if (classFilter.value && s.class_id !== classFilter.value) return false
  if (promotionFilter.value && s.promotion_id !== promotionFilter.value) return false
  if (statusFilter.value && s.status !== statusFilter.value) return false
  return true
}))

// Dialog States & Form
const studentDialog = ref(false)
const isEdit = ref(false)
const studentForm = ref({
  id: null,
  student_id: '',
  name_en: '',
  name_kh: '',
  gender: 'Male',
  dob: null,
  address: '',
  class_id: null,
  faculty_id: null,
  department_id: null,
  major_id: null,
  promotion_id: null,
  academic_year_id: null,
  stage_id: null,
  semester_id: null,
  term_id: null,
  shift_id: null,
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
    dob: null,
    address: '',
    class_id: null,
    faculty_id: null,
    department_id: null,
    major_id: null,
    promotion_id: null,
    academic_year_id: null,
    stage_id: null,
    semester_id: null,
    term_id: null,
    shift_id: null,
    phone: '',
    email: '',
    status: 'Active',
    avatar: ''
  }
  isEdit.value = false
  studentDialog.value = true
}

const editStudent = (data) => {
  studentForm.value = {
    ...data,
    dob: parseApiDate(data.dob),
  }
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
    dob: formatDateForApi(studentForm.value.dob),
    address: studentForm.value.address,
    class_id: studentForm.value.class_id,
    faculty_id: studentForm.value.faculty_id,
    department_id: studentForm.value.department_id,
    major_id: studentForm.value.major_id,
    promotion_id: studentForm.value.promotion_id,
    academic_year_id: studentForm.value.academic_year_id,
    stage_id: studentForm.value.stage_id,
    semester_id: studentForm.value.semester_id,
    term_id: studentForm.value.term_id,
    shift_id: studentForm.value.shift_id,
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

// ======= Assign Class (quick action, separate from full Edit) =======
const assignDialog = ref(false)
const assignForm = ref({ id: null, name_en: '', student_id: '', class_id: null, promotion_id: null, status: 'Active' })
const assignClassSubjects = ref([])

const openAssignDialog = (data) => {
  assignForm.value = {
    id: data.id,
    name_en: data.name_en,
    student_id: data.student_id,
    class_id: data.class_id,
    promotion_id: data.promotion_id,
    status: data.status,
  }
  assignClassSubjects.value = []
  assignDialog.value = true
  if (data.class_id) fetchClassSubjects(data.class_id)
}

const fetchClassSubjects = async (classId) => {
  try {
    const { data } = await api.get('/teacher-assignments', { params: { class_id: classId } })
    assignClassSubjects.value = [...new Set(data.data.map((a) => a.subject_name).filter(Boolean))]
  } catch (error) {
    assignClassSubjects.value = []
  }
}

watch(() => assignForm.value.class_id, (classId) => {
  if (assignDialog.value && classId) fetchClassSubjects(classId)
})

const saveAssign = async () => {
  if (!assignForm.value.class_id) {
    alert('Please select a class.')
    return
  }

  try {
    await api.patch(`/students/${assignForm.value.id}/assign`, {
      class_id: assignForm.value.class_id,
      promotion_id: assignForm.value.promotion_id,
      status: assignForm.value.status,
    })

    assignDialog.value = false
    await fetchStudents()
  } catch (error) {
    alert(extractError(error))
  }
}
</script>

<!-- <style scoped>
/* Spacing រវាង Card Rows */
.students-table :deep(.p-datatable-table) {
  border-collapse: separate;
  border-spacing: 0 0.35rem;
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

/* TABLE HEADER: បន្ថែម Padding 0.75rem លើ-ក្រោម ឲ្យកម្ពស់ខ្ពស់ស្រឡះ */
.students-table :deep(.p-datatable-thead > tr > th) {
  background: #ffffff;
  border: none !important;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  padding: 0.75rem 0.85rem;
  height: 40px !important;
  /* កំណត់កម្ពស់ Header ឲ្យច្បាស់ (52px) */
  padding: 0.85rem 0.85rem !important;
  /* បន្ថែម padding លើ-ក្រោម */
  white-space: nowrap;
  background-color: #002060;
}

.students-table :deep(.p-datatable-thead > tr > th:first-child) {
  border-top-left-radius: 0.5rem;
  border-bottom-left-radius: 0.5rem;
}

.students-table :deep(.p-datatable-thead > tr > th:last-child) {
  border-top-right-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
}

/* TABLE BODY CELLS: រក្សា Padding 0.38rem លើ-ក្រោម ឲ្យទាប Compact */
.students-table :deep(.p-datatable-tbody > tr > td) {
  background: #ffffff;
  border: none !important;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
  line-height: 1.2;
  padding: 0.38rem 0.85rem;
  transition: background-color 0.15s ease;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.students-table :deep(.p-datatable-tbody > tr > td:first-child) {
  border-top-left-radius: 0.5rem;
  border-bottom-left-radius: 0.5rem;
}

.students-table :deep(.p-datatable-tbody > tr > td:last-child) {
  border-top-right-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
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
  padding-top: 0.5rem;
}

/*Filter */

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

/* Hover & Focus Effect */
:deep(.custom-filter-dropdown:hover) {
  border-color: #cbd5e1 !important;      /* slate-300 */
  background-color: #ffffff !important;
}

:deep(.custom-filter-dropdown.p-focus) {
  border-color: #3b82f6 !important;      /* blue-500 */
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
  background-color: #ffffff !important;
}

/* Label & Inner Padding adjustment */
:deep(.custom-filter-dropdown .p-dropdown-label) {
  font-size: 0.75rem !important;
  padding: 0.4rem 0.75rem !important;
  color: #334155 !important;             /* slate-700 */
}

/* Clear & Trigger Icons */
:deep(.custom-filter-dropdown .p-dropdown-trigger),
:deep(.custom-filter-dropdown .p-dropdown-clear-icon) {
  color: #94a3b8 !important;             /* slate-400 */
  width: 2rem !important;
}
</style> -->