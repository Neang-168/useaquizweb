<template>
  <div class="space-y-6 w-full">
    
    <!-- 1. ក្បាល Header នៃ Page (ដក AdminContentView ចេញ រួចជំនួសដោយ Content នេះ) -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 m-0">User Profile</h1>
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