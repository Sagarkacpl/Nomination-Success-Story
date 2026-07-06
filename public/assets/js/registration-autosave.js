// public/assets/js/registration-autosave.js
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('nominationForm');
    if (!form) return;

    const csrfToken = document.getElementById('csrf_token').value;

    const AJAX_URL = 'ajax/save-field';
    const FINAL_URL = 'ajax/final-submit';

    function saveField(fieldName, value) {
        const body = new URLSearchParams();
        body.append('csrf_token', csrfToken);
        body.append('field', fieldName);
        body.append('value', value);

        fetch(AJAX_URL, {
            method: 'POST',
            body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    console.warn('Autosave failed:', data.message);
                }
            })
            .catch(err => console.error('Autosave network error:', err));
    }

    // ---- Har field pe autosave (debounced for text inputs) ----
    let debounceTimer;
    function debouncedSave(fieldName, value) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => saveField(fieldName, value), 800);
    }

    form.querySelectorAll('input, textarea, select').forEach(el => {
        // Achievement rows apne alag AJAX flow (registration-docs.js) se save hote hain
        if (el.classList.contains('achievement-text-input') || el.classList.contains('achievement-doc-input')) {
            return;
        }

        if (!el.name) {
            return;
        }

        // Nature of Engagement (checkbox group)
        if (el.name === 'engagement[]') {
            el.addEventListener('change', () => {
                const checked = Array.from(form.querySelectorAll('input[name="engagement[]"]:checked'))
                    .map(c => c.value);
                saveField('engagement', checked.join(','));

                const otherInput = document.getElementById('engagement_other_text');
                const otherChecked = document.getElementById('eng_other').checked;

                otherInput.classList.toggle('d-none', !otherChecked);

                if (otherChecked) {
                    otherInput.setAttribute('required', 'required');
                } else {
                    otherInput.removeAttribute('required');
                    otherInput.value = '';
                    saveField('engagement_other_text', '');
                }
            });
            return;
        }

        // Declarations (single checkboxes)
        if (['declaration_true', 'declaration_original', 'declaration_no_guarantee'].includes(el.name)) {
            el.addEventListener('change', () => {
                saveField(el.name, el.checked ? 1 : 0);
            });
            return;
        }

        // Dropdowns (State / City)
        if (el.tagName === 'SELECT') {
            el.addEventListener('change', () => saveField(el.name, el.value));
            return;
        }

        // "Other" specify text
        if (el.name === 'engagement_other_text') {
            el.addEventListener('blur', () => saveField('engagement_other_text', el.value));
            return;
        }

        // Text / email / url / textarea fields
        if (el.type === 'text' || el.type === 'email' || el.type === 'url' || el.tagName === 'TEXTAREA') {
            el.addEventListener('input', () => debouncedSave(el.name, el.value));
            el.addEventListener('blur', () => saveField(el.name, el.value));
        }
    });

    if (window.isFinalSubmitted) {
        // Autosave listeners bind hi mat karo
        return; // ya function ko early-exit karwao
    }

    // ---- Final submit ----
    form.addEventListener('submit', function (event) {
    event.preventDefault();

    const anyEngagementChecked = form.querySelectorAll('input[name="engagement[]"]:checked').length > 0;
    const allDeclarationsChecked = ['decl1', 'decl2', 'decl3']
        .every(id => document.getElementById(id).checked);

    form.querySelector('.engagement-error').style.display = anyEngagementChecked ? 'none' : 'block';
    form.querySelector('.declaration-error').style.display = allDeclarationsChecked ? 'none' : 'block';

    const otherInput = document.getElementById('engagement_other_text');
    const otherWasHidden = otherInput.classList.contains('d-none');
    if (otherWasHidden) otherInput.removeAttribute('required');

    const nativeValid = form.checkValidity();

    if (otherWasHidden) otherInput.setAttribute('required', 'required');

    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    if (!nativeValid) {
        const invalidEls = Array.from(form.querySelectorAll(':invalid'));
        invalidEls.forEach(el => el.classList.add('is-invalid'));
        if (invalidEls.length > 0) {
            invalidEls[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            invalidEls[0].focus();
        }
    }

    if (!nativeValid || !anyEngagementChecked || !allDeclarationsChecked) {
        form.classList.add('was-validated');
        return;
    }

    const body = new URLSearchParams();
    body.append('csrf_token', csrfToken);

    fetch(FINAL_URL, {
        method: 'POST',
        body,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const successMsg = document.getElementById('formSuccessMsg');
                successMsg.textContent = 'Nomination submitted successfully!';
                successMsg.classList.remove('d-none');
                window.scrollTo({ top: successMsg.offsetTop - 100, behavior: 'smooth' });
            } else {
                alert(data.message || 'Submission failed. Please try again.');
            }
        })
        .catch(() => alert('Network error. Please try again.'));

    form.classList.add('was-validated');
    });
});