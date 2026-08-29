<template>
  <div class="h-[calc(100vh-2rem)] flex flex-col gap-4 overflow-hidden font-sans text-slate-800">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0 bg-white/60 backdrop-blur-md p-4 rounded-xl border border-slate-200/60 shadow-xs">
      <div>
        <h1 class="text-xl font-bold text-[#002060] tracking-tight flex items-center gap-2.5">
            <i class="pi pi-users text-[#e4ac40] text-2xl"></i>
          User Management
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 m-0 mt-1 pl-0.5">
          Manage every account in the system &mdash; Admins, Teachers, and Students &mdash; from one place.
        </p>
      </div>

      <Button
        :label="addLabel"
        icon="pi pi-plus"
        class="!bg-[#002060] hover:!bg-blue-900 active:!bg-blue-800 !text-white !border-0 !rounded-lg !py-2.5 !px-4 !text-xs !font-semibold shadow-sm hover:shadow transition-all duration-200 gap-2 shrink-0 cursor-pointer"
        @click="openNewDialog(defaultRoleForTab)"
      />
    </div>

    <!-- ======= DATA TABLE ======= -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs flex-1 flex flex-col min-h-0">

      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
        <h3 class="text-sm font-bold text-[#002060] m-0">
          {{ activeTab === 'all' ? 'All Users' : activeTab === 'admin' ? 'Admins' : activeTab === 'teacher' ? 'Teachers' : 'Students' }}
        </h3>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <div class="relative w-full sm:w-64">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
            <InputText
              v-model="filters['global'].value"
              size="small"
              placeholder="Search username or name..."
              class="w-full !pl-9 !pr-3 !bg-slate-50 !border-slate-200 !rounded-lg !text-xs"
            />
          </div>
          <Button
            :label="showAllColumns ? 'Fewer Columns' : 'More Columns'"
            :icon="showAllColumns ? 'pi pi-angle-double-left' : 'pi pi-angle-double-right'"
            size="small"
            class="!bg-[#002060] !border-slate-100 !text-white hover:!bg-blue-900 !rounded-lg !text-xs !font-semibold !px-3 !py-1"
            @click="showAllColumns = !showAllColumns"
          />
        </div>
      </div>

      <!-- Tab Header Buttons -->
      <div class="flex flex-wrap gap-1.5 px-4 pt-3 shrink-0">
        <button
          type="button"
          @click="activeTab = 'all'"
          class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeTab === 'all' ? '!bg-[#002060] !text-white' : '!bg-slate-100  !text-[#002060] hover:!bg-slate-200 '"
        >
          <i class="pi pi-users text-[#e4ac40] text-sm"></i>
          <span>All Users</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeTab === 'all' ? 'bg-white text-[#002060]' : 'bg-[#002060] text-white'">
            {{ countFor('all') }}
          </span>
        </button>

        <button
          type="button"
          @click="activeTab = 'admin'"
          class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeTab === 'admin' ? '!bg-[#002060] !text-white' : '!bg-indigo-50 !text-[#002060] hover:!bg-indigo-100'"
        >
          <i class="pi pi-shield text-[#e4ac40] text-sm"></i>
          <span>Admin</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeTab === 'admin' ? 'bg-white text-[#002060]' : 'bg-[#002060] text-white'">
            {{ countFor('admin') }}
          </span>
        </button>

        <button
          type="button"
          @click="activeTab = 'teacher'"
          class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeTab === 'teacher' ? '!bg-[#002060] !text-white' : '!bg-blue-50 !text-[#002060] hover:!bg-blue-100'"
        >
          <i class="pi pi-id-card text-[#e4ac40] text-sm"></i>
          <span>Teacher</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeTab === 'teacher' ? 'bg-white text-[#002060]' : 'bg-[#002060] text-white'">
            {{ countFor('teacher') }}
          </span>
        </button>

        <button
          type="button"
          @click="activeTab = 'student'"
          class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeTab === 'student' ? '!bg-[#002060] !text-white' : '!bg-blue-50 !text-[#002060] hover:!bg-blue-100'"
        >
          <i class="pi pi-graduation-cap text-[#e4ac40] text-sm"></i>
          <span>Student</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeTab === 'student' ? 'bg-white text-[#002060]' : 'bg-[#002060] text-white'">
            {{ countFor('student') }}
          </span>
        </button>
      </div>

      <!-- ======= FILTER BAR ======= -->
      <div class="flex flex-wrap items-center gap-2.5 shrink-0 px-4 pt-3">
        <i class="pi pi-filter text-[#e4ac40] text-sm ml-1"></i>
        <Dropdown v-model="facultyFilter" :options="faculties" optionLabel="name_en" optionValue="id"
          placeholder="Faculty (Teachers)" showClear class="w-48 !bg-slate-50 !border-slate-200 !rounded-lg text-xs" />
        <Dropdown v-model="genderFilter" :options="['Male', 'Female']"
          placeholder="All Genders" showClear class="w-40 !bg-slate-50 !border-slate-200 !rounded-lg text-xs" />
        <Dropdown v-model="statusFilter" :options="['Active', 'Inactive']"
          placeholder="All Statuses" showClear class="w-40 !bg-slate-50 !border-slate-200 !rounded-lg text-xs" />
        <Button v-if="hasActiveFilters" label="Clear Filters" icon="pi pi-filter-slash" size="small"
          class="!bg-rose-50 !text-rose-600 hover:!bg-rose-100 !border-rose-50 !rounded-lg !text-xs !font-semibold !px-3 !py-1"
          @click="clearFilters" />
      </div>

      <!-- PrimeVue DataTable -->
      <DataTable
        :value="filteredUsers"
        v-model:filters="filters"
        v-model:first="first"
        :globalFilterFields="['username', 'first_name', 'last_name', 'name_kh', 'phone']"
        dataKey="id"
        paginator
        paginatorPosition="bottom"
        :rows="rows"
        :rowsPerPageOptions="[10, 20, 50]"
        scrollable
        scrollHeight="flex"
        :scrollDirection="showAllColumns ? 'both' : 'vertical'"
        responsiveLayout="scroll"
        :loading="loading"
        :tableStyle="showAllColumns ? 'min-width: 1900px' : 'min-width: 100%'"
        class="p-datatable-sm users-table flex-1 min-h-0 mt-3"
      >
        <template #empty>
          <div class="text-center py-10 text-xs text-slate-400">
            No users found.
          </div>
        </template>

        <template #paginatorstart>
          <span class="text-xs text-slate-500">
            Showing <span class="font-semibold text-slate-700">{{ filteredUsers.length ? first + 1 : 0 }}</span>
            to <span class="font-semibold text-slate-700">{{ Math.min(first + rows, filteredUsers.length) }}</span>
            of <span class="font-semibold text-slate-700">{{ filteredUsers.length }}</span>
          </span>
        </template>

        <!-- Username - តែងតែបង្ហាញ -->
        <Column field="username" header="USERNAME" sortable style="min-width: 130px; padding-left: 1.25rem">
          <template #body="{ data }">
            <span class="font-mono font-bold text-indigo-600 text-sm">{{ data.username }}</span>
          </template>
        </Column>

        <!-- Full Name - តែងតែបង្ហាញ -->
        <Column field="first_name" header="FULL NAME" sortable style="min-width: 180px">
          <template #body="{ data }">
            <span class="font-semibold text-slate-800 text-sm">{{ data.first_name }} {{ data.last_name }}</span>
          </template>
        </Column>

        <!-- Name (Khmer) - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="name_kh" header="NAME (KHMER)" style="min-width: 140px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm font-khmer">{{ data.name_kh || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Phone - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="phone" header="PHONE" style="min-width: 130px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm">{{ data.phone || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Gender - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="gender" header="GENDER" style="width: 100px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm">{{ data.gender || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Date of Birth - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="dob" header="DATE OF BIRTH" style="width: 130px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm">{{ formatDisplayDate(data.dob) || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Address - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" field="address" header="ADDRESS" style="min-width: 180px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm">{{ data.address || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Role - តែងតែបង្ហាញ -->
        <Column header="ROLE" sortable style="width: 150px">
          <template #body="{ data }">
            <span
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap"
              :class="roleBadgeClass(data.role?.name)"
            >
              {{ data.role?.name || 'No role' }}
            </span>
          </template>
        </Column>

        <!-- Employee / Student Code - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" header="CODE" style="width: 130px">
          <template #body="{ data }">
            <span class="font-mono font-bold text-indigo-600 text-sm">{{ detailsFor(data).primary || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Department / Faculty / Admission - លាក់/បង្ហាញ -->
        <Column v-if="showAllColumns" header="DEPARTMENT / FACULTY" style="min-width: 180px">
          <template #body="{ data }">
            <span class="text-slate-600 text-sm">{{ detailsFor(data).secondary || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Status - តែងតែបង្ហាញ -->
        <Column field="status" header="STATUS" sortable style="width: 120px">
          <template #body="{ data }">
            <span
              class="font-bold px-2.5 py-0.5 rounded-full text-xs border inline-block whitespace-nowrap"
              :class="data.status ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
            >
              {{ data.status ? 'ACTIVE' : 'INACTIVE' }}
            </span>
          </template>
        </Column>

        <!-- Actions - តែងតែបង្ហាញ -->
        <Column header="ACTIONS" style="width: 100px; padding-right: 1.25rem" class="!text-center users-actions-col">
          <template #body="{ data }">
            <div class="flex items-center justify-center gap-1.5">
              <Button
                icon="pi pi-pencil"
                size="small"
                class="!bg-slate-100  !border-slate-100 !text-[#e4ac14] !rounded-xl !text-xs !px-3 !py-1.5 shadow-xs"
                title="Edit User"
                @click="editUser(data)"
              ><i class="fa-solid fa-pen-to-square"></i></Button>
              <Button
                icon="pi pi-trash"
                size="small"
                class="!bg-slate-100  !border-slate-100 !text-[#d71818] !rounded-xl !text-xs !px-3 !py-1.5 shadow-xs"
                title="Delete User"
                @click="confirmDeleteUser(data)"
              ><i class="fa-solid fa-trash-can"></i></Button>
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- ======= ADD / EDIT DIALOG ======= -->
    <Dialog
      v-model:visible="userDialog"
      :header="isEdit ? 'Edit User' : 'Create New User'"
      :modal="true"
      class="w-full max-w-2xl !rounded-xl overflow-hidden"
      :pt="{
        root: { class: '!rounded-xl !border-0 shadow-2xl !bg-white' },
        header: { class: '!border-b !border-slate-100 !p-5 !bg-slate-50/50' },
        content: { class: '!p-6' },
        footer: { class: '!border-t !border-slate-100 !p-4 !bg-slate-50/50' }
      }"
    >
      <div class="space-y-6">

        <!-- Avatar -->
        <div v-if="isEdit" class="flex items-center gap-4 p-3 bg-slate-50/80 rounded-xl border border-slate-100">
          <Avatar
            :image="form.avatar_url || undefined"
            :label="!form.avatar_url ? (form.first_name || '?').charAt(0).toUpperCase() : undefined"
            shape="circle"
            size="large"
            class="!bg-blue-100 !text-blue-700 font-bold !w-14 !h-14 !text-lg ring-2 ring-white shadow-xs"
          />
          <div>
            <p class="text-xs font-semibold text-slate-700 mb-1.5">Profile Picture</p>
            <FileUpload
              mode="basic"
              accept="image/*"
              :maxFileSize="2000000"
              chooseLabel="Upload Photo"
              :auto="true"
              customUpload
              @uploader="onAvatarSelect"
              class="!text-xs"
            />
          </div>
        </div>

        <!-- ======= SECTION: ACCOUNT ======= -->
        <div class="flex items-center gap-2 text-slate-700 font-bold text-xs uppercase tracking-wider pb-2 border-b border-slate-200">
          <i class="pi pi-key text-blue-600 text-sm"></i>
          <span>Account</span>
        </div>

        <!-- Username / Role -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Username *</label>
            <InputText v-model="form.username" placeholder="e.g. ADM00001"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Role *</label>
            <Dropdown v-model="form.role_id" :options="roles" optionLabel="name" optionValue="id"
              placeholder="Select Role" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>

        <!-- Password -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
              Password {{ isEdit ? '(leave blank to keep current)' : '*' }}
            </label>
            <Password v-model="form.password" :feedback="false" toggleMask
              inputClass="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" class="w-full" />
          </div>
        </div>

        <!-- ======= SECTION: PERSONAL INFORMATION ======= -->
        <div class="flex items-center gap-2 text-slate-700 font-bold text-xs uppercase tracking-wider pb-2 pt-2 border-b border-slate-200">
          <i class="pi pi-user text-blue-600 text-sm"></i>
          <span>Personal Information</span>
        </div>

        <!-- First Name / Last Name / Name (Khmer) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">First Name *</label>
            <InputText v-model="form.first_name" placeholder="Jane"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Last Name *</label>
            <InputText v-model="form.last_name" placeholder="Doe"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name (Khmer)</label>
            <InputText v-model="form.name_kh" placeholder="ឧ. សុខ ចាន់ថាន"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm font-khmer" />
          </div>
        </div>

        <!-- Gender / Date of Birth / Phone -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Gender</label>
            <Dropdown v-model="form.gender" :options="['Male', 'Female']" placeholder="Select Gender" showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Date of Birth</label>
            <DatePicker v-model="form.dob" dateFormat="yy-mm-dd" showIcon iconDisplay="input" placeholder="Select date"
              class="w-full" inputClass="!py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Phone</label>
            <InputText v-model="form.phone" placeholder="012 345 678"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <!-- Status -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
            <Dropdown v-model="form.status"
              :options="[{ label: 'Active', value: true }, { label: 'Inactive', value: false }]"
              optionLabel="label" optionValue="value"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
          </div>
        </div>

        <!-- Address (last Personal Information field, full width) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="sm:col-span-3">
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Address</label>
            <Textarea v-model="form.address" rows="2" placeholder="Street, city, country"
              class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <!-- ======= SECTION: TEACHER PROFILE ======= -->
        <template v-if="isTeacherRole">
          <div class="flex items-center gap-2 text-slate-700 font-bold text-xs uppercase tracking-wider pb-2 pt-2 border-b border-slate-200">
            <i class="pi pi-graduation-cap text-emerald-600 text-sm"></i>
            <span>Academic Information</span>
          </div>

          <!-- Employee Code / Employment Type / Faculty -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Employee Code *</label>
              <InputText v-model="form.employee_code" placeholder="e.g. T-101"
                class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Employment Type</label>
              <Dropdown v-model="form.employment_type"
                :options="[{ label: 'Full-Time', value: 'full_time' }, { label: 'Part-Time', value: 'part_time' }]"
                optionLabel="label" optionValue="value"
                class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty *</label>
              <Dropdown v-model="form.faculty_id" :options="faculties" optionLabel="name_en" optionValue="id"
                placeholder="Select Faculty" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
                @change="form.department_id = null; form.major_id = null" />
            </div>
          </div>

          <!-- Department / Major / Hire Date -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department</label>
              <Dropdown v-model="form.department_id" :options="teacherDepartmentOptions" optionLabel="name_en"
                optionValue="id" placeholder="Select Department" showClear
                class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
                @change="form.major_id = null" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major</label>
              <Dropdown v-model="form.major_id" :options="majorsForSelectedDepartment" optionLabel="name_en"
                optionValue="id" placeholder="Select Major" showClear :disabled="!form.department_id"
                class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Hire Date</label>
              <DatePicker v-model="form.hire_date" dateFormat="yy-mm-dd" showIcon iconDisplay="input"
                placeholder="Select date" class="w-full"
                inputClass="!py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
            </div>
          </div>

          <!-- Qualification / Specialization -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Qualification</label>
              <InputText v-model="form.qualification" placeholder="e.g. MSc Computer Science"
                class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Specialization</label>
              <InputText v-model="form.specialization" placeholder="e.g. Database Systems"
                class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
            </div>
          </div>
        </template>

        <!-- ======= SECTION: STUDENT PROFILE ======= -->
        <template v-if="isStudentRole">
          <div class="flex items-center gap-2 text-slate-700 font-bold text-xs uppercase tracking-wider pb-2 pt-2 border-b border-slate-200">
            <i class="pi pi-graduation-cap text-emerald-600 text-sm"></i>
            <span>Academic Information</span>
          </div>

          <!-- Student Code / Admission Date / Class -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Student Code *</label>
              <InputText v-model="form.student_code" placeholder="e.g. STU-1001"
                class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Admission Date</label>
              <DatePicker v-model="form.admission_date" dateFormat="yy-mm-dd" showIcon iconDisplay="input"
                placeholder="Select date" class="w-full"
                inputClass="!py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Class *</label>
              <Dropdown v-model="form.class_id" :options="classes" optionLabel="name" optionValue="id"
                placeholder="Select Class" class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
          </div>

          <!-- Faculty / Department / Major -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Faculty</label>
              <Dropdown v-model="form.faculty_id" :options="faculties" optionLabel="name_en" optionValue="id"
                placeholder="Select Faculty" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department</label>
              <Dropdown v-model="form.department_id" :options="departments" optionLabel="name_en" optionValue="id"
                placeholder="Select Department" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Major</label>
              <Dropdown v-model="form.major_id" :options="majors" optionLabel="name_en" optionValue="id"
                placeholder="Select Major" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
          </div>

          <!-- Promotion / Academic Year / Stage -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Promotion</label>
              <Dropdown v-model="form.promotion_id" :options="promotions" optionLabel="name_en" optionValue="id"
                placeholder="Select Promotion" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Academic Year</label>
              <Dropdown v-model="form.academic_year_id" :options="academicYears" optionLabel="name_en" optionValue="id"
                placeholder="Select Academic Year" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Stage</label>
              <Dropdown v-model="form.stage_id" :options="stages" optionLabel="name_en" optionValue="id"
                placeholder="Select Stage" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
          </div>

          <!-- Semester / Term / Shift -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Semester</label>
              <Dropdown v-model="form.semester_id" :options="semesters" optionLabel="name_en" optionValue="id"
                placeholder="Select Semester" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Term</label>
              <Dropdown v-model="form.term_id" :options="terms" optionLabel="name_en" optionValue="id"
                placeholder="Select Term" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shift</label>
              <Dropdown v-model="form.shift_id" :options="shifts" optionLabel="name_en" optionValue="id"
                placeholder="Select Shift" showClear class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm" />
            </div>
          </div>
        </template>

        <!-- ======= SECTION: ADMIN PROFILE ======= -->
        <template v-if="isAdminRole">
          <div class="flex items-center gap-2 text-slate-700 font-bold text-xs uppercase tracking-wider pb-2 pt-2 border-b border-slate-200">
            <i class="pi pi-shield text-emerald-600 text-sm"></i>
            <span>Admin Profile</span>
          </div>

          <!-- Employee Code / Position / Department -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Employee Code</label>
              <InputText v-model="form.employee_code" placeholder="e.g. EMP-001"
                class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Position</label>
              <InputText v-model="form.position" placeholder="e.g. Registrar"
                class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Department</label>
              <InputText v-model="form.department" placeholder="e.g. Academic Affairs"
                class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
            </div>
          </div>

          <!-- Hire Date -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Hire Date</label>
              <DatePicker v-model="form.hire_date" dateFormat="yy-mm-dd" showIcon iconDisplay="input"
                placeholder="Select date" class="w-full"
                inputClass="!py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
            </div>
          </div>
        </template>

      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 hover:!bg-slate-200/80 !text-slate-600 !border-0 !rounded-lg !px-4 !py-2 !text-xs !font-semibold transition-all cursor-pointer" @click="userDialog = false" />
          <Button label="Save User" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 active:!bg-blue-800 !text-white !border-0 !rounded-lg !px-4 !py-2 !text-xs !font-semibold shadow-xs transition-all cursor-pointer" @click="saveUser" />
        </div>
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { FilterMatchMode } from '@primevue/core/api'
import api, { extractError } from '../../../api'

// PrimeVue Components Import
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Textarea from 'primevue/textarea'
import Password from 'primevue/password'
import Avatar from 'primevue/avatar'
import DatePicker from 'primevue/datepicker'
import FileUpload from 'primevue/fileupload'


const showAllColumns = ref(false);
// ======= Data =======
const users = ref([])
const roles = ref([])
const faculties = ref([])
const departments = ref([])
const degrees = ref([])
const majors = ref([])
const classes = ref([])
const promotions = ref([])
const academicYears = ref([])
const stages = ref([])
const semesters = ref([])
const terms = ref([])
const shifts = ref([])
const loading = ref(false)

const fetchUsers = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/users', { params: { per_page: 200 } })
    users.value = data.data
  } finally {
    loading.value = false
  }
}

const fetchLookups = async () => {
  const [
    rolesRes, facultiesRes, departmentsRes, degreesRes, majorsRes,
    classesRes, promotionsRes, academicYearsRes, stagesRes, semestersRes, termsRes, shiftsRes,
  ] = await Promise.all([
    api.get('/roles'),
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/departments', { params: { per_page: 100 } }),
    api.get('/degrees', { params: { per_page: 100 } }),
    api.get('/majors', { params: { per_page: 200 } }),
    api.get('/classes', { params: { per_page: 200 } }),
    api.get('/promotions', { params: { per_page: 100 } }),
    api.get('/academic-years', { params: { per_page: 100 } }),
    api.get('/stages', { params: { per_page: 100 } }),
    api.get('/semesters', { params: { per_page: 100 } }),
    api.get('/terms', { params: { per_page: 100 } }),
    api.get('/shifts', { params: { per_page: 100 } }),
  ])
  roles.value = rolesRes.data
  faculties.value = facultiesRes.data.data
  departments.value = departmentsRes.data.data
  degrees.value = degreesRes.data.data
  majors.value = majorsRes.data.data
  classes.value = classesRes.data.data
  promotions.value = promotionsRes.data.data
  academicYears.value = academicYearsRes.data.data
  stages.value = stagesRes.data.data
  semesters.value = semestersRes.data.data
  terms.value = termsRes.data.data
  shifts.value = shiftsRes.data.data
}

onMounted(() => {
  fetchUsers()
  fetchLookups()
})

// ======= Tabs =======
const activeTab = ref('all')
const roleGroups = {
  admin: ['Admin'],
  teacher: ['Teacher'],
  student: ['Student'],
}

const countFor = (key) => {
  if (key === 'all') return users.value.length
  return users.value.filter(u => roleGroups[key].includes(u.role?.name)).length
}

// ======= Filter Bar (Faculty / Gender / Status), layered on top of the tab =======
const facultyFilter = ref(null)
const genderFilter = ref(null)
const statusFilter = ref(null)

const hasActiveFilters = computed(() => !!(facultyFilter.value || genderFilter.value || statusFilter.value))

const clearFilters = () => {
  facultyFilter.value = null
  genderFilter.value = null
  statusFilter.value = null
}

const filteredUsers = computed(() => {
  const byTab = activeTab.value === 'all'
    ? users.value
    : users.value.filter(u => roleGroups[activeTab.value].includes(u.role?.name))

  return byTab.filter((u) => {
    if (facultyFilter.value && u.teacher_profile?.faculty_id !== facultyFilter.value) return false
    if (genderFilter.value && u.gender !== genderFilter.value) return false
    if (statusFilter.value && (u.status ? 'Active' : 'Inactive') !== statusFilter.value) return false
    return true
  })
})

const first = ref(0)
const rows = ref(10)

const defaultRoleForTab = computed(() => {
  if (activeTab.value === 'teacher') return 'Teacher'
  if (activeTab.value === 'student') return 'Student'
  if (activeTab.value === 'admin') return 'Admin'
  return null
})

const addLabel = computed(() => {
  if (activeTab.value === 'teacher') return 'Add New Teacher'
  if (activeTab.value === 'student') return 'Add New Student'
  if (activeTab.value === 'admin') return 'Add New Admin'
  return 'Add New User'
})

// ======= Search Filter =======
const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
})

// Jump back to page 1 whenever the tab, filters, or search change, so the
// paginator never gets stranded past the end of a smaller filtered list.
watch([filteredUsers, () => filters.value.global.value], () => {
  first.value = 0
})

// ======= Dropdown helpers (Teacher section) =======
// Department options are scoped to whichever faculty is currently selected,
// since a department belongs to exactly one faculty.
const teacherDepartmentOptions = computed(() =>
  departments.value.filter(d => d.faculty_id === form.value.faculty_id)
)

// Major options narrow down by the selected Department.
const majorsForSelectedDepartment = computed(() => {
  if (!form.value.department_id) return []
  return majors.value.filter(m => m.department_id === form.value.department_id)
})

// ======= Dialog / Form =======
const userDialog = ref(false)
const isEdit = ref(false)

const emptyForm = () => ({
  id: null,
  username: '',
  password: '',
  first_name: '',
  last_name: '',
  name_kh: '',
  gender: null,
  dob: null,
  phone: '',
  address: '',
  role_id: null,
  status: true,
  avatar_url: null,

  employee_code: '',
  faculty_id: null,
  department_id: null,
  degree_id: null,
  major_id: null,
  qualification: '',
  specialization: '',
  employment_type: 'full_time',
  hire_date: null,

  position: '',
  department: '',

  student_code: '',
  admission_date: null,
  class_id: null,
  promotion_id: null,
  academic_year_id: null,
  stage_id: null,
  semester_id: null,
  term_id: null,
  shift_id: null,
})

const form = ref(emptyForm())

const selectedRoleName = computed(() => roles.value.find(r => r.id === form.value.role_id)?.name)
const isTeacherRole = computed(() => selectedRoleName.value === 'Teacher')
const isStudentRole = computed(() => selectedRoleName.value === 'Student')
const isAdminRole = computed(() => roleGroups.admin.includes(selectedRoleName.value))

const fetchNextUsername = async (roleName) => {
  try {
    const { data } = await api.get('/users/next-username', { params: { role: roleName } })
    form.value.username = data.username
  } catch (error) {
    // Leave the field blank so the admin can type one manually.
  }
}

watch(selectedRoleName, (roleName) => {
  if (!isEdit.value && roleName && ['Admin', 'Teacher', 'Student'].includes(roleName)) {
    fetchNextUsername(roleName)
  }
})

// ======= Formatting helpers =======
const parseApiDate = (value) => (value ? new Date(value) : null)

const formatDateForApi = (date) => {
  if (!date) return null
  const d = new Date(date)
  const yyyy = d.getFullYear()
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const dd = String(d.getDate()).padStart(2, '0')
  return `${yyyy}-${mm}-${dd}`
}

const formatDisplayDate = (value) => {
  if (!value) return null
  return String(value).slice(0, 10)
}

const roleBadgeClass = (roleName) => {
  switch (roleName) {
    case 'Admin': return 'bg-indigo-50 text-indigo-700 border-indigo-200'
    case 'Teacher': return 'bg-blue-50 text-blue-700 border-blue-200'
    case 'Student': return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    default: return 'bg-slate-50 text-slate-600 border-slate-200'
  }
}

const detailsFor = (data) => {
  const roleName = data.role?.name

  if (roleName === 'Teacher' && data.teacher_profile) {
    return {
      primary: data.teacher_profile.employee_code || 'N/A',
      secondary: data.teacher_profile.faculty?.name || 'No faculty assigned',
    }
  }

  if (roleName === 'Student' && data.student_profile) {
    return {
      primary: data.student_profile.student_code || 'N/A',
      secondary: data.student_profile.admission_date ? `Admitted ${formatDisplayDate(data.student_profile.admission_date)}` : 'No admission date',
    }
  }

  if (roleGroups.admin.includes(roleName) && data.admin_profile) {
    return {
      primary: data.admin_profile.employee_code || 'N/A',
      secondary: data.admin_profile.position || data.admin_profile.department || 'No position set',
    }
  }

  return { primary: '—', secondary: '' }
}

// ======= Actions =======
const openNewDialog = (defaultRoleName = null) => {
  const defaultRole = defaultRoleName ? roles.value.find(r => r.name === defaultRoleName) : null
  isEdit.value = false
  form.value = { ...emptyForm(), role_id: defaultRole?.id ?? null }
  userDialog.value = true
}

const editUser = (data) => {
  const tp = data.teacher_profile
  const sp = data.student_profile
  const ap = data.admin_profile
  const enrollment = sp?.enrollments?.[0]

  form.value = {
    id: data.id,
    username: data.username,
    password: '',
    first_name: data.first_name,
    last_name: data.last_name,
    name_kh: data.name_kh || '',
    gender: data.gender || null,
    dob: parseApiDate(data.dob),
    phone: data.phone || '',
    address: data.address || '',
    role_id: data.role_id,
    status: !!data.status,
    avatar_url: data.avatar_url,

    employee_code: tp?.employee_code || ap?.employee_code || '',
    faculty_id: tp?.faculty_id ?? enrollment?.faculty_id ?? null,
    department_id: tp?.department_id ?? enrollment?.department_id ?? null,
    degree_id: tp?.degree_id ?? enrollment?.degree_id ?? null,
    major_id: tp?.major_id ?? enrollment?.major_id ?? null,
    qualification: tp?.qualification || '',
    specialization: tp?.specialization || '',
    employment_type: tp?.employment_type || 'full_time',
    hire_date: parseApiDate(tp?.hire_date || ap?.hire_date),

    position: ap?.position || '',
    department: ap?.department || '',

    student_code: sp?.student_code || '',
    admission_date: parseApiDate(sp?.admission_date),
    class_id: enrollment?.class_id ?? null,
    promotion_id: enrollment?.promotion_id ?? null,
    academic_year_id: enrollment?.academic_year_id ?? null,
    stage_id: enrollment?.stage_id ?? null,
    semester_id: enrollment?.semester_id ?? null,
    term_id: enrollment?.term_id ?? null,
    shift_id: enrollment?.shift_id ?? null,
  }
  isEdit.value = true
  userDialog.value = true
}

const saveUser = async () => {
  if (!form.value.username || !form.value.first_name || !form.value.last_name || !form.value.role_id) {
    alert('Username, first name, last name, and role are required.')
    return
  }
  if (!isEdit.value && !form.value.password) {
    alert('Password is required when creating a new user.')
    return
  }
  if (isTeacherRole.value && (!form.value.employee_code || !form.value.faculty_id)) {
    alert('Employee code and faculty are required for Teacher accounts.')
    return
  }
  if (isStudentRole.value && !form.value.student_code) {
    alert('Student code is required for Student accounts.')
    return
  }
  if (isStudentRole.value && !form.value.class_id) {
    alert('Class is required for Student accounts.')
    return
  }
  if (isStudentRole.value && classes.value.length === 0) {
    alert('No classes exist yet. Create a Class first (Classes page) before adding students.')
    return
  }

  const payload = {
    username: form.value.username,
    first_name: form.value.first_name,
    last_name: form.value.last_name,
    name_kh: form.value.name_kh || null,
    gender: form.value.gender,
    dob: formatDateForApi(form.value.dob),
    phone: form.value.phone || null,
    address: form.value.address || null,
    role_id: form.value.role_id,
    status: form.value.status,

    employee_code: form.value.employee_code || null,
    faculty_id: form.value.faculty_id,
    department_id: form.value.department_id,
    degree_id: form.value.degree_id,
    major_id: form.value.major_id,
    qualification: form.value.qualification || null,
    specialization: form.value.specialization || null,
    employment_type: form.value.employment_type,
    hire_date: formatDateForApi(form.value.hire_date),

    position: form.value.position || null,
    department: form.value.department || null,

    student_code: form.value.student_code || null,
    admission_date: formatDateForApi(form.value.admission_date),
    class_id: form.value.class_id,
    promotion_id: form.value.promotion_id,
    academic_year_id: form.value.academic_year_id,
    stage_id: form.value.stage_id,
    semester_id: form.value.semester_id,
    term_id: form.value.term_id,
    shift_id: form.value.shift_id,
  }
  if (form.value.password) payload.password = form.value.password

  try {
    if (isEdit.value) {
      await api.put(`/users/${form.value.id}`, payload)
    } else {
      await api.post('/users', payload)
    }

    userDialog.value = false
    await fetchUsers()
  } catch (error) {
    alert(extractError(error))
  }
}

const confirmDeleteUser = async (data) => {
  if (confirm(`Are you sure you want to delete ${data.first_name} ${data.last_name}?`)) {
    try {
      await api.delete(`/users/${data.id}`)
      await fetchUsers()
    } catch (error) {
      alert(extractError(error))
    }
  }
}

const onAvatarSelect = async (event) => {
  const file = event.files?.[0]
  if (!file || !form.value.id) return

  const formData = new FormData()
  formData.append('avatar', file)

  try {
    const { data } = await api.post(`/users/${form.value.id}/avatar`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    form.value.avatar_url = data.user.avatar_url
    await fetchUsers()
  } catch (error) {
    alert(extractError(error))
  }
}
</script>

<style scoped>
/* Header two sizes smaller, body two sizes larger, than the table's base text-xs. */
.users-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.users-table :deep(.p-datatable-tbody > tr > td) {
  font-size: 14px;
}

.font-khmer {
  font-family: 'Roboto', ui-sans-serif, system-ui, sans-serif;
}
</style>
