// Ontime - shared frontend behaviour (mobile nav, category filter, cart qty controls)

document.addEventListener('DOMContentLoaded', () => {
    // Mobile nav toggle
    const toggle = document.querySelector('.nav-toggle');
    const links = document.querySelector('.nav-links');
    if (toggle && links) {
        toggle.addEventListener('click', () => links.classList.toggle('open'));
    }

    // Category filter chips (client-side show/hide, data-category on product cards)
    const chips = document.querySelectorAll('.filter-chip');
    const cards = document.querySelectorAll('.product-card');
    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            const cat = chip.dataset.category;
            cards.forEach(card => {
                card.style.display = (cat === 'all' || card.dataset.category === cat) ? '' : 'none';
            });
        });
    });

    // Qty steppers (product page + cart page) - expects [data-qty-minus] [data-qty-plus] [data-qty-input]
    document.querySelectorAll('[data-qty-group]').forEach(group => {
        const input = group.querySelector('[data-qty-input]');
        group.querySelector('[data-qty-minus]')?.addEventListener('click', () => {
            input.value = Math.max(1, parseInt(input.value || 1) - 1);
            input.dispatchEvent(new Event('change'));
        });
        group.querySelector('[data-qty-plus]')?.addEventListener('click', () => {
            input.value = parseInt(input.value || 1) + 1;
            input.dispatchEvent(new Event('change'));
        });
    });

    // Delivery type toggle highlight (checkout page)
    document.querySelectorAll('.delivery-toggle label').forEach(label => {
        const input = label.querySelector('input');
        const sync = () => {
            document.querySelectorAll('.delivery-toggle label').forEach(l => l.classList.remove('active'));
            if (input.checked) label.classList.add('active');
        };
        input.addEventListener('change', sync);
        sync();
    });
});
