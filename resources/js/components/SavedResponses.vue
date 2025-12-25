<template>
  <div v-if="responses && responses.length > 0" class="section">
    <div class="section-title">Saved Responses History</div>
    <div class="saved-responses">
      <div
        v-for="(response, index) in responses"
        :key="index"
        class="saved-response-item"
      >
        <div class="saved-response-content" @click="$emit('view-response', response)">
          <div>
            <strong>Response {{ responses.length - index }}</strong>
          </div>
          <div class="saved-response-time">
            {{ new Date(response.timestamp).toLocaleString() }}
          </div>
        </div>
        <button
          class="delete-btn"
          @click="deleteSavedResponse(index)"
          title="Delete this response"
        >
          ✕
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useToast } from '../composables/useToast'

const props = defineProps({
  responses: {
    type: Array,
    default: () => []
  },
  route: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['view-response', 'delete-response'])
const { showToast } = useToast()

const deleteSavedResponse = async (index) => {
  if (!props.route) return

  try {
    const response = await fetch(
      `${window.location.origin}/api/api-inspector-docs/delete-response`,
      {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
          route_uri: props.route.uri,
          route_method: props.route.http_method,
          index: index
        })
      }
    )

    if (response.ok) {
      emit('delete-response', index)
      showToast('Response deleted successfully', 'success')
    } else {
      showToast('Failed to delete response', 'error')
    }
  } catch (error) {
    console.error('Error deleting response:', error)
    showToast('Error deleting response', 'error')
  }
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

.saved-responses {
  margin-top: 20px;
}

.saved-response-item {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 15px;
  margin-bottom: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: background 0.2s;
}

.saved-response-item:hover {
  background: #fafafa;
}

.saved-response-content {
  flex: 1;
  cursor: pointer;
}

.saved-response-time {
  color: #999;
  font-size: 0.85em;
  margin-top: 5px;
}

.delete-btn {
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 6px 12px;
  cursor: pointer;
  font-size: 16px;
  transition: background 0.2s;
  margin-left: 10px;
}

.delete-btn:hover {
  background: #dc2626;
}
</style>
