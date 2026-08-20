import { ref } from 'vue'

// Set to true while a student has an active, timed quiz attempt open, so the
// sidebar can disable non-essential navigation and the router can block/warn
// on any attempt to leave (back button, address bar, etc.) — the timer is
// tracked server-side and keeps running even if they do leave, so wandering
// off mid-attempt just burns the clock rather than resetting it, but we still
// don't want it to happen by accident.
export const quizInProgress = ref(false)
