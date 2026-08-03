<template>
  <!-- Main Layout Container: លាតពេញ 100% គ្មាន Padding/Margin (លុបចន្លោះសចោល) -->
  <div class="flex w-screen h-screen m-0 p-0 overflow-hidden bg-[#17b26a] font-sans">
    
    <!-- 1. Sidebar ពណ៌ខ្មៅ (ជាប់ឆ្វេង និងជាប់លើ 100%) -->
    <div class="h-full shrink-0">
      <AdminSidebar :active-view="activeView" @select-view="handleSelectView" @logout="logout" />
    </div>

    <!-- 2. Content ពណ៌បៃតង (ជាប់ Sidebar និងជាប់លើ 100%) -->
    <main class="flex-1 h-full overflow-y-auto bg-[#17b26a]">
      <AdminContentView
        :active-view="activeView"
        :user="currentUser"
        :permissions="permissions"
        :users="users"
        :loading-users="loadingUsers"
        @refresh-users="loadUsers"
        @open-dialog="openDialog"
      />
    </main>

    <!-- Dialog បង្កើត User -->
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
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import AdminSidebar from '../components/AdminSidebar.vue'
import AdminContentView from './AdminContentView.vue'

const props = defineProps({
  initialView: { type: String, default: 'profile' },
})

const route = useRoute()
const router = useRouter()
const toast = useToast?.() || null

const currentUser = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
const permissions = computed(() => currentUser.value?.permissions || [])

const users = ref([])
const roles = ref([])
const loadingUsers = ref(false)
const submitting = ref(false)
const dialogVisible = ref(false)
const activeView = ref(props.initialView)

const form = reactive({
  username: '',
  email: '',
  password: '',
  first_name: '',
  last_name: '',
  role_id: null,
  status: true,
})

watch(
  () => route.name,
  (name) => {
    activeView.value = name === 'admin.users' ? 'users' : 'profile'
  },
  { immediate: true }
)

function handleSelectView(view) {
  activeView.value = view
  const targetRoute = view === 'users' ? { name: 'admin.users' } : { name: 'admin.profile' }
  router.push(targetRoute)
}

function openDialog() {
  dialogVisible.value = true
}

function resetForm() {
  form.username = ''
  form.email = ''
  form.password = ''
  form.first_name = ''
  form.last_name = ''
  form.role_id = null
  form.status = true
}

async function loadRoles() {
  try {
    const token = localStorage.getItem('auth_token')
    const response = await fetch('/api/roles', {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Unable to load roles.')
    }

    roles.value = data || []
  } catch (error) {
    if (toast) {
      toast.add({
        severity: 'error',
        summary: 'Unable to load roles',
        detail: error.message,
        life: 4000,
      })
    }
  }
}

async function loadUsers() {
  loadingUsers.value = true

  try {
    const token = localStorage.getItem('auth_token')
    const response = await fetch('/api/users', {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Unable to load users.')
    }

    users.value = data.data || []
  } catch (error) {
    if (toast) {
      toast.add({
        severity: 'error',
        summary: 'Unable to load users',
        detail: error.message,
        life: 4000,
      })
    }
  } finally {
    loadingUsers.value = false
  }
}

async function submitUser() {
  submitting.value = true

  try {
    const token = localStorage.getItem('auth_token')
    const response = await fetch('/api/users', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(form),
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Unable to create user.')
    }

    if (toast) {
      toast.add({
        severity: 'success',
        summary: 'User created',
        detail: data.message || `${form.first_name || form.username} was added.`,
        life: 3000,
      })
    }

    resetForm()
    dialogVisible.value = false
    await loadUsers()
  } catch (error) {
    if (toast) {
      toast.add({
        severity: 'error',
        summary: 'Unable to create user',
        detail: error.message,
        life: 4000,
      })
    }
  } finally {
    submitting.value = false
  }
}

function logout() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  router.replace({ name: 'login' })
}

onMounted(() => {
  if (!currentUser.value) {
    router.replace({ name: 'login' })
    return
  }

  loadRoles()
  loadUsers()
})
</script>