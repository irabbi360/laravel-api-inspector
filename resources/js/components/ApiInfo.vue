<template>
  <div class="api-info-container">
    <!-- Route Information Section -->
    <div v-if="apiInfo" class="info-section">
      <div class="info-group">
        <div class="info-label">Method</div>
        <div class="info-value">
          <span :class="['method-badge', `method-${(apiInfo.http_method || 'GET').toLowerCase()}`]">
            {{ apiInfo.http_method }}
          </span>
        </div>
      </div>

      <div class="info-group">
        <div class="info-label">Controller</div>
        <div class="info-value">{{ apiInfo.controller || '-' }}</div>
      </div>

      <div class="info-group">
        <div class="info-label">Function</div>
        <div class="info-value">{{ apiInfo.method || '-' }}</div>
      </div>

      <div v-if="apiInfo.middleware && apiInfo.middleware.length > 0" class="info-group">
        <div class="info-label">Middlewares</div>
        <div class="info-value middlewares-value">
          <span v-for="(mw, index) in apiInfo.middleware" :key="index" class="middleware-badge">
            {{ mw }}
          </span>
        </div>
      </div>

      <div class="info-group">
        <div class="info-label">Status Codes</div>
        <div class="info-link">
          <div>
            <span :class="['arrow', { expanded: expandedSections.statusCodes }]">›</span>
            <span @click="toggleSection('statusCodes')">{{ expandedSections.statusCodes ? 'Hide' : 'Show' }} Response codes for this request</span>
            <div v-show="expandedSections.statusCodes" class="collapsible-content">
              <div class="response-codes-list">
                <div v-for="code in apiInfo.responses || []" :key="code" :class="['response', `response-${code}`]">
                  - {{ code }} &nbsp; {{ getStatusText(code) }}
                </div>
              </div>
              <div v-if="!apiInfo.responses || apiInfo.responses.length === 0" class="empty-content">
                No response codes defined
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="info-group">
        <div class="info-label">Curl</div>
        <div class="info-link">
          <div>
            <span :class="['arrow', { expanded: expandedSections.curl }]">›</span>
            <span @click="toggleSection('curl')">{{ expandedSections.curl ? 'Hide' : 'Show' }} curl command</span>
            <div v-show="expandedSections.curl" class="collapsible-content">
              <div class="curl-command">
                <div>
                  <textarea readonly rows="6">{{ generateCurlCommand }}</textarea>
                </div>
                <button class="copy-btn" @click="copyToClipboard(generateCurlCommand)">Copy</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
const { showToast } = useToast()

const props = defineProps({
  apiInfo: {
    type: Object,
    default: null
  },
  requestRules: {
    type: Object,
    default: null
  }
})

const toastVisible = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

const expandedSections = ref({
  statusCodes: false,
  curl: false
})

const toggleSection = (section) => {
  expandedSections.value[section] = !expandedSections.value[section]
}

const generateCurlCommand = computed(() => {
  if (!props.apiInfo) return ''
  
  const method = props.apiInfo.http_method || 'GET'
  const uri = props.apiInfo.uri || '/'
  const baseUrl = window.location.origin
  const fullUrl = `${baseUrl}${uri}`
  
  let curl = `curl \\\n -X ${method} \\\n`
  
  // Add headers
  curl += ` -H "Content-Type: application/json" \\\n`
  curl += ` -H "Accept: application/json" \\\n`
  
  // Add URL
  curl += ` ${fullUrl}`
  
  // Add request body for methods that typically have a body
  const methodsWithBody = ['POST', 'PUT', 'PATCH']
  if (methodsWithBody.includes(method.toUpperCase())) {
    curl += ` \\\n -d '{\n`
    
    // Generate sample request body from request rules if available
    if (props.requestRules && Object.keys(props.requestRules).length > 0) {
      const fields = Object.keys(props.requestRules)
      fields.forEach((field, index) => {
        curl += `  "${field}": ""`
        if (index < fields.length - 1) {
          curl += `,\n`
        } else {
          curl += `\n`
        }
      })
    }
    
    curl += `}'`
  } else {
    // For GET and other methods, just add a newline at the end
    curl += ` \\\n`
  }
  
  return curl
})

const copyToClipboard = (curlCode) => {
  // Try modern Clipboard API first
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(curlCode).then(() => {
      showToast('Curl command copied to clipboard!', 'success')
    }).catch((err) => {
      showToast('Failed to copy curl command', 'error')
      toastVisible.value = true
      console.error('Clipboard API error:', err)
      fallbackCopyToClipboard(curlCode)
    })
  } else {
    // Fallback for older browsers or non-secure contexts
    fallbackCopyToClipboard(curlCode)
  }
}

const statusCodeMap = {
  200: 'OK',
  201: 'Created',
  204: 'No Content',
  400: 'Bad Request',
  401: 'Unauthorized',
  403: 'Forbidden',
  404: 'Not Found',
  405: 'Method Not Allowed',
  409: 'Conflict',
  422: 'Unprocessable Entity',
  429: 'Too Many Requests',
  500: 'Internal Server Error',
  502: 'Bad Gateway',
  503: 'Service Unavailable'
}

const getStatusText = (code) => {
  return statusCodeMap[code] || 'Unknown Status'
}

const fallbackCopyToClipboard = (text) => {
  try {
    // Create a temporary textarea element
    const textarea = document.createElement('textarea')
    textarea.value = text
    textarea.style.position = 'fixed'
    textarea.style.opacity = '0'
    textarea.style.pointerEvents = 'none'
    document.body.appendChild(textarea)
    
    // Select and copy the text
    textarea.select()
    textarea.setSelectionRange(0, 99999) // For mobile devices
    
    const successful = document.execCommand('copy')
    
    if (successful) {
      showToast('Curl command copied to clipboard!', 'success')
    } else {
      showToast('Failed to copy curl command', 'error')
      toastVisible.value = true
    }
    
    // Remove the temporary element
    document.body.removeChild(textarea)
  } catch (err) {
    console.error('Fallback copy error:', err)
    
    showToast('Could not copy curl command. Please try again.', 'error')
  }
}
</script>

<style scoped>
.api-info-container {
  display: flex;
  flex-direction: column;
  gap: 40px;
}

/* Route Information Section */
.info-section {
  background: #fff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  padding: 28px;
  display: grid;
  gap: 24px;
}

.info-group {
  display: grid;
  grid-template-columns: 140px 1fr;
  gap: 20px;
  align-items: start;
}

.info-label {
  font-weight: 600;
  color: #1f2937;
  font-size: 14px;
}

.info-value {
  color: #6b7280;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.method-badge {
  display: inline-block;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 13px;
  font-family: monospace;
  min-width: 60px;
  text-align: center;
}

.method-get {
  background: #dcfce7;
  color: #15803d;
}

.method-post {
  background: #fef08a;
  color: #854d0e;
}

.method-put {
  background: #fecaca;
  color: #991b1b;
}

.method-delete {
  background: #fee2e2;
  color: #7f1d1d;
}

.method-patch {
  background: #dbeafe;
  color: #0c4a6e;
}

.middlewares-value {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.middleware-badge {
  background: #f3f4f6;
  color: #4b5563;
  padding: 4px 10px;
  border-radius: 3px;
  font-size: 12px;
  border: 1px solid #e5e7eb;
  font-family: monospace;
}

.info-link {
  color: #2563eb;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: color 0.2s;
}

.info-link:hover {
  color: #1d4ed8;
}

.arrow {
  font-size: 18px;
  font-weight: 300;
  display: inline-block;
  transition: transform 0.2s ease;
}

.arrow.expanded {
  transform: rotate(90deg);
}

.collapsible-content {
  margin-top: 12px;
  padding: 12px;
  background: #f9fafb;
  border-left: 3px solid #2563eb;
  border-radius: 4px;
  animation: slideDown 0.2s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.response-codes-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.response {
  padding: 10px 16px;
  background: #fff;
  border: none;
  border-radius: 4px;
  font-family: monospace;
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  width: 100%;
  display: inline-block;
}

/* Success responses */
.response-200, .response-201, .response-204 {
  background: #10b981;
  color: #fff;
}

/* Client errors - 400s */
.response-400, .response-401, .response-403, .response-404, .response-405, .response-409 {
  background: #f87171;
  color: #fff;
}

/* Client errors - 422, 429 (validation/rate limit) */
.response-422, .response-429 {
  background: #fbbf24;
  color: #78350f;
}

/* Server errors */
.response-500, .response-502, .response-503 {
  background: #f87171;
  color: #fff;
}

.curl-command {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.curl-command > div {
  width: 100%;
}

.curl-command textarea {
  width: 100%;
  background: #1f2937;
  padding: 12px;
  border-radius: 4px;
  border: 1px solid #374151;
  font-size: 12px;
  font-family: 'Monaco', 'Courier New', monospace;
  color: #e5e7eb;
  line-height: 1.6;
  box-sizing: border-box;
}

.curl-command code {
  flex: 1;
  background: #fff;
  padding: 10px;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
  font-size: 12px;
  overflow-x: auto;
  white-space: nowrap;
  color: #374151;
}

.copy-btn {
  align-self: center;
  width: 120px;
  padding: 8px 16px;
  background: #d1d5db;
  color: #374151;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
  height: fit-content;
}

.copy-btn:hover {
  background: #bfdbfe;
}

.copy-btn:active {
  background: #93c5fd;
}

.empty-content {
  padding: 12px;
  text-align: center;
  color: #9ca3af;
  font-size: 13px;
}

/* Request Parameters Section */
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

code {
  background: #f3f4f6;
  padding: 2px 6px;
  border-radius: 3px;
  font-family: monospace;
  font-size: 12px;
  color: #374151;
}
</style>
