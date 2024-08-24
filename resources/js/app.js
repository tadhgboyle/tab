window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

require('bulma');
require('flatpickr');
window.FullCalendar = require('fullcalendar');
window.$ = window.jQuery = require('jquery');
require('jquery-ui/ui/widgets/sortable');
require('datatables.net');