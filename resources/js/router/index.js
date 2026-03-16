import { createRouter, createWebHistory } from 'vue-router';
// Note: Admin panel is now handled by Filament (PHP-based), not Vue
// These Vue pages are deprecated and no longer used

const routes = [
  {
    path: '/',
    redirect: '/admin',
  },
  {
    path: '/admin',
    redirect: '/admin/dashboard',
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/admin',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
});

export default router;
