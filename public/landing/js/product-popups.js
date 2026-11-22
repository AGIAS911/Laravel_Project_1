(() => {
    const confirmedForms = new WeakSet();
    let confirming = false;

    function popupOptions() {
        return {
            buttonsStyling: false,
            customClass: {
                popup: 'product-popup',
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-secondary',
                denyButton: 'btn btn-outline-secondary',
            },
        };
    }

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!form.matches('form[data-popup-title]') || confirmedForms.delete(form) || event.defaultPrevented) return;

        event.preventDefault();
        event.stopImmediatePropagation();
        if (confirming) return;

        const { submitter } = event;
        const { popupTitle, popupText, popupAction } = form.dataset;
        confirming = true;

        let approved;
        try {
            approved = window.Swal
                ? (await Swal.fire({
                    ...popupOptions(),
                    titleText: popupTitle,
                    text: popupText,
                    icon: form.hasAttribute('data-popup-danger') ? 'warning' : 'question',
                    showCancelButton: true,
                    focusCancel: true,
                    confirmButtonText: popupAction || 'Continue',
                    cancelButtonText: 'Cancel',
                })).isConfirmed
                : window.confirm(popupText ? popupTitle + '\n' + popupText : popupTitle);
        } finally {
            confirming = false;
        }

        if (!approved || !form.isConnected) return;
        confirmedForms.add(form);
        form.requestSubmit(submitter ?? undefined);
        confirmedForms.delete(form);
    }, true);

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('a[data-popup-image]');
        if (!link || !window.Swal || event.defaultPrevented || event.button !== 0
            || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

        event.preventDefault();
        await Swal.fire({
            ...popupOptions(),
            titleText: link.dataset.popupImage,
            imageUrl: link.href,
            imageAlt: 'Uploaded payment receipt',
            confirmButtonText: 'Close',
            showCloseButton: true,
        });
    });
})();
