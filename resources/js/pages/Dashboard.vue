<template>
  <div class="app-container">
    <Topbar
      :api-data="apiData"
      :loading="loading"
      :auth-token="authToken"
      @refresh="fetchApiData"
      @update:authToken="(value) => (authToken = value)"
    />
    <div class="content">
      <Dashboard/>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Topbar from '../components/Topbar.vue'
import Dashboard from '../components/Dashboard.vue'

const loading = ref(false)
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

</script>

<style scoped>

</style>
