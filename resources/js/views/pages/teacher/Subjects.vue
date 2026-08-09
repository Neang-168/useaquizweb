<template>
  <div class="space-y-6">
    <!-- 1. Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">My Subjects</h1>
        <p class="text-xs text-slate-500 mt-1">Manage the subjects you teach and their course materials.</p>
      </div>

      <!-- Search -->
      <div class="flex items-center gap-3">
        <div class="relative">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-10"></i>
          <InputText
            v-model="searchQuery"
            size="small"
            placeholder="Search subjects..."
            class="!pl-9 !pr-3 !bg-white !border-slate-200 !rounded-lg !text-xs w-48 sm:w-64"
          />
        </div>
      </div>
    </div>

    <!-- 2. Subjects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="subject in filteredSubjects"
        :key="subject.id"
        class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-indigo-300 hover:shadow-md transition-all flex flex-col justify-between"
      >
        <div>
          <!-- Code Badge & Credit -->
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[11px] font-mono font-bold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100">
              {{ subject.code }}
            </span>
            <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-2 py-0.5 rounded-md">
              {{ subject.credits }} Credits
            </span>
          </div>

          <!-- Subject Title & Faculty -->
          <h3 class="text-base font-bold text-slate-800 m-0">{{ subject.name }}</h3>
          <p class="text-xs text-slate-400 mt-1">{{ subject.faculty }} • {{ subject.degree }}</p>

          <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400">Taught in:</span>
              <span class="font-bold text-slate-700">{{ subject.assignedClasses.join(', ') || '—' }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-400">Course materials:</span>
              <span class="font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                {{ subject.chapters.length }} item{{ subject.chapters.length === 1 ? '' : 's' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-5 pt-3 border-t border-slate-100 flex gap-2">
          <Button
            label="Course Materials"
            icon="pi pi-folder-open"
            size="small"
            class="w-full !bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg !text-xs"
            @click="openMaterialsModal(subject)"
          />
        </div>
      </div>

      <div v-if="filteredSubjects.length === 0" class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-200">
        <i class="pi pi-inbox text-3xl text-slate-300 mb-2"></i>
        <p class="text-xs text-slate-500">No subjects match your search.</p>
      </div>
    </div>

    <!-- 3. Modal: View & Manage Course Materials -->
    <Dialog
      :visible="!!selectedSubject"
      @update:visible="(val) => { if (!val) selectedSubject = null }"
      modal
      class="w-full max-w-2xl"
    >
      <template v-if="selectedSubject" #header>
        <div>
          <div class="flex items-center gap-2">
            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">{{ selectedSubject.code }}</span>
            <h3 class="text-sm font-bold text-slate-800 m-0">{{ selectedSubject.name }}</h3>
          </div>
          <p class="text-xs text-slate-500 mt-1 mb-0">Syllabus and files you share with students in this subject.</p>
        </div>
      </template>

      <template v-if="selectedSubject">
        <!-- Modal Body: Materials List -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Course Materials</h4>
              <p class="text-[11px] text-slate-400 mt-0.5">Lessons, notes, and files students can access for this subject.</p>
            </div>
            <Button
              v-if="!showAddChapterForm"
              label="Add Material"
              icon="pi pi-plus"
              size="small"
              class="!bg-slate-100 hover:!bg-indigo-50 !border-slate-100 !text-indigo-600 !font-bold !rounded-lg !text-xs shrink-0"
              @click="openAddChapterForm"
            />
          </div>

          <!-- Add Material Form -->
          <div v-if="showAddChapterForm" class="border border-indigo-200 rounded-xl p-3 bg-indigo-50/30 space-y-2">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 mb-1">Title *</label>
              <InputText
                v-model="chapterForm.title"
                size="small"
                placeholder="e.g. Introduction to Variables"
                class="w-full !bg-white !border-slate-200 !rounded-lg !text-xs"
              />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 mb-1">Description</label>
              <Textarea
                v-model="chapterForm.description"
                rows="2"
                size="small"
                placeholder="What this lesson covers..."
                class="w-full !bg-white !border-slate-200 !rounded-lg !text-xs"
              />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Duration</label>
                <InputText
                  v-model="chapterForm.duration_label"
                  size="small"
                  placeholder="e.g. 3 hours"
                  class="w-full !bg-white !border-slate-200 !rounded-lg !text-xs"
                />
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Attach a file (optional)</label>
                <FileUpload
                  mode="basic"
                  :auto="false"
                  choose-label="Choose file"
                  accept=".pdf,.ppt,.pptx,.doc,.docx"
                  custom-upload
                  class="w-full"
                  @select="onChapterFileChange"
                />
              </div>
            </div>
            <p class="text-[10px] text-slate-400">Accepted formats: PDF, Word, PowerPoint. Max size 20MB.</p>
            <div class="flex justify-end gap-2 pt-1">
              <Button label="Cancel" text size="small" class="!text-xs !font-semibold !text-slate-500" @click="showAddChapterForm = false" />
              <Button label="Save Material" size="small" class="!text-xs !font-semibold !text-white !bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !rounded-lg" @click="saveChapter" />
            </div>
          </div>

          <div class="space-y-2">
            <div
              v-for="(chapter, idx) in selectedSubject.chapters"
              :key="chapter.id ?? idx"
              class="border border-slate-200 rounded-xl p-3 bg-slate-50/30 hover:border-slate-300 transition-all"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <span class="text-[11px] font-bold text-indigo-600 uppercase">Lesson {{ idx + 1 }}</span>
                  <h5 class="text-sm font-bold text-slate-800 m-0 mt-0.5">{{ chapter.title }}</h5>
                  <p v-if="chapter.description" class="text-xs text-slate-500 mt-1">{{ chapter.description }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <span v-if="chapter.duration" class="text-[10px] bg-slate-200/70 text-slate-600 font-medium px-2 py-0.5 rounded-md">
                    {{ chapter.duration }}
                  </span>
                  <Button
                    v-if="chapter.id"
                    icon="pi pi-trash"
                    text
                    rounded
                    size="small"
                    severity="secondary"
                    title="Remove material"
                    class="!w-7 !h-7 !text-slate-400 hover:!text-rose-500"
                    @click="deleteChapter(chapter)"
                  />
                </div>
              </div>

              <!-- File Attachment -->
              <div class="mt-3 pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                <a
                  v-if="chapter.fileUrl"
                  :href="chapter.fileUrl"
                  target="_blank"
                  class="flex items-center gap-2 text-indigo-600 font-semibold hover:underline"
                >
                  <i :class="fileIcon(chapter.resourceName)"></i>
                  <span>{{ chapter.resourceName || 'Download file' }}</span>
                </a>
                <div v-else class="flex items-center gap-2 text-slate-400 font-semibold">
                  <i class="pi pi-file"></i>
                  <span>No file attached</span>
                </div>
              </div>
            </div>

            <div v-if="selectedSubject.chapters.length === 0 && !showAddChapterForm" class="text-center py-8 text-xs text-slate-400">
              <i class="pi pi-inbox text-2xl text-slate-300 mb-2 block"></i>
              No materials added for this subject yet.
            </div>
          </div>
        </div>
      </template>

      <template v-if="selectedSubject" #footer>
        <Button label="Close" size="small" class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs" @click="selectedSubject = null" />
      </template>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import FileUpload from 'primevue/fileupload'
import api, { extractError } from '../../../api'

const route = useRoute()

const searchQuery = ref('')
const selectedSubject = ref(null)

const mySubjects = ref([])
const loading = ref(false)

const fetchMySubjects = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/subjects')
    mySubjects.value = data.data
  } catch (error) {
    alert(extractError(error))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchMySubjects()

  if (route.query.subject_id) {
    const subject = mySubjects.value.find(s => s.id === Number(route.query.subject_id))
    if (subject) openMaterialsModal(subject)
  }
})

// Filter Search
const filteredSubjects = computed(() => {
  if (!searchQuery.value) return mySubjects.value
  const q = searchQuery.value.toLowerCase()
  return mySubjects.value.filter(s =>
    s.name?.toLowerCase().includes(q) ||
    s.code?.toLowerCase().includes(q)
  )
})

function openMaterialsModal(subject) {
  selectedSubject.value = subject
  showAddChapterForm.value = false
}

function fileIcon(filename) {
  const ext = filename?.split('.').pop()?.toLowerCase()
  if (ext === 'pdf') return 'pi pi-file-pdf'
  if (ext === 'doc' || ext === 'docx') return 'pi pi-file-word'
  if (ext === 'ppt' || ext === 'pptx') return 'pi pi-file'
  return 'pi pi-file'
}

// Add-material form (used inside the materials modal)
const showAddChapterForm = ref(false)
const chapterForm = ref({ title: '', description: '', duration_label: '', file: null })

function openAddChapterForm() {
  chapterForm.value = { title: '', description: '', duration_label: '', file: null }
  showAddChapterForm.value = true
}

function onChapterFileChange(event) {
  chapterForm.value.file = event.files?.[0] || null
}

async function saveChapter() {
  if (!chapterForm.value.title.trim()) {
    alert('Please enter a title for the material.')
    return
  }

  const formData = new FormData()
  formData.append('title', chapterForm.value.title)
  if (chapterForm.value.description) formData.append('description', chapterForm.value.description)
  if (chapterForm.value.duration_label) formData.append('duration_label', chapterForm.value.duration_label)
  if (chapterForm.value.file) formData.append('file', chapterForm.value.file)

  try {
    const { data } = await api.post(`/teacher/subjects/${selectedSubject.value.id}/materials`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    selectedSubject.value.chapters.push(data.material)
    showAddChapterForm.value = false
    await fetchMySubjects()
  } catch (error) {
    alert(extractError(error))
  }
}

async function deleteChapter(chapter) {
  if (!confirm(`Remove "${chapter.title}" from this subject?`)) return

  try {
    await api.delete(`/teacher/materials/${chapter.id}`)
    selectedSubject.value.chapters = selectedSubject.value.chapters.filter(c => c.id !== chapter.id)
    await fetchMySubjects()
  } catch (error) {
    alert(extractError(error))
  }
}
</script>
