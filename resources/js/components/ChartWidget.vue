<template>
  <section class="chart-card">
    <div class="head">
      <h3>{{ title }}</h3>
      <div class="filters" v-if="filters?.length">
        <button
          v-for="f in filters"
          :key="f"
          :class="['filter', { active: modelValue === f }]"
          @click="$emit('update:modelValue', f)"
        >
          {{ f }}
        </button>
      </div>
    </div>
    <apexchart :height="height" :type="type" :options="options" :series="series" />
  </section>
</template>

<script setup>
defineProps({
  title: String,
  type: { type: String, default: 'bar' },
  options: { type: Object, required: true },
  series: { type: Array, required: true },
  filters: { type: Array, default: () => [] },
  modelValue: { type: String, default: '' },
  height: { type: Number, default: 300 },
});

defineEmits(['update:modelValue']);
</script>

<style scoped>
.chart-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  box-shadow: 0 12px 28px rgba(9, 21, 48, 0.08);
  padding: 1rem;
}

.head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.8rem;
}

h3 {
  font-size: 1rem;
}

.filters {
  display: flex;
  gap: 0.3rem;
}

.filter {
  border: 1px solid var(--border);
  background: transparent;
  color: var(--muted);
  border-radius: 999px;
  padding: 0.25rem 0.6rem;
  cursor: pointer;
  font-size: 0.75rem;
}

.filter.active {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}
</style>