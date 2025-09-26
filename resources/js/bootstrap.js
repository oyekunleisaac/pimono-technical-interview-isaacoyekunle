import axios from 'axios'

window.axios = axios

// Always send AJAX header
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Automatically include Bearer token if available
window.axios.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})
