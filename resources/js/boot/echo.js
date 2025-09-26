import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher
Pusher.logToConsole = true

const API_BASE = import.meta.env.VITE_API_BASE_URL.replace('/api', '')

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
  authorizer: (channel) => {
    return {
      authorize: (socketId, callback) => {
        axios.post(
          `${API_BASE}/broadcasting/auth`,
          {
            socket_id: socketId,
            channel_name: channel.name,
          },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`,
            },
          }
        )
        .then(response => callback(false, response.data))
        .catch(error => callback(true, error))
      },
    }
  },
})
