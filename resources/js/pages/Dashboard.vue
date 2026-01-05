<template>
  <div class="app-container">
    <Topbar
      :api-data="apiData"
      :loading="loading"
      :auth-token="authToken"
      :menus="menus"
      @refresh="refreshData"
      @update:authToken="(value) => (authToken = value)"
    />
    <div class="content">
      <div class="dashboard-container pb-5">
        <!-- Header -->
        <div class="dashboard-header">
          <h1>API Inspector Dashboard</h1>
          <div class="header-actions">
            <select v-model="timeRange" @change="refreshData" class="time-range-select">
              <option value="1h">Last 1 Hour</option>
              <option value="24h">Last 24 Hours</option>
              <option value="7d">Last 7 Days</option>
              <option value="30d">Last 30 Days</option>
            </select>
          </div>
        </div>

        <!-- Overview Cards -->
        <div class="overview-grid">
          <div class="stat-card">
            <div class="stat-label">Total Requests</div>
            <div class="stat-value">{{ stats.totalRequests }}</div>
            <div class="stat-change" :class="stats.requestsTrend > 0 ? 'positive' : 'negative'">
              {{ stats.requestsTrend > 0 ? '↑' : '↓' }} {{ Math.abs(stats.requestsTrend) }}%
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-label">Avg Response Time</div>
            <div class="stat-value">{{ stats.avgResponseTime.toFixed(2) }}ms</div>
            <div class="stat-description">{{ stats.slowestRoute }}</div>
          </div>

          <div class="stat-card">
            <div class="stat-label">Error Rate</div>
            <div class="stat-value" :class="stats.errorRate > 10 ? 'error' : ''">
              {{ stats.errorRate.toFixed(2) }}%
            </div>
            <div class="stat-change">{{ stats.errorCount }} errors</div>
          </div>

          <div class="stat-card">
            <div class="stat-label">Avg Memory Usage</div>
            <div class="stat-value">{{ formatBytes(stats.avgMemory) }}</div>
            <div class="stat-description">Per request</div>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
          <div class="chart-card">
            <h3>Request Timeline</h3>
            <div class="chart-placeholder">
              <canvas ref="requestChart"></canvas>
            </div>
          </div>

          <div class="chart-card">
            <h3>Response Time Distribution</h3>
            <div class="chart-placeholder">
              <canvas ref="responseTimeChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Status Code Distribution -->
        <div class="table-section">
          <div class="table-card">
            <h3>Status Code Distribution</h3>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Status Code</th>
                  <th>Count</th>
                  <th>Percentage</th>
                  <th>Avg Duration</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(count, code) in statusCodeStats" :key="code" :class="`status-${code}`">
                  <td>
                    <span class="status-badge" :class="`status-${code}`">
                      {{ code }}
                    </span>
                  </td>
                  <td>{{ count }}</td>
                  <td>{{ getStatusPercentage(code) }}%</td>
                  <td>{{ getAvgDurationForStatus(code) }}ms</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Top Routes -->
        <div class="table-section">
          <div class="table-card">
            <h3>Top Routes by Response Time</h3>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Route</th>
                  <th>Method</th>
                  <th>Requests</th>
                  <th>Avg Time</th>
                  <th>Min/Max</th>
                  <th>Error Rate</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="route in topRoutes" :key="route.route" class="route-row">
                  <td class="route-name">{{ route.route }}</td>
                  <td>
                    <span class="method-badge" :class="`method-${route.method}`">
                      {{ route.method }}
                    </span>
                  </td>
                  <td>{{ route.count }}</td>
                  <td>
                    <span class="duration" :class="route.avg_duration > 500 ? 'slow' : ''">
                      {{ route.avg_duration.toFixed(2) }}ms
                    </span>
                  </td>
                  <td>{{ route.min }}ms / {{ route.max }}ms</td>
                  <td>
                    <span class="error-rate" :class="route.errorRate > 10 ? 'high' : ''">
                      {{ route.errorRate.toFixed(2) }}%
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Errors -->
        <div class="table-section" v-if="recentErrors.length > 0">
          <div class="table-card">
            <h3>Recent Errors (Last 24h)</h3>
            <table class="data-table errors-table">
              <thead>
                <tr>
                  <th>Time</th>
                  <th>Route</th>
                  <th>Status</th>
                  <th>Error</th>
                  <th>IP</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(error, index) in recentErrors.slice(0, 10)" :key="index" class="error-row">
                  <td>{{ formatTime(error.recorded_at) }}</td>
                  <td>{{ error.route }}</td>
                  <td>
                    <span class="status-badge" :class="`status-${error.status_code}`">
                      {{ error.status_code }}
                    </span>
                  </td>
                  <td class="error-message">{{ error.error }}</td>
                  <td>{{ error.ip_address }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import Topbar from '../components/Topbar.vue'
import { useMenu } from '../composables/useMenu';

Chart.register(...registerables)

const loading = ref(false)
const apiData = ref({
  routes: [],
  ...window.ApiInspector
})

const authToken = ref(localStorage.getItem('api-docs-auth-token') || '')

const { menus } = useMenu()

const timeRange = ref('24h')
const stats = reactive({
  totalRequests: 0,
  requestsTrend: 0,
  avgResponseTime: 0,
  slowestRoute: '',
  errorRate: 0,
  errorCount: 0,
  avgMemory: 0,
})

const statusCodeStats = ref({})
const topRoutes = ref([])
const recentErrors = ref([])
const chartData = reactive({
  requestTimeline: null,
  responseDistribution: null,
})

const requestChart = ref(null)
const responseTimeChart = ref(null)

let requestChartInstance = null
let responseTimeChartInstance = null

onMounted(() => {
  refreshData()
  // Auto-refresh every 30 seconds
  // setInterval(() => {
  //   refreshData()
  // }, 30000)
})

const refreshData = async () => {
  try {
    const response = await fetch(`/api/api-inspector-docs/analytics?range=${timeRange.value}`)
    const data = await response.json()

    stats.totalRequests = data.totalRequests
    stats.requestsTrend = data.requestsTrend
    stats.avgResponseTime = data.avgResponseTime
    stats.slowestRoute = data.slowestRoute
    stats.errorRate = data.errorRate
    stats.errorCount = data.errorCount
    stats.avgMemory = data.avgMemory

    statusCodeStats.value = data.statusCodeStats
    topRoutes.value = data.topRoutes
    recentErrors.value = data.recentErrors

    // Wait for DOM to be ready before updating charts
    await nextTick()
    updateCharts(data)
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error)
  }
}

const updateCharts = (data) => {
  updateRequestTimeline(data)
  updateResponseDistribution(data)
}

const updateRequestTimeline = (data) => {
  if (!requestChart.value) return

  const labels = generateTimeLabels()
  
  // Destroy previous chart if it exists
  if (requestChartInstance) {
    requestChartInstance.destroy()
  }

  requestChartInstance = new Chart(requestChart.value, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Requests',
        data: generateRequestData(data, labels.length),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 2,
        tension: 0.4,
        fill: true,
        pointRadius: 4,
        pointBackgroundColor: '#3b82f6',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: true,
          labels: {
            font: { size: 12 },
            usePointStyle: true,
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: { font: { size: 11 } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 } }
        }
      }
    }
  })
}

const updateResponseDistribution = (data) => {
  if (!responseTimeChart.value) return

  const routes = data.topRoutes.slice(0, 8)
  const labels = routes.map(r => r.route.substring(0, 20))
  const durations = routes.map(r => r.avg_duration)

  // Destroy previous chart if it exists
  if (responseTimeChartInstance) {
    responseTimeChartInstance.destroy()
  }

  responseTimeChartInstance = new Chart(responseTimeChart.value, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Avg Response Time (ms)',
        data: durations,
        backgroundColor: durations.map(d => d > 500 ? '#ef4444' : d > 200 ? '#f97316' : '#10b981'),
        borderColor: 'rgba(0, 0, 0, 0.1)',
        borderWidth: 1,
      }],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: true,
          labels: {
            font: { size: 12 },
          }
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: { font: { size: 11 } }
        },
        y: {
          grid: { display: false },
          ticks: { font: { size: 11 } }
        }
      }
    }
  })
}

const generateTimeLabels = () => {
  const labels = []
  const now = new Date()
  for (let i = 11; i >= 0; i--) {
    const time = new Date(now.getTime() - i * 3600000)
    labels.push(time.getHours().toString().padStart(2, '0') + ':00')
  }
  return labels
}

const generateRequestData = (data, count) => {
  // Generate random-ish data based on total requests
  const perHour = Math.ceil(data.totalRequests / count)
  return Array.from({ length: count }, () => 
    Math.max(1, perHour + Math.floor(Math.random() * (perHour * 0.5) - perHour * 0.25))
  )
}

const getStatusPercentage = (code) => {
  const total = Object.values(statusCodeStats.value).reduce((a, b) => a + b, 0)
  return total > 0 ? ((statusCodeStats.value[code] / total) * 100).toFixed(2) : 0
}

const getAvgDurationForStatus = (code) => {
  const routes = topRoutes.value.filter(r => r.status_code === parseInt(code))
  if (routes.length === 0) return '0'
  const avg = routes.reduce((sum, r) => sum + r.avg_duration, 0) / routes.length
  return avg.toFixed(2)
}

const formatBytes = (bytes) => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(Math.abs(bytes)) / Math.log(k))
  return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i]
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  return date.toLocaleTimeString()
}
</script>

<style scoped>

</style>
