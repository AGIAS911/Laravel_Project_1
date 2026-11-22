const adminMenu = document.querySelector('.admin-sidebar');
const adminMenuToggle = document.querySelector('.admin-menu-toggle');
const compactAdminNav = matchMedia('(max-width: 880px)');

if (adminMenu && adminMenuToggle) {
    const setAdminMenu = (open) => {
        document.body.classList.toggle('admin-menu-open', open);
        adminMenuToggle.setAttribute('aria-expanded', String(open));
        adminMenu.inert = compactAdminNav.matches && !open;
    };

    setAdminMenu(false);
    compactAdminNav.addEventListener('change', () => setAdminMenu(false));

    adminMenuToggle.addEventListener('click', () => {
        const open = adminMenuToggle.getAttribute('aria-expanded') !== 'true';
        setAdminMenu(open);
        if (open) adminMenu.querySelector('a').focus();
        else adminMenuToggle.focus();
    });

    document.addEventListener('keydown', (event) => {
        if (!document.body.classList.contains('admin-menu-open')) return;
        if (event.key === 'Escape') {
            setAdminMenu(false);
            adminMenuToggle.focus();
        }
        if (event.key !== 'Tab') return;

        const links = adminMenu.querySelectorAll('a[href], button:not([disabled])');
        const first = links[0];
        const last = links[links.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!adminMenu.contains(event.target) && !adminMenuToggle.contains(event.target)) {
            const wasOpen = document.body.classList.contains('admin-menu-open');
            setAdminMenu(false);
            if (wasOpen) adminMenuToggle.focus();
        }
    });
}

const openSelectedRule = () => {
    const rule = document.getElementById(location.hash.slice(1));
    if (rule?.matches('details.admin-rule-editor')) {
        rule.open = true;
        rule.scrollIntoView({ block: 'start' });
    }
};

window.addEventListener('hashchange', openSelectedRule);
openSelectedRule();

const driverDecision = document.getElementById('driver-decision');
if (driverDecision) {
    const form = driverDecision.querySelector('form');
    for (const trigger of document.querySelectorAll('[data-driver-dialog]')) {
        trigger.addEventListener('click', () => {
            form.reset();
            form.action = trigger.dataset.url;
            form.elements.status.value = trigger.dataset.status;
            const reject = trigger.dataset.status === 'rejected';
            driverDecision.querySelector('h2').textContent = reject ? 'Reject driver' : 'Suspend driver';
            driverDecision.querySelector('[data-submit-decision]').textContent = reject ? 'Reject' : 'Suspend';
            driverDecision.showModal();
            form.elements.reason.focus();
        });
    }
    driverDecision.querySelector('[data-close-decision]').addEventListener('click', () => driverDecision.close());
}
