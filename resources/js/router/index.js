import { createRouter, createWebHistory } from 'vue-router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import DashboardPage from '@/pages/admin/DashboardPage.vue';
import UsersManagementPage from '@/pages/admin/UsersManagementPage.vue';
import UserProfilePage from '@/pages/admin/UserProfilePage.vue';
import FlightSwapsPage from '@/pages/admin/FlightSwapsPage.vue';
import VacationSwapsPage from '@/pages/admin/VacationSwapsPage.vue';
import ReportsModerationPage from '@/pages/admin/ReportsModerationPage.vue';
import ReportDetailPage from '@/pages/admin/ReportDetailPage.vue';
import AirlinesConfigPage from '@/pages/admin/AirlinesConfigPage.vue';
import PositionsConfigPage from '@/pages/admin/PositionsConfigPage.vue';
import SupportChatPage from '@/pages/admin/SupportChatPage.vue';
import AnalyticsPage from '@/pages/admin/AnalyticsPage.vue';
import SettingsPage from '@/pages/admin/SettingsPage.vue';

const routes = [
  {
    path: '/',
    redirect: '/admin/dashboard',
  },
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      { path: 'dashboard', name: 'admin.dashboard', component: DashboardPage },
      { path: 'users', name: 'admin.users', component: UsersManagementPage },
      { path: 'users/:id', name: 'admin.user.profile', component: UserProfilePage, props: true },
      { path: 'swap/flight', name: 'admin.swap.flight', component: FlightSwapsPage },
      { path: 'swap/vacation', name: 'admin.swap.vacation', component: VacationSwapsPage },
      { path: 'reports', name: 'admin.reports', component: ReportsModerationPage },
      { path: 'reports/:id', name: 'admin.report.detail', component: ReportDetailPage, props: true },
      { path: 'configuration/airlines', name: 'admin.config.airlines', component: AirlinesConfigPage },
      { path: 'configuration/positions', name: 'admin.config.positions', component: PositionsConfigPage },
      { path: 'support', name: 'admin.support', component: SupportChatPage },
      { path: 'analytics', name: 'admin.analytics', component: AnalyticsPage },
      { path: 'settings', name: 'admin.settings', component: SettingsPage },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/admin/dashboard',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
});

export default router;
