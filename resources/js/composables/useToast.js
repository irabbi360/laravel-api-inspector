import { ref } from 'vue'

let toastId = 0
const toasts = ref([])

export function useToast() {
  const showToast = (message, type = 'success', duration = 3000) => {
    console.log('toast alert');
    
    const id = toastId++
    const toast = {
      id,
      message,
      type,
      duration,
      visible: true
    }

    toasts.value.push(toast)

    // Auto remove toast after duration
    setTimeout(() => {
      removeToast(id)
    }, duration)

    return id
  }

  const removeToast = (id) => {
    const index = toasts.value.findIndex((t) => t.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  return {
    toasts,
    showToast,
    removeToast
  }
}
