<template>
  <div class="space-y-6">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-users text-blue-600 text-2xl"></i>
          User Accounts
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          Manage login accounts and role assignment for every user in the system.
        </p>
      </div>

      <Button
        label="Add New User"
        icon="pi pi-plus"
        class="!bg-blue-600 hover:!bg-blue-700 !border-0 !rounded-xl !py-2.5 !px-4 !text-sm !font-semibold shadow-sm"
        @click="openNewDialog"
      />
    </div>

    <!-- ======= STATS CARDS ======= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Users</span>
          <span class="text-2xl font-bold text-slate-800">{{ users.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
          <i class="pi pi-users"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active</span>
          <span class="text-2xl font-bold text-emerald-600">{{ activeCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
          <i class="pi pi-check-circle"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Inactive</span>
          <span class="text-2xl font-bold text-rose-500">{{ users.length - activeCount }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl">
          <i class="pi pi-times-circle"></i>
        </div>
      </div>
    </div>

    <!-- ======= DATA TABLE CARD ======= -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
      <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3">
        <div class="relative w-full sm:w-80">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <InputText
            v-model="filters['global'].value"
            placeholder="Search username or email..."
            class="w-full !pl-9 !pr-4 !py-2 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm focus:!bg-white"
          />
        </div>
        <div class="text-xs text-slate-400">
          Showing <b>{{ users.length }}</b> entries
        </div>
      </div>

      <DataTable
        :value="users"
        v-model:filters="filters"
        dataKey="id"
        paginator
        :rows="10"
        :rowsPerPageOptions="[10, 20, 50]"
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <template #empty>
          <div class="text-center py-8 text-slate-400 text-sm">
            No users found.
          </div>
        </template>

        <Column field="username" header="USERNAME" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
              {{ data.username }}
            </span>
          </template>
        </Column>

        <Column header="NAME" sortable class="!py-3.5">
          <template #body="{ data }">
            <div>
              <div class="font-semibold text-slate-800 text-sm">{{ data.first_name }} {{ data.last_name }}</div>
              <div class="text-xs text-slate-400 mt-0.5">{{ data.email }}</div>
            </div>
          </template>
        </Column>

        <Column header="ROLE" sortable class="!py-3.5">
          <template #body="{ data }">
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">
              {{ data.role?.name || 'No role' }}
            </span>
          </template>
        </Column>

        <Column field="status" header="STATUS" sortable class="!py-3.5">
          <template #body="{ data }">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
              :class="data.status ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="data.status ? 'bg-emerald-500' : 'bg-rose-500'"></span>
              {{ data.status ? 'Active' : 'Inactive' }}
            </span>
          </template>
        </Column>

        <Column header="ACTIONS" class="!text-right !py-3.5">
          <template #body="{ data }">
            <div class="flex items-center justify-end gap-2">
              <Button
                icon="pi pi-pencil"
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-blue-600 hover:!bg-slate-100 !border-0"
                @click="editUser(data)"
              />
              <Button
                icon="pi pi-trash"
                class="!p-2 !w-8 !h-8 !rounded-lg !text-slate-500 hover:!text-rose-600 hover:!bg-rose-50 !border-0"
                @click="confirmDeleteUser(data)"
              />
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
      class="w-full max-w-lg"
    >
      <div class="space-y-4 pt-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Username *</label>
            <InputText v-model="userForm.username" placeholder="jane.doe" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email *</label>
            <InputText v-model="userForm.email" type="email" placeholder="jane@example.com" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">First Name *</label>
            <InputText v-model="userForm.first_name" placeholder="Jane" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Last Name *</label>
            <InputText v-model="userForm.last_name" placeholder="Doe" class="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
            Password {{ isEdit ? '(leave blank to keep current password)' : '*' }}
          </label>
          <Password v-model="userForm.password" :feedback="false" toggleMask inputClass="w-full !py-2.5 !px-3 !bg-slate-50 !border-slate-200 !rounded-xl !text-sm" class="w-full" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Role</label>
            <Dropdown
              v-model="userForm.role_id"
              :options="roles"
              optionLabel="name"
              optionValue="id"
              placeholder="Select Role"
              showClear
              class="w-full !bg-slate-50 !border-slate-200 !rounded-xl text-sm"
            />
          </div>
          <div class="flex items-center gap-2 pt-6">
            <input id="user_status" type="checkbox" v-model="userForm.status" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" />
            <label for="user_status" class="text-xs font-bold text-slate-700 cursor-pointer">Active</label>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-3">
          <Button label="Cancel" icon="pi pi-times" class="!bg-slate-100 !text-slate-600 hover:!bg-slate-200 !border-0 !rounded-xl !text-xs !font-semibold" @click="userDialog = false" />
          <Button label="Save User" icon="pi pi-check" class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold" @click="saveUser" />
        </div>
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api, { extractError } from '../../../api'

import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Password from 'primevue/password'

const users = ref([])
const roles = ref([])

const fetchUsers = async () => {
  const { data } = await api.get('/users', { params: { per_page: 100 } })
  users.value = data.data
}

const fetchRoles = async () => {
  const { data } = await api.get('/roles')
  roles.value = data
}

onMounted(() => {
  fetchUsers()
  fetchRoles()
})

const filters = ref({ global: { value: null, matchMode: 'contains' } })

const userDialog = ref(false)
const isEdit = ref(false)
const userForm = ref({
  id: null,
  username: '',
  email: '',
  password: '',
  first_name: '',
  last_name: '',
  role_id: null,
  status: true,
})

const activeCount = computed(() => users.value.filter(u => u.status).length)

const openNewDialog = () => {
  userForm.value = { id: null, username: '', email: '', password: '', first_name: '', last_name: '', role_id: null, status: true }
  isEdit.value = false
  userDialog.value = true
}

const editUser = (data) => {
  userForm.value = { ...data, password: '', role_id: data.role_id ?? data.role?.id ?? null }
  isEdit.value = true
  userDialog.value = true
}

const saveUser = async () => {
  if (!userForm.value.username || !userForm.value.email || !userForm.value.first_name || !userForm.value.last_name) {
    alert('Username, email, first name, and last name are required.')
    return
  }
  if (!isEdit.value && !userForm.value.password) {
    alert('Password is required when creating a new user.')
    return
  }

  const payload = {
    username: userForm.value.username,
    email: userForm.value.email,
    first_name: userForm.value.first_name,
    last_name: userForm.value.last_name,
    role_id: userForm.value.role_id,
    status: userForm.value.status,
  }
  if (userForm.value.password) {
    payload.password = userForm.value.password
  }

  try {
    if (isEdit.value) {
      await api.put(`/users/${userForm.value.id}`, payload)
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
  if (confirm(`Are you sure you want to delete ${data.username}?`)) {
    try {
      await api.delete(`/users/${data.id}`)
      await fetchUsers()
    } catch (error) {
      alert(extractError(error))
    }
  }
}
</script>
