<template>
  <div class="detail-header">
    <div class="detail-method-path">
      <span :class="['detail-method-badge', route.method.toLowerCase()]">
        {{ route.method }}
      </span>
      <span class="detail-path">{{ route.uri }}</span>
      <span v-if="route.auth" class="auth-badge">Requires Auth</span>
    </div>
    <div v-if="route.description" class="detail-description">
      {{ route.description }}
    </div>
  </div>
</template>

<script setup>
defineProps({
  route: {
    type: Object,
    required: true
  }
})
</script>

<style scoped>
.detail-header {
  padding: 30px;
  background: white;
  border-bottom: 1px solid #e0e0e0;
  flex-shrink: 0;
}

.detail-method-path {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 15px;
}

.detail-method-badge {
  font-size: 0.85em;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 3px;
  text-transform: uppercase;
  min-width: 50px;
  text-align: center;
}

.detail-method-badge.get {
  background: #61affe;
  color: white;
}

.detail-method-badge.post {
  background: #49cc90;
  color: white;
}

.detail-method-badge.put {
  background: #fca130;
  color: white;
}

.detail-method-badge.delete {
  background: #f93e3e;
  color: white;
}

.detail-method-badge.patch {
  background: #50e3c2;
  color: white;
}

.detail-path {
  font-family: 'Courier New', monospace;
  font-size: 1.1em;
  font-weight: 600;
  color: #3e3e42;
}

.detail-description {
  color: #666;
  font-size: 0.95em;
  margin-bottom: 10px;
}

.auth-badge {
  display: inline-block;
  background: #fff3cd;
  color: #856404;
  padding: 4px 8px;
  border-radius: 3px;
  font-size: 0.8em;
  border: 1px solid #ffc107;
}
</style>
