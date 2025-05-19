<?php 
function busca_dato($micond_z, $dato) {
    $mivalor = "-1";
    if (array_key_exists ($dato, $micond_z))     $mivalor = $micond_z[$dato];
    return ($mivalor);

  }

function obtenerNumeroAntesDeBarra($cadena) {
  // Busca el patrón: cualquier dígito (1 o más) seguido de una barra
  if (preg_match('/(\d+)\//', $cadena, $matches)) {
      return $matches[1]; // Devuelve el primer grupo capturado (los dígitos)
  }
  return null; // Devuelve null si no se encuentra el patrón
}

  function busca_promotor($codigo) {
    $cia = 1;
    $conn=conecta_pdo();
    $sql_z =  "select * from promotores where codigo = :CODIGO ";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':CODIGO', $codigo, PDO::PARAM_STR);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    return $resultado;
  }

  function busca_usuario($codigo) {
    $cia = 1;
    $conn=conecta_pdo();
    $sql_z =  "select * from usuarios where iniciales = :CODIGO ";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':CODIGO', $codigo, PDO::PARAM_STR);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    return $resultado;
  }

  function busca_usuario_by_id($idusuario) {
    $cia = 1;
    $conn=conecta_pdo();
    $sql_z =  "select * from usuarios where id = :IDUSUARIO ";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':IDUSUARIO', $idusuario, PDO::PARAM_INT);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    return $resultado;
  }

  function busca_concepto($concepto) {
    $conn = conecta_pdo();
    
    // Primero intentamos encontrar el concepto
    $sql = "SELECT * FROM conceptos WHERE concepto = :CONCEPTO";
    $sentencia = $conn->prepare($sql);
    $sentencia->bindParam(':CONCEPTO', $concepto, PDO::PARAM_STR);
    $sentencia->execute();
    
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    if($resultado) {
        return $resultado;
    }
    
    // Si no existe, intentamos insertarlo
    try {
        $sql = "INSERT INTO conceptos(concepto) VALUES (:CONCEPTO)";
        $sentencia = $conn->prepare($sql);
        $sentencia->bindParam(':CONCEPTO', $concepto, PDO::PARAM_STR);
        $sentencia->execute();
        
        $idconcepto = $conn->lastInsertId();
        return array("id" => $idconcepto, "concepto" => $concepto);
    } catch (PDOException $e) {
        // Si falla por duplicado (cuando otro proceso lo insertó primero)
        if($e->errorInfo[1] == 1062) { // Código de error para duplicado en MySQL
            // Volvemos a buscar
            $sentencia = $conn->prepare("SELECT * FROM conceptos WHERE concepto = :CONCEPTO");
            $sentencia->bindParam(':CONCEPTO', $concepto, PDO::PARAM_STR);
            $sentencia->execute();
            return $sentencia->fetch(PDO::FETCH_ASSOC);
        }
        throw $e; // Relanzamos otras excepciones
    }
  }  

  function xbusca_concepto ($concepto) {
    $cia = 1;
    $conn=conecta_pdo();
    $sql_z =  "select * from conceptos where concepto = :CONCEPTO";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':CONCEPTO', $concepto, PDO::PARAM_STR);
    $sentencia->execute();
    echo "Buscando concepto: " . $concepto . "<br>\n";
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    echo "Resultado: " . json_encode($resultado) . "<br>\n";
    if($resultado) {
      echo "Ya existe concepto : " . $concepto . " id " . $resultado["id"] . " <br>\n";
      return $resultado;
    } else {
      $sqlins_z =  "insert into conceptos(concepto) values (:CONCEPTO)";
      $sentenciains = $conn->prepare($sqlins_z);
      $sentenciains->bindParam(':CONCEPTO', $concepto, PDO::PARAM_STR);
      $sentenciains->execute();
      $resultins = $sentenciains->execute();
      if($resultins) {
        $idconcepto = $conn->lastInsertId();
        echo "Concepto agregado: " . $concepto . " id " . $idconcepto . " <br>\n";
      } else {
        $idconcepto = -1;
      }
      $resultado = array("id" => $idconcepto, "concepto" => $concepto);

    }
  }


 function busca_idtienda($codigo) {
    $cia = 1;
    $conn=conecta_pdo();
    $sql_z =  "select * from codigoscaja where tda = :TIENDA ";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':TIENDA', $codigo, PDO::PARAM_STR);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    return $resultado;
  }

  function busca_existencia_renpol($datos) {
    $cia = 1;
    $conn=conecta_pdo();
    $sql_z = "select * from renpol where idpoliza = :IDPOLIZA and idventa = :IDVENTA 
        and tipo = :TIPO and rob = :ROB and importe = :IMPORTE and concepto = :CONCEPTO";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':IDPOLIZA', $datos["idpoliza"], PDO::PARAM_INT);
    $sentencia->bindParam(':IDVENTA', $datos["idventa"], PDO::PARAM_INT);
    $sentencia->bindParam(':TIPO', $datos["tipo"], PDO::PARAM_STR);
    $sentencia->bindParam(':ROB', $datos["rob"], PDO::PARAM_STR);
    $sentencia->bindParam(':IMPORTE', $datos["importe"], PDO::PARAM_STR);
    $sentencia->bindParam(':CONCEPTO', $datos["concepto"], PDO::PARAM_STR);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    return $resultado;
  }

  function busca_movcli($datos) {
    $cia = 1;
    $conn=conecta_pdo();
    $sql_z =  "select * from movclis where idventa = :IDVENTA
      and fecha = :FECHA and idpoliza = :IDTIENDA and recobon = :ROB 
      and importe = :IMPORTE and idconcepto = :IDCONCEPTO";
    $sentencia = $conn->prepare($sql_z);
    $sentencia->bindParam(':IDVENTA', $datos["idventa"], PDO::PARAM_INT);
    $sentencia->bindParam(':FECHA', $datos["fecha"], PDO::PARAM_STR);
    $sentencia->bindParam(':IDTIENDA', $datos["idtienda"], PDO::PARAM_INT);
    $sentencia->bindParam(':ROB', $datos["improb"], PDO::PARAM_STR);
    $sentencia->bindParam(':IMPORTE', $datos["importe"], PDO::PARAM_STR);
    $sentencia->bindParam(':IDCONCEPTO', $datos["idconcepto"], PDO::PARAM_INT);
    #echo " Buscando idventa: " . $datos["idventa"] . " fecha: " . $datos["fecha"] . " idtienda: " . $datos["idtienda"] . " rob: " . $datos["improb"] . " importe: " . $datos["importe"] . " idconcepto: " . $datos["idconcepto"] . "<br>\n";
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    return $resultado;
  } 

?>