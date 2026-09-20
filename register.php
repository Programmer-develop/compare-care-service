<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $place = htmlspecialchars($_POST['place']);

    $entry = "Name: $name | Phone: $phone | Place: $place\n";
    $file = "registrations.txt"; // Make sure this file exists

    if (is_writable($file)) {
        if (file_put_contents($file, $entry, FILE_APPEND)) {
            echo "<h2>✅ Thank you, $name! Your details have been registered.</h2>";
        } else {
            echo "<h3 style='color:red;'>❌ Failed to write to file. Please check permissions.</h3>";
        }
    } else {
        echo "<h3 style='color:red;'>❌ The file exists but is not writable.</h3>";
    }

    echo "<br><a href='index.html'>Go Back</a>";
}
?>