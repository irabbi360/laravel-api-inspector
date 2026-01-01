<template>
  <div class="dashboard-container pb-5">
    <!-- Header -->
    <div class="dashboard-header">
      <h1>API Inspector Dashboard</h1>
      <div class="header-actions">
        <button @click="refreshData" class="btn-refresh">
          <span>🔄 Refresh</span>
        </button>
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
</template>

<script setup>
import { ref, reactive, onMounted, computed, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

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
  setInterval(() => {
    refreshData()
  }, 30000)
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
* {
  box-sizing: border-box;
}

.dashboard-container {
  padding: 20px;
  background: #f8fafc;
  min-height: 100vh;
}

/* Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.dashboard-header h1 {
  margin: 0;
  font-size: 28px;
  color: #1e293b;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.btn-refresh {
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-refresh:hover {
  background: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.time-range-select {
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.time-range-select:hover {
  border-color: #cbd5e1;
}

.time-range-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Overview Grid */
.overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.stat-label {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.stat-value {
  font-size: 32px;
  font-weight: bold;
  color: #1e293b;
  margin-bottom: 8px;
}

.stat-value.error {
  color: #ef4444;
}

.stat-description {
  font-size: 13px;
  color: #64748b;
  margin: 4px 0;
}

.stat-change {
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  margin-top: 4px;
}

.stat-change.positive {
  color: #10b981;
}

.stat-change.negative {
  color: #ef4444;
}

/* Charts Section */
.charts-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.chart-card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-card h3 {
  margin: 0 0 15px 0;
  font-size: 16px;
  color: #1e293b;
  font-weight: 600;
}

.chart-placeholder {
  position: relative;
  height: 300px;
  width: 100%;
}

.chart-placeholder canvas {
  max-height: 300px;
}

/* Tables Section */
.table-section {
  margin-bottom: 30px;
}

.table-card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-card h3 {
  margin: 0 0 15px 0;
  font-size: 16px;
  color: #1e293b;
  font-weight: 600;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: #f1f5f9;
}

.data-table th {
  padding: 12px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e2e8f0;
}

.data-table td {
  padding: 12px;
  border-bottom: 1px solid #e2e8f0;
  font-size: 13px;
  color: #475569;
}

.data-table tbody tr:hover {
  background: #f8fafc;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 30px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  color: white;
}

.status-badge.status-200,
.status-badge.status-201 {
  background: #10b981;
}

.status-badge.status-400,
.status-badge.status-401,
.status-badge.status-403,
.status-badge.status-404,
.status-badge.status-422 {
  background: #f59e0b;
}

.status-badge.status-500,
.status-badge.status-503 {
  background: #ef4444;
}

.method-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 45px;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
  color: white;
}

.method-badge.method-GET {
  background: #3b82f6;
}

.method-badge.method-POST {
  background: #10b981;
}

.method-badge.method-PUT {
  background: #f59e0b;
}

.method-badge.method-DELETE {
  background: #ef4444;
}

.method-badge.method-PATCH {
  background: #8b5cf6;
}

.route-name {
  font-family: 'Monaco', 'Courier New', monospace;
  font-size: 12px;
  color: #1e293b;
  font-weight: 500;
}

.duration {
  font-weight: 600;
  color: #1e293b;
}

.duration.slow {
  color: #ef4444;
  font-weight: 700;
}

.error-rate {
  font-weight: 600;
}

.error-rate.high {
  color: #ef4444;
}

.error-message {
  color: #ef4444;
  font-family: 'Monaco', 'Courier New', monospace;
  font-size: 12px;
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.route-row {
  transition: background 0.2s ease;
}

.route-row:hover {
  background: #f0fdf4;
}

.error-row {
  background: #fef2f2;
  transition: background 0.2s ease;
}

.error-row:hover {
  background: #fee2e2;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-container {
    padding: 12px;
  }

  .dashboard-header {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    flex-wrap: wrap;
  }

  .overview-grid {
    grid-template-columns: 1fr;
  }

  .charts-section {
    grid-template-columns: 1fr;
  }

  .data-table {
    font-size: 12px;
  }

  .data-table th,
  .data-table td {
    padding: 8px;
  }
}
</style>
