<template>
  <div class="h-screen w-full flex flex-col overflow-hidden font-sans">

    <!-- ======= HEADER ======= -->
    <header class="w-full h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0">
      <!-- Left: Logo (mirrors sidebar mark) -->
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
          <i class="pi pi-sparkles text-lg"></i>
        </div>
        <div>
          <h1 class="m-0 text-sm font-bold text-slate-900 leading-tight">QMS</h1>
          <p class="m-0 text-[11px] text-slate-400 font-medium leading-tight">Learning Suite</p>
        </div>
      </div>

      <!-- Right: Search, Notifications, Profile -->
      <div class="flex items-center gap-5">
        <button
          type="button"
          class="border-0 bg-transparent text-slate-400 hover:text-blue-600 transition-colors p-0 cursor-pointer"
          aria-label="Search"
        >
          <i class="pi pi-search text-lg"></i>
        </button>

        <button
          type="button"
          class="relative border-0 bg-transparent text-slate-400 hover:text-blue-600 transition-colors p-0 cursor-pointer"
          aria-label="Notifications"
        >
          <i class="pi pi-bell text-lg"></i>
          <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-blue-600"></span>
        </button>

        <div class="w-px h-6 bg-slate-200"></div>

        <button
          type="button"
          class="flex items-center gap-2 border-0 bg-transparent p-0 cursor-pointer"
        >
          <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">
            <i class="pi pi-user text-sm"></i>
          </div>
          <i class="pi pi-chevron-down text-xs text-slate-400"></i>
        </button>
      </div>
    </header>

    <!-- ======= BODY: SIDEBAR + CONTENT ======= -->
    <div class="flex flex-1 min-h-0">

      <!-- ======= SIDEBAR ======= -->
      <aside class="w-64 h-full m-0 rounded-none bg-indigo-50 border-r border-slate-200 flex flex-col justify-between p-4 font-sans select-none shrink-0">
        <div class="flex flex-col min-h-0 flex-1">

          <!-- Navigation Links Area -->
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
                to="/admin/role-profile"
                class="w-full border-0 text-slate-700 hover:bg-slate-200/60 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all no-underline"
                active-class="!bg-blue-100 !text-blue-600"
              >
                <i class="pi pi-user text-lg"></i>
                <span>Role Profile</span>
              </router-link>
            </div>

          </div>
        </div>
      </aside>

      <!-- ======= MAIN CONTENT ======= -->
      <main class="flex-1 overflow-y-auto bg-slate-50 p-6">
        <router-view />
      </main>

    </div>
  </div>
</template>

<script setup>
// មិនចាំបាច់បន្ថែម Props ទេ — No props needed
</script>