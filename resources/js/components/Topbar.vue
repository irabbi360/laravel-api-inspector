<template>
  <div class="topbar">
    <div class="topbar-content">
      <div class="api-info">
        <div>
          <div class="api-title">
            {{ apiData.title }}
            <a href="https://github.com/irabbi360/laravel-api-inspector" target="_blank" class="github-link">
              <svg
                width="30"
                height="30"
                viewBox="0 0 98 98"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
              >
                <path
                  fill="#fff"
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M48.854 0C21.839 0 0 22 0 49.217c0 21.756 13.993 40.172 33.405 46.69 2.427.49 3.316-1.059 3.316-2.362 0-1.141-.08-5.052-.08-9.127-13.59 2.934-16.42-5.867-16.42-5.867-2.184-5.704-5.42-7.17-5.42-7.17-4.448-3.015.324-3.015.324-3.015 4.934.326 7.523 5.052 7.523 5.052 4.367 7.496 11.404 5.378 14.235 4.074.404-3.178 1.699-5.378 3.074-6.6-10.839-1.141-22.243-5.378-22.243-24.283 0-5.378 1.94-9.778 5.014-13.2-.485-1.222-2.184-6.275.486-13.038 0 0 4.125-1.304 13.426 5.052a46.97 46.97 0 0 1 12.214-1.63c4.125 0 8.33.571 12.213 1.63 9.302-6.356 13.427-5.052 13.427-5.052 2.67 6.763.97 11.816.485 13.038 3.155 3.422 5.015 7.822 5.015 13.2 0 18.905-11.404 23.06-22.324 24.283 1.78 1.548 3.316 4.481 3.316 9.126 0 6.6-.08 11.897-.08 13.526 0 1.304.89 2.853 3.316 2.364 19.412-6.52 33.405-24.935 33.405-46.691C97.707 22 75.788 0 48.854 0z"
                />
              </svg>
            </a>
          </div>
          <div class="api-version">
            Version <span class="version-badge">{{ apiData.version }}</span>
          </div>
        </div>
      </div>
      <div class="topbar-actions">
        <div class="auth-input-group">
          <label>🔐 Bearer Token:</label>
          <input
            :value="authToken"
            @input="$emit('update:authToken', $event.target.value)"
            type="password"
            placeholder="Enter auth token..."
          />
          <div :class="['auth-status', { active: authToken }]">
            <span class="auth-status-dot"></span>
            {{ authToken ? 'Authenticated' : 'No token' }}
          </div>
        </div>
        <div v-if="loading" class="loading-spinner"></div>
        <button v-else @click="$emit('refresh')" class="btn btn-refresh">
          ↻ Refresh
        </button>
        <a
          href="https://github.com/irabbi360/laravel-api-inspector/issues/new"
          target="_blank"
          class="btn btn-feature"
        >
          Feature Request
        </a>
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
  },
  authToken: {
    type: String,
    default: ''
  }
})

defineEmits(['refresh', 'update:authToken'])
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
  display: flex;
  align-items: center;
  gap: 15px;
}

.github-link {
  display: inline-flex;
  align-items: center;
  transition: opacity 0.2s;
}

.github-link:hover {
  opacity: 0.8;
}

.github-link svg {
  width: 30px;
  height: 30px;
}

.api-version {
  color: #999;
  font-size: 0.9em;
  margin-top: 5px;
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
  gap: 15px;
  align-items: center;
}

.auth-input-group {
  display: flex;
  gap: 10px;
  align-items: center;
}

.auth-input-group input {
  min-height: auto;
  min-width: 250px;
  padding: 8px 12px;
  font-size: 12px;
  background: #2a2a2a;
  border: 1px solid #444;
  color: #ccc;
  border-radius: 4px;
  font-family: monospace;
}

.auth-input-group input:focus {
  outline: none;
  border-color: #0066cc;
  box-shadow: 0 0 4px rgba(0, 102, 204, 0.3);
}

.auth-input-group input::placeholder {
  color: #666;
}

.auth-input-group label {
  color: #999;
  font-size: 12px;
  margin: 0;
  white-space: nowrap;
}

.auth-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #999;
}

.auth-status.active {
  color: #49cc90;
}

.auth-status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #999;
}

.auth-status.active .auth-status-dot {
  background: #49cc90;
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
  text-decoration: none;
  display: inline-block;
  font-size: 14px;
}

.btn:hover:not(:disabled) {
  opacity: 0.9;
}

.btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.btn-refresh {
  background: #666;
}

.btn-feature {
  background: #389f71;
  text-decoration: none;
}

.loading-spinner {
  width: 20px;
  height: 20px;
  border: 3px solid #ccc;
  border-top-color: #0066cc;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
