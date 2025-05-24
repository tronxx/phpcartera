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
    importa_movclis($midato["movclis"]);
  }
  if($accion_z == "verifica") {
    verifica_poliza($midato["poliza"]);
  }

  function importa_movclis($movclis_z) {
    $conn=conecta_pdo();
    # echo $condiciones_z;
    $movtosagregados = array();
    foreach($movclis_z as $movcli_z) {
        $codtda_z = $movcli_z["poliza"];
        $tienda = busca_idtienda($codtda_z);
        $idtda = $tienda["id"];
        if(is_null($idtda)) {
          $idtda = -1;
        }
        $iniciales = $movcli_z["usuario"];
        $miusuario = busca_usuario($iniciales);
        if($miusuario) {
          $idusuario = $miusuario["id"];
        } else {
          $idusuario = 0;
        }
        if($movcli_z["trpag"] == "AB") {
            $improb_z = $movcli_z["bonificacion"];
        } else {
            $improb_z = $movcli_z["recargo"];
        }
        $codpromotor = $movcli_z["oper"];
        $promotor = busca_promotor($codpromotor);
        $idpromotor = $promotor["id"];
        $concepto = $movcli_z["concep"];
        $regconcepto = busca_concepto($concepto);
        $idconcepto = $regconcepto["id"];
        $datosmovcli_z = array(
          "idtienda" => $idtda,
          "idventa" => $movcli_z["idcli"],
          "fecha" => $movcli_z["fechamov"],
          "improb" => $improb_z,
          "idconcepto" => $idconcepto,
          "importe" => $movcli_z["importe"],
        );
        # echo "Busca movimiento: " . $movcli_z["idventa"] . " " . $movcli_z["fechamov"] . " " . 
        #$codtda_z . " " . $concepto . " Importe " . $movcli_z["importe"] . "<br>\n";
        $yaexistemovcli = busca_movcli($datosmovcli_z);
        if(!$yaexistemovcli) {
            $nvomovto = agrega_movto($movcli_z, $codtda_z, $idpromotor, $idtda, $idusuario, $idconcepto);
            array_push($movtosagregados, $nvomovto);
        }
    }
    echo json_encode(array("status" => true, "movtosagregados" => $movtosagregados));

  }

    function agrega_movto($movcli, $codtda_z, $idpromotor, $idtienda, $idusuario, $idconcepto) {
        $cia = 1;
        $idpoliza = $idtienda;
        $conse = $movcli["conse"];
        $fecha = $movcli["fechamov"];
        $coa = $movcli["coa"];
        $idventa = $movcli["idcli"];
        $tipo = $movcli["tipag"];
        $concepto = $movcli["concep"];
        $importe = $movcli["importe"]; 
        $cobratario = $movcli["oper"]; 
        $inicialesusuario = $movcli["usuario"]; 
        $regconcepto = busca_concepto($concepto);
        $idconcepto = $regconcepto["id"];

        if($tipo == "AB") {
            $improb = $movcli["bonificacion"];
        } else {
            $improb = $movcli["recargo"];
        }
        # echo "Agregando movimiento: " . $idventa . " " . $fecha . " " .  $concepto . " idconcepto:" . $idconcepto . "<br>\n ";
        if (is_null($idtienda)) $idtienda = -1;
        if(is_null(($idusuario))) $idusuario = -1;
        if(is_null($idpromotor)) $idpromotor = -1;
        if($tipo == "") $tipo = "AB";
        if($inicialesusuario == "") $inicialesusuario = ".";

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
          return json_encode(array("status" => true, "idmovto" => $resultado));
        }
    }


?>
