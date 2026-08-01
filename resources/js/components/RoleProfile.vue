<template>
  <div class="dashboard-page">
    <div class="dashboard-shell">
      <AdminSidebar :active-view="activeView" @select-view="handleSelectView" @logout="logout" />
      <AdminContent
        :active-view="activeView"
        :user="user"
        :permissions="permissions"
        :users="users"
        :loading-users="loadingUsers"
        @refresh-users="loadUsers"
        @open-dialog="openDialog"
      />
    </div>

    <Dialog v-model:visible="dialogVisible" header="Create user" modal :style="{ width: '32rem' }">
      <form class="user-form" @submit.prevent="submitUser">
        <div class="form-grid">
          <div class="form-field">
            <label for="username">Username</label>
            <InputText id="username" v-model="form.username" placeholder="jane.doe" />
          </div>
          <div class="form-field">
            <label for="email">Email</label>
            <InputText id="email" v-model="form.email" type="email" placeholder="jane@example.com" />
          </div>
          <div class="form-field">
            <label for="password">Password</label>
            <Password id="password" v-model="form.password" placeholder="Minimum 8 characters" :feedback="false" toggleMask inputClass="w-full" />
          </div>
          <div class="form-field">
            <label for="role_id">Role</label>
            <select id="role_id" v-model="form.role_id" class="role-select">
              <option :value="null">Select a role</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
          </div>
          <div class="form-field">
            <label for="first_name">First name</label>
            <InputText id="first_name" v-model="form.first_name" placeholder="Jane" />
          </div>
          <div class="form-field">
            <label for="last_name">Last name</label>
            <InputText id="last_name" v-model="form.last_name" placeholder="Doe" />
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
import { computed, onMounted, reactive, ref } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import AdminSidebar from './AdminSidebar.vue'
import AdminContent from './AdminContent.vue'

const props = defineProps({
  user: { type: Object, default: null },
})

const toast = useToast?.() || null

const permissions = computed(() => props.user?.permissions || [])

const users = ref([])
const roles = ref([])
const loadingUsers = ref(false)
const submitting = ref(false)
const dialogVisible = ref(false)
const activeView = ref('profile')

const form = reactive({
  username: '',
  email: '',
  password: '',
  first_name: '',
  last_name: '',
  role_id: null,
  status: true,
})

function handleSelectView(view) {
  activeView.value = view
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
  window.location.reload()
}

onMounted(() => {
  loadRoles()
  loadUsers()
})
</script>

<style scoped>
.dashboard-page {
  /* min-height: 100vh; */
  background: linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%);
  padding: 2rem 1.25rem;
}

.dashboard-shell {
  /* max-width: 1280px; */
  margin: 0 auto;
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 1.25rem;
  min-height: calc(100vh - 4rem);
}

.role-select {
  width: 100%;
  border: 1px solid var(--surface-border, #cbd5e1);
  border-radius: 0.5rem;
  padding: 0.7rem 0.8rem;
  background: white;
  color: var(--text-color, #0f172a);
}

.user-form .form-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(2, 1fr);
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-field label {
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--text-color-secondary, #475569);
}

.w-full {
  width: 100%;
}

@media (max-width: 900px) {
  .dashboard-shell {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 560px) {
  .user-form .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>