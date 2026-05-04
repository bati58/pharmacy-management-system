document.addEventListener('DOMContentLoaded', function () {
    loadTransfers();
    setupTransferForm();
});

async function loadTransfers() {
    try {
        const transfers = await API.getTransfers();
        const tbody = document.getElementById('transfers-table-body');
        if (!tbody) return;

        tbody.innerHTML = '';
        (transfers.data || []).forEach(transfer => {
            const row = `
                <tr>
                    <td>${escapeHtml(transfer.drug_name)}</td>
                    <td>${transfer.quantity}</td>
                    <td>${transfer.from_location} → ${transfer.to_location}</td>
                    <td>${escapeHtml(transfer.branch_name)}</td>
                    <td>${escapeHtml(transfer.created_by_name)}</td>
                    <td>${formatDateTime(transfer.transfer_date)}</td>
                    <td>${transfer.status}</td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    } catch (error) {
        console.error('Load transfers error:', error);
    }
}

async function setupTransferForm() {
    const form = document.getElementById('transfer-form');
    if (!form) return;

    // Populate drug dropdown
    const drugs = await API.getDrugs();
    const drugSelect = document.getElementById('transfer-drug');
    if (drugSelect) {
        drugSelect.innerHTML = '<option value="">Select drug</option>';
        (drugs.data || []).forEach(drug => {
            drugSelect.innerHTML += `<option value="${drug.id}" data-stock="${drug.stock}">${drug.name} (Stock: ${drug.stock})</option>`;
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const drugId = document.getElementById('transfer-drug').value;
        const quantity = parseInt(document.getElementById('transfer-quantity').value);
        const fromLocation = document.getElementById('transfer-from').value;
        const toLocation = document.getElementById('transfer-to').value;

        if (!drugId || quantity <= 0) {
            showToast('Invalid input', 'error');
            return;
        }

        try {
            await API.createTransfer({
                drug_id: drugId,
                quantity: quantity,
                from_location: fromLocation,
                to_location: toLocation
            });
            showToast('Transfer created successfully');
            form.reset();
            loadTransfers();
        } catch (error) {
            showToast(error.message, 'error');
        }
    });
}