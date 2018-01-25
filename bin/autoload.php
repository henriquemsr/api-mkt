<?php
    function autoload($className) {
        set_include_path(__DIR__ . '/class');
        spl_autoload($className);
    }
    spl_autoload_extensions('.class.php');
    spl_autoload_register('autoload');
    