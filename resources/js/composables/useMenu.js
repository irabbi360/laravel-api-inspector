import { ref } from 'vue'

const menus = ref([])
const apiInspector = ref(window.ApiInspector)

export function useMenu() {
  menus.value = [
    { id: 'home', label: 'Home', icon: '🏠', href: apiInspector.value.basePath },
    { id: 'api-stats', label: 'API Stats', icon: '📚', href: apiInspector.value.basePath +'/stats' },
    // { id: 'settings', label: 'Settings', icon: '⚙️', href: '#/settings' }
  ]

  return {
    menus
  }
}
