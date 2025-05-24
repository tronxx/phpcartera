<?php 
  header("Access-Control-Allow-Origin: *");
  header('Access-Control-Allow-Credentials: true');
  header('content-type: application/json; charset=utf-8');
  header("Access-Control-Allow-Methods: GET, PUT, POST, OPTIONS");
  date_default_timezone_set('America/Mazatlan');

  require_once("../libs/buscaDato.php");
  require_once("../conexiones/conecta.php");
  require_once("../libs/define_general.php");
  require_once("../libs/buscaDato.php");

  define_timezone();
  //$miskeys_z = json_decode(file_get_contents("tokens.json"), true);
  $midato = json_decode(file_get_contents('php://input'), true);
  //$misdatfiscal_z = json_decode(file_get_contents("datosfiscales.json"), true);
  $accion_z = $midato["modo"];  
  if($accion_z == "modificar") {
    echo modifica($midato["movcli"]);
  }

  if($accion_z == "agregar") {
    echo agrega($midato["movcli"]);
  }


function modifica($movcli) {
    // Validar datos de entrada
    if (!isset($movcli["id"]) || !is_numeric($movcli["id"])) {
        return json_encode(array("status" => false, "error" => "ID de movimiento inválido"));
    }

    // Obtener conexión PDO
    $conn = conecta_pdo();
    if (!$conn) {
        return json_encode(array("status" => false, "error" => "Error de conexión a la base de datos"));
    }

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        $idmovcli = $movcli["id"];
        $idventa = $movcli["idventa"];

        // 2. Preparar datos para la actualización
        $cobratario = $movcli["cobratario"];
        $promotor = busca_promotor($cobratario);
        $idpromotor = $promotor["id"] ?? -1;
        $concepto = $movcli["concepto"];
        $regconcepto = busca_concepto($concepto);
        $idconcepto = $regconcepto["id"] ?? -1;

        $importe = $movcli["importe"];
        $idusuario = $movcli["iduser"];
        $tipo = $movcli["tipopago"];
        $fecha = $movcli["fecha"];
        $recobon = $movcli["recobon"];

        $miusuario = busca_usuario_by_id($idusuario);
        $inicialesusuario = $miusuario["iniciales"] ?? '';

        // 3. Actualizar el movimiento
        // 3. Actualizar el movimiento
        $sql_update = "UPDATE movclis SET 
            idconcepto = :IDCONCEPTO, 
            fecha = :FECHA,
            tipopago = :TIPO, 
            cobratario = :COBRATARIO, 
            idcobratario = :IDPROMOTOR, 
            importe = :IMPORTE, 
            recobon = :RECOBON,
            usuario = :USUARIO, 
            idusuario = :IDUSUARIO
            WHERE id = :ID";

        $sentencia = $conn->prepare($sql_update);
        $sentencia->bindParam(':IDCONCEPTO', $idconcepto, PDO::PARAM_INT);
        $sentencia->bindParam(':FECHA', $fecha, PDO::PARAM_STR);
        $sentencia->bindParam(':TIPO', $tipo, PDO::PARAM_STR);
        $sentencia->bindParam(':COBRATARIO', $cobratario, PDO::PARAM_STR);
        $sentencia->bindParam(':IDPROMOTOR', $idpromotor, PDO::PARAM_INT);
        $sentencia->bindParam(':IMPORTE', $importe, PDO::PARAM_STR);
        $sentencia->bindParam(':RECOBON', $recobon, PDO::PARAM_STR);
        $sentencia->bindParam(':USUARIO', $inicialesusuario, PDO::PARAM_STR);
        $sentencia->bindParam(':IDUSUARIO', $idusuario, PDO::PARAM_INT);
        $sentencia->bindParam(':ID', $idmovcli, PDO::PARAM_INT);
        
        if (!$sentencia->execute()) {
            throw new Exception("Error al actualizar movimiento: " . implode(", ", $sentencia->errorInfo()));
        }

        // Confirmar transacción
        $conn->commit();

        return json_encode(array(
            "status" => true, 
            "idmovto" => $movcli["id"],
            "message" => "Movimiento actualizado correctamente"
        ));

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        return json_encode(array(
            "status" => false,
            "error" => "Error en la transacción: " . $e->getMessage()
        ));
    } finally {
        // Cerrar conexión
        $conn = null;
    }
}  

function agrega($movcli) {
    // Validar datos de entrada
    // Obtener conexión PDO
    $cia = 1;
    $conn = conecta_pdo();
    if (!$conn) {
        return json_encode(array("status" => false, "error" => "Error de conexión a la base de datos"));
    }

    try {
        // Iniciar transacción
        // $conn->beginTransaction();

        $idventa = $movcli["idventa"];

        // 2. Preparar datos para la actualización
        $codtda_z = $movcli["poliza"];
        $tienda = busca_idtienda($codtda_z);
        $idtda = $tienda["id"];
        if(is_null($idtda)) {
          $idtda = -1;
        }

        $cobratario = $movcli["cobratario"];
        $promotor = busca_promotor($cobratario);
        $idpromotor = $promotor["id"] ?? -1;
        $concepto = $movcli["concepto"];
        $regconcepto = busca_concepto($concepto);
        $idconcepto = $regconcepto["id"] ?? -1;

        $importe = $movcli["importe"];
        $idusuario = $movcli["iduser"];
        $tipo = $movcli["tipopago"];
        $fecha = $movcli["fecha"];
        $recobon = $movcli["recobon"];
        $coa = 'A';

        $miusuario = busca_usuario_by_id($idusuario);
        $inicialesusuario = $miusuario["iniciales"] ?? '';
        $conse = 1;

        // 3. Actualizar el movimiento
        // 3. Actualizar el movimiento
        $sql_update = "call add_movcli ( :IDVENTA, :FECHA, :COA, :IDCONCEPTO, :IDPOLIZA,
            :CONSECUTIVO, :TIPO, :RECOBON, :IMPORTE, :COBRATARIO, :USUARIO, 'A', 
            :IDPROMOTOR, :IDUSUARIO,
            :CIA, :CONCEPTO)";

        $sentencia = $conn->prepare($sql_update);
        $sentencia->bindParam(':IDVENTA', $idventa, PDO::PARAM_INT);
        $sentencia->bindParam(':FECHA', $fecha, PDO::PARAM_STR);
        $sentencia->bindParam(':COA', $coa, PDO::PARAM_STR);
        $sentencia->bindParam(':IDCONCEPTO', $idconcepto, PDO::PARAM_INT);
        $sentencia->bindParam(':IDPOLIZA', $idtda, PDO::PARAM_INT);
        $sentencia->bindParam(':CONSECUTIVO', $conse, PDO::PARAM_INT);
        $sentencia->bindParam(':TIPO', $tipo, PDO::PARAM_STR);
        $sentencia->bindParam(':RECOBON', $recobon, PDO::PARAM_STR);
        $sentencia->bindParam(':IMPORTE', $importe, PDO::PARAM_STR);
        $sentencia->bindParam(':COBRATARIO', $cobratario, PDO::PARAM_STR);
        $sentencia->bindParam(':USUARIO', $inicialesusuario, PDO::PARAM_STR);
        $sentencia->bindParam(':IDPROMOTOR', $idpromotor, PDO::PARAM_INT);
        $sentencia->bindParam(':IDUSUARIO', $idusuario, PDO::PARAM_INT);
        $sentencia->bindParam(':CIA', $cia, PDO::PARAM_INT);
        $sentencia->bindParam(':CONCEPTO', $concepto, PDO::PARAM_STR);
        
        if (!$sentencia->execute()) {
            throw new Exception("Error al agregar movimiento: " . implode(", ", $sentencia->errorInfo()));
        }
        $idmovcli = $conn->lastInsertId();
        // Confirmar transacción
        //$conn->commit();

        return json_encode(array(
            "status" => true, 
            "idmovto" => $idmovcli,
            "message" => "Movimiento agregado correctamente"
        ));

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        return json_encode(array(
            "status" => false,
            "error" => "Error en la transacción: " . $e->getMessage()
        ));
    } finally {
        // Cerrar conexión
        $conn = null;
    }
}  


  function xmodifica($movcli) {
    $id = $movcli["id"];
    $codpromotor = $movcli["cobratario"];
    $promotor = busca_promotor($codpromotor);
    $idpromotor = $promotor["id"];
    $concepto = $movcli["concepto"];
    $regconcepto = busca_concepto($concepto);
    $idconcepto = $regconcepto["id"];
    $importe = $movcli["importe"];
    $idusuario = $movcli["iduser"];
    $tipo = $movcli["tipopago"];
    $idusuario  = $movcli["iduser"];
    $idventa = $movcli["idventa"];
    $miusuario = busca_usuario_by_id($idusuario);
    $inicialesusuario = "";
    if($miusuario) {
        $inicialesusuario = $miusuario["iniciales"];
    }


    $conn=conecta_pdo();
    $sql_z = "select * from movclis where id = :ID"; 
    $buscli = $conn->prepare($sql_z);
    $buscli->bindParam(':ID', $id, PDO::PARAM_INT);
    $resbuscli = $buscli->execute();
    $resbuscli = $buscli->fetch(PDO::FETCH_ASSOC);
    if(!$resbuscli) {
      return json_encode(array("status" => false, "error" => "Id inexistente"));
      exit ();
    }
    $antimporte = $resbuscli["importe"];
    echo "Movimiento anterior: " . json_encode($resbuscli) . "<br>\n";


    $conn=conecta_pdo();
    $sql_z = "update movclis set idconcepto = :IDCONCEPTO, fecha = :FECHA,
      tipopago = :TIPO, cobratario = :PROMOTOR, idcobratario = :IDPROMOTOR, 
      importe = :IMPORTE, recobon = :ROB,
      usuario = :USUARIO, idusuario = :IDUSUARIO where movclis.id = :ID";
    echo "SQL: " . $sql_z . "<br>\n";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':FECHA', $fecha, PDO::PARAM_STR);
    $sentencia->bindParam(':IDCONCEPTO', $idconcepto, PDO::PARAM_INT);
    $sentencia->bindParam(':TIPO', $tipo, PDO::PARAM_STR);
    $sentencia->bindParam(':PROMOTOR', $promotor, PDO::PARAM_STR);
    $sentencia->bindParam(':IDPROMOTOR', $idpromotor, PDO::PARAM_INT);
    $sentencia->bindParam(':IMPORTE', $importe, PDO::PARAM_STR);
    $sentencia->bindParam(':ROB', $improb, PDO::PARAM_STR);
    $sentencia->bindParam(':USUARIO', $inicialesusuario, PDO::PARAM_STR);
    $sentencia->bindParam(':IDUSUARIO', $idusuario, PDO::PARAM_INT);
    $sentencia->bindParam(':ID', $id, PDO::PARAM_INT);
    $resultado = $sentencia->execute();
    $statusmov = $resultado;
    if(!$resultado) { $errormov = $sentencia->errorInfo(); }

    if($importe != $antimporte) {
        $sqlupdtvta_z = "update ventas set abonos = abonos + (:IMPORTE - :ANTIMPORTE) where idventa = :IDVENTA";
        $sentenciaupdtvta = $conn->prepare($sqlupdtvta_z);
        $sentenciaupdtvta->bindParam(':IMPORTE', $importe, PDO::PARAM_STR);
        $sentenciaupdtvta->bindParam(':ANTIMPORTE', $antimporte, PDO::PARAM_STR);
        $sentenciaupdtvta->bindParam(':IDVENTA', $idventa, PDO::PARAM_INT);
        $resultupdtvta = $sentenciaupdtvta->execute();    
        if(!$resultupdtvta) { $error = $sentenciaupdtvta->errorInfo(); }
    }
    if(!$statusmov) {
        return json_encode(array("status" => false, "error" => $errormov[2]));
    } else {
        return json_encode(array("status" => true, "idmovto" => $statusmov));
    }
  }

?>
