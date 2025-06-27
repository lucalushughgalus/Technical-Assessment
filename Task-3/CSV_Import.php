<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Write data to csv files
function writeToCSV(string $filename, array $data, array $headers): void {
    $fp = fopen($filename, 'w');
    if ($fp === false) {
        throw new RuntimeException("Failed to open file: $filename");
    }

    // Add explicit escape char for PHP 8.1+
    fputcsv($fp, $headers, ',', '"', '\\');
    foreach ($data as $row) {
        fputcsv($fp, $row, ',', '"', '\\');
    }
    fclose($fp);
}


// Handle form submission and file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    $file = $_FILES['csvFile']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== false) {
        $headers = fgetcsv($handle, 1000, ',', '"', '\\');
        if (!$headers) {
            die('Invalid or empty CSV file.');
        }

        $seen = [];
        $vehiclesByFuel = [];
        $validVehicles = [];
        $invalidRegCount = 0;

        while (($data = fgetcsv($handle, 1000, ',', '"', '\\')) !== false) {
            if (count($data) !== count($headers)) {
                continue; // Skip malformed rows
            }

            $row = array_combine($headers, $data);
            if (!$row) {
                continue;
            }

            $registration = strtoupper(trim((string)($row['Car Registration'] ?? '')));
            if ($registration === '' || isset($seen[$registration])) {
                continue;
            }

            $make   = trim((string)($row['Make'] ?? ''));
            $model  = trim((string)($row['Model'] ?? ''));
            $colour = trim((string)($row['Colour'] ?? ''));
            $fuel   = trim((string)($row['Fuel'] ?? 'Unknown'));

            $vehicleData = [$registration, $make, $model, $colour, $fuel];

            // Organize by fuel type
            $vehiclesByFuel[$fuel][] = $vehicleData;

            // Validate registration format
            if (preg_match('/^[A-Z]{2}[0-9]{2} [A-Z]{3}$/', $registration)) {
                $validVehicles[] = $vehicleData;
            } else {
                $invalidRegCount++;
            }

            $seen[$registration] = true;
        }

        fclose($handle);

        // Output directory for CSVs
        $outputDir = __DIR__ . '/exports';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Export per fuel type
        echo "<h2>Exported CSVs by Fuel Type</h2><ul>";
        foreach ($vehiclesByFuel as $fuel => $rows) {
            $safeFuel = preg_replace('/[^a-zA-Z0-9]/', '_', $fuel);
            $filename = "$outputDir/vehicles_$safeFuel.csv";
            writeToCSV($filename, $rows, ['Car Registration', 'Make', 'Model', 'Colour', 'Fuel']);

            echo "<li><a href='exports/vehicles_$safeFuel.csv' download>Download CSV for $fuel</a></li>";
        }
        echo "</ul>";

        // Display valid vehicles
        echo "<h2>Vehicles with Valid Registrations</h2>";
        if (!empty($validVehicles)) {
            echo "<table border='1' cellpadding='5'><tr><th>Registration</th><th>Make</th><th>Model</th><th>Colour</th><th>Fuel</th></tr>";
            foreach ($validVehicles as $v) {
                echo "<tr><td>" . implode('</td><td>', $v) . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No vehicles with valid registrations.</p>";
        }

        // Display count of invalid registrations
        echo "<h3>Invalid Registration Count: $invalidRegCount</h3>";

    } else {
        echo "<p>Failed to open uploaded CSV file.</p>";
    }

} else {
    // Show upload form
    ?>
    <form method="post" enctype="multipart/form-data">
        <label>Select CSV File:</label><br>
        <input type="file" name="csvFile" accept=".csv" required><br><br>
        <input type="submit" value="Upload and Process CSV">
    </form>
    <?php
}
?>
