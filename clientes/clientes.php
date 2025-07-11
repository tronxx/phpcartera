<?php 
  header("Access-Control-Allow-Origin: *");
  header('Access-Control-Allow-Credentials: true');
  header('content-type: application/json; charset=utf-8');
  header("Access-Control-Allow-Methods: GET, PUT, POST, OPTIONS");
  date_default_timezone_set('America/Mazatlan');

  require_once("../libs/buscaDato.php");
  require_once("../conexiones/conecta.php");
  require_once("../libs/define_general.php");
  require_once("../libs/exportajson.php");


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
    exportar_cliente_por_id();
  }
  if($accion_z == "EXPORTAR_VENTA") {
    exportar_venta();
  }
  if($accion_z == "EXPORTAR_VENDEDORES_VENTA") {
    exportar_vendedores_venta();
  }
  if($accion_z == "EXPORTAR_MOVIMIENTOS_VENTA") {
    exportar_movimientos_venta();
  }
  if($accion_z == "IMPORTAR_DIAS_GRACIA") {
    importar_dias_gracia();
  }

  if($accion_z == "IMPORTAR_CLIENTES_TC") {
    importar_tc_cliente();
  }

  
  function exportar_vendedores_venta() {
    $body = file_get_contents('php://input');
    $idventa = -1;
    $codigo = "-1"; 
    $cia = 1; // Por default, la cia es 1
    if(isset($_GET['idventa']))   {    $idventa = $_GET['idventa'];  }
    if(isset($_POST['idventa']))  {    $idventa = $_POST['idventa'];  }
    if($body != "") {
      $micond_z = json_decode($body, true);
      if(array_key_exists("idventa", $micond_z)) { $idventa = $micond_z["idventa"]; }
    }
  	$conn=conecta_pdo();
		# echo $condiciones_z;
		$sql_z =  "select e.idventa, e.idvendedor, e.comision, a.codigo as codvnd
      from ventas e
      left outer join vendedores a on e.idvendedor = a.id
      where e.idventa = :IDVENTA";
		$sentencia = $conn->prepare($sql_z);

		#$sentencia=$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$sentencia->bindParam(":IDVENTA", $idventa, PDO::PARAM_INT );
		$sentencia->execute();
		$result_z = $sentencia->fetch(PDO::FETCH_ASSOC);
    $exportar = array (
      "modo" => "agregar_cli_agente",
      "idcli" => $result_z["idventa"],
      "idvnd" => $result_z["idvendedor"],
      "codvnd" => $result_z["codvnd"],
      "comis" => $result_z["comision"]
    );

    $venta = json_encode($exportar);
    echo $venta;
  }


  function exportar_venta() {
    $body = file_get_contents('php://input');
    $idventa = -1;
    $codigo = "-1"; 
    $cia = 1; // Por default, la cia es 1
    if(isset($_GET['idventa']))   {    $idventa = $_GET['idventa'];  }
    if(isset($_GET['codigo']))  {    $codigo = $_GET['codigo'];  }
    if(isset($_GET['cia']))     {    $cia = $_GET['cia'];  }
    if(isset($_POST['idventa']))  {    $idventa = $_POST['idventa'];  }
    if(isset($_POST['codigo'])) {    $codigo = $_POST['codigo'];  }
    if(isset($_POST['cia']))    {    $cia = $_POST['cia'];  }
    if($body != "") {
      $micond_z = json_decode($body, true);
      if(array_key_exists("idventa", $micond_z)) { $idventa = $micond_z["idventa"]; }
      if(array_key_exists("codigo", $micond_z)) { $codigo = $micond_z["codigo"]; }
      if(array_key_exists("cia", $micond_z)) { $cia = $micond_z["cia"]; }
    }
  	$conn=conecta_pdo();
		# echo $condiciones_z;
		$sql_z =  "select e.idventa, e.codigo, e.cia, a.nombre, a.calle, a.numpredio,
      a.codpostal, a.colonia, c.ciudad, e.siono, e.qom, e.ticte, f.codigo as ubica,
      g.codigo as promotor, e.opcion, e.comisionpromotor as comisionprom,
      e.enganc, e.servicio, e.letra1, e.nulets, e.canle, e.bonifi,
      e.precon, e.piva, e.descuento, 
      a.*,
      c.ciudad,
      appat, apmat, nompil1, nompil2
      from ventas e
      join clientes a on e.idcliente = a.id
      left outer join ciudades c on a.idciudad = c.id
      left outer join nombres d on a.idnombre = d.id
      left outer join ubivtas f on e.idtienda = f.id
      left outer join promotores g on e.idpromotor = g.id
      where e.idventa = :IDVENTA or ( a.codigo = :CODIGO and a.cia = :CIA) ";
		$sentencia = $conn->prepare($sql_z);

		#$sentencia=$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$sentencia->bindParam(":CODIGO", $codigo, PDO::PARAM_STR );
		$sentencia->bindParam(":IDVENTA", $idventa, PDO::PARAM_INT );
		$sentencia->bindParam(":CIA", $cia, PDO::PARAM_INT );
		$sentencia->execute();
		$result_z = $sentencia->fetch(PDO::FETCH_ASSOC);
    $venta = json_encode($result_z);
    //echo $venta;
    $miventa = exporta_venta($venta);

		$sql_z =  "select a.*, b.ciudad,
      appat, apmat, nompil1, nompil2
      from avales a 
      left outer join ciudades b on a.idciudad = b.id
      left outer join nombres c on a.idnombre = c.id
      where a.idventa = :IDVENTA ";
		$sentencia = $conn->prepare($sql_z);

		#$sentencia=$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$sentencia->bindParam(":IDVENTA", $idventa, PDO::PARAM_INT );
		$sentencia->execute();
		$result_z = $sentencia->fetch(PDO::FETCH_ASSOC);
    $aval = json_encode($result_z);
    $miaval = exporta_aval($aval);
    $ventacompleta = array (
      "modo" => "agregar_cliente",
      "clienterespu" => json_decode($miventa),
      "avalrespu" => json_decode($miaval)
    );
    
    echo json_encode($ventacompleta);
  }


  function exportar_cliente_por_id() {
    $body = file_get_contents('php://input');
    $idcli = -1;
    $codigo = "-1"; 
    $cia = 1; // Por default, la cia es 1
    if(isset($_GET['idcli']))   {    $idcli = $_GET['idcli'];  }
    if(isset($_GET['codigo']))  {    $codigo = $_GET['codigo'];  }
    if(isset($_GET['cia']))     {    $cia = $_GET['cia'];  }
    if(isset($_POST['idcli']))  {    $idcli = $_POST['idcli'];  }
    if(isset($_POST['codigo'])) {    $codigo = $_POST['codigo'];  }
    if(isset($_POST['cia']))    {    $cia = $_POST['cia'];  }
    if($body != "") {
      $micond_z = json_decode($body, true);
      if(array_key_exists("idcli", $micond_z)) { $idcli = $micond_z["idcli"]; }
      if(array_key_exists("codigo", $micond_z)) { $codigo = $micond_z["codigo"]; }
      if(array_key_exists("cia", $micond_z)) { $cia = $micond_z["cia"]; }
    }

    $cliente = buscar_cliente($idcli, $codigo, $cia);
    echo $cliente;

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

  function buscar_cliente($idcli = -1, $codigo = "-1", $cia = -1) { 

  	$conn=conecta_pdo();
		# echo $condiciones_z;
		$sql_z =  "select a.*, b.clave as regimen, c.ciudad,
      appat, apmat, nompil1, nompil2
      from clientes a 
      left outer join regimenes b on a.idregimen = b.id
      left outer join ciudades c on a.idciudad = c.id
      left outer join nombres d on a.idnombre = d.id
      where a.id = :IDCLI or ( a.codigo = :CODIGO and a.cia = :CIA) ";
		$sentencia = $conn->prepare($sql_z);

		#$sentencia=$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$sentencia->bindParam(":CODIGO", $codigo, PDO::PARAM_STR );
		$sentencia->bindParam(":IDCLI", $idcli, PDO::PARAM_INT );
		$sentencia->bindParam(":CIA", $cia, PDO::PARAM_INT );
		$sentencia->execute();

		$result_z = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    $mijson = json_encode($result_z);
    // echo $mijson . "\n";
    return ($mijson);
  }

  function exportar_movimientos_venta() {
    $body = file_get_contents('php://input');
    $idventa = -1;
    $codigo = "-1"; 
    $cia = 1; // Por default, la cia es 1
    if(isset($_GET['idventa']))   {    $idventa = $_GET['idventa'];  }
    if(isset($_POST['idventa']))  {    $idventa = $_POST['idventa'];  }
    if($body != "") {
      $micond_z = json_decode($body, true);
      if(array_key_exists("idventa", $micond_z)) { $idventa = $micond_z["idventa"]; }
    }
  	$conn=conecta_pdo();
		# echo $condiciones_z;
		$sql_z =  "select a.*, b.concepto, c.tda, d.codigo as promotor,
      e.iniciales 
      from movclis a 
      left outer join conceptos b on a.idconcepto = b.id
      left outer join codigoscaja c on a.idpoliza = c.id
      left outer join promotores d on a.idpoliza = d.id
      left outer join usuarios e on a.idusuario = e.id
      where a.idventa = :IDVENTA order by a.fecha, a.consecutivo";
		$sentencia = $conn->prepare($sql_z);

		#$sentencia=$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$sentencia->bindParam(":IDVENTA", $idventa, PDO::PARAM_INT );
		$sentencia->execute();
		$result_z = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    $movtos = array();
    foreach($result_z as $movto) {
      $iniciales = $movto["iniciales"];
      if (is_null($iniciales)) { $iniciales = ""; }
      $poliza = $movto["tda"];
      if (is_null($poliza)) { $poliza = ""; }
      $promotor = $movto["promotor"];
      if (is_null($promotor)) { $promotor = ""; }

      $mimovto = array (
        "idcli" => $movto["idventa"],
        "fecha" => $movto["fecha"],
        "concepto" => $movto["concepto"],
        "tipag" => $movto["tipopago"],
        "importe" => $movto["importe"],
        "recobon" => $movto["recobon"],
        "iniciales" => $iniciales,
        "poliza" => $poliza,
        "promotor" => $promotor
      );
      array_push($movtos, $mimovto);
    }
    $exportar = array (
      "modo" => "agregar_varios_movimientos",
      "movtos" => $movtos
    );


    $venta = json_encode($exportar);
    echo $venta;
  }

  function importar_dias_gracia() {
    $body = file_get_contents('php://input');
    $micond_z = json_decode($body, true);
    $clientes = $micond_z["clientes"];
    $resultados = array();
    foreach($clientes as $cliente) {
      array_push($resultados, json_decode(agregar_dias_gracia($cliente)) ) ;
    }
    echo json_encode($resultados);
  }
   
  function agregar_dias_gracia($cliente) {
    $idventa = -1;
    $diasgracia = 0;
    $codigo = "-1";
    if(array_key_exists("diasgracia", $cliente)) { $diasgracia = $cliente["diasgracia"]; }
    if(array_key_exists("numcli", $cliente)) { $codigo = $cliente["numcli"]; }
    if($diasgracia < 1) {
      return (json_encode(array("success" => "Días de gracia cero no se graban.")));
    }
    $codigo = $cliente["numcli"];
    $datosventa = busca_idventa($codigo);
    $idventa = $datosventa["idventa"];
    $tipodiasgracia = 810;

    $datosolicitud = array(
      "concepto" => $diasgracia,
      "codigo" => $codigo,
      "tipoventa" => 3, // Tipo de venta para días de gracia
      "tipodatosolicitud" => $tipodiasgracia, // Tipo de datosolicitud para días de gracia
      "idventa" => $cliente["idventa"],
    );
    return (agrega_datosolicitud($datosolicitud));

  }

  function importar_tc_cliente() {
    $body = file_get_contents('php://input');
    $micond_z = json_decode($body, true);
    $clientes = $micond_z["clientes"];
    $resultados = array();
    foreach($clientes as $cliente) {
      array_push($resultados, json_decode(agregar_tc_cliente($cliente)) ) ;
    }
    echo json_encode($resultados);
  }

  function agregar_tc_cliente ($cliente) {
    $idventa = -1;
    $clavetc = 0;
    $idventa = -1;
    $codigo = "-1";
    if(array_key_exists("clavetc", $cliente)) { $clavetc = $cliente["clavetc"]; }
    if(array_key_exists("idcli", $cliente)) { $idventa = $cliente["idcli"]; }
    if(array_key_exists("codigo", $cliente)) { $codigo = $cliente["codigo"]; }
    $tipotc = 630;
    $mitc = explode(" ", trim($clavetc));
    $miclavetc = $mitc[1];
    # echo "Clave TC: " . $miclavetc . "\n";
    $ii = 0;
    $datosolicitud = array(
      "concepto" => $miclavetc,
      "codigo" => $codigo,
      "tipoventa" => 3, // Tipo de venta para días de gracia
      "tipodatosolicitud" => $tipotc, // Tipo de datosolicitud para días de gracia
      "idventa" => $idventa,
    );
    return (agrega_datosolicitud($datosolicitud));

  }


  function agrega_datosolicitud($datosolicitud) {
    $conn=conecta_pdo();
    $concepto = $datosolicitud["concepto"];
    $idventa = $datosolicitud["idventa"];
    $tipoventa = $datosolicitud["tipoventa"];
    $tipodatosolicitud = $datosolicitud["tipodatosolicitud"];

  	$conn=conecta_pdo();
    $cia = 1;
    $datoconcepto = busca_datosolicitud($concepto);
    $idconcepto = $datoconcepto["id"];

    $sql_z = "select * from solicitudes where idcliente = :IDVENTA and tipo = :TIPOVTA and iddato = :TIPODATOSOL and cia = :CIA";
    $sentencia = $conn->prepare($sql_z);
		$sentencia->bindParam(":IDVENTA", $idventa, PDO::PARAM_INT );
		$sentencia->bindParam(":TIPOVTA", $tipoventa, PDO::PARAM_INT );
		$sentencia->bindParam(":TIPODATOSOL", $tipodatosolicitud, PDO::PARAM_INT );
		$sentencia->bindParam(":CIA", $cia, PDO::PARAM_INT );
		$sentencia->execute();
		$result_z = $sentencia->fetch(PDO::FETCH_ASSOC);
    $sql_z = "insert into solicitudes (idcliente, tipo, iddato, iddatosolicitud, cia, status) 
      values (:IDVENTA, :TIPOVTA, :TIPODATOSOL, :IDDATOSOLICITUD, :CIA, 'A')";

    if($result_z) {
      $idsolicitud = $result_z["id"];
      $sql_z = "update solicitudes set  iddatosolicitud = :IDDATOSOLICITUD where  id = :IDSOLICITUD";
      $sentencia = $conn->prepare($sql_z);
      $sentencia->bindParam(":IDSOLICITUD", $idsolicitud, PDO::PARAM_INT );
      $sentencia->bindParam(":IDDATOSOLICITUD", $idconcepto, PDO::PARAM_INT );
      $sentencia->execute();

    } else {
      $sql_z = "insert into solicitudes (idcliente, tipo, iddato, iddatosolicitud, cia, status) 
      values (:IDVENTA, :TIPOVTA, :TIPODATOSOL, :IDDATOSOLICITUD, :CIA, 'A')";
      $sentencia = $conn->prepare($sql_z);
      $sentencia->bindParam(":IDVENTA", $idventa, PDO::PARAM_INT );
      $sentencia->bindParam(":TIPOVTA", $tipoventa, PDO::PARAM_INT );
      $sentencia->bindParam(":TIPODATOSOL", $tipodatosolicitud, PDO::PARAM_INT );
      $sentencia->bindParam(":IDDATOSOLICITUD", $idconcepto, PDO::PARAM_INT );
      $sentencia->bindParam(":CIA", $cia, PDO::PARAM_INT );
      $sentencia->execute();
      $idsolicitud = $conn->lastInsertId();

    }
    return (
      json_encode(
          array("id" => $idsolicitud, 
          "idcliente" => $idventa,
          "iddato" => $tipodatosolicitud,
          "iddatosolicitud" => $idconcepto,
          "status" => "A",
          "cia" => $cia,
        )
      )
    );
  }

  
?>
