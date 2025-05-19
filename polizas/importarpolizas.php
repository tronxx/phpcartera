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
  if($accion_z == "importar") {
    importa_poliza($midato["poliza"]);
  }
  if($accion_z == "verifica") {
    verifica_poliza($midato["poliza"]);
  }

  function importa_poliza($poliza_z) {
    $conn=conecta_pdo();
    # echo $condiciones_z;
    $codtda_z = $poliza_z["tda"];
    $tienda = busca_idtienda($codtda_z);
    $fecha  = $poliza_z["fecha"];
    $idtda = $tienda["id"];
    $idpoliza = 0;
    $cia = 1;
    $sql_z =  sprintf("select * from polizas where fecha = :FECHA and tda= :TIENDA");
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':FECHA', $fecha, PDO::PARAM_STR);
    $sentencia->bindParam(':TIENDA', $codtda_z, PDO::PARAM_STR);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    if($resultado) {
      $idpoliza = $resultado["id"];  
    } else {
      $sql_z =  sprintf("insert into polizas ");
      $sql_z =  $sql_z . sprintf(" (fecha, tda, idtienda, cia, bonif, recar, importe, iduuid, idfactura, status) ");
      $sql_z =  $sql_z . sprintf(" values (:FECHA, :TIENDA, :IDTIENDA, :CIA, 0, 0, 0, 0, 0, 'A')");
      $sentencia = $conn->prepare($sql_z);
      $sentencia->bindParam(':FECHA', $fecha, PDO::PARAM_STR);
      $sentencia->bindParam(':TIENDA', $codtda_z, PDO::PARAM_STR);
      $sentencia->bindParam(':IDTIENDA', $idtda, PDO::PARAM_INT);
      $sentencia->bindParam(':CIA', $cia, PDO::PARAM_INT);
      $sentencia->execute();
      $idpoliza = $conn->lastInsertId();
      #echo $sql_z . "<br>";
    }
    $renglonesagregados = importa_renglones($poliza_z, $idpoliza);
    echo json_encode(array("status" => true, "idpoliza" => $idpoliza, "renglones" => $renglonesagregados));

}

  function importa_renglones($poliza_z, $idpoliza) {
    $conn=conecta_pdo();
    $conse = 0;
    $cia = 1;
    $sql_z = "insert into renpol ";
    
    $sql_z =  $sql_z . " (idpoliza, conse, idventa, sino, concepto, tipo, rob,
      importe, vence, comision, dias, tienda, cobratario, letra, iduuid, idfactura, cia,
      ace, idusuario, salcli) ";
    $sql_z =  $sql_z . " values (:IDPOLIZA, :CONSE, :IDVENTA, :SINO, 
      :CONCEPTO, :TIPO, :ROB, :IMPORTE, :VENCE, :COMISION, :DIAS, :TIENDA, :COBRATARIO,
      :LETRA, :IDUUID, :IDFACTURA, :CIA, :ACE, :IDUSUARIO, :SALCLI )";
    $sentencia = $conn->prepare($sql_z);
    $antpromor = "-1";
    $antusr = "-1";
    $idpromotor = 0;
    $idusuario = 0;
    $iduuid = 0;
    $idfactura = 0;
    $renglonesagregados = [];
    foreach ($poliza_z["renglones"] as $renglon) {
      $rob_z = 0;
      $letra = obtenerNumeroAntesDeBarra($renglon["concep"]);
      $nuletra = intval($letra);
      $codpromotor = $renglon["cobr2"];
      $usuario = $renglon["usuario"];
      if($usuario != $antusr ) {
        $miusuario = busca_usuario($usuario);
        if($miusuario) {
          $idusuario = $miusuario["id"];
        } else {
          $idusuario = 0;
        }
        $antusr = $usuario;
      }

      if($renglon["rob"] == "AB") {
        $rob_z = $renglon["bonificacion"];
      } else {
        $rob_z = $renglon["recargo"];
      }
      $datosrenpol = array("idpoliza" => $idpoliza,  "idventa" => $renglon["idcli"],
        "tipo" => $renglon["tipo"], "rob" => $rob_z, "importe" => $renglon["importe"],
        "concepto" => $renglon["concep"]);
      $existen = busca_existencia_renpol($datosrenpol);
      if(!$existen) {
        $sentencia->bindParam(':IDPOLIZA', $idpoliza, PDO::PARAM_INT);
        $sentencia->bindParam(':CONSE', $conse, PDO::PARAM_INT);
        $sentencia->bindParam(':IDVENTA', $renglon["idcli"], PDO::PARAM_INT);
        $sentencia->bindParam(':SINO', $renglon["sino"], PDO::PARAM_STR);
        $sentencia->bindParam(':CONCEPTO', $renglon["concep"], PDO::PARAM_STR);
        $sentencia->bindParam(':TIPO', $renglon["tipo"], PDO::PARAM_STR);
        $sentencia->bindParam(':ROB', $rob_z, PDO::PARAM_STR);
        $sentencia->bindParam(':IMPORTE', $renglon["importe"], PDO::PARAM_STR);
        $sentencia->bindParam(':VENCE', $renglon["fecven"], PDO::PARAM_STR);
        $sentencia->bindParam(':COMISION', $renglon["comis"], PDO::PARAM_STR);
        $sentencia->bindParam(':DIAS', $renglon["dias"], PDO::PARAM_INT);
        $sentencia->bindParam(':TIENDA', $renglon["tda"], PDO::PARAM_STR);
        $sentencia->bindParam(':COBRATARIO', $renglon["cobr2"], PDO::PARAM_STR);
        $sentencia->bindParam(':LETRA', $nuletra, PDO::PARAM_STR);
        $sentencia->bindParam(':IDUUID', $iduuid, PDO::PARAM_STR);
        $sentencia->bindParam(':IDFACTURA', $idfactura, PDO::PARAM_STR);
        $sentencia->bindParam(':CIA', $cia, PDO::PARAM_INT);
        $sentencia->bindParam(':ACE', $renglon["ace"], PDO::PARAM_STR);
        $sentencia->bindParam(':IDUSUARIO', $idusuario, PDO::PARAM_STR);
        $sentencia->bindParam(':SALCLI', $renglon["salcli"], PDO::PARAM_STR);
        $conse = $conse + 1;
        $resultado = $sentencia->execute();
        if (!$resultado) {
          $error = $sentencia->errorInfo();
          echo json_encode(array("status" => false, "error" => $error[2]));
          return;
        }
        $idrenpol = $conn->lastInsertId();
        array_push($renglonesagregados, $idrenpol);
      } 
      
    }
    return ($renglonesagregados);
  }


  ## --> Revision de la poliza para checar si todos los renglones están afectados

   function verifica_poliza($poliza_z) {
    $conn=conecta_pdo();
    # echo $condiciones_z;
    $codtda_z = $poliza_z["tda"];
    $tienda = busca_idtienda($codtda_z);
    $fecha  = $poliza_z["fecha"];
    $idtda = $tienda["id"];
    $idpoliza = 0;
    $status = [];
    $cia = 1;
    $renglonesagregados = [];
    $sql_z =  sprintf("select * from polizas where fecha = :FECHA and tda= :TIENDA");
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':FECHA', $fecha, PDO::PARAM_STR);
    $sentencia->bindParam(':TIENDA', $codtda_z, PDO::PARAM_STR);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    if($resultado) {
      $idpoliza = $resultado["id"];  
      $renglones = verifica_renglones($idpoliza, $fecha, $codtda_z);
      array_push($renglonesagregados, $renglones);
      $status = array("status" => true, "renglones" => $renglonesagregados);

    } else {
      $status = array("status" => false, "error" => "No se encontró la poliza");
    }
    echo json_encode($status);

}

function verifica_renglones($idpoliza, $fecha, $codtda_z) {
    $antpromor = "-1";
    $antidusuario = "-1";
    $inicialesusuario = "";
    $conn=conecta_pdo();
    $sql_z =  sprintf("select * from renpol where idpoliza = :IDPOLIZA");
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':IDPOLIZA', $idpoliza, PDO::PARAM_INT);
    $sentencia->execute();
    $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    $tienda = busca_idtienda($codtda_z);
    $idtienda = $tienda["id"];
    $movagregados = [];
    if($resultado) {
      //echo var_dump($resultado);
      foreach($resultado as $renglon) {
        $idventa = $renglon["idventa"];
        $importe = $renglon["importe"];
        $improb = $renglon["rob"];
        $concepto = $renglon["concepto"];
        $regconcepto = busca_concepto($concepto);
        $idconcepto = $regconcepto["id"];
        $cobratario = $renglon["cobratario"];
        if($cobratario != $antpromor) {
          $promotor = busca_promotor($cobratario);
          if($promotor) {
            $idpromotor = $promotor["id"];
          } else {
            $idpromotor = 0;
          }
          $antpromor = $cobratario;
        }
        $idusuario = $renglon["idusuario"];
        if($idusuario != $antidusuario) {
          $usuario = busca_usuario_by_id($idusuario);
          if($usuario) {
            $idusuario = $usuario["id"];
            $inicialesusuario = $usuario["iniciales"];
          } else {
            $idusuario = 0;
          }
          $antidusuario = $idusuario;
        }
        $tipo = $renglon["tipo"];
        $sql_z =  sprintf("select * from movclis where idventa = :IDVENTA");
        $sql_z .= sprintf(" and fecha = :FECHA and idpoliza = :IDTIENDA");
        $sql_z .= sprintf(" and recobon = :ROB and importe = :IMPORTE");
        $sql_z .= sprintf(" and idconcepto = :IDCONCEPTO");
        $sentencia = $conn->prepare($sql_z);
        $sentencia->bindParam(':IDVENTA', $idventa, PDO::PARAM_INT);
        $sentencia->bindParam(':FECHA', $fecha, PDO::PARAM_STR);
        $sentencia->bindParam(':IDTIENDA', $idtienda, PDO::PARAM_INT);
        $sentencia->bindParam(':ROB', $improb, PDO::PARAM_STR);
        $sentencia->bindParam(':IMPORTE', $importe, PDO::PARAM_STR);
        $sentencia->bindParam(':IDCONCEPTO', $idconcepto, PDO::PARAM_INT);
        $sentencia->execute();
        $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
        if(!$resultado) {
          $nvoren = agrega_movto($renglon, $fecha, $codtda_z, $idpromotor, $idtienda, $inicialesusuario);
          array_push($movagregados, $nvoren);
        }
      }
    }
  } 

  function agrega_movto($renpol, $fecha, $codtda_z, $idpromotor, $idtienda, $inicialesusuario) {
    $cia = 1;
    $idpoliza = $renpol["idpoliza"];
    $conse = $renpol["conse"];
    $coa = 'A';
    $idventa = $renpol["idventa"];
    $tipo = $renpol["tipo"];
    $improb = $renpol["rob"];
    $importe = $renpol["importe"]; 
    $cobratario = $renpol["cobratario"];
    $usuario = $renpol["usuario"];
    $idusuario = $renpol["idusuario"];
    $concepto = $renpol["concepto"];

    $conn=conecta_pdo();
    $sql_z = "call add_movcli(:IDVENTA, :FECHA, :COA, :IDCONCEPTO, :IDPOLIZA, 
      :CONSECUTIVO, :TIPO, :ROB, :IMPORTE, :COBRATARIO, :USUARIO, 'A', :IDPROMOTOR, :IDUSUARIO,
      :CIA, :CONCEPTO)"; 
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':IDVENTA', $idventa, PDO::PARAM_INT);
    $sentencia->bindParam(':FECHA', $fecha, PDO::PARAM_STR);
    $sentencia->bindParam(':COA', $coa, PDO::PARAM_STR);
    $sentencia->bindParam(':IDCONCEPTO', $idconcepto, PDO::PARAM_INT);
    $sentencia->bindParam(':IDPOLIZA', $idtienda, PDO::PARAM_INT);
    $sentencia->bindParam(':CONSECUTIVO', $conse, PDO::PARAM_INT);
    $sentencia->bindParam(':TIPO', $tipo, PDO::PARAM_STR);
    $sentencia->bindParam(':ROB', $improb, PDO::PARAM_STR);
    $sentencia->bindParam(':IMPORTE', $importe, PDO::PARAM_STR);
    $sentencia->bindParam(':COBRATARIO', $cobratario, PDO::PARAM_STR);
    $sentencia->bindParam(':USUARIO', $inicialesusuario, PDO::PARAM_STR);
    $sentencia->bindParam(':IDPROMOTOR', $idpromotor, PDO::PARAM_INT);
    $sentencia->bindParam(':IDUSUARIO', $idusuario, PDO::PARAM_INT);
    $sentencia->bindParam(':CIA', $cia, PDO::PARAM_INT);
    $sentencia->bindParam(':CONCEPTO', $concepto, PDO::PARAM_STR);
    $resultado = $sentencia->execute();
    if(!$resultado) {
      $error = $sentencia->errorInfo();
      return json_encode(array("status" => false, "error" => $error[2]));
    } else {
      $idmovto = $conn->lastInsertId();
      return json_encode(array("status" => true, "idmovto" => $idmovto));
    }
  }

?>