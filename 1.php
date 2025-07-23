<?php include 'head.php';?>

<div class="max-w-7xl mx-auto bg-white rounded-lg shadow-md p-8 pt-24"> <!-- Augmentat pt-24 per més espai -->
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




    <section id="inici" class="mb-8">
        
        <p class="mb-4 text-gray-700">
            Aquest projecte és un CRUD (Create, Read, Update, Delete) per gestionar dades a la base de dades <code>autonomo_contabilidad</code>. 
            Està basat en l'estructura inicial proporcionada pel repositori 
            <a href="https://github.com/bradtraversy/simple-tailwind-starter" target="_blank" class="text-blue-600 hover:underline">
                bradtraversy/simple-tailwind-starter
            </a>, que utilitza Tailwind CSS per a l'estilització.
        </p>
    </section>

    <h1 class="text-3xl font-bold text-blue-800 mb-6">Documentació amb Blocs de Codi Enriquits</h1>
    
    <section id="config-db" class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Exemple de Configuració de la Base de Dades</h2>
        <p class="mb-4 text-gray-700">Aquest és un exemple del fitxer <code>config.php</code> amb la connexió a la base de dades:</p>
        
        <div class="code-container relative z-10">
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
    </section>

    <section id="consulta-sql" class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Exemple de Consulta SQL</h2>
        <p class="mb-4 text-gray-700">Exemple de consulta preparada per obtenir clients:</p>
        
        <div class="code-container relative z-10">
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
    </section>

    <section id="formulari" class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Exemple de Formulari HTML</h2>
        <p class="mb-4 text-gray-700">Formulari per crear/editar clients amb Tailwind CSS:</p>
        
        <div class="code-container relative z-10">
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
    </section>
</div>

<script>
    // Desplaçament suau per a les àncores
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>

<?php include 'footer.php';?>