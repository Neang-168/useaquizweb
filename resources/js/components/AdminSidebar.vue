<template>
  <!-- h-screen, m-0, rounded-none ដើម្បីបឺតជាប់គែមឆ្វេង និងខាងលើ ១០០% -->
  <aside class="w-64 h-full m-0 rounded-none bg-indigo-50 border-r border-slate-200 flex flex-col justify-between p-4 font-sans select-none shrink-0">
    <!-- Header Logo & Links Area -->
    <div class="flex flex-col min-h-0 flex-1">
      
      <!-- 1. Header Logo (បឺតស្អាតនៅជ្រុងខាងលើឆ្វេង) -->
      <div class="flex items-center gap-3 pb-5 px-1 pt-1">
        <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
          <i class="pi pi-sparkles text-xl"></i>
        </div>
        <div>
          <h2 class="m-0 text-base font-bold text-slate-900 leading-tight">QMS</h2>
          <p class="m-0 text-xs text-slate-400 font-medium">Learning Suite</p>
        </div>
      </div>

      <!-- 2. Navigation Links Area -->
      <div class="flex-1 overflow-y-auto pr-1 my-2 space-y-6">
        
        <!-- OVERVIEW SECTION -->
        <div class="space-y-1">
          <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-2">
            OVERVIEW
          </span>
          <router-link
            to="/admin/dashboard"
            class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
            active-class="!bg-blue-100 !text-blue-600"
          >
            <i class="pi pi-th-large text-lg"></i>
            <span>Dashboard</span>
          </router-link>
        </div>

        <!-- ADMINISTRATION SECTION -->
        <div class="space-y-1">
          <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-2">
            ADMINISTRATION
          </span>
          <router-link
            to="/admin/profile"
            class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
            active-class="!bg-blue-100 !text-blue-600"
          >
            <i class="pi pi-user text-lg"></i>
            <span>Profile</span>
          </router-link>
        </div>

      </div>
    </div>
  </aside>
</template>

<script setup>
// មិនចាំបាច់បន្ថែម Props ទេ
</script>