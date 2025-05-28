// resources/js/app.js

// 1. Importa bootstrap.js (che gestisce jQuery globale, Bootstrap JS, Axios)
// Questa deve essere la PRIMA riga per assicurare che jQuery sia globale
// prima che altri moduli che ne dipendono vengano importati.
import './bootstrap.js';

// 2. Importa i CSS necessari per le librerie jQuery
import 'jquery-ui-dist/jquery-ui.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
// Se hai altri file CSS da librerie, importali qui.

// 3. Importa i file JavaScript dei plugin jQuery
// Ora che $ e jQuery sono globali grazie a bootstrap.js, questi plugin dovrebbero trovarli.

// jQuery UI
// console.log('APP.JS: Prima dell\'import di jQuery UI JS - $.fn.autocomplete:', typeof $?.fn?.autocomplete);
import 'jquery-ui-dist/jquery-ui.min.js';
// console.log('APP.JS: Dopo l\'import di jQuery UI JS - $.fn.autocomplete:', typeof $?.fn?.autocomplete);

// DataTables (Core e integrazione Bootstrap 5)
// console.log('APP.JS: Prima dell\'import di DataTables JS - $.fn.DataTable:', typeof $?.fn?.DataTable);
import 'datatables.net';
import 'datatables.net-bs5';
// console.log('APP.JS: Dopo l\'import di DataTables JS - $.fn.DataTable:', typeof $?.fn?.DataTable);


// 4. Importa altre librerie JavaScript (es. Alpine.js)
import Alpine from 'alpinejs';
window.Alpine = Alpine; // Se vuoi renderlo globale come avevi prima
Alpine.start();

