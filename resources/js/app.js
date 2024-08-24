import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import 'bulma';
import 'flatpickr';
import FullCalendar from 'fullcalendar';
window.FullCalendar = FullCalendar;
window.$ = window.jQuery = require('jquery');
import 'jquery-ui/ui/widgets/sortable';
import 'datatables.net';