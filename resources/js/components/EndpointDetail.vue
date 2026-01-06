<template>
  <div class="endpoint-detail">
    <DetailHeader :route="route" />
    <div class="detail-body">
      <div class="detail-body-left">
        <ParametersSection :parameters="route.parameters" />
        <!-- <RequestBodySection :request-rules="route.request_rules" /> -->
        <RequestTester
          :request-body="requestBody"
          :request-rules="route.request_rules"
          :sending="sending"
          :route="route"
          :path-params="pathParams"
          :query-params="queryParams"
          @update:requestBody="(value) => $emit('update:requestBody', value)"
          @send-request="$emit('send-request')"
          @update:pathParams="(value) => $emit('update:pathParams', value)"
          @update:queryParams="(value) => $emit('update:queryParams', value)"
        />
      </div>
      <div class="detail-body-right">
        <Tabs
          v-model="activeTab"
          :tabs="['Request Body', 'Response', 'Saved Responses', 'Response Schema', 'Info']"
        >
          <template #default="{ activeTab }">
            <div v-show="activeTab === 0" class="tab-content">
              <RequestBodySection
                v-if="route.request_rules && Object.keys(route.request_rules).length > 0"
                :request-rules="route.request_rules"
              />
              <div v-else class="empty-state">
                <div class="empty-state-icon">📄</div>
                <template v-if="route.http_method !== 'GET' && route.http_method !== 'DELETE'">
                  <p>Request body not available!</p>
                  <p>Please use the request rules for expected payload.</p>
                </template>
                <p v-if="route.http_method === 'GET'">
                  This endpoint does not accept a request body.
                </p>
              </div>
            </div>
            
            <div v-show="activeTab === 1" class="tab-content">
              <ResponseViewer
                v-if="lastResponse"
                :response="lastResponse"
                :schema="route.response_schema"
                @save-response="$emit('save-response')"
              />
              <div v-else class="empty-state">
                <div class="empty-state-icon">📄</div>
                <p>Send a request to see the response</p>
              </div>
            </div>
            
            <div v-show="activeTab === 2" class="tab-content">
              <SavedResponses
                v-if="savedResponses && savedResponses.length > 0"
                :responses="savedResponses"
                :route="route"
                @view-response="$emit('view-response', $event)"
                @delete-response="$emit('delete-response', $event)"
              />
              <div v-else class="empty-state">
                <div class="empty-state-icon">📄</div>
                <p>Saved responses empty!</p>
              </div>
            </div>
            
            <div v-show="activeTab === 3" class="tab-content">
              <ResponseSchema
                v-if="route.response_schema && Object.keys(route.response_schema).length > 0"
                :schema="route.response_schema"
                />
              <div v-else class="empty-state">
                <div class="empty-state-icon">📄</div>
                <p class="text-center">Response schema not available!</p>
                <p class="text-center">Please use the resource class to define the response schema.</p>
              </div>
            </div>
            
            <div v-show="activeTab === 4" class="tab-content">
                <ApiInfo
                  :api-info="route"
                  :request-rules="route.request_rules"
                  :parameters="route.parameters"
                />
              </div>
          </template>
        </Tabs>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import DetailHeader from './DetailHeader.vue'
import ParametersSection from './ParametersSection.vue'
import RequestBodySection from './RequestBodySection.vue'
import RequestTester from './RequestTester.vue'
import ResponseSchema from './ResponseSchema.vue'
import ResponseViewer from './ResponseViewer.vue'
import SavedResponses from './SavedResponses.vue'
import Tabs from './Tabs.vue'
import ApiInfo from './ApiInfo.vue'

const props = defineProps({
  route: {
    type: Object,
    required: true
  },
  requestBody: {
    type: String,
    default: '{}'
  },
  lastResponse: {
    type: Object,
    default: null
  },
  savedResponses: {
    type: Array,
    default: () => []
  },
  sending: {
    type: Boolean,
    default: false
  },
  pathParams: {
    type: Object,
    default: () => ({})
  },
  queryParams: {
    type: Object,
    default: () => ({})
  }
})

defineEmits(['update:requestBody', 'send-request', 'save-response', 'view-response', 'update:pathParams', 'update:queryParams', 'delete-response'])

const activeTab = ref(0)

// Watch for response changes and switch to Current Response tab
watch(() => props.lastResponse, (newResponse) => {
  if (newResponse) {
    activeTab.value = 1
  }
})
</script>

<style scoped>
.endpoint-detail {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.detail-body {
  flex: 1;
  overflow-y: auto;
  padding: 0;
  display: flex;
  gap: 0;
}

.detail-body-left {
  flex: 1;
  overflow-y: auto;
  padding: 10px 20px;
  border-right: 1px solid #e0e0e0;
  background: #fff;
}

.detail-body-right {
  flex: 1;
  overflow-y: auto;
  padding: 10px 20px;
  background: #f9f9f9;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #999;
  padding: 40px 20px;
  min-height: 200px;
}

.empty-state-icon {
  font-size: 4em;
  margin-bottom: 20px;
  opacity: 0.3;
}

.tab-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
