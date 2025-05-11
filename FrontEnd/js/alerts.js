class CustomAlert {
    constructor() {
        this.alertContainer = document.createElement('div');
        this.alertContainer.id = 'alertContainer';
        document.body.appendChild(this.alertContainer);
    }

    show(options = {}) {
        const {
            type = 'info',
            title = '',
            message = '',
            duration = 5000
        } = options;

        // Create alert element
        const alertElement = document.createElement('div');
        alertElement.className = `custom-alert ${type}`;

        // Get icon based on type
        const icon = this.getIcon(type);

        // Create alert content
        alertElement.innerHTML = `
            <div class="icon">
                <i class='bx ${icon}'></i>
            </div>
            <div class="content">
                ${title ? `<div class="title">${title}</div>` : ''}
                <div class="message">${message}</div>
            </div>
            <button class="close-btn">
                <i class='bx bx-x'></i>
            </button>
        `;

        // Add to container
        this.alertContainer.appendChild(alertElement);

        // Show animation
        setTimeout(() => alertElement.classList.add('show'), 10);

        // Setup close button
        const closeBtn = alertElement.querySelector('.close-btn');
        closeBtn.addEventListener('click', () => this.close(alertElement));

        // Auto close after duration
        if (duration > 0) {
            setTimeout(() => this.close(alertElement), duration);
        }

        return alertElement;
    }

    close(alertElement) {
        if (!alertElement) return;
        
        alertElement.classList.remove('show');
        
        setTimeout(() => {
            if (alertElement.parentNode === this.alertContainer) {
                this.alertContainer.removeChild(alertElement);
            }
        }, 300);
    }

    getIcon(type) {
        switch (type) {
            case 'success':
                return 'bx-check-circle';
            case 'error':
                return 'bx-x-circle';
            case 'warning':
                return 'bx-error';
            case 'info':
            default:
                return 'bx-info-circle';
        }
    }

    // Convenience methods
    success(message, title = '') {
        return this.show({ type: 'success', title, message });
    }

    error(message, title = '') {
        return this.show({ type: 'error', title, message });
    }

    warning(message, title = '') {
        return this.show({ type: 'warning', title, message });
    }

    info(message, title = '') {
        return this.show({ type: 'info', title, message });
    }
}

// Create global instance
window.customAlert = new CustomAlert(); 