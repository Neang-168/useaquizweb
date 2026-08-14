<template>
  <div class="h-[calc(100vh-2rem)] flex flex-col gap-4 overflow-hidden font-sans text-slate-800">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0 bg-white/60 backdrop-blur-md p-4 rounded-xl border border-slate-200/60 shadow-xs">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
          <span class="p-2 rounded-lg bg-blue-50 text-blue-600 border border-blue-100/80">
            <i class="pi pi-users text-lg"></i>
          </span>
          User Management
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 m-0 mt-1 pl-0.5">
          Manage every account in the system &mdash; Super Admins, Admins, Staff, Teachers, and Students &mdash; from one place.
        </p>
      </div>

      <Button
        :label="addLabel"
        icon="pi pi-plus"
        class="!bg-blue-600 hover:!bg-blue-700 active:!bg-blue-800 !text-white !border-0 !rounded-lg !py-2.5 !px-4 !text-xs !font-semibold shadow-sm hover:shadow transition-all duration-200 gap-2 shrink-0 cursor-pointer"
        @click="openNewDialog(defaultRoleForTab)"
      />
    </div>

    <!-- ======= DATA TABLE CARD ======= -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden flex-1 flex flex-col">

      <!-- Tab Header Buttons -->
      <div class="flex flex-wrap border-b border-slate-200/80 bg-slate-50/70 p-2 gap-1.5 shrink-0">
        <button
          type="button"
          @click="activeTab = 'all'"
          class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeTab === 'all' ? 'bg-white text-blue-600 shadow-xs ring-1 ring-slate-200/60 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/80 bg-transparent'"
        >
          <i class="pi pi-users text-sm"></i>
          <span>All Users</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeTab === 'all' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-slate-200/70 text-slate-600'">
            {{ countFor('all') }}
          </span>
        </button>

        <button
          type="button"
          @click="activeTab = 'staff'"
          class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeTab === 'staff' ? 'bg-white text-blue-600 shadow-xs ring-1 ring-slate-200/60 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/80 bg-transparent'"
        >
          <i class="pi pi-shield text-sm"></i>
          <span>Super Admin, Admin &amp; Staff</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeTab === 'staff' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-slate-200/70 text-slate-600'">
            {{ countFor('staff') }}
          </span>
        </button>

        <button
          type="button"
          @click="activeTab = 'teacher'"
          class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeTab === 'teacher' ? 'bg-white text-blue-600 shadow-xs ring-1 ring-slate-200/60 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/80 bg-transparent'"
        >
          <i class="pi pi-id-card text-sm"></i>
          <span>Teacher</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeTab === 'teacher' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-slate-200/70 text-slate-600'">
            {{ countFor('teacher') }}
          </span>
        </button>

        <button
          type="button"
          @click="activeTab = 'student'"
          class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-200 border-0 cursor-pointer select-none"
          :class="activeTab === 'student' ? 'bg-white text-blue-600 shadow-xs ring-1 ring-slate-200/60 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/80 bg-transparent'"
        >
          <i class="pi pi-graduation-cap text-sm"></i>
          <span>Student</span>
          <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full transition-colors" :class="activeTab === 'student' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-slate-200/70 text-slate-600'">
            {{ countFor('student') }}
          </span>
        </button>
      </div>

      <!-- Table Header Bar / Search -->
      <div class="flex flex-col sm:flex-row justify-between items-center gap-3 shrink-0 p-4 pb-0">
        <div class="relative w-full sm:w-80">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
          <InputText
            v-model="filters['global'].value"
            placeholder="Search username, name, or email..."
            class="w-full !pl-9 !pr-3.5 !py-2 !bg-slate-50/80 hover:!bg-slate-100/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs transition-all placeholder:text-slate-400"
          />
        </div>
      </div>

      <!-- PrimeVue DataTable -->
      <DataTable
        :value="filteredUsers"
        v-model:filters="filters"
        :globalFilterFields="['username', 'email', 'first_name', 'last_name', 'name_kh', 'phone']"
        dataKey="id"
        paginator
        paginatorPosition="bottom"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
        currentPageReportTemplate="Showing {first} to {last} of {totalRecords}"
        :rows="10"
        :rowsPerPageOptions="[10, 20, 50]"
        scrollable
        scrollHeight="flex"
        responsiveLayout="scroll"
        :loading="loading"
        class="p-datatable-sm users-table flex-1 min-h-0 mt-4 mx-4 mb-4 text-xs"
      >
        <template #empty>
          <div class="text-center py-16 text-slate-400 text-xs flex flex-col items-center gap-2.5">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
              <i class="pi pi-inbox text-xl"></i>
            </div>
            <p class="m-0 font-medium text-slate-500">No users found</p>
          </div>
        </template>

        <!-- Username -->
        <Column field="username" header="USERNAME" sortable style="min-width: 130px">
          <template #body="{ data }">
            <span class="font-mono text-[11px] font-bold text-blue-700 bg-blue-50/80 px-2 py-0.5 rounded-md border border-blue-100 inline-block tracking-tight">
              {{ data.username }}
            </span>
          </template>
        </Column>

        <!-- Full Name -->
        <Column field="first_name" header="FULL NAME" sortable style="min-width: 180px">
          <template #body="{ data }">
            <span class="font-medium text-slate-800">{{ data.first_name }} {{ data.last_name }}</span>
          </template>
        </Column>

        <!-- Name (Khmer) -->
        <Column field="name_kh" header="NAME (KHMER)" style="min-width: 140px">
          <template #body="{ data }">
            <span class="text-slate-600 font-khmer">{{ data.name_kh || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Email -->
        <Column field="email" header="EMAIL" sortable style="min-width: 190px">
          <template #body="{ data }">
            <span class="text-slate-600 hover:text-slate-900 transition-colors">{{ data.email }}</span>
          </template>
        </Column>

        <!-- Phone -->
        <Column field="phone" header="PHONE" style="min-width: 130px">
          <template #body="{ data }">
            <span class="text-slate-600 font-mono text-[11px]">{{ data.phone || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Gender -->
        <Column field="gender" header="GENDER" style="width: 100px">
          <template #body="{ data }">
            <span class="text-slate-600">{{ data.gender || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Date of Birth -->
        <Column field="dob" header="DATE OF BIRTH" style="width: 130px">
          <template #body="{ data }">
            <span class="text-slate-600 font-mono text-[11px]">{{ formatDisplayDate(data.dob) || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Address -->
        <Column field="address" header="ADDRESS" style="min-width: 180px">
          <template #body="{ data }">
            <span class="text-slate-600 truncate block max-w-[200px]" :title="data.address">{{ data.address || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Role -->
        <Column header="ROLE" sortable style="width: 150px">
          <template #body="{ data }">
            <span
              class="text-[11px] font-bold px-2.5 py-0.5 rounded-md border inline-block tracking-tight"
              :class="roleBadgeClass(data.role?.name)"
            >
              {{ data.role?.name || 'No role' }}
            </span>
          </template>
        </Column>

        <!-- Employee / Student Code -->
        <Column header="CODE" style="width: 130px">
          <template #body="{ data }">
            <span class="font-mono text-[11px] font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/80">
              {{ detailsFor(data).primary || 'N/A' }}
            </span>
          </template>
        </Column>

        <!-- Department / Faculty / Admission -->
        <Column header="DEPARTMENT / FACULTY" style="min-width: 180px">
          <template #body="{ data }">
            <span class="text-slate-500 font-medium">{{ detailsFor(data).secondary || 'N/A' }}</span>
          </template>
        </Column>

        <!-- Status -->
        <Column field="status" header="STATUS" sortable style="width: 120px">
          <template #body="{ data }">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border"
              :class="data.status ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-rose-50 text-rose-700 border-rose-200/80'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="data.status ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
              {{ data.status ? 'Active' : 'Inactive' }}
            </span>
          </template>
        </Column>

        <!-- Actions -->
        <Column header="ACTIONS" style="width: 160px" class="!text-center users-actions-col">
          <template #body="{ data }">
            <div class="flex items-center justify-center gap-0.5">
              <button
                type="button"
                title="Edit user"
                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-100 hover:bg-blue-200 border border-slate-200/80 hover:border-blue-600 transition-all cursor-pointer"
                @click="editUser(data)"
              >
                <i class="pi pi-pencil text-[11px]"></i>
                <span>Edit</span>
              </button>
              <button
                type="button"
                title="Delete user"
                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold text-rose-600 hover:text-rose-800 bg-rose-100 hover:bg-rose-200 border border-slate-200/80 hover:border-rose-800 transition-all cursor-pointer"
                @click="confirmDeleteUser(data)"
              >
                <i class="pi pi-trash text-[11px]"></i>
                <span>Delete</span>
              </button>
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

        <!-- Account Info -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/70 shadow-2xs">
          <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
            <i class="pi pi-user text-blue-600 text-xs"></i>
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider m-0">Account</h4>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Username *</label>
              <InputText v-model="form.username" placeholder="jane.doe" class="w-full !py-2 !px-3 !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Email *</label>
              <InputText v-model="form.email" type="email" placeholder="jane@example.com" class="w-full !py-2 !px-3 !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                Password {{ isEdit ? '(leave blank to keep current)' : '*' }}
              </label>
              <Password v-model="form.password" :feedback="false" toggleMask inputClass="w-full !py-2 !px-3 !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" class="w-full" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Role *</label>
              <Dropdown
                v-model="form.role_id"
                :options="roles"
                optionLabel="name"
                optionValue="id"
                placeholder="Select Role"
                class="w-full !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 !rounded-lg text-xs"
              />
            </div>
          </div>
        </div>

        <!-- Personal Info -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/70 shadow-2xs">
          <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
            <i class="pi pi-id-card text-blue-600 text-xs"></i>
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider m-0">Personal Information</h4>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">First Name *</label>
              <InputText v-model="form.first_name" placeholder="Jane" class="w-full !py-2 !px-3 !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Last Name *</label>
              <InputText v-model="form.last_name" placeholder="Doe" class="w-full !py-2 !px-3 !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Name (Khmer)</label>
              <InputText v-model="form.name_kh" placeholder="ឧ. សុខ ចាន់ថាន" class="w-full !py-2 !px-3 !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs font-khmer" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Gender</label>
              <Dropdown v-model="form.gender" :options="['Male', 'Female']" placeholder="Select Gender" showClear class="w-full !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 !rounded-lg text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Date of Birth</label>
              <DatePicker v-model="form.dob" dateFormat="yy-mm-dd" showIcon iconDisplay="input" placeholder="Select date" class="w-full" inputClass="!py-2 !px-3 !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Phone</label>
              <InputText v-model="form.phone" placeholder="012 345 678" class="w-full !py-2 !px-3 !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Address</label>
              <Textarea v-model="form.address" rows="2" placeholder="Street, city, province..." class="w-full !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status</label>
              <Dropdown
                v-model="form.status"
                :options="[{ label: 'Active', value: true }, { label: 'Inactive', value: false }]"
                optionLabel="label"
                optionValue="value"
                class="w-full !bg-slate-50/80 focus:!bg-white !border-slate-200 focus:!border-blue-500 !rounded-lg text-xs"
              />
            </div>
          </div>
        </div>

        <!-- Teacher-specific fields -->
        <div v-if="isTeacherRole" class="bg-amber-50/30 p-4 rounded-xl border border-amber-200/60 shadow-2xs">
          <div class="flex items-center gap-2 mb-3 pb-2 border-b border-amber-200/50">
            <i class="pi pi-briefcase text-amber-600 text-xs"></i>
            <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wider m-0">Teacher Profile</h4>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Employee Code *</label>
              <InputText v-model="form.employee_code" placeholder="e.g. T-101" class="w-full !py-2 !px-3 !bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Employment Type</label>
              <Dropdown
                v-model="form.employment_type"
                :options="[{ label: 'Full-Time', value: 'full_time' }, { label: 'Part-Time', value: 'part_time' }]"
                optionLabel="label"
                optionValue="value"
                class="w-full !bg-white !border-slate-200 focus:!border-blue-500 !rounded-lg text-xs"
              />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Faculty *</label>
              <Dropdown v-model="form.faculty_id" :options="faculties" optionLabel="name_en" optionValue="id" placeholder="Select Faculty" class="w-full !bg-white !border-slate-200 focus:!border-blue-500 !rounded-lg text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Degree</label>
              <Dropdown v-model="form.degree_id" :options="filteredDegrees" optionLabel="title_en" optionValue="id" placeholder="Select Degree" showClear class="w-full !bg-white !border-slate-200 focus:!border-blue-500 !rounded-lg text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Major</label>
              <Dropdown v-model="form.major_id" :options="filteredMajors" optionLabel="name_en" optionValue="id" placeholder="Select Major" showClear class="w-full !bg-white !border-slate-200 focus:!border-blue-500 !rounded-lg text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Hire Date</label>
              <DatePicker v-model="form.hire_date" dateFormat="yy-mm-dd" showIcon iconDisplay="input" class="w-full" inputClass="!py-2 !px-3 !bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Qualification</label>
              <InputText v-model="form.qualification" placeholder="e.g. PhD in Computer Science" class="w-full !py-2 !px-3 !bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Specialization</label>
              <InputText v-model="form.specialization" placeholder="e.g. Machine Learning" class="w-full !py-2 !px-3 !bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
          </div>
        </div>

        <!-- Student-specific fields -->
        <div v-if="isStudentRole" class="bg-indigo-50/30 p-4 rounded-xl border border-indigo-200/60 shadow-2xs">
          <div class="flex items-center gap-2 mb-3 pb-2 border-b border-indigo-200/50">
            <i class="pi pi-graduation-cap text-indigo-600 text-xs"></i>
            <h4 class="text-xs font-bold text-indigo-800 uppercase tracking-wider m-0">Student Profile</h4>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Student Code *</label>
              <InputText v-model="form.student_code" placeholder="e.g. STU-1001" class="w-full !py-2 !px-3 !bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Admission Date</label>
              <DatePicker v-model="form.admission_date" dateFormat="yy-mm-dd" showIcon iconDisplay="input" class="w-full" inputClass="!py-2 !px-3 !bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
          </div>
        </div>

        <!-- Super Admin / Admin / Staff-specific fields -->
        <div v-if="isStaffRole" class="bg-emerald-50/30 p-4 rounded-xl border border-emerald-200/60 shadow-2xs">
          <div class="flex items-center gap-2 mb-3 pb-2 border-b border-emerald-200/50">
            <i class="pi pi-shield text-emerald-600 text-xs"></i>
            <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider m-0">Staff Profile</h4>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Employee Code</label>
              <InputText v-model="form.employee_code" placeholder="e.g. EMP-001" class="w-full !py-2 !px-3 !bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Position</label>
              <InputText v-model="form.position" placeholder="e.g. Registrar" class="w-full !py-2 !px-3 !bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Department</label>
              <InputText v-model="form.department" placeholder="e.g. Academic Affairs" class="w-full !py-2 !px-3 !bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Hire Date</label>
              <DatePicker v-model="form.hire_date" dateFormat="yy-mm-dd" showIcon iconDisplay="input" class="w-full" inputClass="!py-2 !px-3 !bg-white !border-slate-200 focus:!border-blue-500 focus:!ring-2 focus:!ring-blue-100 !rounded-lg !text-xs" />
            </div>
          </div>
        </div>

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
import { ref, computed, onMounted } from 'vue'
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



// ======= Data =======
const users = ref([])
const roles = ref([])
const faculties = ref([])
const degrees = ref([])
const majors = ref([])
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
  const [rolesRes, facultiesRes, degreesRes, majorsRes] = await Promise.all([
    api.get('/roles'),
    api.get('/faculties', { params: { per_page: 100 } }),
    api.get('/degrees', { params: { per_page: 100 } }),
    api.get('/majors', { params: { per_page: 200 } }),
  ])
  roles.value = rolesRes.data
  faculties.value = facultiesRes.data.data
  degrees.value = degreesRes.data.data
  majors.value = majorsRes.data.data
}

onMounted(() => {
  fetchUsers()
  fetchLookups()
})

// ======= Tabs =======
const activeTab = ref('all')
const roleGroups = {
  staff: ['Super Admin', 'Admin', 'Staff'],
  teacher: ['Teacher'],
  student: ['Student'],
}

const countFor = (key) => {
  if (key === 'all') return users.value.length
  return users.value.filter(u => roleGroups[key].includes(u.role?.name)).length
}

const filteredUsers = computed(() => {
  if (activeTab.value === 'all') return users.value
  return users.value.filter(u => roleGroups[activeTab.value].includes(u.role?.name))
})

const defaultRoleForTab = computed(() => {
  if (activeTab.value === 'teacher') return 'Teacher'
  if (activeTab.value === 'student') return 'Student'
  return null
})

const addLabel = computed(() => {
  if (activeTab.value === 'teacher') return 'Add New Teacher'
  if (activeTab.value === 'student') return 'Add New Student'
  if (activeTab.value === 'staff') return 'Add New Staff User'
  return 'Add New User'
})

// ======= Search Filter =======
const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
})

// ======= Dropdown helpers =======
const filteredDegrees = computed(() => degrees.value.filter(d => !form.value.faculty_id || d.faculty_id === form.value.faculty_id))
const filteredMajors = computed(() => majors.value.filter(m => !form.value.degree_id || m.degree_id === form.value.degree_id))

// ======= Dialog / Form =======
const userDialog = ref(false)
const isEdit = ref(false)

const emptyForm = () => ({
  id: null,
  username: '',
  email: '',
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
})

const form = ref(emptyForm())

const selectedRoleName = computed(() => roles.value.find(r => r.id === form.value.role_id)?.name)
const isTeacherRole = computed(() => selectedRoleName.value === 'Teacher')
const isStudentRole = computed(() => selectedRoleName.value === 'Student')
const isStaffRole = computed(() => roleGroups.staff.includes(selectedRoleName.value))

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
    case 'Super Admin': return 'bg-purple-50 text-purple-700 border-purple-200'
    case 'Admin': return 'bg-indigo-50 text-indigo-700 border-indigo-200'
    case 'Staff': return 'bg-amber-50 text-amber-700 border-amber-200'
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

  if (roleGroups.staff.includes(roleName) && data.admin_profile) {
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
  form.value = { ...emptyForm(), role_id: defaultRole?.id ?? null }
  isEdit.value = false
  userDialog.value = true
}

const editUser = (data) => {
  const tp = data.teacher_profile
  const sp = data.student_profile
  const ap = data.admin_profile

  form.value = {
    id: data.id,
    username: data.username,
    email: data.email,
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
    faculty_id: tp?.faculty_id || null,
    degree_id: tp?.degree_id || null,
    major_id: tp?.major_id || null,
    qualification: tp?.qualification || '',
    specialization: tp?.specialization || '',
    employment_type: tp?.employment_type || 'full_time',
    hire_date: parseApiDate(tp?.hire_date || ap?.hire_date),

    position: ap?.position || '',
    department: ap?.department || '',

    student_code: sp?.student_code || '',
    admission_date: parseApiDate(sp?.admission_date),
  }
  isEdit.value = true
  userDialog.value = true
}

const saveUser = async () => {
  if (!form.value.username || !form.value.email || !form.value.first_name || !form.value.last_name || !form.value.role_id) {
    alert('Username, email, first name, last name, and role are required.')
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

  const payload = {
    username: form.value.username,
    email: form.value.email,
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
/* Full-grid bordered table look (matches Faculties.vue) */
.users-table :deep(.p-datatable-table) {
  border-collapse: collapse;
}

.users-table :deep(.p-datatable-header),
.users-table :deep(.p-datatable-thead > tr > th) {
  background: #155dfc;
  color: #fff;
  font-size: 0.800rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  border: 1px solid #D4D4D4;
  padding: 1rem 1rem;
  white-space: nowrap;
}

.users-table :deep(.p-datatable-tbody > tr > td) {
  border: 1px solid #d4d4d4;
  padding: 0.6rem 1rem;
  font-size: 0.8rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 220px;
}

.users-table :deep(.p-datatable-tbody > tr > td:last-child) {
  overflow: visible;
  max-width: none;
}

.font-khmer {
  font-family: 'Roboto', ui-sans-serif, system-ui, sans-serif;
}

.users-table :deep(.p-datatable-tbody > tr:hover) {
  background: #f8fafc;
}

.users-table :deep(.p-datatable-tbody > tr:nth-child(even)) {
  background: #fbfcfe;
}

.users-table :deep(.p-datatable-tbody > tr:nth-child(even):hover) {
  background: #f1f5f9;
}

.users-table :deep(.p-datatable-table-container) {
  border-radius: 0.25rem;
  overflow: hidden;
}

/* ===== Action column buttons ===== */
.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.7rem;
  border-radius: 0;
  border: 0;
  color: #fff;
  font-size: 0.72rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.action-btn:active {
  transform: scale(0.94);
}

.action-btn-edit {
  background: #2563eb;
  border-radius: 0.25rem 0 0 0.25rem;
}

.action-btn-edit:hover {
  background: #1d4ed8;
}

.action-btn-delete {
  background: #dc2626;
  border-radius: 0 0.25rem 0.25rem 0;
}

.action-btn-delete:hover {
  background: #b91c1c;
}

/* Pinned, minimal paginator */
.users-table :deep(.p-datatable-paginator-bottom) {
  border-width: 0;
}

.users-table :deep(.p-paginator) {
  border-radius: 0 0 0.25rem 0.25rem;
  background: #ffffff;
  padding: 0.6rem 1rem;
  justify-content: flex-start;
}

.users-table :deep(.p-paginator .p-paginator-pages .p-paginator-page) {
  border-radius: 0.25rem;
  min-width: 2rem;
  height: 2rem;
  font-size: 0.75rem;
}

.users-table :deep(.p-paginator .p-paginator-page.p-highlight) {
  background: #2563eb;
  color: #fff;
}

.users-table :deep(.p-paginator-current) {
  margin-left: auto;
  font-size: 0.75rem;
  color: #64748b;
}

.users-table :deep(.p-datatable-wrapper) {
  min-height: 0;
}
</style>
