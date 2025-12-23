<template>
  <div v-if="requestRules && Object.keys(requestRules).length > 0" class="section">
    <div class="section-title">Request Body Parameters</div>
    <div class="form-fields-info">
      <div v-for="(field, name) in requestRules" :key="name" class="form-field-info">
        <div class="field-label-info">
          <span class="field-name-info">{{ name }}</span>
          <span
            :class="[
              field.required ? 'field-required-badge' : 'field-optional-badge'
            ]"
          >
            {{ field.required ? 'REQUIRED' : 'OPTIONAL' }}
          </span>
        </div>
        <div class="field-meta-info">
          <span class="field-type-info"
            >Type: <strong>{{ field.type || 'mixed' }}</strong></span
          >
          <span v-if="field.example" class="field-example-info"
            >Example: {{ field.example }}</span
          >
          <span v-if="field.description" class="field-desc-info">
            {{ field.description }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  requestRules: {
    type: Object,
    default: null
  }
})
</script>

<style scoped>
.section {
  margin-bottom: 40px;
}

.section-title {
  font-size: 1.1em;
  font-weight: 600;
  margin-bottom: 15px;
  color: #1e1e1e;
  border-bottom: 2px solid #0066cc;
  padding-bottom: 10px;
}

.form-fields-info {
  display: grid;
  gap: 12px;
}

.form-field-info {
  padding: 12px;
  background: #f5f5f5;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
}

.field-label-info {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 8px;
  font-weight: 600;
  font-size: 13px;
}

.field-name-info {
  color: #2196f3;
  font-family: monospace;
  font-weight: 700;
}

.field-required-badge {
  background: #ffebee;
  color: #c62828;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 11px;
  font-weight: 700;
}

.field-optional-badge {
  background: #e3f2fd;
  color: #1565c0;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 11px;
  font-weight: 700;
}

.field-meta-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 12px;
  color: #666;
}

.field-type-info {
  display: block;
}

.field-example-info {
  display: block;
  font-family: monospace;
  background: #fff;
  padding: 4px 6px;
  border-radius: 2px;
}

.field-desc-info {
  display: block;
  font-style: italic;
  color: #888;
}
</style>
