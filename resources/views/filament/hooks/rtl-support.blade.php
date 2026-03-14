<script>
    document.addEventListener('DOMContentLoaded', function() {
        const locale = '{{ app()->getLocale() }}';
        const htmlElement = document.documentElement;
        
        if (locale === 'ar') {
            htmlElement.setAttribute('dir', 'rtl');
            htmlElement.setAttribute('lang', 'ar');
        } else {
            htmlElement.setAttribute('dir', 'ltr');
            htmlElement.setAttribute('lang', 'en');
        }
    });
</script>
