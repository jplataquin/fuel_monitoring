import './bootstrap';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Bootstrap Icons
import 'bootstrap-icons/font/bootstrap-icons.css';

// Alpine is automatically injected by Livewire v3+

// Suppress Alpine internal cancelled transition promise rejections
window.addEventListener('unhandledrejection', (event) => {
    if (event.reason?.isFromCancelledTransition) {
        event.preventDefault();
    }
});


