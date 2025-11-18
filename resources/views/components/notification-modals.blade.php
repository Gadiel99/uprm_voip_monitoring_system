{{-- Custom Notification Modals Component --}}

{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 bg-gradient">
                <h5 class="modal-title fw-bold" id="confirmModalLabel">
                    <i class="bi bi-question-circle-fill text-warning me-2"></i>
                    <span id="confirmModalTitle">Confirm Action</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p id="confirmModalMessage" class="mb-0 text-dark"></p>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmModalConfirmBtn">
                    <i class="bi bi-check-circle me-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Alert/Notification Modal --}}
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 bg-gradient" id="alertModalHeader">
                <h5 class="modal-title fw-bold" id="alertModalLabel">
                    <i id="alertModalIcon" class="bi bi-info-circle-fill me-2"></i>
                    <span id="alertModalTitle">Notification</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p id="alertModalMessage" class="mb-0 text-dark"></p>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="bi bi-check-circle me-1"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #confirmModal .modal-content,
    #alertModal .modal-content {
        border-radius: 16px;
        overflow: hidden;
    }
    
    #confirmModal .bg-gradient,
    #alertModal .bg-gradient {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    #confirmModal .modal-header,
    #alertModal .modal-header {
        padding: 1.25rem 1.5rem;
    }
    
    #confirmModal .modal-body,
    #alertModal .modal-body {
        padding: 1.5rem;
        white-space: pre-wrap;
        line-height: 1.6;
        font-size: 1rem;
    }
    
    #confirmModal .modal-footer,
    #alertModal .modal-footer {
        padding: 1rem 1.5rem;
    }
    
    #confirmModal .btn-primary,
    #alertModal .btn-primary {
        background-color: #00844b;
        border-color: #00844b;
    }
    
    #confirmModal .btn-primary:hover,
    #alertModal .btn-primary:hover {
        background-color: #006f3f;
        border-color: #006f3f;
    }
    
    #confirmModal .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    #confirmModal .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }
    
    #alertModal .modal-header.alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    }
    
    #alertModal .modal-header.alert-danger,
    #confirmModal .modal-header.alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    }
    
    #alertModal .modal-header.alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    }
    
    #alertModal .modal-header.alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    }
    
    #alertModal .icon-success { color: #28a745; }
    #alertModal .icon-danger { color: #dc3545; }
    #alertModal .icon-warning { color: #ffc107; }
    #alertModal .icon-info { color: #17a2b8; }
</style>

<script>
/**
 * Custom confirmation dialog function
 * Replaces native browser confirm() with Bootstrap modal
 * 
 * @param {string} message - The confirmation message to display
 * @param {string} title - Optional title for the modal (default: "Confirm Action")
 * @returns {Promise<boolean>} - Resolves to true if confirmed, false if cancelled
 */
window.customConfirm = function(message, title = 'Confirm Action') {
    return new Promise((resolve) => {
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        const messageEl = document.getElementById('confirmModalMessage');
        const titleEl = document.getElementById('confirmModalTitle');
        const confirmBtn = document.getElementById('confirmModalConfirmBtn');
        const headerEl = document.querySelector('#confirmModal .modal-header');
        const iconEl = document.querySelector('#confirmModal .modal-title i');
        
        messageEl.textContent = message;
        titleEl.textContent = title;
        
        // Detect if this is a delete/remove action
        const isDeleteAction = message.toLowerCase().includes('delete') || 
                               message.toLowerCase().includes('remove') || 
                               message.toLowerCase().includes('clear') ||
                               message.includes('🗑️') ||
                               title.toLowerCase().includes('delete') ||
                               title.toLowerCase().includes('remove') ||
                               title.toLowerCase().includes('clear');
        
        // Reset classes
        headerEl.className = 'modal-header border-0 bg-gradient';
        iconEl.className = 'bi me-2';
        confirmBtn.className = 'btn';
        
        if (isDeleteAction) {
            // Red/danger styling for delete actions
            headerEl.classList.add('alert-danger');
            iconEl.classList.add('bi-exclamation-triangle-fill', 'text-danger');
            confirmBtn.classList.add('btn-danger');
        } else {
            // Default warning styling
            iconEl.classList.add('bi-question-circle-fill', 'text-warning');
            confirmBtn.classList.add('btn-primary');
        }
        
        // Clone button to remove old event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        // Handle confirm
        newConfirmBtn.addEventListener('click', () => {
            modal.hide();
            resolve(true);
        });
        
        // Handle cancel/close
        const handleCancel = () => {
            resolve(false);
        };
        
        document.getElementById('confirmModal').addEventListener('hidden.bs.modal', handleCancel, { once: true });
        
        modal.show();
    });
};

/**
 * Custom alert dialog function
 * Replaces native browser alert() with Bootstrap modal
 * 
 * @param {string} message - The alert message to display
 * @param {string} title - Optional title for the modal (default: "Notification")
 * @param {string} type - Type of alert: 'success', 'error', 'warning', 'info' (default: 'info')
 * @returns {Promise<void>} - Resolves when user closes the modal
 */
window.customAlert = function(message, title = 'Notification', type = 'info') {
    return new Promise((resolve) => {
        const modal = new bootstrap.Modal(document.getElementById('alertModal'));
        const messageEl = document.getElementById('alertModalMessage');
        const titleEl = document.getElementById('alertModalTitle');
        const iconEl = document.getElementById('alertModalIcon');
        const headerEl = document.getElementById('alertModalHeader');
        
        // Detect type from message if not specified
        if (type === 'info') {
            if (message.includes('✅') || message.toLowerCase().includes('success')) {
                type = 'success';
            } else if (message.includes('❌') || message.toLowerCase().includes('error') || message.toLowerCase().includes('failed')) {
                type = 'error';
            } else if (message.includes('⚠️') || message.toLowerCase().includes('warning')) {
                type = 'warning';
            }
        }
        
        // Set message and title
        messageEl.textContent = message;
        titleEl.textContent = title;
        
        // Reset header classes
        headerEl.className = 'modal-header border-0 bg-gradient';
        iconEl.className = 'bi me-2';
        
        // Set icon and style based on type
        switch(type) {
            case 'success':
                iconEl.classList.add('bi-check-circle-fill', 'icon-success');
                headerEl.classList.add('alert-success');
                break;
            case 'error':
                iconEl.classList.add('bi-x-circle-fill', 'icon-danger');
                headerEl.classList.add('alert-danger');
                break;
            case 'warning':
                iconEl.classList.add('bi-exclamation-triangle-fill', 'icon-warning');
                headerEl.classList.add('alert-warning');
                break;
            case 'info':
            default:
                iconEl.classList.add('bi-info-circle-fill', 'icon-info');
                headerEl.classList.add('alert-info');
                break;
        }
        
        // Handle close
        const handleClose = () => {
            resolve();
        };
        
        document.getElementById('alertModal').addEventListener('hidden.bs.modal', handleClose, { once: true });
        
        modal.show();
    });
};

// Override native alert and confirm (optional - can be removed if you prefer explicit usage)
// window.alert = window.customAlert;
// window.confirm = window.customConfirm;
</script>
