<?php 
  function define_errores($error) {
    $errores = array(
       "NOT FOUND" => 404,
        // Agrega más errores según sea necesario
    );
    
    return isset($errores[$error]) ? $errores[$error] : 0;
  }
?>