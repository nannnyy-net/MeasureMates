import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.CSRF_TOKEN = document.head.querySelector('meta[name="csrf-token"]')?.content ?? '';
