<?php
// hotel_completo/app/config/paths.php

// Define the base directory of the project.
// This assumes paths.php is located at hotel_completo/app/config/
// dirname(__DIR__, 2) goes up two levels: from config/ to app/, then from app/ to hotel_completo/
define('BASE_DIR', dirname(__DIR__, 2));

// Define the absolute path to the 'views' folder
define('VIEW_PATH', BASE_DIR . '/app/views/');
// Define the absolute path to the 'layouts' folder (if needed directly)
define('LAYOUT_PATH', BASE_DIR . '/app/views/layouts/');

// Debugging for paths.php
error_log("DEBUG-PATH: BASE_DIR defined as: " . BASE_DIR);
error_log("DEBUG-PATH: VIEW_PATH defined as: " . VIEW_PATH);