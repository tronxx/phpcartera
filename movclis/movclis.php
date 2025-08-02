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

  if($accion_z == "buscarMovclis") {
    echo obtener_movclis($midato["idventa"]);
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
        $fecha = explode("T", $movcli["fecha"])[0] ;
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
        $fecha = explode("T", $movcli["fecha"])[0] ;
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

function obtener_movclis($idventa) {
    $conn = conecta_pdo();
    if (!$conn) {
        return json_encode(array("status" => false, "error" => "Error de conexión a la base de datos"));
    }
    $sql = "SELECT a.*, b.concepto, c.tda ,
        (case tipopago when 'AB' then recobon else null end) as bonifica,
        (case tipopago when 'AR' then recobon else null end) as recargo
        FROM movclis a 
        left outer join conceptos b on a.idconcepto = b.id 
        left outer join codigoscaja c on a.idpoliza = c.id
        WHERE idventa = :IDVENTA order by fecha, consecutivo";
    $sentencia = $conn->prepare($sql);
    $sentencia->bindParam(':IDVENTA', $idventa, PDO::PARAM_INT);
    if (!$sentencia->execute()) {
        throw new Exception("Error al obtener movimientos: " . implode(", ", $sentencia->errorInfo()));
    }

    $resultados = $sentencia->fetchAll(PDO::FETCH_ASSOC);

    $sql = "select b.* from facturas a 
        left outer join renfac b on a.id = b.idfactura
        where a.idventa = :IDVENTA order by b.conse";
    $sentencia = $conn->prepare($sql);
    $sentencia->bindParam(':IDVENTA', $idventa, PDO::PARAM_INT);
    if (!$sentencia->execute()) {
        throw new Exception("Error al obtener compra: " . implode(", ", $sentencia->errorInfo()));
    }
    $resultados2 = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    $compra = "";
    foreach ($resultados2 as $renfac) {
        $compra .=  $renfac["descri"];
        if(strlen($renfac["serie"])  > 0) {
            $compra .= " S/" . $renfac["serie"];
        }
        if(($renfac["folio"])  > 0) {
            $compra .= " #" . $renfac["folio"];
        }

    }

    $primerren = $resultados[0] ?? null;
    $primerren["id"] = -1;
    $primerren["concepto"] = $compra;
    $primerren["coa"] = "C";
    $primerren["importe"] = 0;
    $primerren["bonifica"] = 0;

    array_unshift($resultados, $primerren);
    return json_encode($resultados);
    $conn = null;
}   

?>
