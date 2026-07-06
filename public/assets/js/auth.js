// ==================== Password show/hide toggle ====================
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const icon = btn.querySelector('i');
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
});

// ==================== Generic Bootstrap validation (login/register forms) ====================
(() => {
    'use strict';
    document.querySelectorAll('form').forEach(form => {
        if (form.id === 'nominationForm') return; // has its own custom handler below
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// ==================== Sidebar toggle (mobile) ====================
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('dashboardSidebar');
if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('show'));
}

// ==================== Nomination form ====================
const nominationForm = document.getElementById('nominationForm');
if (nominationForm) {
    const engagementChecks = nominationForm.querySelectorAll('input[name="engagement[]"]');
    const engagementError = nominationForm.querySelector('.engagement-error');
    const otherTextInput = document.getElementById('engagement_other_text');
    const otherCheckbox = document.getElementById('eng_other');

    const declarationChecks = nominationForm.querySelectorAll(
        '#decl1, #decl2, #decl3'
    );
    const declarationError = nominationForm.querySelector('.declaration-error');

    // "Other" select/deselect hone par extra text box dikhao/chhupao
    otherCheckbox.addEventListener('change', () => {
        otherTextInput.classList.toggle('d-none', !otherCheckbox.checked);
        if (otherCheckbox.checked) {
            otherTextInput.setAttribute('required', 'required');
            otherTextInput.focus();
        } else {
            otherTextInput.removeAttribute('required');
            otherTextInput.value = '';
        }
    });

    // Declaration card highlight on check
    declarationChecks.forEach(chk => {
        chk.addEventListener('change', () => {
            chk.closest('.declaration-item').classList.toggle('checked', chk.checked);
        });
    });

    nominationForm.addEventListener('submit', function (event) {
        const anyEngagementChecked = Array.from(engagementChecks).some(c => c.checked);
        const allDeclarationsChecked = Array.from(declarationChecks).every(c => c.checked);

        engagementError.style.display = anyEngagementChecked ? 'none' : 'block';
        declarationError.style.display = allDeclarationsChecked ? 'none' : 'block';

        if (!this.checkValidity() || !anyEngagementChecked || !allDeclarationsChecked) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            event.preventDefault(); // abhi backend nahi hai, isliye actual submit rok diya
            const successMsg = document.getElementById('formSuccessMsg');
            successMsg.classList.remove('d-none');
            window.scrollTo({ top: successMsg.offsetTop - 100, behavior: 'smooth' });
        }

        this.classList.add('was-validated');
    });
}

