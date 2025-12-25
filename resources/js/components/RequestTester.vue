<template>
  <div class="section">
    <div class="section-title">Send Request</div>
    <div class="request-tester">
      <div v-if="extractedPathParams && Object.keys(extractedPathParams).length > 0" class="form-group">
        <label><strong>Path Parameters</strong></label>
        <div class="form-fields-container">
          <div v-for="(value, name) in extractedPathParams" :key="name" class="form-group">
            <label>{{ name }}</label>
            <input
              v-model="pathParams[name]"
              type="text"
              :placeholder="`Enter ${name}`"
              @input="$emit('update:pathParams', pathParams)"
            />
          </div>
        </div>
      </div>

      <div v-if="hasRequestRules" class="tester-tabs">
        <button
          :class="['tab-btn', { active: useJsonEditor }]"
          @click="useJsonEditor = true"
        >
          JSON
        </button>
        <button
          v-if="hasRequestRules"
          :class="['tab-btn', { active: !useJsonEditor }]"
          @click="useJsonEditor = false"
        >
          Form
        </button>
      </div>

      <div v-if="useJsonEditor && hasRequestRules" class="json-editor-container">
        <label><strong>Request Body (JSON)</strong></label>
        <textarea
          v-model="jsonBody"
          class="json-editor"
          rows="8"
          placeholder="{}"
          @input="updateJsonBody($event.target.value)"
        ></textarea>
      </div>

      <div v-else class="form-fields-container">
        <div v-for="(field, name) in requestRules" :key="name" class="form-field-group mb-0">
          <div class="field-label justify-between">
            <div>
              <span class="field-name me-2">{{ name }}</span>
              <span v-if="field.required" class="field-required">REQUIRED</span>
              <span v-else class="field-optional">(optional)</span>
            </div>
            <div>
              <span v-if="field.type" class="field-type">Type: {{ field.type }}</span>
            </div>
          </div>
          <div class="field-meta-info flex">
            <div v-if="field.description" class="field-description">
              {{ field.description }}
            </div>
            <input
              v-model="formData[name]"
              :type="getInputType(name)"
              :placeholder="`Enter ${name}`"
              class="field-input"
              @input="updateRequestBody"
            />
          </div>
  
        </div>
      </div>

      <button
        class="btn"
        :disabled="sending"
        @click="$emit('send-request')"
      >
        {{ sending ? 'Sending...' : 'Send Request' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  requestBody: {
    type: String,
    default: '{}'
  },
  requestRules: {
    type: Object,
    default: null
  },
  sending: {
    type: Boolean,
    default: false
  },
  route: {
    type: Object,
    default: null
  },
  pathParams: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:requestBody', 'send-request', 'update:pathParams'])

const useJsonEditor = ref(true)
const formData = ref({})
const pathParams = ref(props.pathParams)
const jsonBody = ref('{}')

const hasRequestRules = computed(() => {
  return props.requestRules && Object.keys(props.requestRules).length > 0
})

const extractedPathParams = computed(() => {
  if (!props.route || !props.route.uri) return {}
  const matches = props.route.uri.match(/{([^}]+)}/g)
  if (!matches) return {}
  const params = {}
  matches.forEach((match) => {
    const paramName = match.replace(/{|}/g, '')
    params[paramName] = props.pathParams[paramName] || ''
  })
  return params
})

const getInputType = (field) => {
  if (!props.requestRules || !props.requestRules[field]) return 'text'
  const type = props.requestRules[field].type || 'string'
  if (type === 'integer' || type === 'number') return 'number'
  if (type === 'boolean') return 'checkbox'
  return 'text'
}

const updateRequestBody = () => {
  const body = {}
  Object.keys(formData.value).forEach((key) => {
    body[key] = formData.value[key]
  })
  jsonBody.value = JSON.stringify(body, null, 2)
  emit('update:requestBody', jsonBody.value)
}

const updateJsonBody = (value) => {
  jsonBody.value = value
  emit('update:requestBody', value)
}

// Initialize formData from requestRules
watch(
  () => props.requestRules,
  (newRules) => {
    if (newRules) {
      const newFormData = {}
      Object.keys(newRules).forEach((key) => {
        newFormData[key] = newRules[key].example || ''
      })
      formData.value = newFormData
    }
  },
  { immediate: true }
)

// Sync jsonBody with prop
watch(
  () => props.requestBody,
  (newBody) => {
    jsonBody.value = newBody || '{}'
  },
  { immediate: true }
)
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

.request-tester {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 20px;
}

.tester-tabs {
  display: flex;
  gap: 0;
  border-bottom: 2px solid #e0e0e0;
  margin-bottom: 20px;
}

.tab-btn {
  flex: 1;
  padding: 12px 16px;
  border: none;
  background: #f5f5f5;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  color: #666;
  transition: all 0.3s ease;
  border-bottom: 3px solid transparent;
}

.tab-btn:hover {
  background: #e8e8e8;
}

.tab-btn.active {
  background: #fff;
  color: #333;
  border-bottom-color: #2196f3;
}

.form-fields-container {
  display: grid;
  gap: 15px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #3e3e42;
}

.form-field-group {
  margin-bottom: 20px;
  padding: 10px 10px 0;
  background: #f9f9f9;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
}

.field-label {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
  font-weight: 600;
  color: #3e3e42;
}

.field-name {
  font-family: 'Courier New', monospace;
  color: #0066cc;
  font-weight: 700;
}

.field-required {
  display: inline-block;
  background: #ffebee;
  color: #c62828;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 0.75em;
  font-weight: 600;
  border: 1px solid #ef5350;
}

.field-optional {
  display: inline-block;
  color: #999;
  font-size: 0.85em;
  font-weight: 500;
}

.field-input {
  width: 100%;
  padding: 10px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-size: 0.9em;
  margin-bottom: 8px;
}

.field-input:focus {
  outline: none;
  border-color: #0066cc;
  box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.field-meta-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 12px;
  color: #666;
  margin-bottom: 8px;
}

.field-type {
  display: block;
  color: #999;
  font-size: 0.85em;
}

.field-description {
  display: block;
  color: #666;
  font-size: 0.85em;
}

.json-editor-container {
  margin-bottom: 20px;
}

.json-editor-container label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
}

.json-editor {
  width: 100%;
  padding: 10px;
  font-family: monospace;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 13px;
}

.json-editor:focus {
  outline: none;
  border-color: #2196f3;
  box-shadow: 0 0 4px rgba(33, 150, 243, 0.2);
}

.btn {
  background: #0066cc;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}

.btn:hover:not(:disabled) {
  background: #0052a3;
}

.btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}
</style>
