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
          Show Response codes for this request <span class="arrow">›</span>
          <div>
            <ul>
              <li v-for="code in apiInfo.status_codes || []" :key="code">{{ code }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="info-group">
        <div class="info-label"><code>&lt;/&gt; Curl</code></div>
        <div class="info-link">Show curl command <span class="arrow">›</span></div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  apiInfo: {
    type: Object,
    default: null
  },
})
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
