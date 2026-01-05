<template>
  <div class="sidebar">
    <div class="sidebar-search">
      <div class="search-input-group">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          class="search-input"
          placeholder="Search endpoints..."
          @keydown.escape="searchQuery = ''"
        />
        <button
          v-if="searchQuery"
          class="clear-button"
          @click="searchQuery = ''"
          title="Clear search"
        >
          ✕
        </button>
      </div>
      <div v-if="searchQuery && Object.keys(filteredRoutes).length === 0" class="no-results">
        No endpoints found
      </div>
    </div>

    <div v-for="(routes, group) in filteredRoutes" :key="group" class="sidebar-group">
      <div class="sidebar-group-title">{{ group }}</div>
      <div
        v-for="route in routes"
        :key="`${route.http_method}-${route.uri}`"
        :class="['sidebar-route', { active: isActive(route) }]"
        @click="$emit('select-endpoint', route)"
      >
        <span :class="['route-method-badge', route.http_method.toLowerCase()]">
          {{ route.http_method }}
        </span>
        <span class="route-path" :title="route.uri">{{ route.uri }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  groupedRoutes: {
    type: Object,
    default: () => ({})
  },
  selectedRoute: {
    type: Object,
    default: null
  }
})

const searchQuery = ref('')

defineEmits(['select-endpoint'])

const isActive = (route) => {
  if (!props.selectedRoute) return false
  return (
    props.selectedRoute.http_method === route.http_method &&
    props.selectedRoute.uri === route.uri
  )
}

const filteredRoutes = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.groupedRoutes
  }

  const query = searchQuery.value.toLowerCase().trim()
  const filtered = {}

  Object.entries(props.groupedRoutes).forEach(([group, routes]) => {
    const matchedRoutes = routes.filter((route) => {
      const uriMatch = route.uri.toLowerCase().includes(query)
      const methodMatch = route.http_method.toLowerCase().includes(query)
      const groupMatch = group.toLowerCase().includes(query)

      return uriMatch || methodMatch || groupMatch
    })

    if (matchedRoutes.length > 0) {
      filtered[group] = matchedRoutes
    }
  })

  return filtered
})
</script>

<style scoped>
.sidebar {
  width: 320px;
  background: #252526;
  border-right: 1px solid #3e3e42;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 0 0 20px;
  flex-shrink: 0;
  max-height: 100%;
}

.sidebar::-webkit-scrollbar {
  width: 8px;
}

.sidebar::-webkit-scrollbar-track {
  background: #252526;
}

.sidebar::-webkit-scrollbar-thumb {
  background: #555;
  border-radius: 4px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
  background: #666;
}

.sidebar-search {
  padding: 15px 15px;
  border-bottom: 1px solid #3e3e42;
  flex-shrink: 0;
  position: sticky;
  top: 0;
  background: #252526;
  z-index: 10;
}

.search-input-group {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
}

.search-icon {
  width: 18px;
  height: 18px;
  color: #999;
  flex-shrink: 0;
  pointer-events: none;
  position: absolute;
  left: 10px;
}

.search-input {
  width: 100%;
  padding: 8px 8px 8px 36px;
  background: #2a2a2a;
  border: 1px solid #3e3e42;
  border-radius: 4px;
  color: #ccc;
  font-size: 13px;
  font-family: inherit;
  transition: border-color 0.2s, background 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #0066cc;
  background: #333;
}

.search-input::placeholder {
  color: #666;
}

.clear-button {
  position: absolute;
  right: 8px;
  background: none;
  border: none;
  color: #999;
  cursor: pointer;
  padding: 4px 6px;
  font-size: 14px;
  transition: color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 2px;
}

.clear-button:hover {
  color: #ccc;
  background: #3e3e42;
}

.no-results {
  color: #999;
  font-size: 12px;
  padding: 10px;
  text-align: center;
  margin-top: 8px;
}

.sidebar > div:not(.sidebar-search) {
  overflow-y: auto;
}

.sidebar-group {
  margin-bottom: 0;
}

.sidebar-group-title {
  color: #999;
  font-size: 0.85em;
  font-weight: 600;
  padding: 10px 20px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.sidebar-route {
  padding: 10px 20px;
  cursor: pointer;
  border-left: 3px solid transparent;
  transition: background 0.2s, border-color 0.2s;
  display: flex;
  align-items: center;
  gap: 10px;
  color: #ccc;
}

.sidebar-route:hover {
  background: #333;
}

.sidebar-route.active {
  background: #0066cc;
  border-left-color: #0066cc;
  color: white;
}

.route-method-badge {
  font-size: 0.7em;
  font-weight: 600;
  padding: 3px 6px;
  border-radius: 3px;
  min-width: 40px;
  text-align: center;
  text-transform: uppercase;
}

.route-method-badge.get {
  background: #61affe;
  color: white;
}

.route-method-badge.post {
  background: #49cc90;
  color: white;
}

.route-method-badge.put {
  background: #fca130;
  color: white;
}

.route-method-badge.delete {
  background: #f93e3e;
  color: white;
}

.route-method-badge.patch {
  background: #50e3c2;
  color: white;
}

.route-path {
  font-family: 'Courier New', monospace;
  font-size: 0.85em;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
