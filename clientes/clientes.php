<?php 
  header("Access-Control-Allow-Origin: *");
  header('Access-Control-Allow-Credentials: true');
  header('content-type: application/json; charset=utf-8');
  header("Access-Control-Allow-Methods: GET, PUT, POST, OPTIONS");
  date_default_timezone_set('America/Mazatlan');

  require_once("../libs/buscaDato.php");
  require_once("../conexiones/conecta.php");
  require_once("../libs/define_general.php");

  define_timezone();
  //$miskeys_z = json_decode(file_get_contents("tokens.json"), true);
  $factura_z = json_decode(file_get_contents('php://input'), true);
  //$misdatfiscal_z = json_decode(file_get_contents("datosfiscales.json"), true);
  $accion_z = "";
  if(isset($_POST['modo'])) {    $accion_z = $_POST['modo'];  }
  if(isset($_GET['modo'])) {    $accion_z = $_GET['modo'];  }
  $micond_z = json_decode( file_get_contents('php://input'), true);
  if (array_key_exists ("modo", $micond_z))      $accion_z = $micond_z["modo"];

  if($accion_z == "AGREGAR") {
    agregar_cliente($micond_z);
  }
  if($accion_z == "BUSCAR_UN_CLIENTE_POR_ID") {
    buscar_cliente();
  }

  function agregar_cliente() {
    $codigo = "";
    $appat = "";
    $apmat = "";
    $nombre1 = "";
    $nombre2 = "";
    $direccion = "";
    $idciudad = "";
    $rfc = "";
    $codpost = "";
    $cia = 1;
    $micond_z = json_decode( file_get_contents('php://input'), true);
    $codigo = busca_dato($micond_z, "codigo");
    $appat = busca_dato($micond_z, "appat");
    $apmat = busca_dato($micond_z, "apmat");
    $nombre1 = busca_dato($micond_z, "nombre1");
    $nombre2 = busca_dato($micond_z, "nombre2");
    $direccion = busca_dato($micond_z, "direccion");
    $idciudad = busca_dato($micond_z, "idciudad");
    $rfc = busca_dato($micond_z, "rfc");
    $codpost = busca_dato($micond_z, "codpost");
    $cia = busca_dato($micond_z, "cia");

		$conn=conecta_pdo();
		# echo $condiciones_z;
		$sql_z =  sprintf("insert into clientes ");
		$sql_z =  $sql_z . sprintf(" (codigo, appat, apmat, nombre1, nombre2, direccion, idciudad, rfc, codpostal, cia) ");
		$sql_z =  $sql_z . sprintf(" values (:CODIGO, :APPAT, :APMAT, :NOMBRE1, :NOMBRE2, :DIRECCION, :IDCIUDAD, :RFC , :COSPOSTAL, :CIA )");
		$sentencia = $conn->prepare($sql_z);
		#echo $sql_z . "<br>";
		
		#$sentencia=$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$sentencia->bindParam(":CODIGO", $codigo, PDO::PARAM_STR );
		$sentencia->bindParam(":APPAT", $appat, PDO::PARAM_STR );
        $sentencia->bindParam(":APMAT", $apmat, PDO::PARAM_STR );
        $sentencia->bindParam(":NOMBRE1", $nombre1, PDO::PARAM_STR );
        $sentencia->bindParam(":NOMBRE2", $nombre2, PDO::PARAM_STR );
        $sentencia->bindParam(":RFC", $rfc, PDO::PARAM_STR );
        $sentencia->bindParam(":CODPOST", $codpost, PDO::PARAM_STR );
		$sentencia->bindParam(":IDCIUDAD", $idciudad, PDO::PARAM_INT);
        $sentencia->bindParam(":CIA", $cia, PDO::PARAM_INT);
		$sentencia->execute();
        $result["id"]= $conn->lastInsertId();
		return (json_encode($result));
  }

  function buscar_cliente() {
    $idcli = -1;
    $cia = -1;
    $codigo = "-1";
    if(isset($_GET['idcli'])) {    $idcli = $_GET['idcli'];  }
    if(isset($_GET['codigo'])) {    $codigo = $_GET['codigo'];  }

  	$conn=conecta_pdo();
		# echo $condiciones_z;
		$sql_z =  sprintf("select * from clientes where idcli = :IDCLI or ( codigo = :CODIGO and cia = :CIA) ");
		$sentencia = $conn->prepare($sql_z);
		#echo $sql_z . "<br>";
		
		#$sentencia=$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$sentencia->bindParam(":CODIGO", $codigo, PDO::PARAM_STR );
		$sentencia->execute();

		$result_z = $sentencia->fetchAll();
		return (json_encode($result_z));
	    

  }
  
  
?>
