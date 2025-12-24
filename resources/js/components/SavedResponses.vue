<template>
  <div v-if="responses && responses.length > 0" class="section">
    <div class="section-title">Saved Responses History</div>
    <div class="saved-responses">
      <div
        v-for="(response, index) in responses"
        :key="index"
        class="saved-response-item"
        @click="$emit('view-response', response)"
      >
        <div>
          <strong>Response {{ responses.length - index }}</strong>
        </div>
        <div class="saved-response-time">
          {{ new Date(response.timestamp).toLocaleString() }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  responses: {
    type: Array,
    default: () => []
  }
})

defineEmits(['view-response'])
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

.saved-responses {
  margin-top: 20px;
}

.saved-response-item {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 15px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: background 0.2s;
}

.saved-response-item:hover {
  background: #fafafa;
}

.saved-response-time {
  color: #999;
  font-size: 0.85em;
  margin-top: 5px;
}
</style>
