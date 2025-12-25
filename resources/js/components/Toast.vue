<template>
  <Teleport to="body">
    <div class="toasts-container">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast"
        :class="toast.type"
      >
        <div class="toast-content">
          <span class="toast-icon">{{ getIcon(toast.type) }}</span>
          <span>{{ toast.message }}</span>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { useToast } from '../composables/useToast'

const { toasts } = useToast()

const getIcon = (type) => {
  const icons = {
    success: '✓',
    error: '✕',
    info: 'ℹ'
  }
  return icons[type] || '✓'
}
</script>

<style scoped>
.toasts-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
}

.toast {
  padding: 12px 20px;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  animation: slideIn 0.3s ease-out;
  font-size: 14px;
  max-width: 300px;
  pointer-events: auto;
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
