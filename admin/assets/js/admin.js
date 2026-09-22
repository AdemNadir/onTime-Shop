// Ontime Admin - small UX helpers
document.addEventListener('DOMContentLoaded', () => {
    // Live-filter any table with a .search-input + [data-search-target] table
    document.querySelectorAll('[data-search-input]').forEach(input => {
        const table = document.querySelector(input.dataset.searchInput);
        if (!table) return;
        input.addEventListener('input', () => {
            const q = input.value.toLowerCase();
            table.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    });

    // Image upload live preview
    const fileInput = document.querySelector('[data-image-input]');
    const preview = document.querySelector('[data-image-preview]');
    if (fileInput && preview) {
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => { preview.innerHTML = `<img src="${e.target.result}">`; };
                reader.readAsDataURL(file);
            }
        });
    }

    // Confirm before destructive actions
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('submit', e => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });
});
