<template>
  <div class="space-y-6 w-full">

    <div>
      <h1 class="text-2xl font-bold text-slate-800 m-0">My Profile</h1>
      <p class="text-slate-500 text-sm mt-1 m-0">View and manage your account information</p>
    </div>

    <!-- Profile Summary -->
    <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center gap-5">
      <div class="w-20 h-20 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl font-bold overflow-hidden shrink-0">
        <img v-if="displayedAvatarUrl" :src="displayedAvatarUrl" alt="Avatar" class="w-full h-full object-cover" />
        <span v-else>{{ userInitial }}</span>
      </div>

      <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-2">
          <h2 class="text-lg font-bold text-slate-800 m-0">{{ userFullName }}</h2>
          <span v-if="currentUser?.name_kh" class="text-sm text-slate-500">({{ currentUser.name_kh }})</span>
        </div>

        <div class="flex flex-wrap items-center gap-2 mt-2">
          <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-600">
            {{ currentUser?.role || '—' }}
          </span>
          <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
            :class="currentUser?.status ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
            {{ currentUser?.status ? 'Active' : 'Inactive' }}
          </span>
          <span v-if="currentUser?.created_at" class="text-xs text-slate-400">
            Member since {{ formatDate(currentUser.created_at) }}
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 mt-3 text-sm text-slate-600">
          <p class="m-0"><i class="pi pi-user text-slate-400 mr-1.5"></i><strong>Username:</strong> {{ currentUser?.username || '—' }}</p>
          <p class="m-0"><i class="pi pi-envelope text-slate-400 mr-1.5"></i><strong>Email:</strong> {{ currentUser?.email || '—' }}</p>
        </div>
      </div>
    </div>

    <!-- Personal Information & Change Password -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

      <!-- Personal Information -->
      <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Personal Information</h3>

        <form class="space-y-4" @submit.prevent="saveProfile">
          <!-- Profile Picture -->
          <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
            <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl font-bold overflow-hidden shrink-0">
              <img v-if="displayedAvatarUrl" :src="displayedAvatarUrl" alt="Avatar" class="w-full h-full object-cover" />
              <span v-else>{{ userInitial }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <label class="text-xs font-medium text-slate-600 block mb-1.5">Profile picture</label>
              <Button type="button" label="Choose Photo" icon="pi pi-camera" size="small" severity="secondary" outlined
                @click="avatarInputRef?.click()" />
              <input ref="avatarInputRef" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="onAvatarSelected" />
              <p class="text-xs text-slate-400 mt-1.5 m-0">
                {{ avatarFile ? `Selected: ${avatarFile.name} — click "Save Changes" to apply` : 'JPG, PNG or WEBP. Max 2MB.' }}
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="flex flex-col gap-1.5">
              <label for="first_name" class="text-xs font-medium text-slate-600">First name</label>
              <InputText id="first_name" v-model="profileForm.first_name" size="small" placeholder="Jane" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="last_name" class="text-xs font-medium text-slate-600">Last name</label>
              <InputText id="last_name" v-model="profileForm.last_name" size="small" placeholder="Doe" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="name_kh" class="text-xs font-medium text-slate-600">Khmer name</label>
              <InputText id="name_kh" v-model="profileForm.name_kh" size="small" placeholder="ឧ. សុខ វិសាល" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="gender" class="text-xs font-medium text-slate-600">Gender</label>
              <Dropdown id="gender" v-model="profileForm.gender" :options="['Male', 'Female']" placeholder="Select gender" size="small" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="dob" class="text-xs font-medium text-slate-600">Date of birth</label>
              <InputText id="dob" v-model="profileForm.dob" type="date" size="small" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="phone" class="text-xs font-medium text-slate-600">Phone</label>
              <InputText id="phone" v-model="profileForm.phone" size="small" placeholder="012 345 678" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5 sm:col-span-2">
              <label for="email" class="text-xs font-medium text-slate-600">Email</label>
              <InputText id="email" v-model="profileForm.email" type="email" size="small" placeholder="jane@example.com" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5 sm:col-span-2">
              <label for="address" class="text-xs font-medium text-slate-600">Address</label>
              <Textarea id="address" v-model="profileForm.address" rows="2" size="small" placeholder="Street, city, country" class="w-full" />
            </div>
          </div>

          <div class="flex justify-end">
            <Button type="submit" label="Save Changes" icon="pi pi-check" size="small" :loading="savingProfile" />
          </div>
        </form>
      </div>

      <!-- Change Password -->
      <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Change Password</h3>

        <form class="space-y-3" @submit.prevent="savePassword">
          <div class="grid grid-cols-1 gap-3">
            <div class="flex flex-col gap-1.5">
              <label for="current_password" class="text-xs font-medium text-slate-600">Current password</label>
              <Password id="current_password" v-model="passwordForm.current_password" placeholder="Current password" :feedback="false" toggleMask size="small" inputClass="w-full" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="new_password" class="text-xs font-medium text-slate-600">New password</label>
              <Password id="new_password" v-model="passwordForm.password" placeholder="Minimum 8 characters" :feedback="false" toggleMask size="small" inputClass="w-full" class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label for="new_password_confirmation" class="text-xs font-medium text-slate-600">Confirm new password</label>
              <Password id="new_password_confirmation" v-model="passwordForm.password_confirmation" placeholder="Repeat new password" :feedback="false" toggleMask size="small" inputClass="w-full" class="w-full" />
            </div>
          </div>

          <div class="flex justify-end">
            <Button type="submit" label="Update Password" icon="pi pi-lock" size="small" :loading="savingPassword" />
          </div>
        </form>
      </div>

    </div>

    <Toast />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Password from 'primevue/password'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import api, { extractError } from '../api'
import { setAuthUser } from '../store/authUser'

const toast = useToast()

const currentUser = ref(null)
const savingProfile = ref(false)
const savingPassword = ref(false)
const avatarInputRef = ref(null)

// Staged avatar selection — only uploaded when "Save Changes" is clicked
const avatarFile = ref(null)
const avatarPreviewUrl = ref(null)

const displayedAvatarUrl = computed(() => avatarPreviewUrl.value || currentUser.value?.avatar_url || null)

const profileForm = ref({
  first_name: '',
  last_name: '',
  name_kh: '',
  gender: null,
  dob: '',
  phone: '',
  email: '',
  address: '',
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const userFullName = computed(() => {
  const fullName = `${currentUser.value?.first_name || ''} ${currentUser.value?.last_name || ''}`.trim()
  return fullName || currentUser.value?.username || 'User'
})

const userInitial = computed(() => userFullName.value.charAt(0).toUpperCase())

function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

const fetchCurrentUser = async () => {
  const { data } = await api.get('/me')
  currentUser.value = data
  profileForm.value = {
    first_name: data.first_name || '',
    last_name: data.last_name || '',
    name_kh: data.name_kh || '',
    gender: data.gender || null,
    dob: data.dob || '',
    phone: data.phone || '',
    email: data.email || '',
    address: data.address || '',
  }

  // Sync the shared store in case localStorage (from login) is stale
  setAuthUser(data)
}

onMounted(() => {
  fetchCurrentUser()
})

function revokeAvatarPreview() {
  if (avatarPreviewUrl.value) {
    URL.revokeObjectURL(avatarPreviewUrl.value)
    avatarPreviewUrl.value = null
  }
}

// Stage the picked file and show an instant local preview — nothing is
// uploaded until "Save Changes" is clicked.
function onAvatarSelected(event) {
  const file = event.target.files?.[0]
  event.target.value = ''
  if (!file) return

  revokeAvatarPreview()
  avatarFile.value = file
  avatarPreviewUrl.value = URL.createObjectURL(file)
}

onBeforeUnmount(() => revokeAvatarPreview())

const saveProfile = async () => {
  savingProfile.value = true
  try {
    let updatedUser = {}

    if (avatarFile.value) {
      const formData = new FormData()
      formData.append('avatar', avatarFile.value)
      const { data: avatarData } = await api.post('/me/avatar', formData)
      updatedUser = { ...updatedUser, ...avatarData.user }
    }

    const { data } = await api.put('/me', profileForm.value)
    updatedUser = { ...updatedUser, ...data.user }

    currentUser.value = { ...currentUser.value, ...updatedUser }

    // Push the change into the shared store so sidebars update immediately
    setAuthUser(updatedUser)

    avatarFile.value = null
    revokeAvatarPreview()

    toast.add({ severity: 'success', summary: 'Profile updated', detail: 'Your profile was saved.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Could not update profile', detail: extractError(error), life: 5000 })
  } finally {
    savingProfile.value = false
  }
}

const savePassword = async () => {
  if (!passwordForm.value.current_password || !passwordForm.value.password || !passwordForm.value.password_confirmation) {
    toast.add({ severity: 'warn', summary: 'Missing fields', detail: 'Please fill in all password fields.', life: 4000 })
    return
  }

  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    toast.add({ severity: 'warn', summary: 'Passwords do not match', detail: 'New password and confirmation must match.', life: 4000 })
    return
  }

  savingPassword.value = true
  try {
    await api.put('/me/password', passwordForm.value)
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    toast.add({ severity: 'success', summary: 'Password changed', detail: 'Your password was updated.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Could not change password', detail: extractError(error), life: 5000 })
  } finally {
    savingPassword.value = false
  }
}
</script>
