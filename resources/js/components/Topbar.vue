<template>
  <div class="topbar">
    <div class="topbar-content">
      <div class="api-info">
        <div class="api-title">{{ apiData.title }}</div>
        <div v-if="apiData.version" class="api-version">
          v<span class="version-badge">{{ apiData.version }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <div v-if="loading" class="loading-spinner"></div>
        <button v-else class="btn btn-sm" @click="$emit('refresh')">
          Refresh
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  apiData: {
    type: Object,
    default: () => ({ title: 'API Inspector', version: '1.0.0' })
  },
  loading: {
    type: Boolean,
    default: false
  }
})

defineEmits(['refresh'])
</script>

<style scoped>
.topbar {
  background: #1e1e1e;
  padding: 20px;
  border-bottom: 1px solid #3e3e42;
  position: sticky;
  top: 0;
  z-index: 100;
}

.topbar-content {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.api-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.api-title {
  color: #fff;
  font-size: 1.5em;
  font-weight: 500;
}

.api-version {
  color: #999;
  font-size: 0.9em;
}

.version-badge {
  background: #0066cc;
  color: white;
  padding: 3px 8px;
  border-radius: 3px;
  font-size: 0.75em;
  margin-left: 10px;
}

.topbar-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}
</style>
