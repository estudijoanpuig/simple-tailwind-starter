<!-- Botó Tornar amunt -->
<button id="to-top-button" onclick="goToTop()" title="Tornar amunt"
    class="hidden fixed bottom-4 right-4 p-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-colors">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Botó Tornar amunt -->
<button id="to-top-button" onclick="goToTop()" title="Tornar amunt"
    class="hidden fixed bottom-4 right-4 p-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-colors">
    <i class="fas fa-arrow-up"></i>
</button>


<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<!-- DataTables Moment Plugin -->
<script src="https://cdn.datatables.net/plug-ins/1.13.6/sorting/datetime-moment.js"></script>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ca.js"></script>

<script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/google/code-prettify@master/loader/run_prettify.js?skin=sunburst"></script>

<!-- Afegir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Afegir trix -->
<script src="https://unpkg.com/trix@2.0.5/dist/trix.umd.min.js"></script>

<script>
// Inicialitzar Highlight.js
document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightElement(block);
    });

    // Inicialitzar Clipboard.js
    new ClipboardJS('.copy-btn');

    // Afegir feedback al botó de copiar
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.add('copied');
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check mr-1"></i> Copiat!';

            setTimeout(() => {
                this.classList.remove('copied');
                this.innerHTML = originalText;
            }, 2000);
        });
    });
});
</script>

<script>
// Funció per gestionar el botó Tornar amunt
const toTopButton = document.getElementById("to-top-button");
window.addEventListener("scroll", function() {
    if (window.scrollY > 200) {
        toTopButton.classList.remove("hidden");
        toTopButton.classList.add("block");
    } else {
        toTopButton.classList.remove("block");
        toTopButton.classList.add("hidden");
    }
});

function goToTop() {
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}

// Configuració comuna per a DataTables
$(document).ready(function() {
    $.fn.dataTable.moment('DD/MM/YYYY');
    const dataTableOptions = {
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
        },
        order: [
            [0, 'asc']
        ],
        columnDefs: [{
                orderable: true,
                targets: '_all'
            },
            {
                orderable: false,
                targets: -1
            }
        ],
        searching: true,
        paging: true
    };

    // Inicialitzar DataTables per a totes les taules possibles
    const tables = [
        '#tablaClientes', '#tablaDatos', '#tablaProveedores', '#tablaProductos',
        '#tablaCategoriaProductos', '#tablaVentas', '#tablaDetallesVenta',
        '#tablaCompras', '#tablaDetallesCompra', '#tablaResultados', '#tablaIVA'
    ];
    tables.forEach(function(tableId) {
        if ($(tableId).length && !$.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable(dataTableOptions);
        }
    });
});
</script>
</body>

</html>