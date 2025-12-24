<template>
  <div class="endpoint-detail">
    <DetailHeader :route="route" />
    <div class="detail-body">
      <div class="detail-body-left">
        <ParametersSection :parameters="route.parameters" />
        <RequestBodySection :request-rules="route.request_rules" />
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
        <ResponseSchema :schema="route.response_schema" />
        <ResponseViewer
          v-if="lastResponse"
          :response="lastResponse"
          :schema="route.response_schema"
          @save-response="$emit('save-response')"
        />
        <SavedResponses
          :responses="savedResponses"
          @view-response="$emit('view-response', $event)"
        />
        <div v-if="!lastResponse" class="empty-state">
          <div class="empty-state-icon">📄</div>
          <p>Send a request to see the response</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import DetailHeader from './DetailHeader.vue'
import ParametersSection from './ParametersSection.vue'
import RequestBodySection from './RequestBodySection.vue'
import RequestTester from './RequestTester.vue'
import ResponseSchema from './ResponseSchema.vue'
import ResponseViewer from './ResponseViewer.vue'
import SavedResponses from './SavedResponses.vue'

defineProps({
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
