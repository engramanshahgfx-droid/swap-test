<template>
  <div class="portal-shell">
    <SidebarNav :collapsed="ui.sidebarCollapsed" @toggle="ui.toggleSidebar" />
    <div class="portal-main" :class="{ 'portal-main-expanded': ui.sidebarCollapsed }">
      <TopHeader />
      <main class="portal-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import SidebarNav from '@/components/SidebarNav.vue';
import TopHeader from '@/components/TopHeader.vue';
import { useUiStore } from '@/stores/uiStore';

const ui = useUiStore();
const { locale } = useI18n();

watch(
  () => locale.value,
  (newLocale) => {
    const isRtl = newLocale === 'ar';
    document.documentElement.lang = newLocale;
    document.documentElement.dir = isRtl ? 'rtl' : 'ltr';
    ui.isRtl = isRtl;
  },
  { immediate: true },
);
</script>

<style scoped>
.portal-shell {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 280px 1fr;
}

.portal-main {
  min-width: 0;
}

.portal-content {
  padding: 1.2rem 1.4rem 1.5rem;
}

.portal-main-expanded {
  margin-inline-start: -84px;
}

@media (max-width: 1080px) {
  .portal-shell {
    grid-template-columns: 1fr;
  }

  .portal-main-expanded {
    margin-inline-start: 0;
  }
}
</style>