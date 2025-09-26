<template>
  <form @submit.prevent="login" class="login-form">
    <h2 class="form-title">Pimono Mini Wallet Login</h2>
    
    <div class="form-content">
      <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input
          id="email"
          v-model="email"
          type="email"
          class="form-input"
          placeholder="Enter your email"
          required
          autocomplete="email"
        />
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input
          id="password"
          v-model="password"
          type="password"
          class="form-input"
          placeholder="Enter your password"
          required
          autocomplete="current-password"
        />
      </div>

      <button
        :disabled="loading"
        :class="['submit-btn', { loading: loading }]"
        type="submit"
      >
        {{ loading ? 'Logging in...' : 'Login' }}
      </button>

      <p v-if="error" class="error-message">{{ error }}</p>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import api from '../boot/axios'

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref(null)

// Emit event to parent (App.vue)
const emit = defineEmits(['logged-in'])

async function login() {
  error.value = null
  loading.value = true

  try {
    const res = await api.post('/login', {
      email: email.value,
      password: password.value,
    })

    // Save token in localStorage for Bearer auth
    localStorage.setItem('token', res.data.token)
    localStorage.setItem('user_id', res.data.user.id)

    // Ensure userId is a string to avoid Vue prop warnings
    const user = { ...res.data.user, id: res.data.user.id.toString() }

    // Emit token + user object to App.vue
    emit('logged-in', { token: res.data.token, user })
  } catch (err) {
    error.value = err?.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>
