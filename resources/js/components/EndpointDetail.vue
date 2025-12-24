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
          @update:requestBody="(value) => $emit('update:requestBody', value)"
          @send-request="$emit('send-request')"
          @update:pathParams="(value) => $emit('update:pathParams', value)"
        />
      </div>
      <div class="detail-body-right">
        <Tabs
          v-model="activeTab"
          :tabs="['Request Body Parameters', 'Current Response', 'Response Schema', 'Saved Responses History']"
        >
          <template #default="{ activeTab }">
            <div v-show="activeTab === 0" class="tab-content">
              <RequestBodySection :request-rules="route.request_rules" />
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
              <ResponseSchema :schema="route.response_schema" />
            </div>
            <div v-show="activeTab === 3" class="tab-content">
              <SavedResponses
                :responses="savedResponses"
                @view-response="$emit('view-response', $event)"
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
  }
})

defineEmits(['update:requestBody', 'send-request', 'save-response', 'view-response', 'update:pathParams'])

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
  padding: 30px;
  border-right: 1px solid #e0e0e0;
  background: #fff;
}

.detail-body-right {
  flex: 1;
  overflow-y: auto;
  padding: 30px;
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
