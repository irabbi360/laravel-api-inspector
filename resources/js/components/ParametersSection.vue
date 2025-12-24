<template>
  <div v-if="parameters && Object.keys(parameters).length > 0" class="section">
    <div class="section-title">Parameters</div>
    <div class="expandable">
      <div class="expandable-header" @click="expanded = !expanded">
        <span>Route Parameters</span>
        <span class="toggle-icon">▼</span>
      </div>
      <div v-if="expanded" class="expandable-content">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Type</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(param, name) in parameters" :key="name">
              <td><span class="param-name">{{ name }}</span></td>
              <td><span class="param-type">{{ param.type || 'string' }}</span></td>
              <td>@{{ param.description || '' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  parameters: {
    type: Object,
    default: null
  }
})

const expanded = ref(true)
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

.expandable {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  overflow: hidden;
}

.expandable-header {
  background: #f5f5f5;
  padding: 15px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  user-select: none;
}

.expandable-header:hover {
  background: #efefef;
}

.toggle-icon {
  transition: transform 0.2s;
  color: #999;
}

.expandable-content {
  padding: 15px;
  background: white;
  border-top: 1px solid #e0e0e0;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9em;
}

thead {
  background: #fafafa;
}

th {
  text-align: left;
  padding: 12px;
  font-weight: 600;
  color: #666;
  border-bottom: 1px solid #e0e0e0;
}

td {
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
}

tr:hover {
  background: #fafafa;
}

.param-name {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: #0066cc;
}

.param-type {
  font-family: 'Courier New', monospace;
  color: #666;
  background: #f5f5f5;
  padding: 2px 6px;
  border-radius: 3px;
}
</style>
