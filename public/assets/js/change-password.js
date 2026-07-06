document.addEventListener('DOMContentLoaded', function () {
    const form        = document.getElementById('changePasswordForm');
    const alertBox     = document.getElementById('pwdAlert');
    const submitBtn    = document.getElementById('pwdSubmitBtn');
    const newPwdInput  = document.getElementById('new_password');
    const strengthFill = document.getElementById('pwdStrengthFill');

    // ---- Eye toggle for all password fields ----
    document.querySelectorAll('.toggle-password').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const targetId = toggle.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = toggle.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    // ---- Live strength meter (visual guidance only — real checks happen server-side) ----
    newPwdInput.addEventListener('input', function () {
        const val = newPwdInput.value;
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[a-z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[\W_]/.test(val)) score++;

        const pct = (score / 5) * 100;
        strengthFill.style.width = pct + '%';

        if (score <= 2) {
            strengthFill.style.background = '#dc3545'; // weak
        } else if (score <= 4) {
            strengthFill.style.background = '#ffc107'; // medium
        } else {
            strengthFill.style.background = '#198754'; // strong
        }
    });

    function showAlert(type, message) {
        alertBox.className = 'alert alert-' + type;
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        alertBox.classList.add('d-none');

        const currentPassword = document.getElementById('current_password').value;
        const newPassword     = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const csrfToken       = document.getElementById('csrf_token').value;

        if (!currentPassword || !newPassword || !confirmPassword) {
            showAlert('danger', 'All fields are required.');
            return;
        }
        if (newPassword !== confirmPassword) {
            showAlert('danger', 'New password and confirm password do not match.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

        const body = new URLSearchParams({
            csrf_token: csrfToken,
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword,
        });

        fetch('change-password-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString(),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>Update Password';

                if (data.success) {
                    showAlert('success', data.message);
                    form.reset();
                    strengthFill.style.width = '0%';
                } else {
                    showAlert('danger', data.message || 'Something went wrong. Please try again.');
                }
            })
            .catch(function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>Update Password';
                showAlert('danger', 'Network error. Please check your connection and try again.');
            });
    });
});