<template>
  <div class="main-container">
    <!-- If not logged in, show login form (centered) -->
    <div v-if="!token" class="w-full flex justify-center items-center min-h-screen">
      <div class="w-full max-w-md">
        <h1 class="text-4xl font-bold mb-8 text-center text-indigo-600">
          Mini Wallet
        </h1>
        <LoginForm @logged-in="handleLogin" />
      </div>
    </div>

    <!-- If logged in, show wallet UI -->
    <div v-else class="wallet-container">
      <h1 class="text-3xl font-bold mb-6 text-center text-indigo-600">
        Mini Wallet
      </h1>

      <div class="wallet-header">
        <p class="user-info">User ID: {{ userId }}</p>
        <p class="user-info">Balance: {{ balance !== null ? balance : '...' }}</p>
        <button @click="logout" class="logout-btn">Logout</button>
      </div>

      <!-- Transfer Form -->
      <div class="transfer-section">
        <TransferForm :user-id="userId" @success="handleTransactionSuccess" />
      </div>

      <!-- Transactions List -->
      <TransactionsList :user-id="userId" :transactions="transactions" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import LoginForm from './components/LoginForm.vue'
import TransferForm from './components/TransferForm.vue'
import TransactionsList from './components/TransactionsList.vue'
import { echo } from './boot/echo'

const token = ref(localStorage.getItem('token'))
// Ensure userId is always a string for props
const userId = ref(localStorage.getItem('user_id')?.toString() || '')
const balance = ref(null)
const transactions = ref([])

async function fetchInitialData() {
  if (!token.value) return
  try {
    const res = await axios.get('/transactions', {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    balance.value = res.data.balance
    transactions.value = res.data.transactions
  } catch (e) {
    console.error('Failed to fetch transactions', e)
  }
}

function handleLogin({ token: t, user }) {
  localStorage.setItem('token', t)
  localStorage.setItem('user_id', user.id.toString()) // store as string
  token.value = t
  userId.value = user.id.toString()
  fetchInitialData()
  subscribeToPusher()
}

async function logout() {
  try {
    const t = localStorage.getItem('token')
    if (t) {
      await axios.post('/logout', {}, { headers: { Authorization: `Bearer ${t}` } })
    }
  } catch (e) {
    console.error('Logout failed', e)
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user_id')
    token.value = null
    userId.value = ''
    balance.value = null
    transactions.value = []
  }
}

// Called when a transaction is made via the form
function handleTransactionSuccess(transaction) {
  // Already handled via Pusher, so no need to refresh
}

// Real-time subscription
function subscribeToPusher() {
  if (!userId.value) return
  echo.private(`transactions.${userId.value}`)
      .listen('TransactionCreated', (e) => {
          balance.value = e.balance
          // Prevent duplicate entries
          if (!transactions.value.find(t => t.id === e.transaction.id)) {
            transactions.value.unshift(e.transaction)
          }
      })
}

onMounted(() => {
  fetchInitialData()
  if (token.value && userId.value) {
    subscribeToPusher()
  }
})
</script>
