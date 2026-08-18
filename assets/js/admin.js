// Admin Dashboard JavaScript - admin.js

document.addEventListener('DOMContentLoaded', () => {
    // Modal Helpers
    const modalOpenBtns = document.querySelectorAll('[data-modal-target]');
    const modalCloseBtns = document.querySelectorAll('[data-modal-close]');

    modalOpenBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
            }
        });
    });

    modalCloseBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.admin-modal');
            if (modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Close modal on background click
    document.querySelectorAll('.admin-modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Thermal Receipt Print Trigger
    const printReceiptBtn = document.getElementById('printReceiptBtn');
    if (printReceiptBtn) {
        printReceiptBtn.addEventListener('click', () => {
            window.print();
        });
    }
});
