<template>
  <section class="table-card">
    <div class="table-head">
      <h3>{{ title }}</h3>
      <div class="table-tools">
        <input v-model="searchValue" type="search" :placeholder="searchPlaceholder" />
        <button @click="emit('export')">{{ $t('common.exportCsv') }}</button>
      </div>
    </div>

    <div v-if="loading" class="skeleton-wrap">
      <div class="skeleton" v-for="i in 6" :key="i"></div>
    </div>

    <div v-else-if="error" class="error-box">{{ error }}</div>

    <table v-else>
      <thead>
        <tr>
          <th v-for="col in columns" :key="col.key">{{ col.label }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in pagedRows" :key="row.id">
          <td v-for="col in columns" :key="col.key">
            <slot :name="`cell-${col.key}`" :row="row">{{ row[col.key] }}</slot>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="pager" v-if="!loading && !error">
      <button :disabled="page === 1" @click="page--">Prev</button>
      <span>{{ page }} / {{ totalPages }}</span>
      <button :disabled="page >= totalPages" @click="page++">Next</button>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  title: { type: String, required: true },
  rows: { type: Array, default: () => [] },
  columns: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  error: { type: String, default: '' },
  searchPlaceholder: { type: String, default: 'Search...' },
  pageSize: { type: Number, default: 8 },
});

const emit = defineEmits(['export', 'search']);
const page = ref(1);
const searchValue = ref('');

watch(searchValue, (value) => {
  page.value = 1;
  emit('search', value);
});

const totalPages = computed(() => Math.max(1, Math.ceil(props.rows.length / props.pageSize)));

const pagedRows = computed(() => {
  const start = (page.value - 1) * props.pageSize;
  return props.rows.slice(start, start + props.pageSize);
});

watch(totalPages, (value) => {
  if (page.value > value) page.value = value;
});
</script>

<style scoped>
.table-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  box-shadow: 0 12px 28px rgba(9, 21, 48, 0.08);
  padding: 1rem;
}

.table-head {
  display: flex;
  justify-content: space-between;
  gap: 0.8rem;
  margin-bottom: 0.8rem;
}

.table-tools {
  display: flex;
  gap: 0.5rem;
}

input,
button {
  border: 1px solid var(--border);
  border-radius: 0.65rem;
  padding: 0.42rem 0.65rem;
  background: var(--card);
  color: var(--ink);
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  text-align: left;
  padding: 0.65rem 0.45rem;
  border-bottom: 1px solid var(--border);
  font-size: 0.85rem;
}

th {
  color: var(--muted);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.pager {
  margin-top: 0.75rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.4rem;
  align-items: center;
}

.skeleton-wrap {
  display: grid;
  gap: 0.5rem;
}

.skeleton {
  height: 42px;
  border-radius: 0.7rem;
  background: linear-gradient(90deg, #e2e8f0 10%, #f8fafc 40%, #e2e8f0 70%);
  background-size: 200% 100%;
  animation: wave 1.2s linear infinite;
}

.error-box {
  border-radius: 0.8rem;
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #b91c1c;
  padding: 0.7rem;
}

@keyframes wave {
  to {
    background-position: -200% 0;
  }
}
</style>