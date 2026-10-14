<script nonce="{{ $cspNonce }}">
    (() => {
        const theme = localStorage.getItem('ifix-theme') || 'dark';
        document.documentElement.classList.toggle('dark', theme === 'dark');
        document.documentElement.classList.toggle('light', theme === 'light');

        window.toggleTheme = () => {
            const dark = !document.documentElement.classList.contains('dark');
            const theme = dark ? 'dark' : 'light';

            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.classList.toggle('light', !dark);
            localStorage.setItem('ifix-theme', theme);
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark } }));
        };
    })();
</script>
