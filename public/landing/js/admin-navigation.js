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
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.classList.contains('admin-menu-open')) {
            setAdminMenu(false);
            adminMenuToggle.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!adminMenu.contains(event.target) && !adminMenuToggle.contains(event.target)) {
            setAdminMenu(false);
        }
    });
}

const openSelectedRule = () => {
    const rule = document.getElementById(location.hash.slice(1));
    if (rule?.matches('.admin-rule-editor')) {
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
