<template>
  <div class="space-y-6 w-full">
    
    <!-- Header Page -->
    <div>
      <h1 class="text-2xl font-bold text-slate-800 m-0">Welcome to Dashboard</h1>
      <p class="text-slate-500 text-sm mt-1 m-0">ទំព័រគ្រប់គ្រងព័ត៌មានទូទៅ (Dashboard Panel)</p>
    </div>

    <!-- Cards Layout (Grid 3 Columns) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">
      
      <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider block mb-2">• TOTAL USERS</span>
        <h2 class="text-3xl font-bold text-slate-800 m-0">120</h2>
      </div>

      <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider block mb-2">• ACTIVE COURSES</span>
        <h2 class="text-3xl font-bold text-slate-800 m-0">15</h2>
      </div>

      <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider block mb-2">• SYSTEM ROLES</span>
        <h2 class="text-3xl font-bold text-slate-800 m-0">4</h2>
      </div>

    </div>

  </div>
</template>