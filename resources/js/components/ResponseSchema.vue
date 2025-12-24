<template>
  <div v-if="schema && schema.schema && Object.keys(schema.schema).length > 0" class="section">
    <div class="section-title">Response Schema</div>
    <div style="background: white; border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px">
      <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto">{{ JSON.stringify(formatSchema(schema.schema), null, 2) }}</pre>
    </div>
  </div>
</template>

<script setup>
defineProps({
  schema: {
    type: Object,
    default: null
  }
})

const formatSchema = (schema) => {
    const obj = {};

    Object.keys(schema).forEach(key => {
        const field = schema[key];

        if (
            typeof field === 'object' &&
            field !== null &&
            field.schema &&
            typeof field.schema === 'object'
        ) {
            // Directly print schema under current parent
            obj[key] = formatSchema(field.schema);
        }
        else if (typeof field === 'object' && field !== null) {
            obj[key] = formatSchema(field);
        }
        else {
            obj[key] = field;
        }
    });

    return obj;
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
}
</style>
