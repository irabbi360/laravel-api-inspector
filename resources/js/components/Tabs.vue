<template>
  <div class="tabs-component">
    <div class="tab-header">
      <button
        v-for="(tab, index) in tabs"
        :key="index"
        class="tab"
        :class="{ active: modelValue === index }"
        @click="$emit('update:modelValue', index)"
      >
        {{ tab }}
      </button>
    </div>
    <div class="tab-body">
      <slot :activeTab="modelValue" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  tabs: {
    type: Array,
    required: true
  },
  modelValue: {
    type: Number,
    default: 0
  }
})

defineEmits(['update:modelValue'])
</script>

<style scoped>
.tabs-component {
  width: 100%;
}

.tab-header {
  display: flex;
  border-bottom: 2px solid #e0e0e0;
  gap: 0;
  background: #f9f9f9;
}

.tab {
  flex: 1;
  padding: 12px 16px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: #666;
  transition: all 0.3s ease;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
}

.tab:hover {
  color: #333;
  background: #f0f0f0;
}

.tab.active {
  color: #2c3e50;
  border-bottom-color: #42b983;
}

.tab-body {
  padding: 20px 0;
  min-height: 200px;
}
</style>
