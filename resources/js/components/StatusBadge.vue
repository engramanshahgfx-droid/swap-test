<template>
  <span class="badge" :class="variantClass">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: { type: String, required: true },
  variant: { type: String, default: 'neutral' },
});

const variantClass = computed(() => {
  if (['success', 'warning', 'danger', 'info', 'neutral'].includes(props.variant)) {
    return props.variant;
  }
  return 'neutral';
});
</script>

<style scoped>
.badge {
  border-radius: 999px;
  padding: 0.2rem 0.55rem;
  font-size: 0.73rem;
  font-weight: 700;
}

.success {
  background: #dcfce7;
  color: #166534;
}

.warning {
  background: #ffedd5;
  color: #9a3412;
}

.danger {
  background: #fee2e2;
  color: #991b1b;
}

.info {
  background: #dbeafe;
  color: #1d4ed8;
}

.neutral {
  background: #e2e8f0;
  color: #334155;
}
</style>