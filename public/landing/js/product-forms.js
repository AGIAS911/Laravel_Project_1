document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post' || form.matches('[data-workflow-form]')) return;

    if (form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
    }

    queueMicrotask(() => {
        if (event.defaultPrevented) return;
        form.dataset.submitting = 'true';
        if (event.submitter) event.submitter.setAttribute('aria-busy', 'true');
    });
});

window.addEventListener('pageshow', () => {
    for (const form of document.querySelectorAll('form[data-submitting]')) {
        delete form.dataset.submitting;
        form.querySelector('[aria-busy="true"]')?.removeAttribute('aria-busy');
    }
});

for (const region of document.querySelectorAll('.table-responsive, .table-wrap, .admin-table')) {
    const table = region.querySelector('table');
    if (!table || region.hasAttribute('tabindex')) continue;

    const updateScrollAccess = () => {
        if (region.clientWidth && region.scrollWidth > region.clientWidth + 2) {
            region.tabIndex = 0;
            region.setAttribute('role', 'region');
            region.setAttribute('aria-label', table.caption?.textContent.trim() || table.getAttribute('aria-label') || 'Scrollable table');
        } else {
            region.removeAttribute('tabindex');
            region.removeAttribute('role');
            region.removeAttribute('aria-label');
        }
    };
    const observer = new ResizeObserver(updateScrollAccess);
    observer.observe(region);
    observer.observe(table);
}

for (const [index, field] of [...document.querySelectorAll('input.is-invalid, select.is-invalid, textarea.is-invalid')].entries()) {
    field.setAttribute('aria-invalid', 'true');
    const error = [...field.parentElement.children].find(element => element.matches('.invalid-feedback'));
    if (!error) continue;

    error.id ||= 'product-field-error-' + index;
    const descriptions = new Set((field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
    descriptions.add(error.id);
    field.setAttribute('aria-describedby', [...descriptions].join(' '));
}
