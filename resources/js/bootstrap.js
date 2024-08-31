import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import 'bulma';
import 'jquery-ui/ui/widgets/sortable';
import 'datatables.net';
