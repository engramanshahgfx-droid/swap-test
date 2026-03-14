import { createI18n } from 'vue-i18n';
import en from './locales/en';
import ar from './locales/ar';

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  fallbackLocale: 'en',
  messages: { en, ar },
});

export default i18n;