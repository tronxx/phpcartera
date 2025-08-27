<?php 
  header("Access-Control-Allow-Origin: *");
  header('Access-Control-Allow-Credentials: true');
  header('content-type: application/json; charset=utf-8');
  header("Access-Control-Allow-Methods: GET, PUT, POST, OPTIONS");
  date_default_timezone_set('America/Mazatlan');

  function exporta_json($mijson) {
    $micliente = json_decode($mijson, true);
    $arrayFormateado = array();
    $numeroRegistro = 0;
    foreach ($micliente as $registro) {
        foreach ($registro as $nombreCampo => $valor) {
                $lineaFormateada = $numeroRegistro . "." . $nombreCampo . ":" . $valor;
                $arrayFormateado[] = $lineaFormateada;
        }

    } 
    $resultado = implode("\n", $arrayFormateado);
    return ($resultado);

  }

  function exporta_venta($mijson) {
    $micliente = json_decode($mijson, true); 
    $cliente = array (
      "idcli" => $micliente["idventa"],
      "numcli" => $micliente["codigo"],
      "cia" => $micliente["cia"],
      "nombre" => $micliente["nombre"],
      "appat" => $micliente["appat"],
      "apmat" => $micliente["apmat"],
      "nompil1" => $micliente["nompil1"],
      "nompil2" => $micliente["nompil2"],
      "calle" => $micliente["calle"],
      "numpred" => $micliente["numpredio"],
      "codpost" => $micliente["codpostal"],
      "colonia" => $micliente["colonia"],
      "poblac" => $micliente["ciudad"],
      "status" => $micliente["status"],
      "qom"=> $micliente["qom"],
      "ticte"=> $micliente["ticte"],
      "ubica"=> $micliente["ubica"],
      "promotor" => $micliente["promotor"],
      "opcion" => $micliente["opcion"],
      "comisionprom" => $micliente["comisionprom"],
      "enganche" => $micliente["enganc"],
      "servicio" => $micliente["servicio"],
      "letra1" => $micliente["letra1"],
      "nulet" => $micliente["nulets"],
      "canle" => $micliente["canle"],
      "bonificacion" => $micliente["bonifi"],
      "preciolista" => $micliente["precon"],
      "piva" => $micliente["piva"],
      "pgocom" => "", 
      "pdsc" => $micliente["descuento"],
      "email" => $micliente["email"],
      "diacum" => "01",
      "mescum" => "01",
      "tarjetatc" => $micliente["tarjetatc"] ?? "",
    );
    $resultado = json_encode($cliente);
    return ($resultado);
    
  }

  function exporta_aval($mijson) {
    $micliente = json_decode($mijson, true); 
    $nombre = $micliente["nombre"];
    $nombreaval = explode(" ", $nombre ?? "");
    $dirav1 = $micliente["calle"] . " " . $micliente["numpredio"] . " " . $micliente["colonia"];
    $dirav2 = $micliente["ciudad"] . " " . $micliente["codpostal"];
    if (is_null($dirav1)) { $dirav1 = ""; }
    if (is_null($dirav2)) { $dirav2 = ""; }
    $aval = array (
      "appat" => $nombreaval[0] ?? "",
      "apmat" => $nombreaval[1] ?? "",
      "nompil1" => $nombreaval[2] ?? "",
      "nompil2" => $nombreaval[3] ?? "",
      "dirav1" => trim($dirav1),
      "dirav2" => trim($dirav2)
    );
    $resultado = json_encode($aval);
    return ($resultado);
    
  }


?>