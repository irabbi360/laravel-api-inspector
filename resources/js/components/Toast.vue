<template>
  <Teleport to="body">
    <div v-if="visible" class="toast" :class="type">
      <div class="toast-content">
        <span class="toast-icon">{{ typeIcon }}</span>
        <span>{{ message }}</span>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error', 'info'].includes(value)
  },
  duration: {
    type: Number,
    default: 3000
  },
  visible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const typeIcon = computed(() => {
  const icons = {
    success: '✓',
    error: '✕',
    info: 'ℹ'
  }
  return icons[props.type] || '✓'
})

watch(
  () => props.visible,
  (newVal) => {
    if (newVal) {
      setTimeout(() => {
        emit('close')
      }, props.duration)
    }
  }
)
</script>

<style scoped>
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 12px 20px;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 9999;
  animation: slideIn 0.3s ease-out;
  font-size: 14px;
  max-width: 300px;
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.toast-icon {
  font-weight: bold;
  font-size: 16px;
}

.toast.success {
  background-color: #10b981;
  color: white;
  border-left: 4px solid #059669;
}

.toast.error {
  background-color: #ef4444;
  color: white;
  border-left: 4px solid #dc2626;
}

.toast.info {
  background-color: #3b82f6;
  color: white;
  border-left: 4px solid #1d4ed8;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>
