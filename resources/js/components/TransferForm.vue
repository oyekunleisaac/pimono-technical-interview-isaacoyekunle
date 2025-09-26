<template>
  <div>
    <h2 class="section-title">Send Money</h2>

    <form @submit.prevent="transfer" class="transfer-form">
      <div class="transfer-form-group">
        <label for="receiver-id" class="transfer-label">Receiver ID</label>
        <input
          id="receiver-id"
          v-model="receiverId"
          type="number"
          class="transfer-input"
          placeholder="Enter receiver's user ID"
          required
        />
      </div>

      <div class="transfer-form-group">
        <label for="amount" class="transfer-label">Amount</label>
        <input
          id="amount"
          v-model="amount"
          type="number"
          step="0.01"
          min="0.01"
          class="transfer-input"
          placeholder="Enter amount"
          required
        />
      </div>

      <button
        type="submit"
        :disabled="loading"
        :class="['transfer-btn', { loading: loading }]"
      >
        {{ loading ? 'Sending...' : 'Send Money' }}
      </button>

      <p v-if="error" class="error-message-transfer">{{ error }}</p>
      <p v-if="success" class="success-message">Transaction successful!</p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../boot/axios' 

const receiverId = ref('')
const amount = ref('')
const loading = ref(false)
const error = ref(null)
const success = ref(false)

const emit = defineEmits(['success'])

async function transfer() {
  error.value = null
  success.value = false
  loading.value = true

  try {
    const res = await api.post('/transactions', {
      receiver_id: receiverId.value,
      amount: amount.value,
    })

    success.value = true
    receiverId.value = ''
    amount.value = ''
    
    // Auto-hide success message after 3s
    setTimeout(() => {
      success.value = false
    }, 3000)
    
    emit('success', res.data)
  } catch (err) {
    error.value = err?.response?.data?.message || 'Transaction failed'
  } finally {
    loading.value = false
  }
}
</script>
