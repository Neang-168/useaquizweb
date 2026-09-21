<template>
  <div class="space-y-6">
    <!-- Back link -->
    <router-link :to="{ name: 'teacher.classes' }" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#002060] no-underline">
      <i class="pi pi-arrow-left text-[10px]"></i>
      <span>Back to My Classes</span>
    </router-link>

    <div v-if="!classInfo" class="text-center py-16 bg-[#F8F8F8] rounded-2xl border border-slate-200">
      <p class="text-xs text-slate-400">Loading...</p>
    </div>

    <template v-else>
      <!-- Header -->
      <div class="bg-white border border-slate-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <i class="pi pi-book text-[#002060] text-sm"></i>
            <h1 class="text-xl font-bold text-[#002060] m-0">{{ classInfo.subject }}</h1>
            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-[#D8E7EC]/50 text-[#002060] border border-[#D8E7EC]">{{ classInfo.shift }}</span>
          </div>
          <p class="text-xs text-slate-500 mt-1">
            <span class="font-semibold text-slate-700">{{ classInfo.className }}</span>
            • {{ classInfo.totalStudents }} students
            <!-- • Room {{ classInfo.room }} -->
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Button as="router-link" size="small"
            :to="{ name: 'teacher.questionbank', query: { subject_id: classInfo.subject_id } }"
            label="Question Bank" icon="pi pi-book"
            class="!bg-[#e4ac40] hover:!bg-[#e4ac40] !border-slate-200 !text-white !rounded-lg !text-xs no-underline !font-semibold"
          />
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-[#F8F8F8] p-1 rounded-xl w-full sm:w-fit overflow-x-auto border border-slate-200">
        <SelectButton
          v-model="activeTab"
          :options="tabs"
          option-label="label"
          option-value="value"
          :allow-empty="false"
          class="teacher-tabs"
        />
      </div>

      <!-- ============ TAB: OVERVIEW ============ -->
      <div v-if="activeTab === 'overview'" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h3 class="text-sm font-bold text-[#002060] m-0">
            Student Roster <span class="text-slate-400 font-medium">({{ filteredStudents.length }})</span>
          </h3>
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <div class="relative w-full sm:w-56">
              <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
              <InputText
                v-model="rosterSearch"
                size="small"
                placeholder="Search by name or username..."
                class="w-full !pl-9 !pr-3 !bg-[#F8F8F8] !border-slate-200 !rounded-lg !text-xs focus:!border-[#002060]"
              />
            </div>
            <Dropdown
              v-model="rosterGenderFilter"
              :options="rosterGenderOptions"
              option-label="label"
              option-value="value"
              size="small"
              class="!bg-[#F8F8F8] !border-slate-200 !rounded-lg !text-xs w-full sm:w-36"
            />
            <SplitButton
              label="Export CSV"
              icon="pi pi-file-excel"
              size="small"
              :model="rosterExportMenuItems"
              class="!text-xs whitespace-nowrap export-split-button"
              @click="exportRosterCsv"
            />
          </div>
        </div>

        <DataTable :value="filteredStudents" dataKey="id"
          paginator :rows="rosterRows" v-model:first="rosterFirst" :rowsPerPageOptions="[10, 20, 50]"
          scrollable scrollHeight="calc(100vh - 460px)"
          class="p-datatable-sm roster-table">
          <template #empty>
            <div class="text-center py-10 text-xs text-slate-400">
              {{ classInfo.students.length === 0 ? 'No students are enrolled in this class yet.' : 'No students match your search or filter.' }}
            </div>
          </template>

          <template #paginatorstart>
            <span class="text-xs text-slate-500">
              Showing <span class="font-semibold text-slate-700">{{ filteredStudents.length ? rosterFirst + 1 : 0 }}</span>
              to <span class="font-semibold text-slate-700">{{ Math.min(rosterFirst + rosterRows, filteredStudents.length) }}</span>
              of <span class="font-semibold text-slate-700">{{ filteredStudents.length }}</span> students
            </span>
          </template>

          <Column header="#" style="width: 60px; padding-left: 1.25rem">
            <template #body="{ index }">
              <span class="text-slate-400 font-mono text-sm">{{ index + 1 }}</span>
            </template>
          </Column>
          <Column field="username" header="USERNAME">
            <template #body="{ data }">
              <span class="font-mono font-bold text-[#002060] text-sm">{{ data.username }}</span>
            </template>
          </Column>
          <Column field="name" header="NAME">
            <template #body="{ data }">
              <span class="font-semibold text-slate-800 text-sm">{{ data.name }}</span>
            </template>
          </Column>
          <Column field="name_kh" header="NAME (KH)">
            <template #body="{ data }">
              <span class="text-slate-600 text-sm font-khmer">{{ data.name_kh || '—' }}</span>
            </template>
          </Column>
          <Column field="gender" header="GENDER">
            <template #body="{ data }">
              <span class="text-slate-500 text-sm">{{ data.gender }}</span>
            </template>
          </Column>
          <Column field="dob" header="DATE OF BIRTH">
            <template #body="{ data }">
              <span class="text-slate-500 text-sm">{{ data.dob || '—' }}</span>
            </template>
          </Column>
          <Column field="phone" header="PHONE">
            <template #body="{ data }">
              <span class="text-slate-500 text-sm">{{ data.phone || '—' }}</span>
            </template>
          </Column>
          <Column header="STATUS" class="!text-center">
            <template #body>
              <span class="bg-[#63c7df] text-white border border-[#D8E7EC] font-semibold px-2 py-0.5 rounded-md text-[10px]">
                Enrolled
              </span>
            </template>
          </Column>
        </DataTable>
      </div>

      <!-- ============ TAB: QUIZZES & EXAMS ============ -->
      <div v-if="activeTab === 'quizzes'" class="space-y-4">
        <div class="flex justify-end">
          <Button
            label="Create Quiz"
            icon="pi pi-plus"
            size="small"
            class="!bg-[#002060] hover:!bg-[#002060]/90 !border-[#002060] !text-white !rounded-lg !text-xs shadow-sm"
            @click="openQuizModal()"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="quiz in quizzes"
            :key="quiz.id"
            class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-[#63C7DF] hover:shadow-md transition-all flex flex-col justify-between"
          >
            <div>
              <div class="flex items-center justify-between gap-2 mb-3">
                <span
                  :class="statusBadgeClass(quiz.status)"
                  class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border"
                >
                  {{ statusLabel(quiz.status) }}
                </span>
                <span v-if="quiz.passMark !== null && quiz.passMark !== undefined" class="text-[10px] text-slate-400">Pass mark: {{ quiz.passMark }}%</span>
              </div>

              <h3 class="text-base font-bold text-[#002060] m-0">{{ quiz.title }}</h3>
              <p v-if="quiz.description" class="text-xs text-slate-500 mt-1 line-clamp-2">{{ quiz.description }}</p>

              <div class="mt-4 pt-3 border-t border-slate-100 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-500">
                  <span class="flex items-center gap-1.5"><i class="pi pi-clock text-[11px]"></i> Duration:</span>
                  <span class="font-bold text-slate-700">{{ quiz.duration }} min</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                  <span class="flex items-center gap-1.5"><i class="pi pi-list text-[11px]"></i> Questions:</span>
                  <span class="font-bold text-[#002060] bg-[#D8E7EC]/40 px-2 py-0.5 rounded">{{ quiz.totalQuestions }} ({{ quiz.totalPoints }} pt{{ quiz.totalPoints === 1 ? '' : 's' }})</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                  <span class="flex items-center gap-1.5"><i class="pi pi-refresh text-[11px]"></i> Attempts allowed:</span>
                  <span class="font-medium text-slate-700">{{ quiz.maxAttempts }}</span>
                </div>
                <div v-if="quiz.startAt || quiz.endAt" class="flex items-center justify-between text-slate-500">
                  <span class="flex items-center gap-1.5"><i class="pi pi-calendar text-[11px]"></i> Available:</span>
                  <span class="font-medium text-slate-700 text-[11px]">{{ formatWindow(quiz) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                  <span class="flex items-center gap-1.5"><i class="pi pi-users text-[11px]"></i> Submitted:</span>
                  <span class="font-bold text-[#63C7DF]">{{ quiz.submittedCount }}/{{ quiz.totalStudents }} students</span>
                </div>
              </div>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
              <div class="flex items-center gap-1">
                <Button icon="pi pi-eye" text rounded size="small" severity="secondary" title="Preview" class="!w-7 !h-7 !text-[#e4ac14] hover:!text-[#002060]" @click="openPreview(quiz)" />
                <Button
                  v-if="isEditable(quiz)"
                  icon="pi pi-pencil" text rounded size="small" severity="secondary" title="Edit"
                  class="!w-7 !h-7 !text-slate-400 hover:!text-[#002060]"
                  @click="openQuizModal(quiz)"
                />
                <Button
                  v-if="quiz.status === 'Draft'"
                  icon="pi pi-trash" text rounded size="small" severity="secondary" title="Delete"
                  class="!w-7 !h-7 !text-slate-400 hover:!text-[#D71818]"
                  @click="deleteQuiz(quiz.id)"
                />
              </div>

              <Button
                v-if="quiz.status === 'Draft'"
                label="Publish"
                size="small"
                text
                class="!bg-[#63C7DF]/15 hover:!bg-[#63C7DF]/30 !text-[#002060] !rounded-xl !text-xs !font-bold !px-3 !py-1.5"
                @click="publishQuiz(quiz)"
              />
              <Button
                v-else-if="quiz.status === 'Published'"
                label="Close Quiz"
                size="small"
                text
                class="!bg-[#E4AC40]/15 hover:!bg-[#E4AC40]/30 !text-[#E4AC40] !rounded-xl !text-xs !font-bold !px-3 !py-1.5"
                @click="closeQuiz(quiz)"
              />
              <span v-else class="text-[11px] text-[#e4ac14] font-semibold">Closed</span>
            </div>
          </div>

          <div v-if="quizzes.length === 0" class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-200">
            <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
            <p class="text-xs text-slate-500">This class doesn't have any quizzes or exams yet.</p>
          </div>
        </div>
      </div>

      <!-- ============ TAB: SCORE REPORT ============ -->
      <div v-if="activeTab === 'scores'" class="space-y-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Select Quiz / Exam</label>
            <Dropdown
              v-model="selectedScoreQuiz"
              :options="quizzes"
              option-label="title"
              option-value="id"
              placeholder="Select a quiz"
              size="small"
              class="!bg-[#F8F8F8] !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700 w-full sm:w-64"
            />
          </div>
        </div>

        <template v-if="selectedScoreQuiz">
          <!-- Analytics Graph & Summary Card -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <h4 class="text-xs font-bold text-[#002060] uppercase tracking-wider m-0 flex items-center gap-2">
                <i class="pi pi-chart-pie text-[#63C7DF]"></i> Pass / Fail Analytics
              </h4>
              <span class="text-xs text-slate-500 font-semibold">
                Total Submissions: <strong class="text-[#002060]">{{ scoreStats.totalSubmitted }}</strong>
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
              <!-- Pass Rate Box -->
              <div class="bg-emerald-50/60 border border-emerald-100 rounded-xl p-4 flex items-center justify-between">
                <div>
                  <p class="text-[11px] font-bold text-emerald-600 uppercase m-0">Passed Students</p>
                  <h3 class="text-2xl font-black text-emerald-700 m-0 mt-1">{{ scoreStats.passRate }}%</h3>
                  <p class="text-[10px] text-emerald-600/80 m-0 mt-0.5">{{ scoreStats.passedCount }} Students Passed</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                  <i class="pi pi-check-circle text-xl"></i>
                </div>
              </div>

              <!-- Fail Rate Box -->
              <div class="bg-rose-50/60 border border-rose-100 rounded-xl p-4 flex items-center justify-between">
                <div>
                  <p class="text-[11px] font-bold text-rose-600 uppercase m-0">Failed Students</p>
                  <h3 class="text-2xl font-black text-rose-700 m-0 mt-1">{{ scoreStats.failRate }}%</h3>
                  <p class="text-[10px] text-rose-600/80 m-0 mt-0.5">{{ scoreStats.failedCount }} Students Failed</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-rose-500/10 flex items-center justify-center text-rose-600">
                  <i class="pi pi-times-circle text-xl"></i>
                </div>
              </div>

              <!-- Visual Progress Bar Graph -->
              <div class="bg-[#F8F8F8] border border-slate-200 rounded-xl p-4 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold">
                  <span class="text-slate-600">Pass vs Fail Ratio</span>
                  <span class="text-[#002060]">{{ scoreStats.totalSubmitted > 0 ? '100%' : 'No Data' }}</span>
                </div>
                
                <!-- Stacked Bar Visual -->
                <div class="w-full h-4 bg-slate-200 rounded-full overflow-hidden flex shadow-inner">
                  <div 
                    class="bg-emerald-500 h-full transition-all duration-500 flex items-center justify-center text-[9px] font-bold text-white"
                    :style="{ width: `${scoreStats.passRate}%` }"
                    title="Passed"
                  >
                    <span v-if="Number(scoreStats.passRate) > 15">{{ scoreStats.passRate }}%</span>
                  </div>
                  <div 
                    class="bg-rose-500 h-full transition-all duration-500 flex items-center justify-center text-[9px] font-bold text-white"
                    :style="{ width: `${scoreStats.failRate}%` }"
                    title="Failed"
                  >
                    <span v-if="Number(scoreStats.failRate) > 15">{{ scoreStats.failRate }}%</span>
                  </div>
                </div>

                <div class="flex justify-between items-center text-[10px] text-slate-500 pt-1 font-medium">
                  <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span> Passed</span>
                  <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span> Failed</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Score Table Component -->
          <ScoreTable :quiz-id="selectedScoreQuiz" />
        </template>

        <div v-else class="text-center py-12 bg-white rounded-2xl border border-slate-200">
          <p class="text-xs text-slate-500">Create a quiz first to see its score report here.</p>
        </div>
      </div>

      <!-- ============ TAB: FEEDBACK ============ -->
      <div v-if="activeTab === 'feedback'" class="space-y-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quiz / Exam</label>
          <Dropdown
            v-model="selectedFeedbackQuiz"
            :options="quizzes"
            option-label="title"
            option-value="id"
            placeholder="Select a quiz"
            size="small"
            class="!bg-[#F8F8F8] !border-slate-200 !rounded-lg !text-xs !font-medium !text-slate-700 w-full sm:w-64"
          />
        </div>
        <FeedbackTable v-if="selectedFeedbackQuiz" :quiz-id="selectedFeedbackQuiz" />
        <div v-else class="text-center py-12 bg-white rounded-2xl border border-slate-200">
          <p class="text-xs text-slate-500">Create a quiz first to send feedback to students.</p>
        </div>
      </div>
    </template>

    <!-- ============ MODAL: QUIZ BUILDER ============ -->
    <Dialog
      :visible="showQuizModal"
      @update:visible="(val) => { if (!val) closeQuizModal() }"
      modal
      class="w-full max-w-5xl"
    >
      <template #header>
        <h3 class="text-sm font-bold text-[#002060] m-0">
          {{ editingQuizId ? 'Edit Quiz' : 'Create Quiz / Exam' }}
        </h3>
      </template>

      <div class="space-y-4 text-xs">
        <!-- Title & Description -->
        <div>
          <label class="block font-bold text-slate-700 mb-1">Quiz / Exam Title *</label>
          <InputText
            v-model="builderForm.title"
            size="small"
            placeholder="e.g. Midterm Exam - Vue.js Basics"
            class="w-full !bg-[#F8F8F8] !border-slate-200 !rounded-lg"
          />
        </div>
        <div>
          <label class="block font-bold text-slate-700 mb-1">Description</label>
          <Textarea
            v-model="builderForm.description"
            rows="2"
            size="small"
            placeholder="A short summary of this quiz for students..."
            class="w-full !bg-[#F8F8F8] !border-slate-200 !rounded-lg"
          />
        </div>

        <!-- Duration, Attempts, Total Score, Pass mark -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Duration (minutes) *</label>
            <InputNumber v-model="builderForm.duration" :min="1" size="small" class="w-full" input-class="w-full !bg-[#F8F8F8] !border-slate-200 !rounded-lg" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Attempts allowed *</label>
            <InputNumber v-model="builderForm.maxAttempts" :min="1" size="small" class="w-full" input-class="w-full !bg-[#F8F8F8] !border-slate-200 !rounded-lg" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Total Score</label>
            <InputNumber v-model="builderForm.totalScore" :min="1" :max="1000" size="small" placeholder="e.g. 100" class="w-full" input-class="w-full !bg-[#F8F8F8] !border-slate-200 !rounded-lg" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Pass mark (%)</label>
            <InputNumber v-model="builderForm.passMark" :min="0" :max="100" size="small" placeholder="e.g. 50" class="w-full" input-class="w-full !bg-[#F8F8F8] !border-slate-200 !rounded-lg" />
          </div>
        </div>

        <!-- Availability window -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Opens at</label>
            <input
              type="datetime-local"
              v-model="builderForm.startAt"
              class="w-full bg-[#F8F8F8] border border-slate-200 rounded-lg text-xs px-3 py-2 text-slate-700 focus:border-[#002060] focus:outline-none focus:ring-2 focus:ring-[#002060]/20"
            />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Closes at</label>
            <input
              type="datetime-local"
              v-model="builderForm.endAt"
              class="w-full bg-[#F8F8F8] border border-slate-200 rounded-lg text-xs px-3 py-2 text-slate-700 focus:border-[#002060] focus:outline-none focus:ring-2 focus:ring-[#002060]/20"
            />
          </div>
        </div>
        <p class="text-[11px] text-slate-400 -mt-2">Leave these blank for no fixed schedule. Closes at auto-fills from Opens at + Duration — you can still edit it. A published quiz is only open to students inside this window.</p>

        <!-- Shuffle toggles -->
        <div class="flex flex-wrap gap-3">
          <label class="flex items-center gap-2 cursor-pointer bg-[#F8F8F8] border border-slate-200 px-2.5 py-1.5 rounded-lg">
            <Checkbox v-model="builderForm.shuffleQuestions" binary />
            <span class="font-semibold text-slate-700">Shuffle question order</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer bg-[#F8F8F8] border border-slate-200 px-2.5 py-1.5 rounded-lg">
            <Checkbox v-model="builderForm.shuffleOptions" binary />
            <span class="font-semibold text-slate-700">Shuffle answer options</span>
          </label>
        </div>

        <!-- Question picker -->
        <div class="pt-1 border-t border-slate-100 space-y-2">
          <div class="flex items-center justify-between">
            <label class="block font-bold text-slate-700">
              Select Questions from the Question Bank
              <span class="text-[#002060]">({{ builderForm.selectedQuestionIds.length }} selected, {{ selectedPoints }} raw pt{{ selectedPoints === 1 ? '' : 's' }} &rarr; scaled to Total Score above)</span>
            </label>
            <Button
              label="Add New Question"
              icon="pi pi-plus"
              text
              size="small"
              class="!text-[#002060] hover:!text-[#63C7DF] !font-bold !text-[11px] !p-0"
              @click="openInlineQuestionEditor"
            />
          </div>

          <p v-if="pointsOverLimit" class="flex items-center gap-1.5 text-[11px] font-semibold text-[#E4AC40] bg-[#E4AC40]/10 border border-[#E4AC40]/30 rounded-lg px-2.5 py-1.5">
            <i class="pi pi-exclamation-triangle text-[10px]"></i>
            Selected questions total {{ selectedPoints }} raw pts, above your Total Score of {{ builderForm.totalScore }} &mdash; they'll be scaled down to fit. You can still add more if you want.
          </p>

          <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-3 items-start">
            <!-- Left: filters, list, add controls -->
            <div class="space-y-2 min-w-0">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <Dropdown
                  v-model="pickerCloFilter"
                  :options="clos"
                  option-label="title"
                  option-value="id"
                  placeholder="All CLOs"
                  show-clear
                  size="small"
                  class="!bg-[#F8F8F8] !border-slate-200 !rounded-lg !text-xs"
                />
                <Dropdown
                  v-model="pickerLloFilter"
                  :options="lloOptionsForFilter"
                  option-label="title"
                  option-value="id"
                  placeholder="All LLOs"
                  show-clear
                  size="small"
                  class="!bg-[#F8F8F8] !border-slate-200 !rounded-lg !text-xs"
                />
              </div>

              <div class="flex items-center gap-2">
                <InputText
                  v-model="pickerSearch"
                  size="small"
                  placeholder="Search questions..."
                  class="flex-1 !bg-[#F8F8F8] !border-slate-200 !rounded-lg !text-xs"
                />
                <Dropdown
                  v-model="pickerTypeFilter"
                  :options="pickerTypeOptions"
                  option-label="label"
                  option-value="value"
                  size="small"
                  class="!bg-[#F8F8F8] !border-slate-200 !rounded-lg !text-xs"
                />
              </div>

              <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 max-h-56 overflow-y-auto">
                <div
                  v-for="q in filteredPickerQuestions"
                  :key="q.id"
                  class="flex items-center gap-3 px-3 py-2 hover:bg-[#F8F8F8]"
                >
                  <Checkbox :value="q.id" v-model="builderForm.selectedQuestionIds" class="shrink-0" />
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#F8F8F8] text-slate-600 shrink-0 border border-slate-200">{{ formatType(q.type) }}</span>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#D8E7EC]/50 text-[#002060] border border-[#D8E7EC] shrink-0">{{ q.points }} pt{{ q.points === 1 ? '' : 's' }}</span>
                  <span class="text-slate-700 flex-1 truncate">{{ q.title || '(image question)' }}</span>
                  <span
                    :class="{
                      'bg-[#63C7DF]/15 text-[#002060] border-[#63C7DF]/40': q.difficulty === 'Easy',
                      'bg-[#E4AC40]/15 text-[#E4AC40] border-[#E4AC40]/40': q.difficulty === 'Medium',
                      'bg-[#D71818]/15 text-[#D71818] border-[#D71818]/40': q.difficulty === 'Hard'
                    }"
                    class="text-[10px] font-bold px-2 py-0.5 rounded border shrink-0"
                  >
                    {{ q.difficulty }}
                  </span>
                </div>
                <div v-if="filteredPickerQuestions.length === 0" class="px-3 py-6 text-center text-xs text-slate-400">
                  No questions match this filter. Try "Add New Question" to create one.
                </div>
              </div>

              <div class="flex flex-wrap items-center gap-2 bg-[#F8F8F8] border border-slate-200 rounded-lg px-3 py-2">
                <span class="text-[11px] font-semibold text-slate-600 flex-1">
                  {{ matchingCount }} question{{ matchingCount === 1 ? '' : 's' }} match &middot; {{ matchingSelectedCount }} already selected
                </span>
                <InputNumber v-model="randomCount" :min="1" :max="200" size="small" class="!w-20" input-class="w-full !bg-white !border-slate-200 !rounded-lg !text-xs" />
                <Button label="Add Random" size="small" class="!bg-[#63C7DF] hover:!bg-[#4bb6cf] !border-[#63C7DF] !text-white !rounded-lg !text-xs" @click="addRandom" />
                <Button label="Add All" size="small" outlined class="!border-[#002060] !text-[#002060] !rounded-lg !text-xs" @click="addAll" />
                <Button
                  label="Keep N Random"
                  size="small"
                  outlined
                  class="!border-[#e4ac40] !text-[#e4ac40] !rounded-lg !text-xs"
                  @click="keepRandom"
                />
              </div>
            </div>

            <!-- Right: Balance panel -->
            <div class="bg-white border border-slate-200 rounded-xl p-3 space-y-3 lg:sticky lg:top-2">
              <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide m-0">Balance</p>
                <p class="text-sm font-bold text-[#002060] m-0">{{ builderForm.selectedQuestionIds.length }} question{{ builderForm.selectedQuestionIds.length === 1 ? '' : 's' }} &middot; {{ selectedPoints }} pts</p>
              </div>

              <div class="space-y-1">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide m-0">By Learning Outcome</p>
                <div v-for="row in balanceByLlo" :key="row.id" class="flex items-center justify-between gap-2 text-[11px]">
                  <span :class="row.count === 0 ? 'text-rose-500 font-semibold' : 'text-slate-600'" class="truncate flex-1">{{ row.title }}</span>
                  <span
                    class="font-bold px-1.5 py-0.5 rounded-full shrink-0"
                    :class="row.count === 0 ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-[#D8E7EC]/50 text-[#002060] border border-[#D8E7EC]'"
                  >{{ row.count }}</span>
                </div>
                <p v-if="balanceByLlo.length === 0" class="text-[11px] text-slate-400 m-0">No LLOs defined for this subject yet.</p>
              </div>

              <div class="space-y-1">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide m-0">By Difficulty</p>
                <div v-for="row in balanceByDifficulty" :key="row.label" class="flex items-center justify-between gap-2 text-[11px]">
                  <span class="text-slate-600">{{ row.label }}</span>
                  <span class="font-bold px-1.5 py-0.5 rounded-full bg-[#D8E7EC]/50 text-[#002060] border border-[#D8E7EC] shrink-0">{{ row.count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <Button label="Cancel" size="small" class="!bg-[#F8F8F8] hover:!bg-slate-200 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="closeQuizModal" />
        <Button label="Save" size="small" class="!bg-[#002060] hover:!bg-[#002060]/90 !border-[#002060] !text-white !rounded-lg !text-xs" @click="saveQuiz" />
      </template>
    </Dialog>

    <!-- ============ MODAL: PREVIEW ============ -->
    <Dialog
      :visible="showPreviewModal"
      @update:visible="(val) => { if (!val) showPreviewModal = false }"
      modal
      class="w-full max-w-2xl"
    >
      <template #header>
        <div>
          <h3 class="text-sm font-bold text-[#002060] m-0">Preview: {{ previewQuiz?.title }}</h3>
          <p class="text-xs text-slate-400 mt-0.5 mb-0">{{ previewQuiz?.totalQuestions }} questions • {{ previewQuiz?.duration }} min • {{ previewQuiz?.maxAttempts }} attempt(s) allowed</p>
        </div>
      </template>

      <template v-if="showPreviewModal">
        <div class="space-y-3 text-xs">
          <p v-if="previewQuiz?.description" class="text-slate-600 bg-[#F8F8F8] border border-slate-200 rounded-xl p-3">{{ previewQuiz.description }}</p>

          <div
            v-for="(q, idx) in previewQuiz?.questions ?? []"
            :key="q.id"
            class="border border-slate-200 rounded-xl p-4 space-y-2"
          >
            <div class="flex items-start gap-2">
              <span class="text-xs font-bold text-slate-400 font-mono">Q{{ idx + 1 }}.</span>
              <h4 class="text-sm font-semibold text-slate-800 m-0 flex-1">{{ q.title || '(image question)' }}</h4>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#D8E7EC]/50 text-[#002060] border border-[#D8E7EC] shrink-0">{{ q.points }} pt{{ q.points === 1 ? '' : 's' }}</span>
            </div>

            <div v-if="q.imageUrl" class="pl-6">
              <div class="inline-block rounded-lg border border-slate-200 bg-[#F8F8F8] overflow-hidden">
                <Image :src="q.imageUrl" :alt="q.imageAlt || ''" preview image-class="max-h-40 object-contain block" />
              </div>
            </div>

            <div v-if="q.type === 'multiple_choice' || q.type === 'true_false'" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-6">
              <div
                v-for="(opt, oIdx) in q.options"
                :key="oIdx"
                :class="opt.isCorrect ? 'bg-[#63C7DF]/15 border-[#63C7DF] text-[#002060] font-semibold' : 'bg-[#F8F8F8] border-slate-200 text-slate-600'"
                class="p-2 rounded-lg border text-xs flex items-center gap-2"
              >
                <Image v-if="opt.imageUrl" :src="opt.imageUrl" alt="" preview image-class="w-8 h-8 rounded object-cover border border-slate-200 shrink-0 cursor-pointer" />
                <span class="flex-1">{{ String.fromCharCode(65 + oIdx) }}. {{ opt.text }}</span>
                <i v-if="opt.isCorrect" class="pi pi-check-circle text-[#002060] text-xs shrink-0"></i>
              </div>
            </div>

            <div v-else-if="q.type === 'matching'" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-6">
              <div v-for="(pair, pIdx) in q.matchingPairs" :key="pIdx" class="p-2 rounded-lg border border-slate-200 bg-[#F8F8F8] text-xs flex items-center gap-2 text-slate-600">
                <Image v-if="pair.leftImageUrl" :src="pair.leftImageUrl" alt="" preview image-class="w-8 h-8 rounded object-cover border border-slate-200 shrink-0 cursor-pointer" />
                <span class="font-semibold text-slate-700">{{ pair.leftText }}</span>
                <i class="pi pi-arrow-right-arrow-left text-slate-300 text-[10px] shrink-0"></i>
                <Image v-if="pair.rightImageUrl" :src="pair.rightImageUrl" alt="" preview image-class="w-8 h-8 rounded object-cover border border-slate-200 shrink-0 cursor-pointer" />
                <span>{{ pair.rightText }}</span>
              </div>
            </div>
          </div>

          <div v-if="!previewQuiz?.questions?.length" class="text-center py-8 text-xs text-slate-400">
            This quiz doesn't have any questions yet.
          </div>
        </div>
      </template>

      <template #footer>
        <Button label="Close" size="small" class="!bg-[#F8F8F8] hover:!bg-slate-200 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="showPreviewModal = false" />
      </template>
    </Dialog>

    <QuestionEditorModal
      v-model:visible="showQuestionEditor"
      :subjects="classInfo ? [{ id: classInfo.subject_id, code: '', name: classInfo.subject }] : []"
      :clos="clos"
      :llos="activeLlos"
      :locked-subject-id="classInfo?.subject_id"
      :editing-question="null"
      @saved="onNewQuestionSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import Button from 'primevue/button'
import SelectButton from 'primevue/selectbutton'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Checkbox from 'primevue/checkbox'
import Image from 'primevue/image'
import SplitButton from 'primevue/splitbutton'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import api, { toastFromError } from '../../../api'
import ScoreTable from '../../../components/teacher/ScoreTable.vue'
import FeedbackTable from '../../../components/teacher/FeedbackTable.vue'
import QuestionEditorModal from '../../../components/teacher/QuestionEditorModal.vue'
import { downloadCsv, downloadXlsx } from '../../../utils/exportTable'
import { formatDateTime } from '../../../utils/formatDateTime'

const route = useRoute()
const toast = useToast()
const confirm = useConfirm()
const assignmentId = computed(() => Number(route.params.assignmentId))

const tabs = [
  { label: 'Quizzes & Exams', value: 'quizzes' },
  { label: 'Score Report', value: 'scores' },
  { label: 'Feedback', value: 'feedback' },
  { label: 'Student List', value: 'overview' },
]
const validTabs = tabs.map(t => t.value)
const activeTab = ref(validTabs.includes(route.query.tab) ? route.query.tab : 'quizzes')

const classInfo = ref(null)
const quizzes = ref([])
const pickerQuestions = ref([])
const clos = ref([])
const llos = ref([])

const activeLlos = computed(() => llos.value.filter(l => l.status === 'Active'))

const fetchClassInfo = async () => {
  const { data } = await api.get('/teacher/classes')
  classInfo.value = data.data.find(c => c.id === assignmentId.value) ?? null
}

const fetchQuizzes = async () => {
  if (!classInfo.value) return
  const { data } = await api.get('/teacher/quizzes', {
    params: { class_id: classInfo.value.class_id, subject_id: classInfo.value.subject_id },
  })
  quizzes.value = data.data
  if (!selectedScoreQuiz.value) selectedScoreQuiz.value = quizzes.value[0]?.id ?? null
  if (!selectedFeedbackQuiz.value) selectedFeedbackQuiz.value = quizzes.value[0]?.id ?? null
}

const fetchPickerQuestions = async () => {
  if (!classInfo.value) return
  const { data } = await api.get('/teacher/questions', { params: { subject_id: classInfo.value.subject_id } })
  pickerQuestions.value = data.data
}

const fetchClos = async () => {
  if (!classInfo.value) return
  const { data } = await api.get('/teacher/clos', { params: { subject_id: classInfo.value.subject_id } })
  clos.value = data.data
}

// include_inactive so a since-archived LLO that already has questions tagged
// to it still shows up (with its true count) in the Balance panel below,
// instead of silently vanishing from the breakdown.
const fetchLlos = async () => {
  if (!classInfo.value) return
  const { data } = await api.get('/teacher/llos', { params: { subject_id: classInfo.value.subject_id, include_inactive: 1 } })
  llos.value = data.data
}

async function loadWorkspace() {
  classInfo.value = null
  quizzes.value = []
  pickerQuestions.value = []
  clos.value = []
  llos.value = []
  selectedScoreQuiz.value = null
  selectedFeedbackQuiz.value = null

  try {
    await fetchClassInfo()
    await Promise.all([fetchQuizzes(), fetchPickerQuestions(), fetchClos(), fetchLlos()])
  } catch (error) {
    toast.add({ summary: 'Failed to load class workspace', ...toastFromError(error) })
  }
}

onMounted(loadWorkspace)
watch(assignmentId, loadWorkspace)

/* ===== Overview tab: Student Roster ===== */
const rosterSearch = ref('')
const rosterGenderFilter = ref('')
const rosterGenderOptions = [
  { label: 'All Genders', value: '' },
  { label: 'Male', value: 'Male' },
  { label: 'Female', value: 'Female' },
]

const filteredStudents = computed(() => {
  if (!classInfo.value) return []
  const q = rosterSearch.value.trim().toLowerCase()

  return classInfo.value.students.filter(s => {
    if (rosterGenderFilter.value && s.gender !== rosterGenderFilter.value) return false
    if (!q) return true
    return s.name?.toLowerCase().includes(q) || s.name_kh?.toLowerCase().includes(q) || s.username?.toLowerCase().includes(q)
  })
})

const rosterFirst = ref(0)
const rosterRows = ref(10)

// Jump back to page 1 whenever the underlying list changes shape, so the
// paginator never gets stranded past the end of a smaller filtered list.
watch(filteredStudents, () => {
  rosterFirst.value = 0
})

const rosterExportHeader = ['#', 'Username', 'Name', 'Name (KH)', 'Gender', 'Date of Birth', 'Phone', 'Status']

const rosterExportRows = () => filteredStudents.value.map((s, i) => [
  i + 1, s.username, s.name, s.name_kh, s.gender, s.dob, s.phone, 'Enrolled',
])

function exportRosterCsv() {
  downloadCsv('student-roster.csv', rosterExportHeader, rosterExportRows())
}

function exportRosterXlsx() {
  downloadXlsx('student-roster.xlsx', rosterExportHeader, rosterExportRows(), 'Roster')
}

const rosterExportMenuItems = [
  { label: 'Export as CSV', icon: 'pi pi-file', command: exportRosterCsv },
  { label: 'Export as Excel (.xlsx)', icon: 'pi pi-file-excel', command: exportRosterXlsx },
]

function formatType(type) {
  if (type === 'multiple_choice') return 'MC'
  if (type === 'true_false') return 'T/F'
  if (type === 'matching') return 'Matching'
  return type
}

function statusLabel(status) {
  return status
}

function statusBadgeClass(status) {
  if (status === 'Published') return 'bg-emerald-50 text-emerald-600 border-emerald-200'
  if (status === 'Draft') return 'bg-slate-100 text-slate-500 border-slate-200'
  return 'bg-rose-50 text-rose-500 border-rose-200'
}

function formatWindow(quiz) {
  const start = quiz.startAt ? formatDateTime(quiz.startAt) : null
  const end = quiz.endAt ? formatDateTime(quiz.endAt) : null
  if (start && end) return `${start} → ${end}`
  if (start) return `From ${start}`
  if (end) return `Until ${end}`
  return 'No fixed schedule'
}

function isEditable(quiz) {
  if (quiz.status === 'Draft') return true
  return quiz.status === 'Published' && !!quiz.startAt && new Date(quiz.startAt) > new Date()
}

/* ===== Quiz builder modal ===== */
const showQuizModal = ref(false)
const editingQuizId = ref(null)
const pickerSearch = ref('')
const pickerTypeFilter = ref('')
const pickerCloFilter = ref(null)
const pickerLloFilter = ref(null)
const randomCount = ref(5)

// Picking a CLO invalidates whatever LLO was filtered on, since LLOs are
// scoped to a single CLO — keep the two dropdowns from disagreeing.
watch(pickerCloFilter, () => {
  pickerLloFilter.value = null
})

function cloIdForLlo(lloId) {
  return llos.value.find(l => l.id === lloId)?.clo_id ?? null
}

const lloOptionsForFilter = computed(() => {
  const base = activeLlos.value
  return pickerCloFilter.value ? base.filter(l => l.clo_id === pickerCloFilter.value) : base
})

const pickerTypeOptions = [
  { label: 'All Types', value: '' },
  { label: 'Multiple Choice', value: 'multiple_choice' },
  { label: 'True/False', value: 'true_false' },
  { label: 'Matching', value: 'matching' },
]

function defaultBuilderForm() {
  return {
    title: '',
    description: '',
    duration: 45,
    maxAttempts: 1,
    shuffleQuestions: false,
    shuffleOptions: false,
    passMark: null,
    totalScore: 100,
    startAt: '',
    endAt: '',
    selectedQuestionIds: [],
  }
}

const builderForm = ref(defaultBuilderForm())

function localStringToDate(str) {
  if (!str) return null
  const [datePart, timePart] = str.split('T')
  const [y, m, d] = datePart.split('-').map(Number)
  const [hh, mm] = (timePart || '00:00').split(':').map(Number)
  return new Date(y, m - 1, d, hh, mm)
}

function dateToLocalString(date) {
  if (!date) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

// Auto-fill "Closes at" from "Opens at" + Duration whenever either changes —
// still a plain field afterwards, so the teacher can edit it by hand.
watch(() => [builderForm.value.startAt, builderForm.value.duration], ([newStart, newDuration]) => {
  if (!newStart) return
  const start = localStringToDate(newStart)
  start.setMinutes(start.getMinutes() + (newDuration || 0))
  builderForm.value.endAt = dateToLocalString(start)
})

const filteredPickerQuestions = computed(() => {
  return pickerQuestions.value.filter(q => {
    const matchType = !pickerTypeFilter.value || q.type === pickerTypeFilter.value
    const matchSearch = !pickerSearch.value || (q.title || '').toLowerCase().includes(pickerSearch.value.toLowerCase())
    const matchClo = !pickerCloFilter.value || cloIdForLlo(q.llo_id) === pickerCloFilter.value
    const matchLlo = !pickerLloFilter.value || q.llo_id === pickerLloFilter.value
    return matchType && matchSearch && matchClo && matchLlo
  })
})

const matchingCount = computed(() => filteredPickerQuestions.value.length)

const matchingSelectedCount = computed(() => {
  const selected = new Set(builderForm.value.selectedQuestionIds)
  return filteredPickerQuestions.value.filter(q => selected.has(q.id)).length
})

const selectedQuestionObjects = computed(() => {
  const selected = new Set(builderForm.value.selectedQuestionIds)
  return pickerQuestions.value.filter(q => selected.has(q.id))
})

const selectedPoints = computed(() => {
  return selectedQuestionObjects.value.reduce((sum, q) => sum + (q.points || 0), 0)
})

const pointsOverLimit = computed(() => {
  const total = builderForm.value.totalScore
  return !!total && selectedPoints.value > total
})

// Every LLO of the subject is listed (even ones with zero selected, flagged
// in rose) so a teacher can immediately see which outcomes this quiz has
// no coverage for at all.
const balanceByLlo = computed(() => {
  const counts = new Map()
  let unassigned = 0

  selectedQuestionObjects.value.forEach(q => {
    if (q.llo_id) {
      counts.set(q.llo_id, (counts.get(q.llo_id) || 0) + 1)
    } else {
      unassigned++
    }
  })

  // LLO codes are only unique within their own CLO, so two different CLOs
  // can legitimately both have an "LLO01" — prefix with the CLO title too,
  // otherwise same-coded LLOs from different CLOs look like duplicates.
  const rows = llos.value.map(l => ({
    id: l.id,
    title: `${l.clo_title ? l.clo_title + ' · ' : ''}${l.code ? l.code + ' — ' : ''}${l.title}`,
    count: counts.get(l.id) || 0,
  }))

  if (unassigned > 0) {
    rows.push({ id: 'unassigned', title: 'Unassigned', count: unassigned })
  }

  return rows
})

const balanceByDifficulty = computed(() => {
  const counts = { Easy: 0, Medium: 0, Hard: 0 }
  selectedQuestionObjects.value.forEach(q => {
    if (counts[q.difficulty] !== undefined) counts[q.difficulty]++
  })
  return ['Easy', 'Medium', 'Hard'].map(label => ({ label, count: counts[label] }))
})

async function addRandom() {
  if (!classInfo.value) return

  try {
    const { data } = await api.post('/teacher/questions/random', {
      subject_id: classInfo.value.subject_id,
      clo_id: pickerCloFilter.value || undefined,
      llo_id: pickerLloFilter.value || undefined,
      question_type: pickerTypeFilter.value || undefined,
      count: randomCount.value,
      exclude_ids: builderForm.value.selectedQuestionIds,
    })

    const existingIds = new Set(pickerQuestions.value.map(q => q.id))
    data.data.forEach(q => {
      if (!existingIds.has(q.id)) pickerQuestions.value.push(q)
      if (!builderForm.value.selectedQuestionIds.includes(q.id)) builderForm.value.selectedQuestionIds.push(q.id)
    })

    if (data.shortfall > 0) {
      toast.add({ severity: 'warn', summary: 'Fewer questions available', detail: `Only ${data.data.length} available for this filter.`, life: 4000 })
    } else {
      toast.add({ severity: 'success', summary: 'Questions added', detail: `${data.data.length} question(s) added at random.`, life: 3000 })
    }
  } catch (error) {
    toast.add({ summary: 'Failed to add random questions', ...toastFromError(error) })
  }
}

// Trims the selection down, not up: among the questions matching the
// current filter that are ALREADY selected, keep only a random `randomCount`
// of them and deselect the rest. Unlike addRandom() (which only ever adds
// unselected matches), this is how you go from "all 12 tagged to this LLO
// are selected" down to "just 4 of them," e.g. to balance a quiz across
// several outcomes without hand-unchecking rows one by one.
function keepRandom() {
  const matchingSelectedIds = filteredPickerQuestions.value
    .map(q => q.id)
    .filter(id => builderForm.value.selectedQuestionIds.includes(id))

  if (matchingSelectedIds.length <= randomCount.value) {
    toast.add({
      severity: 'info',
      summary: 'Nothing to trim',
      detail: `Only ${matchingSelectedIds.length} question(s) matching this filter are selected already.`,
      life: 3500,
    })
    return
  }

  const shuffled = [...matchingSelectedIds].sort(() => Math.random() - 0.5)
  const toKeep = new Set(shuffled.slice(0, randomCount.value))
  const toRemove = new Set(matchingSelectedIds.filter(id => !toKeep.has(id)))

  builderForm.value.selectedQuestionIds = builderForm.value.selectedQuestionIds.filter(id => !toRemove.has(id))

  toast.add({
    severity: 'success',
    summary: 'Selection trimmed',
    detail: `Kept ${randomCount.value} random question(s) for this filter, removed ${toRemove.size}.`,
    life: 3500,
  })
}

function addAll() {
  const toAdd = filteredPickerQuestions.value
    .map(q => q.id)
    .filter(id => !builderForm.value.selectedQuestionIds.includes(id))

  if (toAdd.length === 0) {
    toast.add({ severity: 'info', summary: 'Nothing to add', detail: 'All matching questions are already selected.', life: 3000 })
    return
  }

  const applyAdd = () => {
    builderForm.value.selectedQuestionIds.push(...toAdd)
    toast.add({ severity: 'success', summary: 'Questions added', detail: `${toAdd.length} question(s) added.`, life: 3000 })
  }

  if (toAdd.length > 20) {
    confirm.require({
      header: 'Add all matching questions',
      message: `Add all ${toAdd.length} matching questions to this quiz?`,
      icon: 'pi pi-exclamation-triangle',
      acceptLabel: 'Add All',
      rejectLabel: 'Cancel',
      accept: applyAdd,
    })
  } else {
    applyAdd()
  }
}

async function openQuizModal(quiz = null) {
  pickerSearch.value = ''
  pickerTypeFilter.value = ''
  pickerCloFilter.value = null
  pickerLloFilter.value = null

  if (quiz) {
    editingQuizId.value = quiz.id
    try {
      const { data } = await api.get(`/teacher/quizzes/${quiz.id}`)
      const full = data.quiz
      builderForm.value = {
        title: full.title,
        description: full.description || '',
        duration: full.duration,
        maxAttempts: full.maxAttempts,
        shuffleQuestions: full.shuffleQuestions,
        shuffleOptions: full.shuffleOptions,
        passMark: full.passMark,
        totalScore: full.totalScore ?? 100,
        startAt: full.startAt || '',
        endAt: full.endAt || '',
        selectedQuestionIds: full.questions.map(q => q.id),
      }
    } catch (error) {
      toast.add({ summary: 'Failed to load quiz details', ...toastFromError(error) })
      return
    }
  } else {
    editingQuizId.value = null
    builderForm.value = defaultBuilderForm()
  }

  showQuizModal.value = true
}

function closeQuizModal() {
  showQuizModal.value = false
}

async function saveQuiz() {
  if (!builderForm.value.title.trim()) {
    toast.add({ severity: 'warn', summary: 'Missing information', detail: 'Please enter a title for the quiz.', life: 4000 })
    return
  }

  const payload = {
    title: builderForm.value.title,
    description: builderForm.value.description || null,
    subject_id: classInfo.value.subject_id,
    class_id: classInfo.value.class_id,
    duration_minutes: builderForm.value.duration,
    max_attempts: builderForm.value.maxAttempts,
    shuffle_questions: builderForm.value.shuffleQuestions,
    shuffle_options: builderForm.value.shuffleOptions,
    pass_mark: builderForm.value.passMark === null || builderForm.value.passMark === undefined ? null : builderForm.value.passMark,
    total_score: builderForm.value.totalScore === null || builderForm.value.totalScore === undefined ? null : builderForm.value.totalScore,
    start_at: builderForm.value.startAt || null,
    end_at: builderForm.value.endAt || null,
    question_ids: builderForm.value.selectedQuestionIds,
  }

  try {
    if (editingQuizId.value) {
      await api.put(`/teacher/quizzes/${editingQuizId.value}`, payload)
      toast.add({ severity: 'success', summary: 'Quiz updated', detail: `"${payload.title}" was updated.`, life: 3000 })
    } else {
      await api.post('/teacher/quizzes', payload)
      toast.add({ severity: 'success', summary: 'Quiz created', detail: `"${payload.title}" was created.`, life: 3000 })
    }
    closeQuizModal()
    await fetchQuizzes()
  } catch (error) {
    toast.add({ summary: 'Failed to save quiz', ...toastFromError(error) })
  }
}

function deleteQuiz(id) {
  confirm.require({
    header: 'Delete quiz',
    message: 'Are you sure you want to delete this quiz?',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Delete',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await api.delete(`/teacher/quizzes/${id}`)
        toast.add({ severity: 'success', summary: 'Quiz deleted', detail: 'The quiz was deleted.', life: 3000 })
        await fetchQuizzes()
      } catch (error) {
        toast.add({ summary: 'Failed to delete quiz', ...toastFromError(error) })
      }
    },
  })
}

function publishQuiz(quiz) {
  confirm.require({
    header: 'Publish quiz',
    message: `Publish "${quiz.title}"? Students will be able to see and take it immediately.`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Publish',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await api.post(`/teacher/quizzes/${quiz.id}/publish`)
        toast.add({ severity: 'success', summary: 'Quiz published', detail: `"${quiz.title}" is now live for students.`, life: 3000 })
        await fetchQuizzes()
      } catch (error) {
        toast.add({ summary: 'Failed to publish quiz', ...toastFromError(error) })
      }
    },
  })
}

function closeQuiz(quiz) {
  confirm.require({
    header: 'Close quiz',
    message: 'Close this quiz? Students will no longer be able to take it.',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Close Quiz',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await api.post(`/teacher/quizzes/${quiz.id}/close`)
        toast.add({ severity: 'success', summary: 'Quiz closed', detail: `"${quiz.title}" is now closed to students.`, life: 3000 })
        await fetchQuizzes()
      } catch (error) {
        toast.add({ summary: 'Failed to close quiz', ...toastFromError(error) })
      }
    },
  })
}

/* ===== Inline "create new question" from the builder ===== */
const showQuestionEditor = ref(false)

function openInlineQuestionEditor() {
  showQuestionEditor.value = true
}

function onNewQuestionSaved(question) {
  pickerQuestions.value.unshift(question)
  builderForm.value.selectedQuestionIds.push(question.id)
}

/* ===== Preview modal ===== */
const showPreviewModal = ref(false)
const previewQuiz = ref(null)

async function openPreview(quiz) {
  try {
    const { data } = await api.get(`/teacher/quizzes/${quiz.id}`)
    previewQuiz.value = data.quiz
    showPreviewModal.value = true
  } catch (error) {
    toast.add({ summary: 'Failed to load quiz preview', ...toastFromError(error) })
  }
}

/* ===== Score / Feedback tabs & Graph Analytics ===== */
const selectedScoreQuiz = ref(null)
const selectedFeedbackQuiz = ref(null)

const scoreStats = ref({
  passedCount: 0,
  failedCount: 0,
  passRate: 0,
  failRate: 0,
  totalSubmitted: 0,
  loading: false,
})

// Fetch Score Analytics dynamically when active quiz changes
const fetchQuizScoreStats = async (quizId) => {
  if (!quizId) return
  scoreStats.value.loading = true
  try {
    const { data } = await api.get(`/teacher/quizzes/${quizId}/scores`)
    const scores = data.data || []

    let passed = 0
    let failed = 0

    scores.forEach(s => {
      if (s.passed) {
        passed++
      } else {
        failed++
      }
    })

    const total = scores.length
    scoreStats.value = {
      passedCount: passed,
      failedCount: failed,
      passRate: total > 0 ? ((passed / total) * 100).toFixed(1) : 0,
      failRate: total > 0 ? ((failed / total) * 100).toFixed(1) : 0,
      totalSubmitted: total,
      loading: false,
    }
  } catch (error) {
    scoreStats.value.loading = false
  }
}

watch(selectedScoreQuiz, (newQuizId) => {
  if (newQuizId) fetchQuizScoreStats(newQuizId)
}, { immediate: true })
</script>

 <style scoped>
.roster-table :deep(.p-datatable-thead > tr > th) {
  font-size: 13px;
}

.roster-table :deep(.p-datatable-tbody > tr > td) {
  font-size: 14px;
}

.teacher-tabs :deep(.p-togglebutton) {
  border: 0;
  background: transparent;
  color: #64748b;
  font-weight: 500;
  font-size: 0.75rem;
  padding: 0.3rem 0.75rem;
  border-radius: 0.5rem;
  white-space: nowrap;
}

.teacher-tabs :deep(.p-togglebutton:hover) {
  color: #1e293b;
}

.teacher-tabs :deep(.p-togglebutton-checked) {
  background: #ffffff;
  color: #002060;
  font-weight: 700;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

.export-split-button :deep(.p-splitbutton-button),
.export-split-button :deep(.p-splitbutton-dropdown) {
  background: #059669;
  border-color: #059669;
  color: #fff;
}

.export-split-button :deep(.p-splitbutton-button:hover),
.export-split-button :deep(.p-splitbutton-dropdown:hover) {
  background: #047857;
  border-color: #047857;
}
</style> 