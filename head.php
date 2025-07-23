<?php include 'config.php';?>

<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentació amb Blocs de Codi Enriquits</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Highlight.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/php.min.js"></script>
    <!-- Clipboard.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js"></script>
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/ca.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
	
	 <!-- Trix -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <!-- Fancybox -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css" />


    <style>
    body {
        font-family: 'Instrument Sans', sans-serif;
    }
    </style>

    <style>
    .code-container {
        position: relative;
        margin: 1rem 0;
    }

    .code-header {
        background-color: #2d3748;
        color: white;
        padding: 0.5rem 1rem;
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .copy-btn {
        background-color: #4a5568;
        border: none;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        cursor: pointer;
        font-size: 0.75rem;
        transition: background-color 0.2s;
    }

    .copy-btn:hover {
        background-color: #718096;
    }

    .copy-btn.copied {
        background-color: #48bb78;
    }

    pre {
        margin: 0 !important;
        border-radius: 0 0 0.375rem 0.375rem !important;
    }

    .dropdown:hover .dropdown-menu {
        display: block;
    }

    .mobile-menu {
        display: none;
    }

    .mobile-menu.active {
        display: block;
    }

    /* Estilos mejorados para los dropdowns */
    .dropdown-menu {
        transition: all 0.3s ease;
        transform-origin: top;
        transform: scaleY(0);
        opacity: 0;
        display: block !important;
        height: 0;
        overflow: hidden;
    }

    .dropdown-menu.show {
        transform: scaleY(1);
        opacity: 1;
        height: auto;
    }

    .mobile-dropdown-content {
        transition: all 0.3s ease;
        transform-origin: top;
        transform: scaleY(0);
        opacity: 0;
        display: block !important;
        height: 0;
        overflow: hidden;
    }

    .mobile-dropdown-content.show {
        transform: scaleY(1);
        opacity: 1;
        height: auto;
    }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navbar Responsive -->
    <nav class="bg-blue-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <!-- Logo -->
                <div class="flex items-center">
                    <img src="img/logo.png" align="left" width="50" height="" />
                    <span class="font-semibold text-xl"><a href="index.php">AutonomoContabilidad </a></span>
                </div>

                <!-- Menú per a mòbil -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>

                <!-- Menú principal -->
                <div class="hidden md:flex">
                    <div class="flex space-x-1">
                        <!-- Dropdown 1: Clients -->
                        <div class="dropdown relative">
                            <button class="px-4 py-2 hover:bg-blue-700 rounded flex items-center dropdown-btn">
                                Cruds taules bbdd <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div class="dropdown-menu absolute bg-white text-gray-800 rounded shadow-lg mt-1 w-48 z-10">

                                <a href="index.php" class="block px-4 py-2 hover:bg-gray-100">Gestio clients</a>
                                <a href="productes.php" class="block px-4 py-2 hover:bg-gray-100">gestio productes</a>
                                <a href="categoria_productes.php" class="block px-4 py-2 hover:bg-gray-100">categoria
                                    productes</a>
                            </div>
                        </div>

                        <!-- Dropdown 2: Factures -->
                        <div class="dropdown relative">
                            <button class="px-4 py-2 hover:bg-blue-700 rounded flex items-center dropdown-btn">
                                Factures <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div class="dropdown-menu absolute bg-white text-gray-800 rounded shadow-lg mt-1 w-48 z-10">
                                <a href="vendas.php" class="block px-4 py-2 hover:bg-gray-100">vendas</a>

                            </div>
                        </div>

                        <!-- Dropdown 3: Informes -->
                        <div class="dropdown relative">
                            <button class="px-4 py-2 hover:bg-blue-700 rounded flex items-center dropdown-btn">
                                Informes <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div class="dropdown-menu absolute bg-white text-gray-800 rounded shadow-lg mt-1 w-48 z-10">
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100">Trimestrals</a>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100">Anuals</a>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100">Personalitzats</a>
                            </div>
                        </div>

                        <!-- Dropdown 4: Configuració -->
                        <div class="dropdown relative">
                            <button class="px-4 py-2 hover:bg-blue-700 rounded flex items-center dropdown-btn">
                                Configuració <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div class="dropdown-menu absolute bg-white text-gray-800 rounded shadow-lg mt-1 w-48 z-10">
                                <a href="documentacio.php" class="block px-4 py-2 hover:bg-gray-100">Documentacio</a>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100">Preferències</a>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100">Sincronització</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menú mòbil (ocult per defecte) -->
        <div id="mobile-menu" class="mobile-menu md:hidden bg-blue-700 px-4 pb-3">
            <!-- Dropdown 1: Clients -->
            <div class="mb-2">
                <button
                    class="mobile-dropdown-btn w-full text-left px-3 py-2 rounded flex justify-between items-center bg-blue-600">
                    Cruds taules bbdd <i class="fas fa-chevron-down"></i>
                </button>
                <div class="mobile-dropdown-content pl-4 mt-1">
                    <a href="index.php" class="block px-3 py-2 rounded hover:bg-blue-600">Clients</a>
                    <a href="productes.php" class="block px-3 py-2 rounded hover:bg-blue-600">productes</a>
                    <a href="categoria_productes.php" class="block px-3 py-2 rounded hover:bg-blue-600">categoria
                        productes</a>
                </div>
            </div>

            <!-- Dropdown 2: Factures -->
            <div class="mb-2">
                <button
                    class="mobile-dropdown-btn w-full text-left px-3 py-2 rounded flex justify-between items-center bg-blue-600">
                    Factures <i class="fas fa-chevron-down"></i>
                </button>
                <div class="mobile-dropdown-content pl-4 mt-1">
                    <a href="vendas.php" class="block px-3 py-2 rounded hover:bg-blue-600">vendas</a>
                    <a href="#" class="block px-3 py-2 rounded hover:bg-blue-600">Factures Pendents</a>
                    <a href="#" class="block px-3 py-2 rounded hover:bg-blue-600">Històric</a>
                </div>
            </div>

            <!-- Dropdown 3: Informes -->
            <div class="mb-2">
                <button
                    class="mobile-dropdown-btn w-full text-left px-3 py-2 rounded flex justify-between items-center bg-blue-600">
                    Informes <i class="fas fa-chevron-down"></i>
                </button>
                <div class="mobile-dropdown-content pl-4 mt-1">
                    <a href="#" class="block px-3 py-2 rounded hover:bg-blue-600">Trimestrals</a>
                    <a href="#" class="block px-3 py-2 rounded hover:bg-blue-600">Anuals</a>
                    <a href="#" class="block px-3 py-2 rounded hover:bg-blue-600">Personalitzats</a>
                </div>
            </div>

            <!-- Dropdown 4: Configuració -->
            <div class="mb-2">
                <button
                    class="mobile-dropdown-btn w-full text-left px-3 py-2 rounded flex justify-between items-center bg-blue-600">
                    Configuració <i class="fas fa-chevron-down"></i>
                </button>
                <div class="mobile-dropdown-content pl-4 mt-1">
                    <a href="documentacio.php" class="block px-3 py-2 rounded hover:bg-blue-600">Documentacio</a>
                    <a href="#" class="block px-3 py-2 rounded hover:bg-blue-600">Preferències</a>
                    <a href="#" class="block px-3 py-2 rounded hover:bg-blue-600">Sincronització</a>
                </div>
            </div>
        </div>
    </nav>

    <script>
    // Toggle del menú mòbil
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            mobileMenu.classList.toggle('active');

            // Canviar icona de hamburguesa a X i viceversa
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Toggle dels desplegables mòbils
    document.querySelectorAll('.mobile-dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const content = this.nextElementSibling;
            content.classList.toggle('show');

            // Rotar l’icona
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        });
    });

    // Toggle dels desplegables d'escriptori
    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.closest('.dropdown');
            const menu = dropdown.querySelector('.dropdown-menu');
            menu.classList.toggle('show');

            // Rotar l’icona
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        });
    });

    // Tancar tots els menús al fer clic fora
    document.addEventListener('click', function() {
        // Tancar menú mòbil
        if (mobileMenu) {
            mobileMenu.classList.remove('active');
        }

        // Restaurar icona del botó del menú mòbil
        if (mobileMenuButton) {
            const icon = mobileMenuButton.querySelector('i');
            if (icon.classList.contains('fa-times')) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }

        // Tancar tots els desplegables mòbils
        document.querySelectorAll('.mobile-dropdown-content').forEach(content => {
            content.classList.remove('show');
        });

        // Tancar tots els desplegables d'escriptori
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });

        // Restaurar icones dels desplegables
        document.querySelectorAll('.dropdown-btn i, .mobile-dropdown-btn i').forEach(icon => {
            if (icon.classList.contains('fa-chevron-up')) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    });

    // Evitar que es tanquin els desplegables al fer clic dins seu
    document.querySelectorAll('.dropdown-menu, .mobile-dropdown-content').forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    </script>