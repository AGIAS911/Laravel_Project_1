(() => {
    for (const button of document.querySelectorAll('[data-password-reveal]')) {
        const password = document.getElementById(button.getAttribute('aria-controls'));
        if (!password) continue;

        button.hidden = false;
        button.addEventListener('click', () => {
            const showing = password.type === 'password';
            password.type = showing ? 'text' : 'password';
            button.querySelector('[data-password-label]').textContent = showing ? 'Hide' : 'Show';
            button.querySelector('i').className = showing ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
            button.setAttribute('aria-label', (showing ? 'Hide ' : 'Show ') + button.dataset.passwordReveal);
        });
    }

    const driverFields = document.getElementById('driver-fields');
    if (!driverFields) return;

    const accountTypes = driverFields.form.elements.account_type;
    const submitLabel = driverFields.form.querySelector('[data-auth-submit-label]');
    function syncDriverFields() {
        const isDriver = accountTypes.value === 'driver';
        driverFields.hidden = !isDriver;
        driverFields.disabled = !isDriver;
        submitLabel.textContent = isDriver ? 'Submit driver application' : 'Create account';
    }

    for (const input of accountTypes) input.addEventListener('change', syncDriverFields);
    syncDriverFields();
})();
