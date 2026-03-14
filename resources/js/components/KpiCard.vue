<template>
  <article class="kpi-card">
    <div class="kpi-top">
      <div class="icon" :style="{ background: iconBg }">
        <component :is="icon" />
      </div>
      <span class="growth" :class="growthClass">{{ growth }}</span>
    </div>
    <h4>{{ title }}</h4>
    <div class="value">{{ value }}</div>
    <p>{{ description }}</p>
  </article>
</template>

<script setup>
defineProps({
  title: String,
  value: [String, Number],
  description: String,
  growth: String,
  growthClass: { type: String, default: 'neutral' },
  icon: { type: [Object, Function], required: true },
  iconBg: { type: String, default: 'rgba(14, 165, 233, 0.18)' },
});
</script>

<style scoped>
.kpi-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 1rem;
  box-shadow: 0 12px 28px rgba(9, 21, 48, 0.08);
}

.kpi-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.9rem;
}

.icon {
  width: 38px;
  height: 38px;
  border-radius: 0.8rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.icon :deep(svg) {
  width: 18px;
  height: 18px;
}

.growth {
  font-size: 0.76rem;
  font-weight: 700;
  padding: 0.24rem 0.5rem;
  border-radius: 999px;
}

.growth.up {
  background: #dcfce7;
  color: #166534;
}

.growth.down {
  background: #fee2e2;
  color: #b91c1c;
}

.growth.neutral {
  background: #e2e8f0;
  color: #334155;
}

h4 {
  color: var(--muted);
  font-size: 0.85rem;
  margin-bottom: 0.28rem;
}

.value {
  font-size: 1.8rem;
  font-weight: 800;
}

p {
  margin-top: 0.35rem;
  color: var(--muted);
  font-size: 0.78rem;
}
</style>