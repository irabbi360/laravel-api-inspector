import { ref } from 'vue'

const menus = ref([])

export function useMenu() {
  menus.value = [
    { id: 'home', label: 'Home', icon: '🏠', href: '/api-docs' },
    { id: 'api-stats', label: 'API Stats', icon: '📚', href: '/api-docs/dashboard' },
    { id: 'settings', label: 'Settings', icon: '⚙️', href: '#/settings' }
  ]

  return {
    menus
  }
}
