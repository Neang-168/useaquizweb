import { reactive } from 'vue'

function readAuthUser() {
  try {
    return JSON.parse(localStorage.getItem('auth_user')) || {}
  } catch (e) {
    return {}
  }
}

// Shared reactive copy of the logged-in user, kept in sync with localStorage.
// Import `authUser` anywhere a live view of the current user is needed (e.g. sidebars),
// and call setAuthUser() whenever the profile is updated so those views refresh immediately.
export const authUser = reactive(readAuthUser())

export function setAuthUser(partial) {
  Object.assign(authUser, partial)
  localStorage.setItem('auth_user', JSON.stringify({ ...authUser }))
}

export function clearAuthUser() {
  Object.keys(authUser).forEach((key) => delete authUser[key])
  localStorage.removeItem('auth_user')
}
