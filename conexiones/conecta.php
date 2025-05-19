<?php
    error_reporting(E_ALL  ^  E_WARNING );    

    $accion_z = "";
    //$accion_z = "$_GET['accion']";
  	
    if($accion_z == "getcia") {
      $cia_z = $_GET['cia'];
      busca_cia($cia_z);
    }

	function busca_cia($cia_z) {
		return (0);
	}

	function conecta_pdo() {
		$datosbd_z = "datosbd.json";
		if(!file_exists($datosbd_z)) {
			$datosbd_z = __DIR__ . "/../config/datosbd.json";
		}
		// echo $datosbd_z;
        $misdatosbd_z = json_decode(file_get_contents($datosbd_z), true);

		$data_source=$misdatosbd_z["source"];
		$user= $misdatosbd_z["user"];
		$password=strrev($misdatosbd_z["pwd"]);
		$basedatos = $misdatosbd_z["database"];
		//$data_source='localhost:3306';
		//$user='root';
		//$password='';
		//$basedatos = 'facturacion';
		$conn = new PDO('mysql:host='. $data_source. ';dbname=' . $basedatos, $user, $password);
		if (!($conn)) { 
			$error = array("error" => "Conection to DB Failed");
			echo json_encode($error);
		}
 	    
		return ($conn);

	}

	
?>
