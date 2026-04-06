import { ref, watch } from 'vue';

const isDark = ref(localStorage.getItem('darkMode') === 'true');

// Apply on load
if (isDark.value) {
    document.documentElement.classList.add('dark');
}

export function useDarkMode() {
    watch(isDark, (val) => {
        localStorage.setItem('darkMode', String(val));
        document.documentElement.classList.toggle('dark', val);
    });

    function toggle() {
        isDark.value = !isDark.value;
    }

    return { isDark, toggle };
}
