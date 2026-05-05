<template>
  <aside class="sidebar" :class="{ collapsed }">
    <div class="brand">
      <div class="brand-dot"></div>
      <div v-if="!collapsed" class="brand-text">flightSwap  Administration Portal</div>
    </div>

    <nav class="menu">
      <router-link class="menu-item" to="/admin/dashboard"><LayoutDashboard class="icon" /><span v-if="!collapsed">{{ $t('nav.dashboard') }}</span></router-link>
      <router-link class="menu-item" to="/admin/users"><Users class="icon" /><span v-if="!collapsed">{{ $t('nav.users') }}</span></router-link>

      <div class="menu-label" v-if="!collapsed">{{ $t('nav.swapOps') }}</div>
      <router-link class="menu-item menu-child" to="/admin/swap/flight"><PlaneTakeoff class="icon" /><span v-if="!collapsed">{{ $t('nav.flightSwaps') }}</span></router-link>
      <router-link class="menu-item menu-child" to="/admin/swap/vacation"><CalendarClock class="icon" /><span v-if="!collapsed">{{ $t('nav.vacationSwaps') }}</span></router-link>

      <router-link class="menu-item" to="/admin/reports"><ShieldAlert class="icon" /><span v-if="!collapsed">{{ $t('nav.reports') }}</span></router-link>

      <div class="menu-label" v-if="!collapsed">{{ $t('nav.config') }}</div>
      <router-link class="menu-item menu-child" to="/admin/configuration/airlines"><Building2 class="icon" /><span v-if="!collapsed">{{ $t('nav.airlines') }}</span></router-link>
      <router-link class="menu-item menu-child" to="/admin/configuration/positions"><BriefcaseBusiness class="icon" /><span v-if="!collapsed">{{ $t('nav.positions') }}</span></router-link>

      <router-link class="menu-item" to="/admin/support"><MessageSquareText class="icon" /><span v-if="!collapsed">{{ $t('nav.support') }}</span></router-link>
      <router-link class="menu-item" to="/admin/analytics"><ChartNoAxesCombined class="icon" /><span v-if="!collapsed">{{ $t('nav.analytics') }}</span></router-link>
      <router-link class="menu-item" to="/admin/settings"><Settings class="icon" /><span v-if="!collapsed">{{ $t('nav.settings') }}</span></router-link>
    </nav>

    <button class="collapse-btn" @click="$emit('toggle')">
      <PanelLeftClose class="icon" />
      <span v-if="!collapsed">Collapse</span>
    </button>
  </aside>
</template>

<script setup>
import {
  LayoutDashboard,
  Users,
  PlaneTakeoff,
  CalendarClock,
  ShieldAlert,
  Building2,
  BriefcaseBusiness,
  MessageSquareText,
  ChartNoAxesCombined,
  Settings,
  PanelLeftClose,
} from 'lucide-vue-next';

defineProps({
  collapsed: { type: Boolean, default: false },
});

defineEmits(['toggle']);
</script>

<style scoped>
.sidebar {
  position: sticky;
  top: 0;
  height: 100vh;
  background: linear-gradient(180deg, #072055 0%, #0a2a66 60%, #10387e 100%);
  color: #dce6fb;
  border-inline-end: 1px solid rgba(148, 181, 242, 0.2);
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1rem 0.8rem;
  transition: width 0.2s ease;
}

.collapsed {
  width: 96px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.5rem;
}

.brand-dot {
  width: 18px;
  height: 18px;
  border-radius: 999px;
  background: linear-gradient(130deg, #0ea5e9, #7dd3fc);
  box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
}

.brand-text {
  font-size: 0.88rem;
  line-height: 1.2;
  font-weight: 700;
}

.menu {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  flex: 1;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  color: #dce6fb;
  text-decoration: none;
  font-size: 0.9rem;
  padding: 0.65rem;
  border-radius: 0.7rem;
}

.menu-item:hover,
.router-link-active.menu-item {
  background: rgba(14, 165, 233, 0.22);
}

.menu-label {
  margin-top: 0.5rem;
  padding-inline: 0.65rem;
  font-size: 0.75rem;
  opacity: 0.8;
}

.menu-child {
  padding-inline-start: 1rem;
}

.collapse-btn {
  border: 0;
  background: rgba(255, 255, 255, 0.12);
  color: #e2ecff;
  padding: 0.6rem;
  border-radius: 0.7rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.icon {
  width: 18px;
  height: 18px;
}

@media (max-width: 1080px) {
  .sidebar {
    position: static;
    height: auto;
  }
}
</style>
