
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('open');
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('open');
}

function editRow(row, keys) {
    const form = document.getElementById('crudForm');
    if (!form) return;

    document.getElementById('rowId').value = row.id;
    document.getElementById('modalTitle').textContent = 'Ubah Data Dosen';

    keys.forEach(function (key) {
        const input = form.elements[key];

        if (input) {
            input.value = row[key] ?? '';
        }
    });

    openModal('addModal');
}

function filterTable(input, id) {
    const q = input.value.toLowerCase();
    const table = document.getElementById(id);

    if (!table) return;

    table.querySelectorAll('tbody tr').forEach(function (row) {
        row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
}

function updateSks() {
    let total = 0;

    document.querySelectorAll('input[name="jadwal_ids[]"]:checked').forEach(function (el) {
        total += Number(el.dataset.sks || 0);
    });

    const out = document.getElementById('totalSks');

    if (out) out.textContent = total;
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.classList.remove('open');
    }
});
