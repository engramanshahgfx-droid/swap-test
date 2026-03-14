<template>
  <div v-if="open" class="modal-wrap" @click.self="$emit('close')">
    <div class="modal">
      <header>
        <h3>{{ title }}</h3>
        <button @click="$emit('close')">x</button>
      </header>
      <div class="body">
        <slot />
      </div>
      <footer>
        <button @click="$emit('close')">{{ $t('common.cancel') }}</button>
        <button class="save" @click="$emit('save')">{{ $t('common.save') }}</button>
      </footer>
    </div>
  </div>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
});

defineEmits(['close', 'save']);
</script>

<style scoped>
.modal-wrap {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.55);
  display: grid;
  place-items: center;
  padding: 1rem;
  z-index: 200;
}

.modal {
  width: min(560px, 100%);
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  box-shadow: 0 24px 60px rgba(2, 6, 23, 0.28);
}

header,
footer {
  padding: 0.9rem 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.body {
  padding: 0 1rem 1rem;
}

button {
  border: 1px solid var(--border);
  border-radius: 0.65rem;
  padding: 0.45rem 0.75rem;
  background: var(--card);
  color: var(--ink);
}

.save {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}
</style>