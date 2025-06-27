<?php
// Looking out for POST from form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["csvFile"])) {
    $file = $_FILES["csvFile"]["tmp_name"];

    if (($handle = fopen($file, "r")) !== false) {
        $headers = fgetcsv($handle, 1000, ",", '"', "\\");

        //Array to store existing reg
        $seen = [];

        //Array for inavlid registrations
        $invalidReg = [];

        while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== false) {
            $row = array_combine($headers, $data);

            if (!$row) {
                continue;
            }

            $registration = isset($row["Car Registration"]) ? trim((string) $row["Car Registration"]) : '';

            // Skip duplicates based on Car Registration
            if ($registration === '' || isset($seen[$registration])) {
                continue;
            }

            // Mark reg as seen
            $seen[$registration] = true;


            $make = isset($row["Make"]) ? trim((string) $row["Make"]) : '';
            $model = isset($row["Model"]) ? trim((string) $row["Model"]) : '';
            $colour = isset($row["Colour"]) ? trim((string) $row["Colour"]) : '';
            $fuel = isset($row["Fuel"]) ? trim((string) $row["Fuel"]) : '';

            // Validate registration format so that it must follow the pattern: two letters, two digits, space, three letters
            if (preg_match('/^[A-Z]{2}[0-9]{2} [A-Z]{3}$/i', $registration)) {
                // Check for duplicates
                if (!in_array(strtoupper($registration), $seen)) {
                    echo "$registration, $make, $model, $colour, $fuel<br>";
                    $seen[] = strtoupper($registration);
                }
            } else {
                if (!in_array(strtoupper($registration), $seen)) {
                    $invalidReg[] = strtoupper($registration);
                }
            }
        }

        fclose($handle);
    } else {
        echo "Failed to open file.";
    }
} else {
    ?>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="csvFile" accept=".csv">
        <input type="submit" value="Upload CSV">
    </form>
    <?php
}
?>