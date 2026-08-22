<template>
  <div class="space-y-6">

    <!-- ======= PAGE HEADER ======= -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-[#002060] m-0 flex items-center gap-2">
          <i class="pi pi-users text-[#e4ac40] text-2xl"></i>
          Classes Management
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          Manage all classes, their details, and student enrollment information.
        </p>
      </div>

      <Button label="Add New Class" icon="pi pi-plus"
        class="!bg-[#002060] hover:!bg-blue-900 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog" />
    </div>

    <!-- TODO: ======= STATS CARDS ======= -->
    <!-- <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Students
            Enrolled</span>
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
    </div> -->

    <!-- TODO: ======= TOOLBAR: SEARCH & VIEW MODE SWITCHER ======= -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 ">
      <div class="relative w-full sm:w-80">
        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
        <InputText v-model="filters['global'].value" placeholder="Search class code or name..."
          class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white" />
      </div>

      <div class="flex items-center justify-between w-full sm:w-auto gap-4">
        <!-- <span class="text-xs text-slate-400">
          Showing <b>{{ filteredClasses.length }}</b> entries
        </span> -->

        <!-- View Mode Switcher Buttons -->
        <div class="flex items-center bg-white p-1 rounded-xl border border-slate-200">
          <button @click="viewMode = 'grid'"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
            :class="viewMode === 'grid' ? 'bg-[#002060] text-white shadow-sm' : 'text-slate-500 hover:text-slate-800'">
            <i class="pi pi-th-large"></i> Cards
          </button>
          <button @click="viewMode = 'list'"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
            :class="viewMode === 'list' ? 'bg-[#002060] text-white shadow-sm' : 'text-slate-500 hover:text-slate-800'">
            <i class="pi pi-list"></i> List
          </button>
        </div>
      </div>
    </div>

    <!-- ======= FILTER BAR ======= -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
      <!-- Header/Title Section សម្រាប់ Filter Bar -->
      <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-2 text-slate-700 font-semibold text-xs uppercase tracking-wider">
          <i class="pi pi-filter text-[#e4ac40] text-sm"></i>
          <span>Filter Options</span>
        </div>

        <!-- Clear Filters Button -->
        <Button v-if="hasActiveFilters" label="Reset Filters" icon="pi pi-filter-slash"
          class="!bg-rose-50 !text-rose-600 hover:!bg-rose-100 !border-0 !rounded-lg !py-1.5 !px-3 !text-xs !font-medium transition-all cursor-pointer"
          @click="clearFilters" />
      </div>

      <!-- Dropdowns Grid Container (5 Columns on Large Screens) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5">

        <!-- Major Filter -->
        <Dropdown v-model="majorFilter" :options="majors" optionLabel="name_en" optionValue="id"
          placeholder="All Departments/Majors" showClear class="w-full custom-filter-dropdown" />

        <!-- Stage Filter -->
        <Dropdown v-model="stageFilter" :options="stages" optionLabel="name_en" optionValue="id"
          placeholder="All Stages" showClear class="w-full custom-filter-dropdown" />

        <!-- Shift Filter -->
        <Dropdown v-model="shiftFilter" :options="shifts" optionLabel="name_en" optionValue="id"
          placeholder="All Shifts" showClear class="w-full custom-filter-dropdown" />

        <!-- Term Filter -->
        <Dropdown v-model="termFilter" :options="terms" optionLabel="name_en" optionValue="id" placeholder="All Terms"
          showClear class="w-full custom-filter-dropdown" />

        <!-- Status Filter -->
        <Dropdown v-model="statusFilter" :options="['Active', 'Inactive']" placeholder="All Statuses" showClear
          class="w-full custom-filter-dropdown" />

      </div>
    </div>

    <!-- ======= 1. LIST VIEW (DataTable) ======= -->
    <div v-if="viewMode === 'list'" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="text-sm font-bold text-[#002060] m-0">All Classes</h3>
        <div class="flex items-center gap-2">
          <!-- <span class="text-xs text-slate-500">
            <span class="font-semibold text-slate-700">{{ filteredClasses.length }}</span> class(es) found
          </span> -->

          <!-- Show More / Show Less Toggle Button -->
          <Button :label="showAllColumns ? 'Fewer Column' : 'More Column'"
            :icon="showAllColumns ? 'pi pi-angle-double-left' : 'pi pi-angle-double-right'" size="small"
            class="!bg-[#002060] !border-slate-100 !text-white hover:!bg-blue-900 !rounded-lg !text-xs !font-semibold !px-3 !py-1"
            @click="showAllColumns = !showAllColumns" />
        </div>
      </div>

      <DataTable :value="filteredClasses" dataKey="id" paginator :rows="rows" v-model:first="first"
        :rowsPerPageOptions="[10, 20, 50]" scrollable scrollHeight="calc(100vh - 428px)"
        :scrollDirection="showAllColumns ? 'both' : 'vertical'"
        :tableStyle="tableMinWidthStyle"
        responsiveLayout="scroll" class="p-datatable-sm classes-table">
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">
            No classes found.
          </div>
        </template>

        <template #paginatorstart>
          <span class="text-xs text-[#002060]">
            Showing <span class="font-semibold text-[slate-700]">{{ filteredClasses.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, filteredClasses.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ filteredClasses.length }}</span>
          </span>
        </template>

        <!-- Class Code - តែងតែបង្ហាញ -->
        <Column field="code" header="CODE" sortable style="padding-left: 1.25rem">
          <template #body="{ data }">
            <span class="font-mono font-bold text-indigo-600 text-sm whitespace-nowrap">{{ data.code }}</span>
          </template>
        </Column>

        <!-- Class Name - តែងតែបង្ហាញ -->
        <Column field="name" header="CLASS NAME" sortable>
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-sm whitespace-nowrap">{{ data.name }}</span>
          </template>
        </Column>

        <!-- Class Name (Khmer) - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="name_kh" header="CLASS NAME (KH)">
          <template #body="{ data }">
            <span v-if="data.name_kh" class="text-slate-600 text-sm font-khmer whitespace-nowrap">{{ data.name_kh }}</span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Faculty - តែងតែបង្ហាញ -->
        <Column field="faculty_name" header="FACULTY" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-sm whitespace-nowrap">{{ data.faculty_name || '—' }}</span>
          </template>
        </Column>

        <!-- Department - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="department_name" header="DEPARTMENT" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-sm whitespace-nowrap">{{ data.department_name || '—' }}</span>
          </template>
        </Column>

        <!-- Major - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="major_name" header="MAJOR" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-sm whitespace-nowrap">{{ data.major_name || '—' }}</span>
          </template>
        </Column>

        <!-- Promotion - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="promotion_name" header="PROMOTION" sortable>
          <template #body="{ data }">
            <span v-if="data.promotion_name"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap bg-indigo-50 text-indigo-600 border-indigo-200">
              {{ data.promotion_name }}
            </span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Academic Year - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="academic_year" header="ACADEMIC YEAR" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-sm whitespace-nowrap">{{ data.academic_year || '—' }}</span>
          </template>
        </Column>

        <!-- Stage - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="stage" header="STAGE" sortable>
          <template #body="{ data }">
            <span class="text-slate-700 text-sm font-semibold whitespace-nowrap">{{ data.stage }}</span>
          </template>
        </Column>

        <!-- Semester - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="semester" header="SEMESTER" sortable>
          <template #body="{ data }">
            <span class="text-slate-600 text-sm whitespace-nowrap">{{ data.semester || '—' }}</span>
          </template>
        </Column>

        <!-- Session -->
        <!-- <Column field="study_session_name" header="SESSION" sortable>
          <template #body="{ data }">
            <span v-if="data.study_session_name"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block bg-teal-50 text-teal-600 border-teal-200">
              {{ data.study_session_name }}
            </span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column> -->

        <!-- Term - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="term" header="TERM" sortable>
          <template #body="{ data }">
            <span v-if="data.term"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap bg-amber-50 text-amber-600 border-amber-200">
              {{ data.term }}
            </span>
            <span v-else class="text-slate-400 text-sm">—</span>
          </template>
        </Column>

        <!-- Shift - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="shift" header="SHIFT">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm whitespace-nowrap">{{ data.shift }}</span>
          </template>
        </Column>

        <!-- Enrolled / Capacity - តែងតែបង្ហាញ -->
        <Column field="students_count" header="STUDENTS" sortable>
          <template #body="{ data }">
            <div class="flex items-center gap-2 whitespace-nowrap">
              <span class="font-bold text-slate-700 text-sm whitespace-nowrap">
                {{ data.students_count }} / {{ data.capacity }}
              </span>
              <div class="w-16 bg-slate-100 h-1.5 rounded-full overflow-hidden shrink-0">
                <div class="h-full rounded-full"
                  :class="data.students_count >= data.capacity ? 'bg-rose-500' : 'bg-blue-500'"
                  :style="{ width: Math.min((data.students_count / data.capacity) * 100, 100) + '%' }"></div>
              </div>
            </div>
          </template>
        </Column>

        <!-- Status - តែងតែបង្ហាញ -->
        <Column field="status" header="STATUS" sortable>
          <template #body="{ data }">
            <span
              :class="data.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap">
              {{ data.status.toUpperCase() }}
            </span>
          </template>
        </Column>

        <!-- Actions - តែងតែបង្ហាញ -->
        <Column header="ACTIONS" class="!text-right" style="padding-right: 1.25rem">
          <template #body="{ data }">
            <div class="flex items-center justify-end gap-1.5">
              <Button icon="pi pi-book"
                class="!p-2 !w-8 !h-8 !rounded-xl !bg-indigo-50 !text-indigo-600 hover:!bg-indigo-100 hover:!text-indigo-700 !border !border-indigo-100 shadow-xs"
                title="Subjects Taught" @click="openSubjectsDialog(data)" />
              <Button icon="pi pi-pencil"
                class="!p-2 !w-8 !h-8 !rounded-xl !bg-slate-100 !text-slate-600 hover:!bg-slate-200 hover:!text-slate-800 !border !border-slate-100 shadow-xs"
                title="Edit Class" @click="editClass(data)" />
              <Button icon="pi pi-trash"
                class="!p-2 !w-8 !h-8 !rounded-xl !bg-rose-50 !text-rose-600 hover:!bg-rose-100 hover:!text-rose-700 !border !border-rose-100 shadow-xs"
                title="Delete Class" @click="confirmDeleteClass(data)" />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- ======= 2. REDESIGNED COMPACT CARD VIEW ======= -->
    <div v-else>
      <div v-if="filteredClasses.length === 0"
        class="text-center py-12 bg-white rounded-2xl border border-slate-200 text-slate-400 text-sm">
        No classes found.
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div v-for="cls in filteredClasses" :key="cls.id"
          class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-sm hover:border-[#63c7df] hover:shadow-md transition-all duration-200 flex flex-col justify-between relative group">
          <div>
            <!-- Header Row: Code & Status -->
            <div class="flex items-center justify-between mb-2.5">
              <span
                class="font-mono text-[11px] font-bold text-white bg-[#002060] px-2 py-0.5 rounded-md border border-blue-100/80">
                {{ cls.code }}
              </span>

              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold"
                :class="cls.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200/60' : 'bg-rose-50 text-rose-600 border border-rose-200/60'">
                <span class="w-1.5 h-1.5 rounded-full"
                  :class="cls.status === 'Active' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                {{ cls.status }}
              </span>
            </div>

            <!-- Title & Subtitle -->
            <div class="mb-3">
              <h3 class="font-bold text-[#002060] text-sm line-clamp-1 m-0" :title="cls.name">{{ cls.name }}</h3>
              <p v-if="cls.name_kh" class="text-[11px] text-slate-500 font-khmer m-0 mt-0.5 line-clamp-1">{{ cls.name_kh
                }}
              </p>
              <p class="text-[11px] text-slate-400 m-0 mt-0.5 line-clamp-1">{{ cls.department || 'No Department' }}</p>
            </div>

            <!-- Detailed Grid Info -->
            <div class="space-y-1.5 py-2.5 border-t border-b border-slate-100 text-xs">
              <!-- Stage -->
              <div class="flex items-center justify-between">
                <span class="text-slate-400 flex items-center gap-1.5 text-[11px]">
                  <i class="pi pi-graduation-cap text-slate-400 text-xs"></i> Stage
                </span>
                <span class="font-semibold text-slate-700 text-xs">{{ cls.stage }}</span>
              </div>

              <!-- Shift -->
              <div class="flex items-center justify-between">
                <span class="text-slate-400 flex items-center gap-1.5 text-[11px]">
                  <i class="pi pi-clock text-slate-400 text-xs"></i> Shift
                </span>
                <span class="font-medium text-slate-700 text-xs">{{ cls.shift }}</span>
              </div>

              <!-- Term -->
              <div v-if="cls.term" class="flex items-center justify-between">
                <span class="text-slate-400 flex items-center gap-1.5 text-[11px]">
                  <i class="pi pi-calendar text-slate-400 text-xs"></i> Term
                </span>
                <span
                  class="font-semibold text-amber-700 bg-amber-50/80 px-2 py-0.5 rounded text-[11px] border border-amber-100/60">
                  {{ cls.term }}
                </span>
              </div>
            </div>

            <!-- Students Enrolled Progress -->
            <div class="mt-3">
              <div class="flex justify-between items-center text-[11px] font-semibold mb-1">
                <span class="text-slate-400">Students Enrolled</span>
                <span class="text-slate-700 font-bold">{{ cls.students_count || 0 }} / {{ cls.capacity }}</span>
              </div>
              <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-300"
                  :class="cls.students_count >= cls.capacity ? 'bg-rose-500' : 'bg-blue-600'"
                  :style="{ width: Math.min(((cls.students_count || 0) / cls.capacity) * 100, 100) + '%' }"></div>
              </div>
            </div>
          </div>

          <!-- Bottom Action Buttons -->
          <div class="flex items-center justify-end gap-1.5 pt-3 mt-3 border-t border-slate-100">
            <Button label="Subjects" icon="pi pi-book"
              class="!py-1 !px-2.5 !text-[11px] !font-medium !bg-indigo-50/60 hover:!bg-indigo-100 !text-indigo-600 !border-indigo-100 !rounded-lg"
              @click="openSubjectsDialog(cls)" />
            <Button label="Edit" icon="pi pi-pencil"
              class="!py-1 !px-2.5 !text-[11px] !font-medium !bg-slate-50 hover:!bg-blue-50 hover:!text-blue-600 !text-slate-600 !border-slate-200 !rounded-lg"
              @click="editClass(cls)" />
            <Button label="Delete" icon="pi pi-trash"
              class="!py-1 !px-2.5 !text-[11px] !font-medium !bg-rose-50/60 hover:!bg-rose-100 !text-rose-600 !border-rose-100 !rounded-lg"
              @click="confirmDeleteClass(cls)" />
          </div>
        </div>
      </div>
    </div>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog v-model:visible="classDialog" :header="isEdit ? 'Edit Class' : 'Create New Class'" :modal="true"
      class="w-full max-w-3xl">
      <div class="space-y-4 pt-2">
        <!-- Code / Class Name / Class Name (Khmer) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Code *</label>
            <InputText v-model="classForm.code" placeholder="e.g. M1-CS"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class Name *</label>
            <InputText v-model="classForm.name" placeholder="e.g. Class M1-CS (Year 1 Morning)"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class Name (Khmer)</label>
            <InputText v-model="classForm.name_kh" placeholder="ឧ. ថ្នាក់ M1-CS (ឆ្នាំទី១ ព្រឹក)"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <!-- Faculty / Department / Major -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty</label>
            <Dropdown v-model="classForm.faculty_id" :options="faculties" optionLabel="name_en" optionValue="id"
              placeholder="Select Faculty" showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department</label>
            <Dropdown v-model="classForm.department_id" :options="departments" optionLabel="name_en" optionValue="id"
              placeholder="Select Department" showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major *</label>
            <Dropdown v-model="classForm.major_id" :options="majors" optionLabel="name_en" optionValue="id"
              placeholder="Select Major" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>

        <!-- Promotion / Academic Year / Semester -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Promotion</label>
            <Dropdown v-model="classForm.promotion_id" :options="promotions" optionLabel="name_en" optionValue="id"
              placeholder="Select Promotion" showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Academic Year</label>
            <Dropdown v-model="classForm.academic_year_id" :options="academicYears" optionLabel="name_en"
              optionValue="id" placeholder="Select Academic Year"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Semester</label>
            <Dropdown v-model="classForm.semester_id" :options="semesters" optionLabel="name_en" optionValue="id"
              placeholder="Select Semester" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>

        <!-- Session / Term / Stage -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Session</label>
            <Dropdown v-model="classForm.study_session_id" :options="studySessions" optionLabel="name_en"
              optionValue="id" placeholder="Select Session" showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Term</label>
            <Dropdown v-model="classForm.term_id" :options="terms" optionLabel="name_en" optionValue="id"
              placeholder="Select Term" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Stage / Level *</label>
            <Dropdown v-model="classForm.stage_id" :options="stages" optionLabel="name_en" optionValue="id"
              placeholder="Select Stage" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>

        <!-- Shift / Capacity / Status -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shift *</label>
            <Dropdown v-model="classForm.shift_id" :options="shifts" optionLabel="name_en" optionValue="id"
              placeholder="Select Shift" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Capacity *</label>
            <InputText v-model="classForm.capacity" type="number" placeholder="35"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown v-model="classForm.status" :options="['Active', 'Inactive']"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times"
            class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold"
            @click="classDialog = false" />
          <Button label="Save Class" icon="pi pi-check"
            class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold"
            @click="saveClass" />
        </div>
      </template>
    </Dialog>

    <!-- ======= SUBJECTS TAUGHT DIALOG (Subject + Teacher per Class) ======= -->
    <Dialog v-model:visible="subjectsDialog"
      :header="assignmentClass ? `Subjects Taught - ${assignmentClass.name}` : 'Subjects Taught'" :modal="true"
      class="w-full max-w-2xl">
      <div class="space-y-4 pt-2">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end bg-slate-50 p-3 rounded-xl border border-slate-200">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Subject</label>
            <Dropdown v-model="subjectAssignForm.subject_id" :options="subjectOptionsForClass" optionLabel="name_en"
              optionValue="id" placeholder="Select Subject"
              class="w-full !bg-white !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Teacher</label>
            <Dropdown v-model="subjectAssignForm.teacher_profile_id" :options="teacherOptionsForClass"
              optionLabel="name_en" optionValue="id" placeholder="Select Teacher"
              class="w-full !bg-white !border-slate-200 !rounded-xl text-sm" />
          </div>
          <Button label="Add Subject" icon="pi pi-plus"
            class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !text-xs !font-semibold"
            @click="addSubjectToClass" />
        </div>
        <p class="text-[11px] text-slate-400 -mt-2">
          Only subjects and teachers within this class's faculty are shown.
        </p>

        <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 max-h-72 overflow-y-auto">
          <div v-for="assignment in classSubjectAssignments" :key="assignment.id"
            class="flex items-center justify-between px-4 py-2.5">
            <div>
              <span class="text-xs font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded mr-2">{{
                assignment.subject_code }}</span>
              <span class="text-sm font-semibold text-slate-800">{{ assignment.subject_name }}</span>
              <span class="text-xs text-slate-400 ml-2">taught by {{ assignment.teacher_name }}</span>
            </div>
            <Button icon="pi pi-trash"
              class="!p-1.5 !w-7 !h-7 !rounded-lg !text-slate-400 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
              @click="removeSubjectFromClass(assignment)" />
          </div>
          <div v-if="classSubjectAssignments.length === 0" class="px-4 py-6 text-center text-xs text-slate-400">
            No subjects assigned to this class yet.
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end pt-3">
          <Button label="Close" icon="pi pi-times"
            class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold"
            @click="subjectsDialog = false" />
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

// View Mode State: 'grid' or 'list'
const viewMode = ref('grid')

//show column more and less
const showAllColumns = ref(false);

// Visual-only sizing for the table wrapper: with all columns shown the table
// needs enough min-width to fit every column (so it scrolls horizontally),
// but with only the "always visible" columns shown it should fit the
// container width without a horizontal scrollbar.
const tableMinWidthStyle = computed(() =>
  showAllColumns.value ? 'min-width: 1700px' : 'min-width: 100%'
)

const classes = ref([])
const majors = ref([])
const faculties = ref([])
const departments = ref([])
const promotions = ref([])
const stages = ref([])
const shifts = ref([])
const academicYears = ref([])
const semesters = ref([])
const terms = ref([])
const studySessions = ref([])
const subjects = ref([])
const teachers = ref([])

const fetchClasses = async () => {
  const { data } = await api.get('/classes', { params: { per_page: 100 } })
  classes.value = data.data
}

const fetchLookups = async () => {
  const [majorsRes, facultiesRes, departmentsRes, promotionsRes, stagesRes, shiftsRes, academicYearsRes, semestersRes, termsRes, studySessionsRes, subjectsRes, teachersRes] = await Promise.all([
    api.get('/majors', { params: { per_page: 100 } }),
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/departments', { params: { per_page: 100 } }),
    api.get('/promotions', { params: { per_page: 100 } }),
    api.get('/stages', { params: { per_page: 100 } }),
    api.get('/shifts', { params: { per_page: 100 } }),
    api.get('/academic-years', { params: { per_page: 100 } }),
    api.get('/semesters', { params: { per_page: 100 } }),
    api.get('/terms', { params: { per_page: 100 } }),
    api.get('/study-sessions', { params: { per_page: 100 } }),
    api.get('/subjects', { params: { per_page: 200 } }),
    api.get('/teachers', { params: { per_page: 200 } }),
  ])
  majors.value = majorsRes.data.data
  faculties.value = facultiesRes.data.data
  departments.value = departmentsRes.data.data
  promotions.value = promotionsRes.data.data
  stages.value = stagesRes.data.data
  shifts.value = shiftsRes.data.data
  academicYears.value = academicYearsRes.data.data
  semesters.value = semestersRes.data.data
  terms.value = termsRes.data.data
  studySessions.value = studySessionsRes.data.data
  subjects.value = subjectsRes.data.data.map(s => ({ id: s.id, faculty_id: s.faculty_id, name_en: `${s.name_en} (${s.code})` }))
  teachers.value = teachersRes.data.data.map(t => ({ id: t.id, faculty_id: t.faculty_id, name_en: t.name_en }))
}

onMounted(() => {
  fetchClasses()
  fetchLookups()
})

// Search Filter
const filters = ref({ global: { value: null, matchMode: 'contains' } })

// Pagination display state for the list-view table (visual only).
const first = ref(0)
const rows = ref(10)

// ======= Filter Bar (Major / Stage / Shift / Term / Status) =======
const majorFilter = ref(null)
const stageFilter = ref(null)
const shiftFilter = ref(null)
const termFilter = ref(null)
const statusFilter = ref(null)

const hasActiveFilters = computed(() =>
  !!(majorFilter.value || stageFilter.value || shiftFilter.value || termFilter.value || statusFilter.value)
)

const clearFilters = () => {
  majorFilter.value = null
  stageFilter.value = null
  shiftFilter.value = null
  termFilter.value = null
  statusFilter.value = null
}

// Computed Filter for Grid View Search Integration
const filteredClasses = computed(() => {
  const query = filters.value.global.value?.toLowerCase().trim()

  return classes.value.filter((c) => {
    if (query) {
      const matchesQuery = c.code.toLowerCase().includes(query) ||
        c.name.toLowerCase().includes(query) ||
        (c.name_kh || '').toLowerCase().includes(query) ||
        (c.department || '').toLowerCase().includes(query)
      if (!matchesQuery) return false
    }
    if (majorFilter.value && c.major_id !== majorFilter.value) return false
    if (stageFilter.value && c.stage_id !== stageFilter.value) return false
    if (shiftFilter.value && c.shift_id !== shiftFilter.value) return false
    if (termFilter.value && c.term_id !== termFilter.value) return false
    if (statusFilter.value && c.status !== statusFilter.value) return false
    return true
  })
})

// Dialog States & Form
const classDialog = ref(false)
const isEdit = ref(false)
const classForm = ref({
  id: null,
  code: '',
  name: '',
  name_kh: '',
  major_id: null,
  faculty_id: null,
  department_id: null,
  promotion_id: null,
  stage_id: null,
  shift_id: null,
  academic_year_id: null,
  semester_id: null,
  term_id: null,
  study_session_id: null,
  capacity: 35,
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
    name_kh: '',
    major_id: null,
    faculty_id: null,
    department_id: null,
    promotion_id: null,
    stage_id: null,
    shift_id: null,
    academic_year_id: null,
    semester_id: null,
    term_id: null,
    study_session_id: null,
    capacity: 35,
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

const saveClass = async () => {
  if (!classForm.value.code || !classForm.value.name || !classForm.value.major_id || !classForm.value.stage_id || !classForm.value.shift_id) {
    alert('Code, name, department/major, stage, and shift are required.')
    return
  }

  const payload = {
    code: classForm.value.code,
    name: classForm.value.name,
    name_kh: classForm.value.name_kh,
    major_id: classForm.value.major_id,
    faculty_id: classForm.value.faculty_id,
    department_id: classForm.value.department_id,
    promotion_id: classForm.value.promotion_id,
    stage_id: classForm.value.stage_id,
    shift_id: classForm.value.shift_id,
    academic_year_id: classForm.value.academic_year_id,
    semester_id: classForm.value.semester_id,
    term_id: classForm.value.term_id,
    study_session_id: classForm.value.study_session_id,
    capacity: classForm.value.capacity,
    status: classForm.value.status,
  }

  try {
    if (isEdit.value) {
      await api.put(`/classes/${classForm.value.id}`, payload)
    } else {
      await api.post('/classes', payload)
    }

    classDialog.value = false
    await fetchClasses()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteClass = async (data) => {
  if (confirm(`Are you sure you want to delete ${data.name}?`)) {
    try {
      await api.delete(`/classes/${data.id}`)
      await fetchClasses()
    } catch (error) {
      alert(extractError(error))
    }
  }
}

// ======= Subjects Taught Dialog (Subject + Teacher per Class) =======
const subjectsDialog = ref(false)
const assignmentClass = ref(null)
const classSubjectAssignments = ref([])
const subjectAssignForm = ref({ subject_id: null, teacher_profile_id: null })

// Subjects/teachers are scoped to the faculty of whichever class's dialog
// is open, mirroring the same faculty-scoping used on the Teachers page.
const subjectOptionsForClass = computed(() =>
  assignmentClass.value
    ? subjects.value.filter(s => s.faculty_id === assignmentClass.value.faculty_id)
    : []
)
const teacherOptionsForClass = computed(() =>
  assignmentClass.value
    ? teachers.value.filter(t => t.faculty_id === assignmentClass.value.faculty_id)
    : []
)

const fetchClassSubjectAssignments = async (classId) => {
  const { data } = await api.get('/teacher-assignments', { params: { class_id: classId } })
  classSubjectAssignments.value = data.data
}

const openSubjectsDialog = async (data) => {
  assignmentClass.value = data
  subjectAssignForm.value = { subject_id: null, teacher_profile_id: null }
  subjectsDialog.value = true
  try {
    await fetchClassSubjectAssignments(data.id)
  } catch (error) {
    alert(extractError(error))
  }
}

const addSubjectToClass = async () => {
  if (!subjectAssignForm.value.subject_id || !subjectAssignForm.value.teacher_profile_id) {
    alert('Please select both a subject and a teacher.')
    return
  }

  try {
    await api.post('/teacher-assignments', {
      teacher_profile_id: subjectAssignForm.value.teacher_profile_id,
      subject_id: subjectAssignForm.value.subject_id,
      class_id: assignmentClass.value.id,
    })
    subjectAssignForm.value = { subject_id: null, teacher_profile_id: null }
    await fetchClassSubjectAssignments(assignmentClass.value.id)
  } catch (error) {
    alert(extractError(error))
  }
}

const removeSubjectFromClass = async (assignment) => {
  if (!confirm(`Remove ${assignment.subject_name} (${assignment.teacher_name}) from this class?`)) return

  try {
    await api.delete(`/teacher-assignments/${assignment.id}`)
    await fetchClassSubjectAssignments(assignmentClass.value.id)
  } catch (error) {
    alert(extractError(error))
  }
}
</script>

<style scoped>
/* Header two sizes smaller, body two sizes larger, than the table's base text-xs. */
.classes-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.classes-table :deep(.p-datatable-tbody > tr > td) {
  font-size: 14px;
}

/* បង្ខំកាត់ Text វែងៗកុំឲ្យធ្លាក់ជួរ (សម្រាប់ Card View) */
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

:deep(.custom-filter-dropdown) {
  background-color: #f8fafc !important;
  /* slate-50 */
  border: 1px solid #e2e8f0 !important;
  /* slate-200 */
  border-radius: 0.75rem !important;
  /* rounded-xl */
  font-size: 0.75rem !important;
  /* text-xs */
  transition: all 0.2s ease;
  height: 38px;
  display: flex;
  align-items: center;
}

/* Hover & Focus State */
:deep(.custom-filter-dropdown:hover) {
  border-color: #cbd5e1 !important;
  /* slate-300 */
  background-color: #ffffff !important;
}

:deep(.custom-filter-dropdown.p-focus) {
  border-color: #3b82f6 !important;
  /* blue-500 */
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
  background-color: #ffffff !important;
}

/* Inner Label Padding & Text Style */
:deep(.custom-filter-dropdown .p-dropdown-label) {
  font-size: 0.75rem !important;
  padding: 0.4rem 0.75rem !important;
  color: #334155 !important;
  /* slate-700 */
}

/* Clear Icon and Arrow Dropdown */
:deep(.custom-filter-dropdown .p-dropdown-trigger),
:deep(.custom-filter-dropdown .p-dropdown-clear-icon) {
  color: #94a3b8 !important;
  /* slate-400 */
  width: 2rem !important;
}
</style>