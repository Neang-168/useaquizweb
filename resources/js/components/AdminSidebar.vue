<template>
  <!-- 1. Main Dashboard Wrapper (ពេញ Screen ១០០%) -->
  <div class="flex h-screen w-screen overflow-hidden bg-slate-50 font-sans antialiased">
    
    <!-- 2. SIDEBAR COMPONENT (ជាប់គែមឆ្វេង) -->
    <aside class="w-64 h-full bg-indigo-50 border-r border-slate-200 flex flex-col justify-between p-4 font-sans select-none shrink-0">
      
      <!-- Header + Scrollable Nav Navigation -->
      <div class="flex flex-col min-h-0 flex-1">
        
        <!-- Brand Logo & Name -->
        <div class="flex items-center gap-3 pb-5 px-1">
          <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
            <i class="pi pi-sparkles text-xl"></i>
          </div>
          <div>
            <h2 class="m-0 text-base font-bold text-slate-900 leading-tight">QMS</h2>
            <p class="m-0 text-xs text-slate-400 font-medium">Learning Suite</p>
          </div>
        </div>

        <!-- Navigation Menu Sections (Scrollable Area) -->
        <div class="flex-1 overflow-y-auto pr-1 my-2 space-y-6 scrollbar-thin scrollbar-thumb-slate-200">
          
          <!-- SECTION 1: OVERVIEW -->
          <div class="space-y-1">
            <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-2">
              OVERVIEW
            </span>
            
            <button
              class="w-full border-0 bg-transparent text-slate-700 hover:bg-slate-50 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all cursor-pointer text-left"
              :class="{ '!bg-blue-50/80 !text-blue-600': activeView === 'dashboard' }"
              @click="activeView = 'dashboard'"
            >
              <i class="pi pi-th-large text-lg" :class="activeView === 'dashboard' ? 'text-blue-600' : 'text-slate-600'"></i>
              <span>Dashboard</span>
            </button>
          </div>

          <!-- SECTION 2: TEACHING -->
          <div class="space-y-1">
            <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-2">
              TEACHING
            </span>
            
            <button
              class="w-full border-0 bg-transparent text-slate-700 hover:bg-slate-50 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all cursor-pointer text-left"
              :class="{ '!bg-blue-50/80 !text-blue-600': activeView === 'quizzes' }"
              @click="activeView = 'quizzes'"
            >
              <i class="pi pi-question-circle text-lg" :class="activeView === 'quizzes' ? 'text-blue-600' : 'text-slate-600'"></i>
              <span>Quizzes</span>
            </button>

            <button
              class="w-full border-0 bg-transparent text-slate-700 hover:bg-slate-50 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all cursor-pointer text-left"
              :class="{ '!bg-blue-50/80 !text-blue-600': activeView === 'question-bank' }"
              @click="activeView = 'question-bank'"
            >
              <i class="pi pi-bars text-lg" :class="activeView === 'question-bank' ? 'text-blue-600' : 'text-slate-600'"></i>
              <span>Question Bank</span>
            </button>

            <button
              class="w-full border-0 bg-transparent text-slate-700 hover:bg-slate-50 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all cursor-pointer text-left"
              :class="{ '!bg-blue-50/80 !text-blue-600': activeView === 'subjects' }"
              @click="activeView = 'subjects'"
            >
              <i class="pi pi-book text-lg" :class="activeView === 'subjects' ? 'text-blue-600' : 'text-slate-600'"></i>
              <span>Subjects</span>
            </button>

            <button
              class="w-full border-0 bg-transparent text-slate-700 hover:bg-slate-50 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all cursor-pointer text-left"
              :class="{ '!bg-blue-50/80 !text-blue-600': activeView === 'classes' }"
              @click="activeView = 'classes'"
            >
              <i class="pi pi-graduation-cap text-lg" :class="activeView === 'classes' ? 'text-blue-600' : 'text-slate-600'"></i>
              <span>Classes</span>
            </button>
          </div>

          <!-- SECTION 3: INSIGHTS -->
          <div class="space-y-1">
            <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-2">
              INSIGHTS
            </span>

            <button
              class="w-full border-0 bg-transparent text-slate-700 hover:bg-slate-50 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all cursor-pointer text-left"
              :class="{ '!bg-blue-50/80 !text-blue-600': activeView === 'results' }"
              @click="activeView = 'results'"
            >
              <i class="pi pi-chart-line text-lg" :class="activeView === 'results' ? 'text-blue-600' : 'text-slate-600'"></i>
              <span>Results</span>
            </button>

            <button
              class="w-full border-0 bg-transparent text-slate-700 hover:bg-slate-50 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all cursor-pointer text-left"
              :class="{ '!bg-blue-50/80 !text-blue-600': activeView === 'reports' }"
              @click="activeView = 'reports'"
            >
              <i class="pi pi-chart-bar text-lg" :class="activeView === 'reports' ? 'text-blue-600' : 'text-slate-600'"></i>
              <span>Reports</span>
            </button>

            <button
              class="w-full border-0 bg-transparent text-slate-700 hover:bg-slate-50 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all cursor-pointer text-left"
              :class="{ '!bg-blue-50/80 !text-blue-600': activeView === 'leaderboard' }"
              @click="activeView = 'leaderboard'"
            >
              <i class="pi pi-trophy text-lg" :class="activeView === 'leaderboard' ? 'text-blue-600' : 'text-slate-600'"></i>
              <span>Leaderboard</span>
            </button>
          </div>

          <!-- SECTION 4: ADMINISTRATION -->
          <div class="space-y-1">
            <span class="block text-[11px] font-bold tracking-wider text-slate-400 uppercase px-2 mb-2">
              ADMINISTRATION
            </span>

            <button
              class="w-full border-0 bg-transparent text-slate-700 hover:bg-slate-50 hover:text-blue-600 py-2.5 px-3 rounded-xl flex items-center gap-3 text-sm font-semibold transition-all cursor-pointer text-left"
              :class="{ '!bg-blue-50/80 !text-blue-600': activeView === 'users' }"
              @click="activeView = 'users'"
            >
              <i class="pi pi-users text-lg" :class="activeView === 'users' ? 'text-blue-600' : 'text-slate-600'"></i>
              <span>Users</span>
            </button>
          </div>

        </div>
      </div>

      <!-- Bottom Help Card -->
      <div class="pt-2 shrink-0">
        <div class="bg-blue-50/50 border border-blue-100/60 rounded-2xl p-3.5">
          <div class="flex items-center gap-1.5 text-blue-600 font-bold text-sm mb-1">
            <i class="pi pi-question-circle"></i>
            <span>Need help?</span>
          </div>
          <p class="text-xs text-slate-500 leading-relaxed mb-3">
            Browse the docs or contact support.
          </p>
          <button 
            class="w-full bg-white hover:bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 shadow-xs transition-all cursor-pointer"
            @click="handleOpenHelp"
          >
            Open Help Center
          </button>
        </div>
      </div>

    </aside>

    <!-- 3. MAIN CONTENT AREA (ខាងស្តាំលាតពេញ Scroll បាន) -->
    <main class="flex-1 h-full overflow-y-auto p-6 md:p-10">
      <div class="max-w-6xl mx-auto space-y-8">
        
        <!-- User Profile Header -->
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center text-sm shadow-md">
            SU
          </div>
          <div>
            <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase block mb-0.5">
              AUTHENTICATED PROFILE
            </span>
            <h1 class="text-2xl font-bold text-slate-900 m-0">
              Super Admin Dashboard
            </h1>
          </div>
        </div>

        <!-- Info Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-amber-500 uppercase tracking-wider block mb-2">
              • USERNAME
            </span>
            <p class="text-slate-800 font-semibold text-lg m-0">super.admin</p>
          </div>

          <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-amber-500 uppercase tracking-wider block mb-2">
              • EMAIL
            </span>
            <p class="text-slate-800 font-semibold text-lg m-0">superadmin@example.com</p>
          </div>
        </div>

        <!-- Permissions Section -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
          <span class="text-[11px] font-bold text-amber-500 uppercase tracking-wider block mb-4">
            • PERMISSIONS
          </span>
          <div class="flex flex-wrap gap-2.5">
            <span 
              v-for="permission in permissions" 
              :key="permission"
              class="px-3.5 py-1.5 bg-slate-100/80 text-slate-700 text-xs font-semibold rounded-full border border-slate-200/60"
            >
              {{ permission }}
            </span>
          </div>
        </div>

      </div>
    </main>

  </div>
</template>

<script setup>
import { ref } from 'vue'

// State សម្រាប់កំណត់ Tab ដែលកំពុងជ្រើសរើស
const activeView = ref('dashboard')

// បញ្ជី Permissions
const permissions = [
  'manage_users', 
  'manage_roles', 
  'manage_courses', 
  'manage_exams', 
  'view_dashboard', 
  'view_reports', 
  'manage_profile', 
  'view_schedule'
]

const handleOpenHelp = () => {
  alert('Open Help Center clicked!')
}
</script>