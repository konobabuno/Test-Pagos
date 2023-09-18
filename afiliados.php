<?php session_start();

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once('blog/wp-load.php');
include('funciones.php');

function extraerASIN($url)
{

  $asin_arr = array();
  preg_match('/(?:dp|o|gp|-|dp\/product|gp\/product)\/(B[0-9]{2}[0-9A-Z]{7}|[0-9]{9}(?:X|[0-9]))/', $url, $asin_arr);
  $asin = $asin_arr[1];
  return $asin;
}

function get_domain($url)
{
  // Remove protocol from $url
  $url = str_replace("http://", "", $url);
  $url = str_replace("https://", "", $url);

  // Remove page and directory references
  if (stristr($url, "/"))
    $url = substr($url, 0, strpos($url, "/"));

  return $url;
}

function elim($cadena)
{

  //Codificamos la cadena en formato utf8 en caso de que nos de errores
  $cadena = utf8_encode($cadena);

  //Ahora reemplazamos las letras
  $cadena = str_replace(
    array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
    array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
    $cadena
  );

  $cadena = str_replace(
    array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
    array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
    $cadena
  );

  $cadena = str_replace(
    array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
    array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
    $cadena
  );

  $cadena = str_replace(
    array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
    array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
    $cadena
  );

  $cadena = str_replace(
    array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
    array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
    $cadena
  );

  $cadena = str_replace(
    array('ñ', 'Ñ', 'ç', 'Ç'),
    array('n', 'N', 'c', 'C'),
    $cadena
  );

  return $cadena;
}

define('BOT_TOKEN', '1748851607:AAEUymmNqI2uZviRMa4Oq7bP_zSXbA2MySM');
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

function apiRequest($method, $parameters)
{
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
      $val = json_encode($val);
    }
  }
  $url = API_URL . $method . '?' . http_build_query($parameters);

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);

  return exec_curl_request($handle);
}

function exec_curl_request($handle)
{
  $response = curl_exec($handle);

  if ($response === false) {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    error_log("Curl returned error $errno: $error\n");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if ($http_code >= 500) {
    // do not wat to DDOS server if something goes wrong
    sleep(10);
    return false;
  } else if ($http_code != 200) {
    $response = json_decode($response, true);
    error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401) {
      throw new Exception('Invalid access token provided');
    }
    return false;
  } else {
    $response = json_decode($response, true);
    if (isset($response['description'])) {
      error_log("Request was successful: {$response['description']}\n");
    }
    $response = $response['result'];
  }

  return $response;
}

function generateRandomString($length = 10)
{
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

function getUrl($url)
{


  //if(@function_exists('curl_init')) {

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
  curl_setopt($ch, CURLOPT_ENCODING, "");
  curl_setopt($ch, CURLOPT_AUTOREFERER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); # required for https urls
  curl_setopt($ch, CURLOPT_MAXREDIRS, 15);
  $site = curl_exec($ch);
  curl_close($ch);
  //} else {
  //	global $site;
  //	$site = file_get_contents($url);
  //}
  return $site;
}

$afiliado = sanitize_text_field($_GET['afil']);
$asin = sanitize_text_field($_POST['asin']);
$precio_actual = sanitize_text_field($_POST['pactual']);
$precio_actual = str_replace(",", "", $precio_actual);
$precio_oferta = sanitize_text_field($_POST['poferta']);
$precio_oferta = str_replace(",", "", $precio_oferta);

$mystring = $asin;
$findme = 'amazon';
$pos = strpos($mystring, $findme);

if ($pos === false) {
  $asin = $asin;
} else {
  $asin = extraerASIN($asin);
}

if ($_SESSION['aff'] <> "") {
  $seguir = 1;
}

if ($afiliado) {
  $_SESSION['aff'] = $afiliado;

}

if ($seguir == 1) {

  if ($asin <> "") {

    $url_desc = "https://api.keepa.com/product?key=4ai5gobaqk7ilgn3gs185j0pqeun2fjfggidct99bpc3dfv20d9ab2efjg43jm2m&domain=11&asin=" . $asin;

    $result = getUrl($url_desc);
    $links = (json_decode($result, true));
    if ($links['products'][0]['csv'][8][(count($links['products'][0]['csv'][8]) - 1)] == -1) {
      $preciofer = $links['products'][0]['csv'][8][(count($links['products'][0]['csv'][8]) - 3)];
    } else {
      $preciofer = $links['products'][0]['csv'][8][(count($links['products'][0]['csv'][8]) - 1)];
    }

    if ($links['products'][0]['csv'][1][(count($links['products'][0]['csv'][1]) - 1)] == -1) {
      $precioact = $links['products'][0]['csv'][1][(count($links['products'][0]['csv'][1]) - 3)];
    } else {
      $precioact = $links['products'][0]['csv'][1][(count($links['products'][0]['csv'][1]) - 1)];
    }
    //echo "preciofer1 ".$precioact."<br>";
    if (trim($precioact) == "" or trim($precioact) == "0") {
      $arr = getUrl("https://www.ubi.com.mx/GetItems4.php?id=" . $asin . "&p=herramientas-top-20");
      //print_r($arr);
      $gen = unserialize($arr);
      $precioact = ($gen[2]);
      $desc = ($gen[3]);
      $preciofer = $precioact - $desc;
    }
    //echo "preciofer ".$precioact;
    $desc = (($precioact - $preciofer) / $precioact) * 100;
    $desc = round($desc, 2);

    $imagenes = $links['products'][0]['imagesCSV'];
    $ximg = explode(",", $imagenes);
    $nimg = $ximg[0];
    $categoria = $links['products'][0]['categoryTree'][0]['catId'];
    $titulo = $links['products'][0]['title'];
    $car = serialize($links['products'][0]['features']);
    $TXTT = $titulo;


    $arr = getUrl("https://www.ubi.com.mx/GetItems4.php?id=" . $asin . "&p=" . $_SESSION['aff']);
    $gen = unserialize($arr);

    //$imagen=($gen[0]);
    //$titulo=($gen[1]);
    $linkaff = ($gen[5]);

    //$descr=($gen[6]);
    //$precio_ofer=($gen[2]);
    $name = "k" . time();
    $url = "https://api.keepa.com/graphimage?key=4ai5gobaqk7ilgn3gs185j0pqeun2fjfggidct99bpc3dfv20d9ab2efjg43jm2m&domain=11&range=90&width=1350&bb=1&ld=1&asin=" . $asin;
    $result = getUrl($url);
    file_put_contents('amazon/' . $name . '.jpg', $result);
    $imagen2 = 'amazon/' . $name . '.jpg';

    $xtitulo = explode(" ", $titulo);
    $ntitulo = "";
    if (count($xtitulo) < 13) {
      $ntitulo = $titulo;
    } else {
      $cont = 0;
      while ($cont < 12) {
        $ntitulo .= $xtitulo[$cont] . " ";
        $cont++;
      }
    }
    $precof = number_format($preciofer / 100, 2);
    if ($precof == 0) {
      $precof = round($precioact / 100, 2);
    }
    //echo "precof ".$$precof;
    $ahora = time();

    $codigo = generateRandomString();
    $ahora = time();
    $sql = "INSERT INTO afiliados (asin, codigo, fecha, idaf) VALUES (";
    $sql .= "'" . $asin . "'";
    $sql .= ",'" . $codigo . "'";
    $sql .= ",'" . $ahora . "'";
    $sql .= ",'" . $_SESSION['aff'] . "'";
    $sql .= ")";
    mysqli_query($connection, $sql);

    $linkg = 'https://www.ubi.com.mx/articulo/FB-' . $codigo . '/';
    $linkg2 = 'https://www.ubi.com.mx/articulo/' . $codigo . '/';

    $ahorro = $precio_actual - $precio_oferta;
    $des_ahorro = round(($ahorro / $precio_actual) * 100);
    $enlaceg = "https://www.amazon.com.mx/dp/" . $asin . "?tag=" . $_SESSION['aff'] . "&linkCode=ogi&th=1&psc=1";

    /*--------------------------------------------------------------------------editar texto a mostrar-----------------------------------------------------*/
    //https://www.amazon.com.mx/gp/product/$asin/?tag=
    $texto2 = $titulo . "<br><br> " . $linkg . " <br><br>Precio OFERTA: $" . $precio_oferta . "   <br> Ahorro: " . $des_ahorro . "% => $" . $ahorro . " <br><br> Precio Anterior (Keepa): $" . $precio_actual . "   <br>https://www.ubi.com.mx/ sitio oficial del grupo Ofertones MX
";


    /*--------------------------------------------------------------------------editar texto a mostrar-----------------------------------------------------*/
    $chat_id = "-1001470471840";
    $TXT3 = $titulo . '
' . $linkg;
    $imag = 'https://images-na.ssl-images-amazon.com/images/I/' . $nimg;
    //apiRequest("sendPhoto", array('chat_id' => $chat_id, "caption" => $TXT3, "parse_mode" => "html", "photo" => $imag));   

    $sql3 = "SELECT id_ofer FROM ofertas_amaz WHERE asin='" . $asin . "'";
    $rs3 = mysqli_query($connection, $sql3);

    $num_rows = mysqli_num_rows($rs3);

    if ($num_rows == 0) {


      /*foreach ($links['products'][0]['features'] as $feat)
      {
      $car.= $feat."<br>";  
      }*/

      $fecha = date("Y-m-d");
      $ahora = time();
      $sql = "INSERT INTO ofertas_amaz (asin, precio_med, precio_desc, pdesc, descuento, titulo, categoria, imagen, fecha, car, destacado) VALUES (";
      $sql .= "'" . $asin . "'";
      $sql .= ",'" . ($precio_actual / 100) . "'";
      $sql .= ",'" . ($precio_oferta / 100) . "'";
      $sql .= ",'" . ($precio_oferta / 100) . "'";
      $sql .= ",'" . $des_ahorro . "'";
      $sql .= ",'" . utf8_decode($titulo) . "'";
      $sql .= ",'" . $categoria . "'";
      $sql .= ",'" . $nimg . "'";
      $sql .= ",'" . $fecha . "'";
      $sql .= ",'" . $car . "'";
      $sql .= ",'1'";
      $sql .= ")";
      mysqli_query($connection, $sql);



    } else {
      $fecha = date("Y-m-d");
      $sql45 = "UPDATE ofertas_amaz SET precio_med='" . ($precioact / 100) . "', precio_desc='" . ($preciofer / 100) . "', descuento='" . $desc . "', fecha='" . $fecha . "', destacado='1' WHERE asin='" . $asin . "' ORDER BY id_ofer desc LIMIT 1";
      mysqli_query($connection, $sql45);
      // Execute Query 
      $resa45 = mysqli_query($connection, $sql45) or die("An error has ocured: " . mysql_error() . ":" . mysql_errno());
    }

  }

} ?>
<!doctype html>
<html>

<head>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
  <link rel="stylesheet" href="https://use.typekit.net/akm4qgs.css">
  <title>Afiliados pagina de generacion</title>
  <style type="text/css">
    body {
  font-family: "neue-haas-grotesk-display", sans-serif;
  font-weight: 400;
  font-style: normal;
  background-color: rgb(230, 230, 230);
}
body input {
  background-color: rgb(255, 255, 255);
  min-height: 40px;
  padding-left: 30px;
  border: 1px solid black;
  border-radius: 50px;
  width: 70%;
  margin-left: 20px;
  font-size: 20px;
}
body .button-container {
  width: 100%;
  display: flex;
  justify-content: center;
  column-gap: 20px;
}
body .button-container button {
  font-family: "neue-haas-grotesk-display", sans-serif;
  padding: 5px 30px;
  font-size: 30px;
  border-radius: 60px;
  background-color: white;
  border: none;
  cursor: pointer;
}
body .button-container button:hover {
  border: 1px solid black;
}
body .menu {
  width: 100%;
  display: flex;
  justify-content: center;
}
body .menu .bread {
  background-color: rgb(255, 255, 255);
  border-radius: 500px;
  font-size: 20px;
  margin-top: 15px;
  padding: 8px 20px;
}
body .menu .bread:hover {
  scale: 1.03;
}
body .menu .bread a {
  transition: color 0.3s ease;
  padding-left: 5px;
  padding-right: 5px;
  text-decoration: none;
  color: #000;
}
body .menu .bread a.active {
  color: rgb(255, 208, 0);
  font-weight: 600;
}
body .menu .bread a:hover {
  color: rgb(205, 55, 9);
}
@media screen and (max-width: 768px) {
  body .menu .bread {
    max-width: fit-content;
  }
}
body .inner-container {
  margin: 0 auto;
  display: flex;
  justify-content: center;
}
body .inner-container .parent {
  width: 100%;
  padding: 20px 60px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-template-rows: 1fr 1fr 210px;
  grid-column-gap: 15px;
  grid-row-gap: 15px;
}
@media screen and (max-width: 992px) {
  body .inner-container .parent {
    padding: 20px 40px;
  }
}
@media screen and (max-width: 768px) {
  body .inner-container .parent {
    padding: 10px 20px;
  }
}
@media screen and (max-width: 576px) {
  body .inner-container .parent {
    padding: 10px 0px;
  }
}
@media screen and (max-width: 768px) {
  body .inner-container .parent {
    grid-template-columns: 100%;
    grid-template-rows: 1fr 1fr repeat(3, 210px);
  }
}
body .inner-container .parent:hover div[class*=div]:not(:hover) {
  opacity: 0.9;
}
body .inner-container .parent .badge {
  opacity: 1;
  animation: badge 0.3s ease-in-out 0.05s forwards;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0px 40px;
  border-radius: 50px;
  background-color: black;
  font-size: 30px;
  color: white;
  margin-left: 30px;
  box-shadow: 0px 10px 60px 2px rgba(0, 0, 0, 0.1);
}
@media screen and (max-width: 576px) {
  body .inner-container .parent .badge {

  }
}
@keyframes badge {
  50% {
    scale: 0.97;
  }
  75% {
    scale: 0.98;
  }
  100% {
    scale: 1;
  }
}
body .inner-container .parent .div1 {
  grid-area: 1/1/3/4;
  padding: 30px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
@media screen and (max-width: 768px) {
  body .inner-container .parent .div1 {
    grid-area: 1/1/3/2;
  }
}
body .inner-container .parent .div1.rec {
  min-height: 200px;
}
@media screen and (max-width: 992px) {
  body .inner-container .parent .div1.rec {
    min-height: 60vh;
  }
}
@media screen and (max-width: 768px) {
  body .inner-container .parent .div1.rec {
    min-height: 60vh;
  }
}
body .inner-container .parent .div1 .bread {
  border: 1px solid black;
  border-radius: 500px;
  font-size: 20px;
  margin-top: 15px;
  padding: 8px 20px;
}
@media screen and (max-width: 768px) {
  body .inner-container .parent .div1 .bread {
    max-width: fit-content;
  }
}
body .inner-container .parent .div1 .top {
  font-size: 30px;
  width: 100%;
  display: flex;
  justify-content: space-between;
}
@media screen and (max-width: 768px) {
  body .inner-container .parent .div1 .top {
    justify-content: center;
    align-items: center;
    flex-direction: column;
    row-gap: 30px;
  }
}
body .inner-container .parent .div1 .top a {
  transition: color 0.3s ease;
  padding-left: 5px;
  padding-right: 5px;
  text-decoration: none;
  color: #000;
}
body .inner-container .parent .div1 .top a.active {
  color: rgb(255, 208, 0);
  font-weight: 600;
}
body .inner-container .parent .div1 .top a:hover {
  color: rgb(205, 55, 9);
}
body .inner-container .parent .div1 .header {
  font-family: neue-haas-grotesk-display, sans-serif;
  font-weight: 500;
  font-style: normal;
  font-size: 45px;
  height: fit-content;
  display: flex;
  align-content: center;
  flex-direction: row;
}
body .inner-container .parent .div1 p.text {
  font-size: 24px;
  justify-self: flex-end;
  width: 50%;
}
@media screen and (max-width: 768px) {
  body .inner-container .parent .div1 p.text {
    text-align: center;
    width: 100%;
    line-height: 160%;
  }
}

.m-2{
    margin-top: 20px;
}
body .inner-container .parent .rec {
  border-radius: 20px;
  border: 2px solid rgba(255, 255, 255, 0.1);
  background: rgb(255, 255, 255);
  transition: ease-in-out 0.2s;
}
body .inner-container .parent .rec-header {
  padding: 30px;
  font-size: 30px;
}
@media screen and (max-width: 992px) {
  body .inner-container .parent .rec-header {
    font-size: 27px;
  }
}
body .inner-container .parent .rec:hover {
  opacity: 1;
  transform: scale(1.01);
}
body .inner-container .parent .rec .number-pay {
  font-weight: 500;
  padding: 0px 30px;
  font-size: 50px;
}
@media screen and (max-width: 992px) {
  body .inner-container .parent .rec .number-pay {
    font-size: 40px;
  }
}
body .inner-container .parent .rec .number-pay a {
  transition: color 0.2s ease-in;
  text-decoration: none;
  color: black;
}
body .inner-container .parent .rec .number-pay a:hover {
  color: rgb(255, 208, 0);
}
body .inner-container .parent .rec .number-pay.active {
  color: rgb(133, 133, 133);
}

/*# sourceMappingURL=afiliados.css.map */



  </style>


</head>

<body>

<form method="post" target="">
    <div class="menu">
        <div class="bread">
            <a href="./afiliados.html" class="active">Afiliados</a>
            /
            <a href="./pagos.html">Pagos</a>
        </div>
    </div>

    <div class="inner-container">
        <div class="parent">
            <div class="div1 rec">
                <div class="top">
                    <div class="header">
                        Generar link
                        <?php
                            if ($_SESSION['aff'] == "") {
                            ?>
                            <div></div>
                            <?php
                            } else {
                            ?>
                            <div class="badge"><?= $_SESSION['aff'] ?></div>
                        <?php
                        }
                        ?>
                        
                    </div>
                    <div class="bread">
                        <a href="afiliados.php?afil=face-rodo-20">Fofo</a>
                        /
                        <a href="afiliados.php?afil=face-casta-20">Paul</a>
                        /
                        <a href="afiliados.php?afil=face-buzo-20">Buzo</a>
                        /
                        <a href="afiliados.php?afil=afilarmada1-20">Coque</a>
                    </div>
                </div>


                <p class="text">
                    Selecciona el link en la parte superior y enseguida podras copiar el texto e imágenes.
                </p>
            </div>
            <div class="rec">
                <div class="rec-header">
                    ID o URL
                </div>
                <input name="asin" type="text" required class="form-control" id="asin"
                    placeholder="ID o url completa de Amazon" aria-label="ID del producto" value="<?= $asin ?>">
            </div>
            <div class="rec">
                <div class="rec-header">
                    Precio Oferta
                </div>
                <input class="form-control" name="poferta" type="text" id="poferta" required>
            </div>
            <div class="rec">
                <div class="rec-header">
                    Precio Anterior
                </div>
                <input class="form-control" name="pactual" type="text" id="pactual" required>
            </div>
        </div>
    </div>
    <div class="button-container">
        <button type="submit" id="button-addon2">Enviar</button>
    </div>
    </form>

    <div class="button-container m-2">
        <button onclick="copyContent()">Copiar Texto</button>
    </div>

        <?php
        if ($asin) {
          ?>
          <div class="container">
          <div>
            <div style="clear: both;height: 30px"></div>
            <p id="myText">
              <?= $texto2 ?>
            </p>
          </div>
            <div style="clear: both;height: 30px"></div>
            <div class="d-flex flex-wrap container">
              <?php
              foreach ($ximg as $imagen) {
                ?>
                <div class="w-25 mx-1 my-1">
                  <img src="https://images-na.ssl-images-amazon.com/images/I/<?= $imagen ?>" class="img-responsive">
                </div>
                <div style="clear: both;height: 10px"></div>
                <?php
              }
              ?>
            </div>
          </div>
          <?php
        }
        ?>
      </div>
    </div>
  </div>

  <div style="clear: both;height: 80px"></div>

  <script src="../assets/global/plugins/jquery.min.js" type="text/javascript"></script>

  <script>

let text = document.getElementById('myText').innerHTML;

const copyContent = async () => {
  try {
    let formattedText = text.replace(/<br>/g, '\n');
    formattedText = formattedText.replace(/&gt;/g, '>');
    formattedText = formattedText.trim(); // Elimina espacios en blanco al principio y al final
    await navigator.clipboard.writeText(formattedText);
    console.log('Content copied to clipboard');
  } catch (err) {
    console.error('Failed to copy: ', err);
  }
}



    $(document).ready(function () {
      $("#contact_form").submit(function (e) {

        e.preventDefault(); // avoid to execute the actual submit of the form.
        $("#btnt").prop("disabled", true);
        var form = $(this);
        var url = form.attr('action');
        //$("#news_form").hide('fast');
        //$("#load_cont").show();
        $.ajax({
          type: "POST",
          url: url,
          data: form.serialize(), // serializes the form's elements.
          success: function (data) {

            //$("#load_cont").hide();
            //$("#contact_form").show('fast');
            $("#res_cont").html(data);
            $("#contact_form")[0].reset();
            $("#btnt").prop("disabled", false);
          }
        });


      });
    });   
  </script>

</body>

</html>