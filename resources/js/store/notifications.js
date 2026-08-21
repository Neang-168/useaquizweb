import { reactive } from 'vue'
import api from '../api'

// Shared reactive notification state, polled from the backend so quiz-published /
// quiz-starting-soon / quiz-ending-soon / feedback alerts show up as popups without
// a page reload. Import `notificationState` anywhere a live view is needed (bell icon,
// toast stack, the notifications page).
export const notificationState = reactive({
  items: [],
  unreadCount: 0,
  toasts: [],
})

let seenIds = new Set()
let initialized = false
let pollTimer = null

function pushToast(notification) {
  const toast = { ...notification, _key: `${notification.id}-${Date.now()}` }
  notificationState.toasts.push(toast)
  setTimeout(() => dismissToast(toast._key), 6000)
}

export function dismissToast(key) {
  const index = notificationState.toasts.findIndex((t) => t._key === key)
  if (index !== -1) notificationState.toasts.splice(index, 1)
}

export async function fetchNotifications() {
  try {
    const { data } = await api.get('/student/notifications')
    notificationState.items = data.data
    notificationState.unreadCount = data.unreadCount

    if (!initialized) {
      data.data.forEach((n) => seenIds.add(n.id))
      initialized = true
      return
    }

    data.data
      .filter((n) => !n.isRead && !seenIds.has(n.id))
      .forEach((n) => pushToast(n))

    data.data.forEach((n) => seenIds.add(n.id))
  } catch (error) {
    // Silent: polling failures shouldn't interrupt the UI.
  }
}

export function startPolling() {
  if (pollTimer) return
  fetchNotifications()
  pollTimer = setInterval(fetchNotifications, 20000)
}

export function stopPolling() {
  if (pollTimer) clearInterval(pollTimer)
  pollTimer = null
}

export async function markRead(id) {
  const item = notificationState.items.find((n) => n.id === id)
  if (item && !item.isRead) {
    item.isRead = true
    notificationState.unreadCount = Math.max(0, notificationState.unreadCount - 1)
  }
  try {
    await api.post(`/student/notifications/${id}/read`)
  } catch (error) {
    // Ignore — state will resync on next poll.
  }
}

export async function markAllRead() {
  notificationState.items.forEach((n) => { n.isRead = true })
  notificationState.unreadCount = 0
  try {
    await api.post('/student/notifications/read-all')
  } catch (error) {
    // Ignore — state will resync on next poll.
  }
}

export async function deleteNotification(id) {
  const item = notificationState.items.find((n) => n.id === id)
  notificationState.items = notificationState.items.filter((n) => n.id !== id)
  if (item && !item.isRead) {
    notificationState.unreadCount = Math.max(0, notificationState.unreadCount - 1)
  }
  seenIds.delete(id)
  try {
    await api.delete(`/student/notifications/${id}`)
  } catch (error) {
    // Ignore — state will resync on next poll.
  }
}

export async function clearAllNotifications() {
  notificationState.items = []
  notificationState.unreadCount = 0
  seenIds.clear()
  try {
    await api.delete('/student/notifications/clear-all')
  } catch (error) {
    // Ignore — state will resync on next poll.
  }
}
