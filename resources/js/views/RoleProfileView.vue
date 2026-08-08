<template>
  <div class="space-y-6 w-full">
    
    <!-- 1. ក្បាល Header នៃ Page (ដក AdminContentView ចេញ រួចជំនួសដោយ Content នេះ) -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 m-0">Role Profile</h1>
        <p class="text-slate-500 text-sm mt-1 m-0">គ្រប់គ្រងព័ត៌មាន និងអ្នកប្រើប្រាស់</p>
      </div>
      <Button label="Create User" icon="pi pi-plus" @click="openDialog" />
    </div>

    <!-- 2. Content ព័ត៌មាន Profile ឬ Table Users -->
    <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
      <h3 class="text-lg font-semibold text-slate-700 mb-2">Account Information</h3>
      <p class="text-slate-600"><strong>Username:</strong> {{ currentUser?.username }}</p>
      <p class="text-slate-600"><strong>Email:</strong> {{ currentUser?.email }}</p>
    </div>

    <!-- 3. Dialog បង្កើត User (រក្សាទុកដដែល) -->
    <Dialog v-model:visible="dialogVisible" header="Create user" modal class="w-[32rem]">
      <form class="py-2" @submit.prevent="submitUser">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="flex flex-col gap-1.5">
            <label for="username" class="text-xs font-medium text-slate-600">Username</label>
            <InputText id="username" v-model="form.username" placeholder="jane.doe" class="w-full" />
          </div>
          <div class="flex flex-col gap-1.5">
            <label for="email" class="text-xs font-medium text-slate-600">Email</label>
            <InputText id="email" v-model="form.email" type="email" placeholder="jane@example.com" class="w-full" />
          </div>
          <div class="flex flex-col gap-1.5">
            <label for="password" class="text-xs font-medium text-slate-600">Password</label>
            <Password id="password" v-model="form.password" placeholder="Minimum 8 characters" :feedback="false" toggleMask inputClass="w-full" />
          </div>
          <div class="flex flex-col gap-1.5">
            <label for="role_id" class="text-xs font-medium text-slate-600">Role</label>
            <select id="role_id" v-model="form.role_id" class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option :value="null">Select a role</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
          </div>
          <div class="flex flex-col gap-1.5">
            <label for="first_name" class="text-xs font-medium text-slate-600">First name</label>
            <InputText id="first_name" v-model="form.first_name" placeholder="Jane" class="w-full" />
          </div>
          <div class="flex flex-col gap-1.5">
            <label for="last_name" class="text-xs font-medium text-slate-600">Last name</label>
            <InputText id="last_name" v-model="form.last_name" placeholder="Doe" class="w-full" />
          </div>
        </div>
      </form>

      <template #footer>
        <Button label="Cancel" text severity="secondary" @click="dialogVisible = false" />
        <Button label="Create user" icon="pi pi-check" :loading="submitting" @click="submitUser" />
      </template>
    </Dialog>

    <Toast />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import api, { extractError } from '../api'

const toast = useToast()

const currentUser = ref(null)
const roles = ref([])

const fetchCurrentUser = async () => {
  const { data } = await api.get('/me')
  currentUser.value = data
}

const fetchRoles = async () => {
  const { data } = await api.get('/roles')
  roles.value = data
}

onMounted(() => {
  fetchCurrentUser()
  fetchRoles()
})

const dialogVisible = ref(false)
const submitting = ref(false)
const form = ref({
  username: '',
  email: '',
  password: '',
  role_id: null,
  first_name: '',
  last_name: '',
})

const openDialog = () => {
  form.value = { username: '', email: '', password: '', role_id: null, first_name: '', last_name: '' }
  dialogVisible.value = true
}

const submitUser = async () => {
  if (!form.value.username || !form.value.email || !form.value.password || !form.value.first_name || !form.value.last_name) {
    toast.add({ severity: 'warn', summary: 'Missing fields', detail: 'Username, email, password, first name, and last name are required.', life: 4000 })
    return
  }

  submitting.value = true
  try {
    await api.post('/users', form.value)
    toast.add({ severity: 'success', summary: 'User created', detail: `${form.value.first_name} ${form.value.last_name} was created.`, life: 3000 })
    dialogVisible.value = false
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Could not create user', detail: extractError(error), life: 5000 })
  } finally {
    submitting.value = false
  }
}
</script>