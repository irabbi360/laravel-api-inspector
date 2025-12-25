<template>
  <div v-if="response" class="section">
    <div class="section-title">Response</div>
    <div class="response-section">
      <div class="response-status" :class="response.status < 400 ? 'success' : 'error'">
        Status: {{ response.status }} {{ getStatusText(response.status) }}
      </div>
      <div class="response-code">{{ formatResponse(response.data) }}</div>
      <button
        v-if="!response?.is_saved"
        class="btn"
        style="margin-top: 15px"
        @click="$emit('save-response')"
      >
        Save Response
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  response: {
    type: Object,
    default: null
  }
})

defineEmits(['save-response'])

const getStatusText = (status) => {
  const statusTexts = {
    200: 'OK',
    201: 'Created',
    204: 'No Content',
    400: 'Bad Request',
    401: 'Unauthorized',
    403: 'Forbidden',
    404: 'Not Found',
    500: 'Internal Server Error'
  }
  return statusTexts[status] || 'Unknown'
}

const formatResponse = (data) => {
  if (typeof data === 'string') {
    try {
      return JSON.stringify(JSON.parse(data), null, 2)
    } catch {
      return data
    }
  }
  return JSON.stringify(data, null, 2)
}
</script>

<style scoped>
.section {
  margin-bottom: 40px;
}

.section-title {
  font-size: 1.1em;
  font-weight: 600;
  margin-bottom: 15px;
  color: #1e1e1e;
  border-bottom: 2px solid #0066cc;
  padding-bottom: 10px;
}

.response-section {
  background: #f5f5f5;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 15px;
  margin-top: 20px;
}

.response-status {
  font-weight: 600;
  margin-bottom: 15px;
}

.response-status.success {
  color: #27ae60;
}

.response-status.error {
  color: #e74c3c;
}

.response-code {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 15px;
  overflow-x: auto;
  font-family: 'Courier New', monospace;
  font-size: 0.85em;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.btn {
  background: #0066cc;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}

.btn:hover {
  background: #0052a3;
}
</style>
