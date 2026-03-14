<template>
  <header class="header">
    <div class="left">
      <div class="search-box">
        <Search class="icon" />
        <input v-model="ui.globalSearch" :placeholder="$t('app.search')" />
      </div>
    </div>
    <div class="right">
      <button class="btn" @click="ui.toggleLanguage">{{ ui.language.toUpperCase() }}</button>
      <button class="icon-btn" :title="$t('app.notifications')">
        <Bell class="icon" />
        <span class="badge">{{ ui.unreadNotifications }}</span>
      </button>
      <button class="btn" @click="ui.toggleDarkMode">{{ ui.darkMode ? 'Light' : 'Dark' }}</button>
      <div class="profile">
        <img src="https://i.pravatar.cc/100?img=13" alt="admin" />
        <div>
          <div class="name">Captain Admin</div>
          <div class="email">ops.admin@crewswap.app</div>
        </div>
      </div>
      <button class="logout">{{ $t('app.logout') }}</button>
    </div>
  </header>
</template>

<script setup>
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Bell, Search } from 'lucide-vue-next';
import { useUiStore } from '@/stores/uiStore';

const ui = useUiStore();
const { locale } = useI18n();

watch(
  () => ui.language,
  (value) => {
    locale.value = value;
  },
  { immediate: true },
);

watch(
  () => ui.darkMode,
  (isDark) => {
    document.body.classList.toggle('dark', isDark);
  },
  { immediate: true },
);
</script>

<style scoped>
.header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.8rem 1.2rem;
}

.left {
  flex: 1;
}

.search-box {
  max-width: 440px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: 1px solid var(--border);
  border-radius: 0.8rem;
  padding: 0.55rem 0.7rem;
  background: var(--card);
}

.search-box input {
  width: 100%;
  border: 0;
  outline: none;
  background: transparent;
  color: var(--ink);
}

.right {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.btn,
.icon-btn,
.logout {
  border: 1px solid var(--border);
  background: var(--card);
  border-radius: 0.75rem;
  padding: 0.5rem 0.7rem;
  cursor: pointer;
  color: var(--ink);
}

.icon-btn {
  position: relative;
}

.badge {
  position: absolute;
  top: -6px;
  inset-inline-end: -6px;
  background: var(--danger);
  color: #fff;
  border-radius: 999px;
  min-width: 18px;
  height: 18px;
  font-size: 11px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.profile {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 0.35rem 0.5rem;
}

.profile img {
  width: 34px;
  height: 34px;
  border-radius: 999px;
}

.name {
  font-size: 0.82rem;
  font-weight: 700;
}

.email {
  font-size: 0.72rem;
  color: var(--muted);
}

.logout {
  background: #fee2e2;
  color: #b91c1c;
  border-color: #fecaca;
}

.icon {
  width: 18px;
  height: 18px;
}

@media (max-width: 1080px) {
  .header {
    flex-direction: column;
    align-items: stretch;
  }

  .right {
    flex-wrap: wrap;
  }
}
</style>