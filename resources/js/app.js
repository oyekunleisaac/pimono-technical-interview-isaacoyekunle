// resources/js/app.js
import '../css/app.css'
import { createApp, reactive } from 'vue'
import App from './App.vue'
import api from './boot/axios'
import './boot/echo'            
import './bootstrap'           

// Global reactive auth state
const authState = reactive({
  token: localStorage.getItem('token'),
  user: JSON.parse(localStorage.getItem('user')) || null,
})

// Login/Logout helpers
function handleLogin({ token, user }) {
  localStorage.setItem('token', token)
  localStorage.setItem('user', JSON.stringify(user))
  authState.token = token
  authState.user = user
}

async function handleLogout() {
  try {
    if (authState.token) {
      await api.post('/logout')  
    }
  } catch (err) {
    console.error('Logout failed', err)
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    authState.token = null
    authState.user = null
  }
}

// Mount Vue app
const app = createApp(App)

// Provide global properties
app.config.globalProperties.$api = api        
app.config.globalProperties.$auth = authState
app.config.globalProperties.$login = handleLogin
app.config.globalProperties.$logout = handleLogout

app.mount('#app')
