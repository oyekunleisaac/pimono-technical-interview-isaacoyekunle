<template>
  <div class="transactions-section">
    <h2 class="transactions-title">Transaction History</h2>

    <div v-if="loading && !transactions.length" class="loading-state">
      Loading transactions...
    </div>
    
    <div v-else-if="transactions.length === 0" class="empty-state">
      <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
      </svg>
      <p>No transactions found.</p>
      <p class="text-sm mt-2">Your transaction history will appear here.</p>
    </div>
    
    <div v-else class="transactions-list">
      <div 
        v-for="transaction in transactions" 
        :key="transaction.id" 
        class="transaction-item"
      >
        <div class="transaction-details">
          <span class="font-medium">{{ transaction.sender.name }}</span>
          <span class="mx-2 text-gray-400">→</span>
          <span class="font-medium">{{ transaction.receiver.name }}</span>
        </div>
        
        <div 
          :class="[
            'transaction-amount',
            transaction.sender.id == userId ? 'amount-sent' : 'amount-received'
          ]"
        >
          {{ transaction.sender.id == userId ? '-' : '+' }}${{ transaction.amount }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  userId: { type: String, required: true },
  refresh: { type: Number, default: 0 }
})
const emit = defineEmits(['balance'])

const transactions = ref([])
const loading = ref(false)
let silentInterval = null
let pusherChannel = null

async function fetchTransactions() {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('/transactions', {
      headers: { Authorization: `Bearer ${token}` }
    })
    transactions.value = res.data.transactions
    emit('balance', res.data.balance) 
  } catch (err) {
    console.error('Failed to fetch transactions', err)
  }
}

// Setup Pusher with reconnect fallback
function setupPusher() {
  if (!props.userId) return

  try {
    pusherChannel = echo.private(`transactions.${props.userId}`)
      .listen('TransactionCreated', (e) => {
        const newTx = e.transaction
        if (!transactions.value.find(t => t.id === newTx.id)) {
          transactions.value.unshift(newTx)
        }
        emit('balance', e.balance) 
    })
  } catch (err) {
    console.warn('Pusher setup failed. Using silent reload.', err)
    fetchTransactions()
  }
}

// Silent polling as fallback
function startSilentReload() {
  silentInterval = setInterval(fetchTransactions, 1000)
}

function stopSilentReload() {
  if (silentInterval) clearInterval(silentInterval)
}

onMounted(() => {
  fetchTransactions()
  setupPusher()
  startSilentReload()
})

onUnmounted(() => {
  stopSilentReload()
  if (pusherChannel) pusherChannel.stopListening('TransactionCreated')
})

watch(() => props.refresh, fetchTransactions)
</script>