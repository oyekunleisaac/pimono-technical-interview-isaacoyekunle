import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher
Pusher.logToConsole = true

// Get API base URL from environment
const API_BASE = import.meta.env.VITE_API_BASE_URL.replace('/api', '')

// Function to get token from localStorage
function getToken() {
  return localStorage.getItem('token')
}

export const echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  wsHost: `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
  wsPort: 443,
  wssPort: 443,
  forceTLS: true,
  enabledTransports: ['ws', 'wss'],
  authEndpoint: `${API_BASE}/broadcasting/auth`,
  auth: {
    headers: {
      // Bearer token authorization for private channels
      Authorization: `Bearer ${getToken()}`,
    },
  },
})
