// resources/js/app.js

// 1. Importa bootstrap.js (che gestisce jQuery globale, Bootstrap JS, Axios)
import './bootstrap.js';

// 2. Importa i CSS necessari per le librerie
import 'jquery-ui-dist/jquery-ui.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
// Se usi un file app.scss principale, potresti importare questi SCSS lì

// 3. Importa i file JavaScript dei plugin
import 'jquery-ui-dist/jquery-ui.min.js'; // jQuery UI

// DataTables (Core e integrazione Bootstrap 5)
import 'datatables.net';
import 'datatables.net-bs5';

// 4. Importa altre librerie JavaScript (es. Alpine.js)
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
