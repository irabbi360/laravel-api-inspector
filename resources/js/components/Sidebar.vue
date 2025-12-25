<template>
  <div class="sidebar">
    <div v-for="(routes, group) in groupedRoutes" :key="group" class="sidebar-group">
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
import { computed } from 'vue'

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

defineEmits(['select-endpoint'])

const isActive = (route) => {
  if (!props.selectedRoute) return false
  return (
    props.selectedRoute.http_method === route.http_method &&
    props.selectedRoute.uri === route.uri
  )
}
</script>

<style scoped>
.sidebar {
  width: 320px;
  background: #252526;
  border-right: 1px solid #3e3e42;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 20px 0;
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
