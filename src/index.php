<?php
require_once(__DIR__.'/config/constants.php');

spl_autoload_register(function ($class_name) {
    include_once(__DIR__.'/class/'.$class_name.".php");
});

$request = new Request();

try {
    $request->process();
} catch (Exception $e) {
    $request->showError(503, "Server not ready! Data unavailable. Please contact administrator");
    //echo $e->getMessage();
}
