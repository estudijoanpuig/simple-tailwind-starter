<?php include 'head.php';?>

<div class="max-w-7xl mx-auto bg-white rounded-lg shadow-md p-8">

<nav class="">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex justify-between items-center h-16">
      <h2 class="text-2xl font-semibold text-orange-600 mb-4">navega pagina</h2>

      <!-- Links -->
      <div class="space-x-6">
	  <a href="#llibreries" class="text-gray-700 hover:text-blue-600 transition">llibreries</a>
        <a href="#config-db" class="text-gray-700 hover:text-blue-600 transition">config-db</a>
        <a href="#plantilla" class="text-gray-700 hover:text-blue-600 transition">plantilla</a>
        <a href="#consulta" class="text-gray-700 hover:text-blue-600 transition">consulta</a>
        <a href="#formulari" class="text-gray-700 hover:text-blue-600 transition">formulari</a>
		<a href="#estructura_bd" class="text-gray-700 hover:text-blue-600 transition">estuctura_bd</a>
      </div>
    </div>
  </div>
</nav>


    <section class="mb-8">
        <h1 class="mb-2 text-4xl font-extrabold leading-none tracking-normal text-gray-900 md:text-6xl md:tracking-tight text-center">
                    Introduccio<span class="block w-full py-2 text-transparent bg-clip-text leading-12 bg-gradient-to-r from-green-400 to-purple-500 lg:inline"> a la documentacio del projecte</span>
                </h1>
		<script>
        // Copia el text del <h1> al <title>
        document.title = document.querySelector('h1').textContent;
    </script>		
        <p class="mb-4 text-gray-700">
            Aquest projecte és un CRUD (Create, Read, Update, Delete) per gestionar dades a la base de dades <code>autonomo_contabilidad</code>. 
            Està basat en l'estructura inicial proporcionada pel repositori 
            <a href="https://github.com/bradtraversy/simple-tailwind-starter" target="_blank" class="text-blue-600 hover:underline">
                bradtraversy/simple-tailwind-starter
            </a>, que utilitza Tailwind CSS per a l'estilització.
        </p>
    </section>
	
	<div id="llibreries"class="code-container">
    <div class="code-header">
        <span>head</span>
        <button class="copy-btn" data-clipboard-target="#dir-code">
            <i class="far fa-copy mr-1"></i> Copiar
        </button>
    </div>
	
	
    <pre><code id="dir-code" class="text"><?php
echo htmlspecialchars('<link rel="preconnect" href="https://fonts.bunny.net">
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
');
    ?></code></pre>
</div>


	<div class="code-container">
    <div class="code-header">
        <span>directorio proyecto</span>
        <button class="copy-btn" data-clipboard-target="#dir-code">
            <i class="far fa-copy mr-1"></i> Copiar
        </button>
    </div>
    <pre><code id="dir-code" class="text"><?php
echo htmlspecialchars('C:\Apache24\htdocs\simple-tailwind-starter
├── .gitignore
├── 1.php
├── config.php
├── css/
├── documentacio.php
├── footer.php
├── head.php
├── img/
├── index.php
├── index2.html
├── input.css
├── node_modules/
├── package-lock.json
├── package.json
├── readme.md
├── video/
');
    ?></code></pre>
</div>



	
	
	
	<section id="plantilla"class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Exemple de Codi per a la Plantilla</h2>
        <p class="mb-4 text-gray-700">Aquest codi mostra l'estructura bàsica d'una pàgina amb Tailwind CSS:</p>
        
        <div class="code-container">
            <div class="code-header">
                <span>template.php</span>
                <button class="copy-btn" data-clipboard-target="#template-code">
                    <i class="far fa-copy mr-1"></i> Copiar
                </button>
            </div>
            <pre><code id="template-code" class="php"><?php
echo htmlspecialchars('<?php include "head.php";?>

<div class="max-w-7xl mx-auto bg-white rounded-lg shadow-md p-8">
    <h1 class="text-3xl font-bold text-blue-800 mb-6">Títol de la Pàgina</h1>
	
    <section class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Secció</h2>
        <p class="mb-4 text-gray-700">Contingut de la secció.</p>
    </section>
</div>

<?php include "footer.php";?>');
            ?></code></pre>
        </div>
    </section>
	
	<div class="code-container">
    <div class="code-header">
        <span>script.js</span>
        <button class="copy-btn" data-clipboard-target="#script-code">
            <i class="far fa-copy mr-1"></i> Copiar
        </button>
    </div>
    <pre><code id="script-code" class="javascript"><?php
echo htmlspecialchars('<script>
// Copia el text del <h1> al <title>
document.title = document.querySelector(\'h1\').textContent;
</script>');
    ?></code></pre>
</div>
	

    <h1 class="text-3xl font-bold text-blue-800 mb-6">Documentació amb Blocs de Codi Enriquits</h1>
    
    <div id="config-db" class="section mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Exemple de Configuració de la Base de Dades</h2>
        <p class="mb-4 text-gray-700">Aquest és un exemple del fitxer <code>config.php</code> amb la connexió a la base de dades:</p>
        
        <div class="code-container">
            <div class="code-header">
                <span>config.php</span>
                <button class="copy-btn" data-clipboard-target="#config-code">
                    <i class="far fa-copy mr-1"></i> Copiar
                </button>
            </div>
            <pre><code id="config-code" class="php"><?php
echo htmlspecialchars('<?php
// Configuració de la base de dades
$host = "localhost";
$dbname = "autonomo_contabilidad";
$username = "usuari";
$password = "contrasenya";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");
} catch (PDOException $e) {
    die("Error de connexió: " . $e->getMessage());
}
?>');
            ?></code></pre>
        </div>
    </div>

    <div id="consulta"class="section mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Exemple de Consulta SQL</h2>
        <p class="mb-4 text-gray-700">Exemple de consulta preparada per obtenir clients:</p>
        
        <div class="code-container">
            <div class="code-header">
                <span>crud_clientes.php</span>
                <button class="copy-btn" data-clipboard-target="#query-code">
                    <i class="far fa-copy mr-1"></i> Copiar
                </button>
            </div>
            <pre><code id="query-code" class="php"><?php
echo htmlspecialchars('<?php
// Obtenir tots els clients
try {
    $sql = "SELECT * FROM wp_contabilidad_clientes ORDER BY nombre ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class=\"alert alert-error\">Error: " . $e->getMessage() . "</div>";
}
?>');
            ?></code></pre>
        </div>
    </div>

    <div id="formulari"class="section mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Exemple de Formulari HTML</h2>
        <p class="mb-4 text-gray-700">Formulari per crear/editar clients amb Tailwind CSS:</p>
        
        <div class="code-container">
            <div class="code-header">
                <span>formulari_client.html</span>
                <button class="copy-btn" data-clipboard-target="#form-code">
                    <i class="far fa-copy mr-1"></i> Copiar
                </button>
            </div>
            <pre><code id="form-code" class="html"><?php
echo htmlspecialchars('<form method="POST" class="bg-white p-6 rounded-lg shadow-md">
    <div class="mb-4">
        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
        <input type="text" id="nombre" name="nombre" required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" id="email" name="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
        Guardar Client
    </button>
</form>');
            ?></code></pre>
        </div>
    </div>
	
	



<div id="estructura_bd"class="code-container">
    <div class="code-header">
        <span>sql</span>
        <button class="copy-btn" data-clipboard-target="#dir-code">
            <i class="far fa-copy mr-1"></i> Copiar
        </button>
    </div>
	
	
    <pre><code id="estructura_bd" class="text"><?php
echo htmlspecialchars('

-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Temps de generació: 24-07-2025 a les 02:41:25
-- Versió del servidor: 8.4.4
-- Versió de PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dades: `autonomo_contabilidad`
--

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_categoria_productos`
--

CREATE TABLE `wp_contabilidad_categoria_productos` (
  `id` int NOT NULL,
  `nombre_categoria` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_clientes`
--

CREATE TABLE `wp_contabilidad_clientes` (
  `id` mediumint NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nif` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_compras`
--

CREATE TABLE `wp_contabilidad_compras` (
  `id` mediumint NOT NULL,
  `fecha` date NOT NULL,
  `proveedor_id` mediumint NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `iva_monto` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_detalles_compra`
--

CREATE TABLE `wp_contabilidad_detalles_compra` (
  `id` mediumint NOT NULL,
  `compra_id` mediumint NOT NULL,
  `producto_id` mediumint NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `iva_porcentaje` decimal(5,2) NOT NULL,
  `iva_monto` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_detalles_venta`
--

CREATE TABLE `wp_contabilidad_detalles_venta` (
  `id` mediumint NOT NULL,
  `venta_id` mediumint NOT NULL,
  `producto_id` mediumint NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_empleados`
--

CREATE TABLE `wp_contabilidad_empleados` (
  `id` mediumint NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nif` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_import_logs`
--

CREATE TABLE `wp_contabilidad_import_logs` (
  `id` int NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `import_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_productos`
--

CREATE TABLE `wp_contabilidad_productos` (
  `id` mediumint NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `img` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_categoria_producto` int NOT NULL DEFAULT ,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `protocol` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT ,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_proveedores`
--

CREATE TABLE `wp_contabilidad_proveedores` (
  `id` mediumint NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nif` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_ventas`
--

CREATE TABLE `wp_contabilidad_ventas` (
  `id` mediumint NOT NULL,
  `fecha` date NOT NULL,
  `cliente_id` mediumint NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `iva_porcentaje` decimal(5,2) NOT NULL,
  `iva_monto` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `empleado_id` mediumint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Índexs per a les taules bolcades
--

--
-- Índexs per a la taula `wp_contabilidad_categoria_productos`
--
ALTER TABLE `wp_contabilidad_categoria_productos`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `wp_contabilidad_clientes`
--
ALTER TABLE `wp_contabilidad_clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `wp_contabilidad_compras`
--
ALTER TABLE `wp_contabilidad_compras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_compra` (`fecha`,`proveedor_id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Índexs per a la taula `wp_contabilidad_detalles_compra`
--
ALTER TABLE `wp_contabilidad_detalles_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Índexs per a la taula `wp_contabilidad_detalles_venta`
--
ALTER TABLE `wp_contabilidad_detalles_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Índexs per a la taula `wp_contabilidad_empleados`
--
ALTER TABLE `wp_contabilidad_empleados`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `wp_contabilidad_import_logs`
--
ALTER TABLE `wp_contabilidad_import_logs`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `wp_contabilidad_productos`
--
ALTER TABLE `wp_contabilidad_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria_producto` (`id_categoria_producto`);

--
-- Índexs per a la taula `wp_contabilidad_proveedores`
--
ALTER TABLE `wp_contabilidad_proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `wp_contabilidad_ventas`
--
ALTER TABLE `wp_contabilidad_ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_venta` (`fecha`,`cliente_id`,`empleado_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- AUTO_INCREMENT per les taules bolcades
--

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_categoria_productos`
--
ALTER TABLE `wp_contabilidad_categoria_productos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_clientes`
--
ALTER TABLE `wp_contabilidad_clientes`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_compras`
--
ALTER TABLE `wp_contabilidad_compras`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_detalles_compra`
--
ALTER TABLE `wp_contabilidad_detalles_compra`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_detalles_venta`
--
ALTER TABLE `wp_contabilidad_detalles_venta`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_empleados`
--
ALTER TABLE `wp_contabilidad_empleados`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_import_logs`
--
ALTER TABLE `wp_contabilidad_import_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_productos`
--
ALTER TABLE `wp_contabilidad_productos`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_proveedores`
--
ALTER TABLE `wp_contabilidad_proveedores`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_ventas`
--
ALTER TABLE `wp_contabilidad_ventas`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;




');
    ?></code></pre>
</div>




</div>
<?php include 'footer.php';?>