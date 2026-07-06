document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('achievementsContainer');
    const addBtn = document.getElementById('addMoreAchievementBtn');
    const csrfToken = document.getElementById('csrf_token')?.value;
    if (!container || !addBtn) return;

    const MAX_ACHIEVEMENTS = 5;
    const debounceTimers = new WeakMap();

    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.achievement-row');
        rows.forEach((row) => {
            const removeBtn = row.querySelector('.remove-achievement-btn');
            removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
        });
    }

    function saveRow(row) {
        const textInput = row.querySelector('.achievement-text-input');
        const docInput = row.querySelector('.achievement-doc-input');
        const text = textInput.value.trim();

        if (text.length < 10) return;

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('achievement_text', text);
        formData.append('achievement_id', row.dataset.achievementId || '0');
        formData.append('sort_order', Array.from(container.children).indexOf(row));

        if (docInput.files[0]) {
            formData.append('achievement_doc', docInput.files[0]);
        }

        fetch('ajax/save-achievement', { method: 'POST', body: formData })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    row.dataset.achievementId = data.achievement_id;
                } else {
                    console.error('Save failed:', data.message);
                }
            })
            .catch((err) => console.error('Achievement save failed:', err));
    }

    function debounceSave(row) {
        clearTimeout(debounceTimers.get(row));
        debounceTimers.set(row, setTimeout(() => saveRow(row), 800));
    }

    function wireRow(row) {
        const textInput = row.querySelector('.achievement-text-input');
        const docInput = row.querySelector('.achievement-doc-input');
        const removeBtn = row.querySelector('.remove-achievement-btn');

        textInput.addEventListener('input', () => debounceSave(row));
        docInput.addEventListener('change', () => saveRow(row));

        removeBtn.addEventListener('click', function () {
            const achievementId = row.dataset.achievementId;
            if (achievementId) {
                const formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('achievement_id', achievementId);
                fetch('ajax/delete-achievement', { method: 'POST', body: formData })
                    .catch((err) => console.error('Delete failed:', err));
            }
            row.remove();
            updateRemoveButtons();
        });
    }

    container.querySelectorAll('.achievement-row').forEach(wireRow);

    addBtn.addEventListener('click', function () {
        const currentCount = container.querySelectorAll('.achievement-row').length;
        if (currentCount >= MAX_ACHIEVEMENTS) {
            alert(`You can add a maximum of ${MAX_ACHIEVEMENTS} achievements.`);
            return;
        }

        const template = container.querySelector('.achievement-row');
        const newRow = template.cloneNode(true);
        newRow.dataset.achievementId = '';
        newRow.querySelector('.achievement-text-input').value = '';
        newRow.querySelector('.achievement-doc-input').value = '';
        const existingFileNote = newRow.querySelector('.existing-file-note');
        if (existingFileNote) existingFileNote.remove();

        container.appendChild(newRow);
        wireRow(newRow);
        updateRemoveButtons();
    });

    updateRemoveButtons();
});