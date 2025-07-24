<?php include 'head.php';?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Pujar i importar CSV (Sheet Google Vendes o Despeses)</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

   
       
    

    <?php
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file']) && isset($_POST['import_type'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES['csv_file']['name']);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Comprovar si és un fitxer CSV
        if ($fileType != "csv") {
            $message = "<div class='alert alert-danger'>Error: Només es permeten fitxers CSV.</div>";
            $uploadOk = 0;
        }

        // Comprovar si hi ha errors en la pujada
        if ($uploadOk && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
            if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $target_file)) {
                $message = "<div class='alert alert-success'>L'arxiu " . htmlspecialchars(basename($_FILES['csv_file']['name'])) . " s'ha pujat correctament.</div>";

                // Executar el script Python adequat
                $python_path = "C:\\Users\\joanp\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";
                $script_dir = dirname(__FILE__) . '\\';

                if (!file_exists($python_path)) {
                    $message .= "<div class='alert alert-danger'>Error: No es pot trobar Python a $python_path. Verifica la ruta.</div>";
                    $uploadOk = 0;
                }

                $python_script = '';
                if ($_POST['import_type'] === 'ventas') {
                    $script_file = $script_dir . 'import_csv_to_db.py';
                    $python_script = "\"$python_path\" \"$script_file\" " . escapeshellarg($target_file);
                } elseif ($_POST['import_type'] === 'despeses') {
                    $script_file = $script_dir . 'import_despeses_to_db.py';
                    $python_script = "\"$python_path\" \"$script_file\" " . escapeshellarg($target_file);
                } else {
                    $message .= "<div class='alert alert-danger'>Error: Tipus d'importació no vàlid.</div>";
                    $uploadOk = 0;
                }

                if ($uploadOk && file_exists($script_file)) {
                    exec($python_script . " 2>&1", $output, $return_var);
                    $status = ($return_var === 0) ? "Importat correctament" : "Error";
                    $message .= "<div class='alert alert-" . ($return_var === 0 ? "success" : "danger") . "'>Importació a la base de dades: $status</div>";

                    // Guardar a la taula de logs
                    try {
                        $stmt = $pdo->prepare("INSERT INTO wp_contabilidad_import_logs (file_name, import_type, status) VALUES (:file_name, :import_type, :status)");
                        $stmt->execute([
                            ':file_name' => basename($_FILES['csv_file']['name']),
                            ':import_type' => $_POST['import_type'],
                            ':status' => $status
                        ]);
                    } catch (PDOException $e) {
                        $message .= "<div class='alert alert-danger'>Error en guardar el log: " . $e->getMessage() . "</div>";
                    }
                } else {
                    $message .= "<div class='alert alert-danger'>Error: No es pot trobar el script Python a $script_file. Verifica que els fitxers estiguin a " . $script_dir . "</div>";
                }
            } else {
                $message .= "<div class='alert alert-danger'>Error en pujar l'arxiu.</div>";
            }
        }
    }
    echo $message;
    ?>
	

	<div class="container mx-auto mt-6 px-4">
    
  
   <div class="flex flex-row gap-6 items-center">
    <!-- Formulari -->
    <div class="w-1/2">
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
            <div class="space-y-4">
                <div>
                    <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">Selecciona un arxiu CSV</label>
                    <input type="file" 
                           class="block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100"
                           id="csv_file" 
                           name="csv_file" 
                           accept=".csv" 
                           required>
                </div>
                
                <div>
                    <label for="import_type" class="block text-sm font-medium text-gray-700 mb-2">Tipus d'importació</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            id="import_type" 
                            name="import_type" 
                            required>
                        <option value="ventas">Vendes</option>
                        <option value="despeses">Despeses</option>
                    </select>
                </div>
                
                <button type="submit" 
                        class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm">
                    Pujar i importar
                </button>
            </div>
        </form>
    </div>
    
    <!-- Imatge -->
    <div class="w-1/2 flex justify-center">
        <img src="img/uploadandimport.png" 
             class="max-h-80 rounded-lg shadow-md border border-gray-200" 
             alt="Il·lustració pujada d'arxius">
    </div>
</div>

    <!-- Taula de resultats -->
    <div class="mt-8">
        <h3 class="text-xl font-semibold mb-4">Historial d'importacions</h3>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table id="importTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arxiu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipus</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estat</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT file_name, import_type, status, created_at FROM wp_contabilidad_import_logs ORDER BY created_at DESC");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<tr>';
                                echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['file_name']) . '</td>';
                                echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . htmlspecialchars($row['import_type']) . '</td>';
                                echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . htmlspecialchars($row['status']) . '</td>';
                                echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . date('d/m/Y H:i:s', strtotime($row['created_at'])) . '</td>';
                                echo '</tr>';
                            }
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600">Error en carregar l\'historial: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<script>
$(document).ready(function() {
    $('#importTable').DataTable({
		
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json' // Traducció al català
        },
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],order: [[0, 'asc']],
        columnDefs: [
            { orderable: true, targets: '_all' },
            { orderable: false, targets: -1 } // Desactivar ordenació a la columna d'accions
        ],
        searching: true,
        paging: true
		
		
		
		
    });
	
	
       
       
      
});

</script>

<?php include 'footer.php';?>	