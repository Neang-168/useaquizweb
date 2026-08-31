<template>
  <Dialog
    :visible="visible"
    @update:visible="(val) => { if (!val) close() }"
    modal
    class="w-full max-w-xl"
  >
    <template #header>
      <div>
        <h3 class="text-sm font-bold text-slate-800 m-0">
          {{ isEditing ? 'Edit Question' : 'Add Question' }}
        </h3>
        <p class="text-xs text-slate-400 mt-0.5 mb-0">
          <span v-if="isEditing">Update the details below and save your changes.</span>
          <span v-else-if="sessionCount > 0">
            {{ sessionCount }} question{{ sessionCount === 1 ? '' : 's' }} added so far — keep adding, or close when you're done.
          </span>
          <span v-else>Fill in the details below. After you save, this form clears so you can add the next question right away.</span>
        </p>
      </div>
    </template>

    <template v-if="visible">
      <!-- Modal Form Body -->
      <div class="space-y-3 text-xs">
        <!-- Recently added chips -->
        <div v-if="!isEditing && sessionTitles.length > 0" class="flex flex-wrap gap-1.5 pb-1">
          <span
            v-for="(title, idx) in sessionTitles"
            :key="idx"
            class="text-[10px] font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1"
          >
            <i class="pi pi-check-circle text-[9px]"></i>
            {{ title.length > 28 ? title.slice(0, 28) + '…' : title }}
          </span>
        </div>

        <!-- Subject, Difficulty & Points -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Subject *</label>
            <Dropdown
              v-model="form.subject_id"
              :options="subjects"
              option-label="name"
              option-value="id"
              :disabled="!!lockedSubjectId"
              placeholder="Select subject"
              size="small"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-lg"
            >
              <template #option="{ option }">{{ option.code ? `${option.name} (${option.code})` : option.name }}</template>
              <template #value="{ value }">
                {{ subjectLabel(value) }}
              </template>
            </Dropdown>
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Difficulty *</label>
            <Dropdown
              v-model="form.difficulty"
              :options="['Easy', 'Medium', 'Hard']"
              placeholder="Select difficulty"
              size="small"
              class="w-full !bg-slate-50 !border-slate-200 !rounded-lg"
            />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Points *</label>
            <InputNumber
              v-model="form.points"
              :min="1"
              :max="1000"
              size="small"
              class="w-full"
              input-class="w-full !bg-slate-50 !border-slate-200 !rounded-lg"
            />
          </div>
        </div>

        <!-- Type Select -->
        <div>
          <label class="block font-bold text-slate-700 mb-1">Question Type *</label>
          <Dropdown
            v-model="form.type"
            :options="questionTypeOptions"
            option-label="label"
            option-value="value"
            placeholder="Select type"
            size="small"
            class="w-full !bg-slate-50 !border-slate-200 !rounded-lg"
          />
        </div>

        <!-- Question Title + Image -->
        <div>
          <label class="block font-bold text-slate-700 mb-1">Question Text</label>
          <p class="text-[11px] text-slate-400 -mt-0.5 mb-1">Add a question text, an image, or both.</p>
          <div class="flex items-start gap-2">
            <Textarea
              v-model="form.title"
              rows="3"
              size="small"
              placeholder="Type the question here..."
              class="flex-1 !bg-slate-50 !border-slate-200 !rounded-lg"
            />
            <QuestionImageField
              v-model:token="form.imageToken"
              v-model:removed="form.removeImage"
              :existing-url="form.existingImageUrl"
              :alt-text="form.imageAlt"
              label="Question image"
            />
          </div>
          <InputText
            v-if="form.imageToken || (form.existingImageUrl && !form.removeImage)"
            v-model="form.imageAlt"
            size="small"
            placeholder="Image description (optional, for accessibility)"
            class="w-full mt-1.5 !bg-slate-50 !border-slate-200 !rounded-lg"
          />
        </div>

        <!-- Options Section (Multiple Choice - checkboxes, one or more correct) -->
        <div v-if="form.type === 'multiple_choice'" class="space-y-2 pt-1">
          <label class="block font-bold text-slate-700">Answer Options (check every correct answer) *</label>
          <p class="text-[11px] text-slate-400 -mt-1">Each option needs text, an image, or both.</p>
          <div v-for="(opt, idx) in form.options" :key="idx" class="flex items-center gap-2">
            <Checkbox v-model="opt.isCorrect" binary />
            <QuestionImageField
              v-model:token="opt.imageToken"
              v-model:removed="opt.removeImage"
              :existing-url="opt.existingImageUrl"
              :label="'Option ' + String.fromCharCode(65 + idx) + ' image'"
            />
            <InputText
              v-model="opt.text"
              size="small"
              :placeholder="'Option ' + String.fromCharCode(65 + idx)"
              class="flex-1 !bg-slate-50 !border-slate-200 !rounded-lg"
            />
            <Button
              v-if="form.options.length > 2"
              icon="pi pi-trash"
              text
              rounded
              size="small"
              severity="secondary"
              class="!w-7 !h-7 !text-slate-400 hover:!text-rose-500"
              @click="form.options.splice(idx, 1)"
            />
          </div>
          <Button
            label="Add Option"
            icon="pi pi-plus"
            text
            size="small"
            class="!text-indigo-600 hover:!text-indigo-700 !font-bold !text-[11px] !p-0"
            @click="form.options.push(blankOption())"
          />
        </div>

        <!-- Options Section (True/False) -->
        <div v-if="form.type === 'true_false'" class="space-y-2 pt-1">
          <label class="block font-bold text-slate-700">Correct Answer *</label>
          <div class="flex gap-3">
            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">
              <RadioButton v-model="form.tfCorrect" value="True" size="small" />
              <span class="font-bold text-emerald-600">True</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">
              <RadioButton v-model="form.tfCorrect" value="False" size="small" />
              <span class="font-bold text-rose-600">False</span>
            </label>
          </div>
        </div>

        <!-- Matching pairs editor -->
        <div v-if="form.type === 'matching'" class="space-y-2 pt-1">
          <label class="block font-bold text-slate-700">Matching Pairs (left item ↔ right item) *</label>
          <p class="text-[11px] text-slate-400 -mt-1">Students will match each left item to the correct right item. Each side needs text, an image, or both.</p>
          <div v-for="(pair, idx) in form.matchingPairs" :key="idx" class="flex items-center gap-2">
            <span class="text-slate-400 font-mono w-5 shrink-0">{{ idx + 1 }}.</span>
            <QuestionImageField
              v-model:token="pair.leftImageToken"
              v-model:removed="pair.removeLeftImage"
              :existing-url="pair.leftExistingImageUrl"
              :label="'Pair ' + (idx + 1) + ' left image'"
            />
            <InputText
              v-model="pair.leftText"
              size="small"
              placeholder="Left item"
              class="flex-1 !bg-slate-50 !border-slate-200 !rounded-lg"
            />
            <i class="pi pi-arrow-right-arrow-left text-slate-300 text-[10px] shrink-0"></i>
            <QuestionImageField
              v-model:token="pair.rightImageToken"
              v-model:removed="pair.removeRightImage"
              :existing-url="pair.rightExistingImageUrl"
              :label="'Pair ' + (idx + 1) + ' right image'"
            />
            <InputText
              v-model="pair.rightText"
              size="small"
              placeholder="Right item"
              class="flex-1 !bg-slate-50 !border-slate-200 !rounded-lg"
            />
            <Button
              v-if="form.matchingPairs.length > 2"
              icon="pi pi-trash"
              text
              rounded
              size="small"
              severity="secondary"
              class="!w-7 !h-7 !text-slate-400 hover:!text-rose-500"
              @click="form.matchingPairs.splice(idx, 1)"
            />
          </div>
          <Button
            label="Add Pair"
            icon="pi pi-plus"
            text
            size="small"
            class="!text-indigo-600 hover:!text-indigo-700 !font-bold !text-[11px] !p-0"
            @click="form.matchingPairs.push(blankPair())"
          />
        </div>
      </div>

    </template>

    <template #footer>
      <Button
        :label="isEditing || sessionCount === 0 ? 'Cancel' : 'Close'"
        size="small"
        class="!bg-slate-200 hover:!bg-slate-300 !border-slate-200 !text-slate-700 !rounded-lg !text-xs"
        @click="close"
      />
      <Button
        :label="isEditing ? 'Save Changes' : 'Save & Add Another'"
        :icon="!isEditing ? 'pi pi-plus' : undefined"
        size="small"
        class="!bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 !text-white !rounded-lg !text-xs"
        @click="save"
      />
    </template>
  </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'
import RadioButton from 'primevue/radiobutton'
import QuestionImageField from './QuestionImageField.vue'
import { useToast } from 'primevue/usetoast'
import api, { toastFromError } from '../../api'

const toast = useToast()

const props = defineProps({
  visible: { type: Boolean, default: false },
  subjects: { type: Array, default: () => [] },
  editingQuestion: { type: Object, default: null },
  lockedSubjectId: { type: [Number, String], default: null },
})

const emit = defineEmits(['update:visible', 'saved'])

const isEditing = ref(false)
const editingId = ref(null)
const sessionCount = ref(0)
const sessionTitles = ref([])

function blankOption() {
  return { text: '', isCorrect: false, imageToken: null, removeImage: false, existingImageUrl: null }
}

function blankPair() {
  return {
    leftText: '', leftImageToken: null, removeLeftImage: false, leftExistingImageUrl: null,
    rightText: '', rightImageToken: null, removeRightImage: false, rightExistingImageUrl: null,
  }
}

function defaultForm(keep = null) {
  return {
    subject_id: keep?.subject_id ?? props.lockedSubjectId ?? props.subjects[0]?.id ?? null,
    difficulty: keep?.difficulty ?? 'Medium',
    points: keep?.points ?? 1,
    type: keep?.type ?? 'multiple_choice',
    title: '',
    imageToken: null,
    removeImage: false,
    imageAlt: '',
    existingImageUrl: null,
    options: [
      { ...blankOption(), isCorrect: true },
      blankOption(),
      blankOption(),
      blankOption(),
    ],
    tfCorrect: 'True',
    matchingPairs: [blankPair(), blankPair()],
  }
}

const form = ref(defaultForm())

const questionTypeOptions = [
  { label: 'Multiple Choice', value: 'multiple_choice' },
  { label: 'True / False', value: 'true_false' },
  { label: 'Matching', value: 'matching' },
]

function subjectLabel(id) {
  const subject = props.subjects.find(s => s.id === id)
  if (!subject) return ''
  return subject.code ? `${subject.name} (${subject.code})` : subject.name
}

watch(() => props.visible, (isVisible) => {
  if (!isVisible) return

  sessionCount.value = 0
  sessionTitles.value = []

  const src = props.editingQuestion

  if (src) {
    isEditing.value = true
    editingId.value = src.id

    const cloned = {
      subject_id: src.subject_id,
      difficulty: src.difficulty,
      points: src.points ?? 1,
      type: src.type,
      title: src.title || '',
      imageToken: null,
      removeImage: false,
      imageAlt: src.imageAlt || '',
      existingImageUrl: src.imageUrl || null,
      options: defaultForm().options,
      tfCorrect: 'True',
      matchingPairs: defaultForm().matchingPairs,
    }

    if (src.type === 'multiple_choice' && src.options?.length) {
      cloned.options = src.options.map(o => ({
        text: o.text || '',
        isCorrect: !!o.isCorrect,
        imageToken: null,
        removeImage: false,
        existingImageUrl: o.imageUrl || null,
      }))
    }

    if (src.type === 'true_false') {
      cloned.tfCorrect = src.options?.find(o => o.isCorrect)?.text === 'False' ? 'False' : 'True'
    }

    if (src.type === 'matching' && src.matchingPairs?.length) {
      cloned.matchingPairs = src.matchingPairs.map(p => ({
        leftText: p.leftText || '',
        leftImageToken: null,
        removeLeftImage: false,
        leftExistingImageUrl: p.leftImageUrl || null,
        rightText: p.rightText || '',
        rightImageToken: null,
        removeRightImage: false,
        rightExistingImageUrl: p.rightImageUrl || null,
      }))
    }

    form.value = cloned
  } else {
    isEditing.value = false
    editingId.value = null
    form.value = defaultForm()
  }
})

function close() {
  emit('update:visible', false)
}

function hasStemContent() {
  if (form.value.title.trim()) return true
  if (form.value.imageToken) return true
  return !!form.value.existingImageUrl && !form.value.removeImage
}

async function save() {
  if (!hasStemContent()) {
    toast.add({ severity: 'warn', summary: 'Question text required', detail: 'Please enter the question text, add an image, or both.', life: 4000 })
    return
  }

  const payload = {
    subject_id: form.value.subject_id,
    type: form.value.type,
    difficulty: form.value.difficulty,
    points: form.value.points,
    title: form.value.title,
    image_token: form.value.imageToken,
    remove_image: form.value.removeImage,
    image_alt: form.value.imageAlt || null,
    options: form.value.options.map(o => ({
      text: o.text,
      isCorrect: o.isCorrect,
      image_token: o.imageToken,
      remove_image: o.removeImage,
    })),
    tfCorrect: form.value.tfCorrect,
    matchingPairs: form.value.matchingPairs.map(p => ({
      leftText: p.leftText,
      leftImageToken: p.leftImageToken,
      removeLeftImage: p.removeLeftImage,
      rightText: p.rightText,
      rightImageToken: p.rightImageToken,
      removeRightImage: p.removeRightImage,
    })),
  }

  try {
    const response = isEditing.value
      ? await api.put(`/teacher/questions/${editingId.value}`, payload)
      : await api.post('/teacher/questions', payload)

    emit('saved', response.data.question)

    if (isEditing.value) {
      toast.add({ severity: 'success', summary: 'Question updated', detail: 'Your changes have been saved.', life: 3000 })
      close()
      return
    }

    toast.add({ severity: 'success', summary: 'Question added', detail: `"${form.value.title || 'Image question'}" was added.`, life: 3000 })

    // Creating: keep the modal open, remember the subject/type/difficulty,
    // and clear the rest so the teacher can add the next question immediately.
    sessionCount.value += 1
    sessionTitles.value.push(form.value.title || '(image question)')
    form.value = defaultForm(form.value)
  } catch (error) {
    toast.add({ summary: 'Failed to save question', ...toastFromError(error) })
  }
}
</script>
