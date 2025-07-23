<?php include 'head.php';?>

<div class="max-w-7xl mx-auto bg-white rounded-lg shadow-md p-8">

<nav class="">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex justify-between items-center h-16">
      <h2 class="text-2xl font-semibold text-gray-800 mb-4">Introducció al Projecte</h2>

      <!-- Links -->
      <div class="space-x-6">
        <a href="#config-db" class="text-gray-700 hover:text-blue-600 transition">config-db</a>
        <a href="#template" class="text-gray-700 hover:text-blue-600 transition">template</a>
        <a href="#about" class="text-gray-700 hover:text-blue-600 transition">Nosaltres</a>
        <a href="#contacte" class="text-gray-700 hover:text-blue-600 transition">Contacte</a>
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
	
	<div class="code-container">
    <div class="code-header">
        <span>directorio.txt</span>
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
	
	
	
	<section class="mb-8">
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

    <div class="section mb-8">
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

    <div class="section mb-8">
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
</div>

<?php include 'footer.php';?>