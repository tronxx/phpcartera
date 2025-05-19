<?php 
  header("Access-Control-Allow-Origin: *");
  header('Access-Control-Allow-Credentials: true');
  header('content-type: application/json; charset=utf-8');
  header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS");

  require_once ('../conexiones/conecta.php');
  require_once ('../libs/define_general.php');
  
  define_timezone();
  //$miskeys_z = json_decode(file_get_contents("tokens.json"), true);
  //$misdatfiscal_z = json_decode(file_get_contents("datosfiscales.json"), true);
  $accion_z = $_SERVER['REQUEST_METHOD'];
  if($accion_z == "POST") {
    echo (agregar_uso_cfdi());
  }

  if($accion_z == "GET") {
    echo buscar_uso_cfdi();
  }

  if($accion_z == "PUT") {
    echo modificar_uso_cfdi();
  }
  
  if($accion_z == "DELETE") {
    echo borrar_uso_cfdi();
  }
  

  function agregar_uso_cfdi() {
    $misdatos_z = json_decode(file_get_contents('php://input'), true);
    $clave_z = $misdatos_z["clave"];
    $nombre_z = $misdatos_z["nombre"];
    $cia_z = $misdatos_z["cia"];
    $status_z = $misdatos_z["status"];
    $usocfdi_z = json_decode( buscar_uso_cfdi_x_codigo_y_cia($clave_z, $cia_z)) ;
    
    if( !empty( $usocfdi_z)) {
      $error = array("error" => "Ya existe esa clave");
      throw New Exception( json_encode($error) );


    }

    $sql = 'insert into usocfdi (clave, nombre, cia, status ) 
      values ( :CLAVE, :NOMBRE, :CIA, :STATUS)';
    $conn=conecta_pdo();
    $sentencia = $conn->prepare($sql);
    $sentencia->bindParam(":CLAVE", $clave_z, PDO::PARAM_STR );
    $sentencia->bindParam(":NOMBRE", $nombre_z, PDO::PARAM_STR );
    $sentencia->bindParam(":STATUS", $status_z, PDO::PARAM_STR );
    $sentencia->bindParam(":CIA", $cia_z, PDO::PARAM_INT );  
    try {    
      $sentencia->execute();
      $id = $conn->lastInsertId();
      
      return (buscar_uso_cfdi_x_id($id) );  
    } catch(Exception $e) {
      throw New Exception( $e->getMessage() );  
    }

  }

  function buscar_uso_cfdi() {
    $id_z = -1;
    $cia_z = -1;
    $codigo_z = "-1";
    if(isset($_GET['id'])) {    $id_z = $_GET['id'];  }
    if(isset($_GET['codigo'])) {    $codigo_z = $_GET['codigo'];  }
    if(isset($_GET['cia'])) {    $cia_z = $_GET['cia'];  }
    $sql_z = "";
    $sentencia = "";

    
    if ($id_z != -1) {
      return buscar_uso_cfdi_x_id($id_z);
    }

    if ($codigo_z != "-1") {
      return buscar_uso_cfdi_x_codigo_y_cia($codigo_z, $cia_z);
  
    }
    if($cia_z != -1 && $codigo_z == "-1" && $id_z == "-1") {
      return buscar_uso_cfdi_x_cia($cia_z);
  
    }

  }

  function buscar_uso_cfdi_x_id($id) {
    $sql_z = "";
    $sentencia = "";

    $conn=conecta_pdo();
    $sql_z =  "select * from usocfdi  where id = :ID";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(":ID", $id, PDO::PARAM_INT );  
    $sentencia->execute();
    $result_z = $sentencia->fetch(PDO::FETCH_ASSOC);
    if($result_z) {
      return (json_encode($result_z));
    } else {
      return "{}";
    }
  }

  function buscar_uso_cfdi_x_cia($cia) {
    $sql_z = "";
    $sentencia = "";

    $conn=conecta_pdo();
    $sql_z =  "select * from usocfdi  where cia = :CIA order by clave";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(":CIA", $cia, PDO::PARAM_INT );  
    $sentencia->execute();
    $result_z = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    return (json_encode($result_z));
  }

  function buscar_uso_cfdi_x_codigo_y_cia($codigo, $cia) {
    $sql_z = "";
    $sentencia = "";

    $conn=conecta_pdo();
    $sql_z =  "select * from usocfdi  where clave = :CLAVE and cia = :CIA";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(":CLAVE", $codigo, PDO::PARAM_STR );
    $sentencia->bindParam(":CIA", $cia, PDO::PARAM_INT );  
    $sentencia->execute();
    $result_z = $sentencia->fetch(PDO::FETCH_ASSOC);
    return (json_encode($result_z));
  }

  function modificar_uso_cfdi() {
    $misdatos_z = json_decode(file_get_contents('php://input'), true);
    $id_z = $misdatos_z["id"];
    $clave_z = $misdatos_z["clave"];
    $nombre_z = $misdatos_z["nombre"];
    $cia_z = $misdatos_z["cia"];
    $status_z = $misdatos_z["status"];

    $usocfdi_z = json_decode(buscar_uso_cfdi_x_id($id_z))  ;
    if( empty( $usocfdi_z)) {
      $error = array("error" => "Uso cfdi Inexistente");
      throw New Exception( json_encode($error) );
    }

    $sql = 'update usocfdi set nombre = :NOMBRE, status = :STATUS 
       where id = :ID'; 
    $conn=conecta_pdo();
    $sentencia = $conn->prepare($sql);
    $sentencia->bindParam(":NOMBRE", $nombre_z, PDO::PARAM_STR );
    $sentencia->bindParam(":STATUS", $status_z, PDO::PARAM_STR );
    $sentencia->bindParam(":ID", $id_z, PDO::PARAM_INT );  
    try {    
      $sentencia->execute();    
      $result_z = $sentencia->fetch(PDO::FETCH_ASSOC);
      return (buscar_uso_cfdi_x_id($id_z) );  
    } catch(Exception $e) {
      throw New Exception( $e->getMessage() );  
    }

  }

  function borrar_uso_cfdi() {
    $misdatos_z = json_decode(file_get_contents('php://input'), true);
    $id_z = $misdatos_z["id"];
    $usocfdi_z = json_decode(buscar_uso_cfdi_x_id($id_z));
    if( empty( $usocfdi_z)) {
      $error = array("error" => "Uso cfdi Inexistente");
      throw New Exception( json_encode($error) );
    }

    $sql = 'delete from usocfdi where id = :ID'; 
    $conn=conecta_pdo();
    $sentencia = $conn->prepare($sql);
    $sentencia->bindParam(":ID", $id_z, PDO::PARAM_INT );  
    try {    
      $sentencia->execute();    
      $rowsdeleted = $sentencia->rowCount();
      $result_z = array("status" => "Ok", "rowsdeleted" => $rowsdeleted ) ;
      return (json_encode($result_z));  
    } catch(Exception $e) {
      throw New Exception( $e->getMessage() );  
    }

  }


?>