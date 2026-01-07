<template>
  <div class="detail-header flex justify-between">
    <div class="detail-description">
      <span class="fs-20 font-bold me-2">{{ route.description }}</span>
      <span v-if="route.requires_auth" class="auth-badge">Requires Auth</span>
    </div>
    <div class="detail-method-path">
      <span :class="['detail-method-badge', route.http_method.toLowerCase()]">{{ route.http_method }}</span>
      <span class="detail-path text-truncate">{{ route.uri }}</span>
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
  padding: 15px 15px 10px;
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
  padding: 1px 5px;
  border-radius: 5px;
  font-size: .8em;
  border: 1px solid #ffc107;
}
</style>
