<template>
  <!-- Main Container: ប្រើ w-full h-screen លុប Margin/Padding ចោលទាំងអស់ -->
  <div class="flex h-screen w-full m-0 p-0 overflow-hidden bg-slate-100 font-sans antialiased">

    <!-- 2. Main Content Container ខាងស្តាំ -->
    <div class="flex-1 h-full overflow-y-auto">
      
      <!-- 3. កន្លែងលោតបង្ហាញ Dashboard / Profile / Users -->
      <!-- ដក p-6 ចេញពី <main> ហើយយកទៅដាក់ក្នុង Wrapper ខាងក្នុងវិញ ដើម្បីឱ្យ Content មិនបុកគែម តែ Layout មេនៅតែបឺតជាប់ ១០០% -->
      <main class="w-full h-full">
        <router-view />
      </main>

    </div>

    <Toast />
    <ConfirmDialog />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import api from './api'
import { setAuthUser } from './store/authUser'

// Refresh the shared user store from the server on every app load so the
// header/sidebars show the real name and avatar immediately, instead of only
// after the user happens to visit My Profile (which is the only other place
// that calls /me). Login already seeds full profile data too; this just
// keeps existing sessions (older localStorage, profile edited elsewhere) in sync.
onMounted(async () => {
  if (!localStorage.getItem('auth_token')) return

  try {
    const { data } = await api.get('/me')
    setAuthUser(data)
  } catch (error) {
    // A 401 is already handled globally by the axios interceptor (logout + redirect);
    // any other failure here just leaves the existing cached profile in place.
  }
})
</script>