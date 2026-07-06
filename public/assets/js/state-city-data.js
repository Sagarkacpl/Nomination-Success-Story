// Complete India: States/UTs list (cities data ab use nahi ho rahi, State field ke liye rakha hai)
const STATE_LIST = [
    "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh",
    "Goa", "Gujarat", "Haryana", "Himachal Pradesh", "Jharkhand",
    "Karnataka", "Kerala", "Madhya Pradesh", "Maharashtra", "Manipur",
    "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Punjab",
    "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana", "Tripura",
    "Uttar Pradesh", "Uttarakhand", "West Bengal",
    "Delhi (NCT)", "Jammu and Kashmir", "Ladakh", "Chandigarh", "Puducherry",
];

// State dropdown ko populate karo
document.addEventListener('DOMContentLoaded', () => {
    const stateSelect = document.getElementById('state');
    if (!stateSelect) return;

    STATE_LIST.slice().sort().forEach(state => {
        const opt = document.createElement('option');
        opt.value = state;
        opt.textContent = state;
        stateSelect.appendChild(opt);
    });

    // Saved state prefill (page load / refresh ke baad)
    const savedState = stateSelect.dataset.savedState;
    if (savedState) {
        stateSelect.value = savedState;
    }
});