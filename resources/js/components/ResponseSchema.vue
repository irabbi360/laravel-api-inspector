<template>
  <div v-if="schema && schema.data && Object.keys(schema.data).length > 0" class="section">
    <div class="section-title">Response Schema</div>
    <div class="bg-white" style="border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px; position: relative;">
      <button @click="copyToClipboard" class="pre-copy-icon" title="Copy JSON schema">📋</button>
      <pre style="padding: 10px; border-radius: 4px; overflow-x: auto; cursor: pointer; margin: 0;" title="Click to copy">{{ JSON.stringify(formatSchema(schema), null, 2) }}</pre>
    </div>
  </div>
  <div v-else class="section">
    <div>No response schema available.</div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const { showToast } = useToast()

const props = defineProps({
  schema: {
    type: Object,
    default: null
  }
})

const isCopied = ref(false)
const toastMessage = ref('Copy schema to clipboard')

const formatSchema = (schema) => {
    // Skip metadata fields like resource_class
    const metadataFields = ['resource_class'];
    const obj = {};

    Object.keys(schema).forEach(key => {
        // Skip metadata fields
        if (metadataFields.includes(key)) {
            return;
        }

        const field = schema[key];

        // If field has a nested schema object, use that
        if (
            typeof field === 'object' &&
            field !== null &&
            field.schema &&
            typeof field.schema === 'object'
        ) {
            // Recursively format the nested schema
            obj[key] = formatSchema(field.schema);
        }
        // If field is an object (but not metadata), recurse into it
        else if (typeof field === 'object' && field !== null) {
            obj[key] = formatSchema(field);
        }
        // Otherwise, it's a primitive value - include it as-is
        else {
            obj[key] = field;
        }
    });

    return obj;
};

const fallbackCopyToClipboard = (text) => {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        showToast('Response schema copied to clipboard!', 'success');
    } catch (err) {
        showToast('Failed to copy response schema', 'error');
        console.error('Fallback copy error:', err);
    }
    document.body.removeChild(textArea);
};

const copyToClipboard = () => {
    const schemaJson = JSON.stringify(formatSchema(props.schema), null, 2);
    
    // Try modern Clipboard API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(schemaJson).then(() => {
            showToast('Response schema copied to clipboard!', 'success');
        }).catch((err) => {
            showToast('Failed to copy response schema', 'error');
            console.error('Clipboard API error:', err);
            fallbackCopyToClipboard(schemaJson);
        });
    } else {
        // Fallback for older browsers or non-secure contexts
        fallbackCopyToClipboard(schemaJson);
    }
};
</script>

<style scoped>
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

pre {
  font-family: 'Courier New', monospace;
  font-size: 0.85em;
  line-height: 1.5;
  background-color: #1e1e1e;
  color: #ffffff;
  transition: background-color 0.2s ease;
}

pre:hover {
  background-color: #2a2a2a;
}

.copy-btn {
  background-color: #0066cc;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9em;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}

.copy-btn:hover {
  background-color: #0052a3;
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 102, 204, 0.3);
}

.copy-btn:active {
  transform: translateY(0);
  box-shadow: 0 1px 4px rgba(0, 102, 204, 0.2);
}

.pre-copy-icon {
  position: absolute;
  top: 25px;
  right: 25px;
  background: none;
  border: none;
  font-size: 1.2em;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
  z-index: 10;
  line-height: 1;
}

.pre-copy-icon:hover {
  background-color: rgba(0, 102, 204, 0.1);
  transform: scale(1.15);
}

.pre-copy-icon:active {
  transform: scale(0.95);
}
</style>
