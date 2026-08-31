import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: {
    Accept: 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      localStorage.removeItem('auth_role')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Turns a Laravel error response (validation 422, or any other failure)
// into a single readable string for display to the user.
export function extractError(error) {
  const data = error?.response?.data
  if (data?.errors) {
    return Object.values(data.errors).flat().join('\n')
  }
  return data?.message || error?.message || 'Something went wrong. Please try again.'
}

// Shared shape for toast.add() on a failed API call — validation errors (422)
// surface as a 'warn' toast with the field messages, everything else as an
// 'error' toast with the server message or a generic fallback.
export function toastFromError(error) {
  const isValidation = error?.response?.status === 422
  return {
    severity: isValidation ? 'warn' : 'error',
    detail: extractError(error),
    life: isValidation ? 4000 : 5000,
  }
}

export default api
