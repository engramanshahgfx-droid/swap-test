import { createRouter, createWebHistory } from 'vue-router';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Dashboard from '@/pages/Dashboard.vue';
import Settings from '@/pages/Settings.vue';
import SwapHistory from '@/pages/SwapHistory.vue';
import AdminSettings from '@/pages/AdminSettings.vue';

const routes = [
  {
    path: '/',
    component: DashboardLayout,
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: Dashboard,
      },
      {
        path: 'settings',
        name: 'Settings',
        component: Settings,
      },
      {
        path: 'swap-operation',
        name: 'SwapOperation',
        component: SwapHistory,
      },
    ],
  },
  {
    path: '/admin',
    component: AdminLayout,
    redirect: '/admin/settings',
    children: [
      {
        path: 'settings',
        name: 'AdminSettings',
        component: AdminSettings,
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
});

export default router;
