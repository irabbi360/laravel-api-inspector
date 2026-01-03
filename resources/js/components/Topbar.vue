<template>
  <div class="topbar">
    <div class="topbar-top pb-0">
      <div class="api-branding">
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

      <div class="topbar-controls">
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
          <button
            v-if="authToken"
            class="btn-logout"
            @click="handleLogout"
            title="Logout / Clear token"
          >
            Logout
          </button>
        </div>
        <div v-if="loading" class="loading-spinner"></div>
        <button v-else @click="$emit('refresh')" class="btn btn-refresh">
          ↻ Refresh
        </button>
        <button @click="downloadPostman" class="btn btn-postman" title="Download Postman Collection">
          📮 Postman
        </button>
        <button @click="downloadOpenApi" class="btn btn-openapi" title="Download OpenAPI Specification">
          📋 OpenAPI
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

    <nav class="topbar-menu pb-0">
      <router-link
        v-for="item in menus"
        :key="item.id"
        :to="item.href"
        :class="['menu-item', { active: item.active }]"
        @click="handleMenuClick(item)"
      >
        <span v-if="item.icon" class="menu-icon">{{ item.icon }}</span>
        <span class="menu-label">{{ item.label }}</span>
      </router-link>
    </nav>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
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
  },
  menus: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['refresh', 'update:authToken', 'menu-click'])

const activeMenuId = ref(null)

const handleMenuClick = (item) => {
  activeMenuId.value = item.id
  emit('menu-click', item)
}

const handleLogout = () => {
  emit('update:authToken', '')
}

const downloadPostman = async () => {
  try {
    const apiPath = '/api/api-inspector-docs/postman';
    
    const response = await fetch(apiPath)
    if (!response.ok) {
      throw new Error('Failed to download Postman collection')
    }
    
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = 'postman_collection.json'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error downloading Postman collection:', error)
    alert('Failed to download Postman collection')
  }
}

const downloadOpenApi = async () => {
  try {
    const apiPath = '/api/api-inspector-docs/openapi';
    
    const response = await fetch(apiPath)
    if (!response.ok) {
      throw new Error('Failed to download OpenAPI specification')
    }
    
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = 'openapi.json'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error downloading OpenAPI specification:', error)
    alert('Failed to download OpenAPI specification')
  }
}
</script>

<style scoped>
.topbar {
  background: #1e1e1e;
  border-bottom: 1px solid #3e3e42;
  position: sticky;
  top: 0;
  z-index: 100;
}

.topbar-top {
  max-width: 1400px;
  margin: 0 auto;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 30px;
  min-height: 70px;
}

.api-branding {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-shrink: 0;
}

.api-title {
  color: #fff;
  font-size: 1.4em;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0;
}

.github-link {
  display: inline-flex;
  align-items: center;
  transition: opacity 0.2s;
  flex-shrink: 0;
}

.github-link:hover {
  opacity: 0.8;
}

.github-link svg {
  width: 28px;
  height: 28px;
}

.api-version {
  color: #999;
  font-size: 0.85em;
  white-space: nowrap;
}

.version-badge {
  background: #0066cc;
  color: white;
  padding: 4px 10px;
  border-radius: 3px;
  font-size: 0.8em;
  margin-left: 8px;
  font-weight: 600;
}

.topbar-controls {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-shrink: 0;
}

.topbar-menu {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px 16px 20px;
  display: flex;
  gap: 8px;
  align-items: center;
  border-top: 1px solid #2a2a2a;
}

.menu-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  color: #bbb;
  text-decoration: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  border: 1px solid transparent;
  white-space: nowrap;
  font-weight: 500;
}

.menu-item:hover {
  background: #2a2a2a;
  color: #fff;
  border-color: #444;
}

.menu-item.active {
  background: #0066cc;
  color: white;
  border-color: #0052a3;
}

.menu-icon {
  font-size: 16px;
}

.menu-label {
  font-weight: 500;
}

.auth-input-group {
  display: flex;
  gap: 10px;
  align-items: center;
  border-right: 1px solid #3e3e42;
  padding-right: 12px;
}

.auth-input-group input {
  min-height: auto;
  width: 240px;
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
  font-weight: 500;
}

.auth-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #999;
  white-space: nowrap;
}

.auth-status.active {
  color: #49cc90;
}

.auth-status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #999;
  flex-shrink: 0;
}

.auth-status.active .auth-status-dot {
  background: #49cc90;
}

.btn-logout {
  background: #f93e3e;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  font-size: 12px;
  white-space: nowrap;
  transition: background 0.2s, transform 0.1s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-logout:hover {
  background: #d92e2e;
  transform: scale(1.05);
}

.btn-logout:active {
  transform: scale(0.98);
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
  display: inline-flex;
  align-items: center;
  font-size: 14px;
  white-space: nowrap;
  flex-shrink: 0;
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

.btn-postman {
  background: #ff6c37;
}

.btn-postman:hover {
  opacity: 0.9;
}

.btn-openapi {
  background: #008c45;
}

.btn-openapi:hover {
  opacity: 0.9;
}

.loading-spinner {
  width: 20px;
  height: 20px;
  border: 3px solid #ccc;
  border-top-color: #0066cc;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  flex-shrink: 0;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 1200px) {
  .topbar-top {
    gap: 20px;
  }

  .api-title {
    font-size: 1.2em;
  }

  .topbar-controls {
    gap: 10px;
  }

  .auth-input-group input {
    width: 200px;
  }
}

@media (max-width: 900px) {
  .topbar-top {
    flex-wrap: wrap;
    min-height: auto;
  }

  .api-branding {
    flex-basis: 100%;
  }

  .topbar-controls {
    flex-basis: 100%;
    justify-content: flex-end;
  }

  .topbar-menu {
    flex-basis: 100%;
    padding: 12px 20px;
    border-top: 1px solid #2a2a2a;
  }

  .menu-item {
    padding: 8px 14px;
    font-size: 13px;
  }

  .auth-input-group input {
    width: 180px;
  }
}

@media (max-width: 768px) {
  .topbar-top {
    padding: 12px 15px;
    gap: 15px;
  }

  .api-title {
    font-size: 1.1em;
  }

  .github-link svg {
    width: 24px;
    height: 24px;
  }

  .topbar-menu {
    padding: 10px 15px;
  }

  .menu-item {
    padding: 8px 12px;
    font-size: 12px;
  }

  .auth-input-group {
    padding-right: 10px;
  }

  .auth-input-group input {
    width: 150px;
    padding: 6px 10px;
  }

  .btn-logout {
    padding: 6px 10px;
    font-size: 11px;
  }

  .btn {
    padding: 8px 15px;
    font-size: 12px;
  }
}

@media (max-width: 600px) {
  .topbar-top {
    flex-direction: column;
    align-items: flex-start;
  }

  .api-branding {
    width: 100%;
  }

  .topbar-controls {
    width: 100%;
    gap: 8px;
    flex-wrap: wrap;
  }

  .topbar-menu {
    width: 100%;
  }

  .auth-input-group {
    flex-wrap: wrap;
    border-right: none;
    padding-right: 0;
    width: 100%;
  }

  .auth-input-group label {
    flex-basis: 100%;
  }

  .auth-input-group input {
    width: 100%;
    min-width: 120px;
  }

  .menu-item {
    flex: 1;
    justify-content: center;
    padding: 8px 10px;
  }

  .menu-label {
    display: none;
  }

  .menu-icon {
    font-size: 18px;
  }

  .btn {
    flex: 1;
    justify-content: center;
    padding: 8px 10px;
  }
}

@media (max-width: 480px) {
  .topbar-top {
    padding: 10px 12px;
  }

  .api-title {
    font-size: 0.95em;
  }

  .api-version {
    display: none;
  }

  .github-link svg {
    width: 20px;
    height: 20px;
  }

  .topbar-menu {
    padding: 8px 12px;
  }

  .menu-item {
    padding: 6px 8px;
    font-size: 11px;
  }

  .auth-input-group {
    gap: 8px;
  }

  .auth-input-group label {
    display: none;
  }

  .auth-input-group input {
    padding: 6px 8px;
  }

  .auth-status {
    font-size: 10px;
  }

  .btn-logout {
    padding: 6px 10px;
    font-size: 10px;
  }

  .btn {
    padding: 6px 12px;
    font-size: 11px;
  }
}
</style>
