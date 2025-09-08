<?php
echo "<h1>XAMPP Test Page</h1>";
echo "<p>PHP is working correctly!</p>";

// Check if the register.php file exists
if (file_exists(__DIR__ . '/register.php')) {
    echo "<p style='color: green;'>register.php file exists in this directory.</p>";
} else {
    echo "<p style='color: red;'>register.php file does NOT exist in this directory.</p>";
}

// Display the current directory
echo "<p>Current directory: " . __DIR__ . "</p>";

// List all files in the current directory
echo "<h2>Files in this directory:</h2>";
echo "<ul>";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file != "." && $file != "..") {
        echo "<li>" . $file . "</li>";
    }
}
echo "</ul>";

// Check if the assets directory exists
if (is_dir(__DIR__ . '/assets')) {
    echo "<p style='color: green;'>assets directory exists.</p>";
} else {
    echo "<p style='color: red;'>assets directory does NOT exist.</p>";
}

// Display server information
echo "<h2>Server Information:</h2>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Server Name: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>Server Port: " . $_SERVER['SERVER_PORT'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
?>