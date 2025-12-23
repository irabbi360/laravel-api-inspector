<template>
  <div class="app-container">
    <Topbar
      :api-data="apiData"
      :loading="loading"
      :auth-token="authToken"
      @refresh="fetchApiData"
      @update:authToken="(value) => (authToken = value)"
    />
    <div class="main-container">
      <Sidebar
        :grouped-routes="groupedRoutes"
        :selected-route="selectedRoute"
        @select-endpoint="selectEndpoint"
      />
      <div class="content">
        <EndpointDetail
          v-if="selectedRoute"
          :route="selectedRoute"
          :request-body="requestBody"
          :last-response="lastResponse"
          :saved-responses="savedResponses"
          :sending="sending"
          :path-params="pathParams"
          @update:requestBody="(value) => (requestBody = value)"
          @send-request="sendRequest"
          @save-response="saveCurrentResponse"
          @view-response="viewSavedResponse"
          @update:pathParams="(value) => (pathParams = value)"
        />
        <div v-else class="empty-state">
          <div class="empty-state-icon">🚀</div>
          <div >
            <h6>Welcome to {{ apiData.title }}</h6>
            <p><strong>Laravel API Inspector</strong> automatically generates API documentation from your Laravel routes,</p>
            <p> FormRequest validation rules, and API Resources. It's like Postman + Swagger combined, but deeply integrated with Laravel.</p>
            <p>Version: {{ apiData.version }}</p>
          </div>
          <p>Select an endpoint from the sidebar to get started</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Topbar from '../components/Topbar.vue'
import Sidebar from '../components/Sidebar.vue'
import EndpointDetail from '../components/EndpointDetail.vue'

const loading = ref(true)
const sending = ref(false)
const apiData = ref({
  routes: [],
  title: 'API Inspector Documentation',
  version: '1.0.0'
})
const selectedRoute = ref(null)
const requestBody = ref('{}')
const lastResponse = ref(null)
const savedResponses = ref([])
const authToken = ref(localStorage.getItem('api-docs-auth-token') || '')
const pathParams = ref({})

const groupedRoutes = computed(() => {
    if (!apiData.value.routes) return {};

    return apiData.value.routes.reduce((groups, route) => {
        const parts = route.uri.split('/').filter(p => p);
        const prefix = parts.length > 0 ? `/${parts[0]}` : 'Other';

        if (!groups[prefix]) {
            groups[prefix] = [];
        }
        groups[prefix].push(route);
        return groups;
    }, {});
});

const fetchApiData = async () => {
  try {
    loading.value = true
    const response = await fetch(
      `${window.location.origin}/api/api-inspector-docs/fetch`,
      {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Authorization': authToken.value ? `Bearer ${authToken.value}` : ''
        }
      }
    )

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    apiData.value = {
      routes: data.routes || [],
      title: data.title || window.apiInspector?.title || 'API Documentation',
      version: data.version || '1.0.0'
    }
  } catch (error) {
    console.error('Error fetching API data:', error)
    apiData.value = {
      routes: [],
      title: 'Error',
      version: '1.0.0'
    }
  } finally {
    loading.value = false
  }
}

const selectEndpoint = (route) => {
  selectedRoute.value = JSON.parse(JSON.stringify(route))
  
  // Initialize requestBody with structure from request_rules
  if (route.request_rules && Object.keys(route.request_rules).length > 0) {
    const bodyObject = {}
    Object.keys(route.request_rules).forEach((key) => {
      bodyObject[key] = route.request_rules[key].example || ''
    })
    requestBody.value = JSON.stringify(bodyObject, null, 2)
  } else {
    requestBody.value = '{}'
  }
  
  lastResponse.value = null
  pathParams.value = {}
  loadSavedResponses()
}

const sendRequest = async () => {
  if (!selectedRoute.value) return

  try {
    sending.value = true
    const url = new URL(
      `${window.location.origin}/${selectedRoute.value.uri}`
    )

    // Replace path parameters
    let urlString = url.toString()
    Object.keys(pathParams.value).forEach((param) => {
      urlString = urlString.replace(
        `{${param}}`,
        pathParams.value[param]
      )
    })

    const options = {
      method: selectedRoute.value.method,
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    }

    if (authToken.value) {
      options.headers['Authorization'] = `Bearer ${authToken.value}`
    }

    if (
      selectedRoute.value.method !== 'GET' &&
      selectedRoute.value.method !== 'HEAD'
    ) {
      options.body = requestBody.value
    }

    const response = await fetch(urlString, options)
    const contentType = response.headers.get('content-type')

    let data = ''
    if (contentType?.includes('application/json')) {
      data = await response.json()
    } else {
      data = await response.text()
    }

    lastResponse.value = {
      status: response.status,
      data: data,
      timestamp: new Date().toISOString()
    }
  } catch (error) {
    console.error('Error sending request:', error)
    lastResponse.value = {
      status: 0,
      data: `Error: ${error.message}`,
      timestamp: new Date().toISOString()
    }
  } finally {
    sending.value = false
  }
}

const saveCurrentResponse = async () => {
  if (!selectedRoute.value || !lastResponse.value) return

  try {
    const response = await fetch(
      `${window.location.origin}/api/api-inspector-docs/save-response`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
          route_uri: selectedRoute.value.uri,
          route_method: selectedRoute.value.method,
          response: lastResponse.value.data,
          status: lastResponse.value.status
        })
      }
    )

    if (response.ok) {
      loadSavedResponses()
    }
  } catch (error) {
    console.error('Error saving response:', error)
  }
}

const loadSavedResponses = async () => {
  if (!selectedRoute.value) return

  try {
    const response = await fetch(
      `${window.location.origin}/api/api-inspector-docs/get-saved-responses?uri=${encodeURIComponent(selectedRoute.value.uri)}&method=${selectedRoute.value.method}`,
      {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
    )

    if (response.ok) {
      const data = await response.json()
      savedResponses.value = data.responses || []
    }
  } catch (error) {
    console.error('Error loading saved responses:', error)
  }
}

const viewSavedResponse = (saved) => {
  lastResponse.value = saved
}

// Watch for authToken changes and save to localStorage
watch(authToken, (newToken) => {
  if (newToken) {
    localStorage.setItem('api-docs-auth-token', newToken)
  } else {
    localStorage.removeItem('api-docs-auth-token')
  }
})

onMounted(() => {
  fetchApiData()
})
</script>

<style scoped>
.app-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}

.main-container {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #999;
}

.empty-state-icon {
  font-size: 4em;
  margin-bottom: 20px;
  opacity: 0.3;
}
</style>
