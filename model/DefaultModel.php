<?php

$session_id = session_id();
if (empty($session_id)) {
    session_start();
}
/**
 * Description of DefaultController
 *
 * @author Kevin Campos
 */

class DefaultModel implements IModel {

    public $pdo;

    public function __CONSTRUCT() {
        
    }
  

    public function login($data) {
        try {
            $sql = "SELECT * FROM user WHERE username= '" . $data['user'] . "' and pass= '" . $data['pass'] . "'";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                $_SESSION['username'] = $result[0]['username'];
                $_SESSION['idCard'] = $result[0]['idCard'];
                $_SESSION['roll'] = $result[0]['roll'];
                header('Location: /');
            } else {
                header('Location: /?error=1');
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    
    public function dolar() {
        include 'libs/tipoCambio.php';
        $result = tipo_cambio($data = date('d/m/Y'));
        $_SESSION['purchase'] = bcdiv($result["compra"], '1', 2);
        $_SESSION['sale'] = bcdiv($result["venta"], '1', 2);
    }

    public function index() {
       
    }

    public function all($data) {
        
    }

    public function create($data) {
        
    }

    public function deleted($data) {
        
    }

    public function search($data) {
        
    }

    public function update($data) {
        
    }

    function parseAuthRedirectUrl($url) {
        parse_str($url, $qsArray);
        return array(
            'code' => $qsArray['code'],
            'realmId' => $qsArray['realmId']
        );
    }

    //inicio de session a la app
    public function logout() {
        session_destroy();
        header('Location: /sincronizador/');
    }

    function downloadMails($username, $password, $idCard) {
        // Connect to gmail
        $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
        echo "conectando.... ";
        /* try to connect */
        $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());
        echo "conectado <br>";
        /* grab emails */
        $emails = imap_search($inbox, 'FROM ' . $username);
        $emails = imap_search($inbox, 'UNSEEN');

        /* if emails are returned, cycle through each... */
        if ($emails) {

            /* begin output var */
            $output = '';

            /* put the newest emails on top */
            rsort($emails);
            $cont = 1;
            foreach ($emails as $email_number) {//recorre emails
                echo "Correo #" . $cont++ . "<br>";
                /* get information specific to this email */
                $overview = imap_fetch_overview($inbox, $email_number, 0);
                $message = imap_fetchbody($inbox, $email_number, 2);
                $structure = imap_fetchstructure($inbox, $email_number);
                $claveXML = "";
                $archXMLF = "";
                $archXMLM = "";
                $archPDFF = "";
                $bandera = "";

                $attachments = array();
                if (isset($structure->parts) && count($structure->parts)) {//if partes email
                    echo "Adjuntos: " . count($structure->parts) . "<br>";
                    for ($i = 0; $i < count($structure->parts); $i++) {// for recorre partes
                        echo "Adjunto: " . $i . "<br>";
                        if (strcmp($structure->parts[$i]->subtype, "XML") === 0 || strcmp($structure->parts[$i]->subtype, "PDF") === 0 || strcmp($structure->parts[$i]->subtype, "OCTET-STREAM") === 0) {//if tipo de estructuras
                            $attachments[$i] = array(
                                'is_attachment' => false,
                                'filename' => '',
                                'name' => '',
                                'attachment' => '');

                            if ($structure->parts[$i]->ifdparameters) {
                                foreach ($structure->parts[$i]->dparameters as $object) {
                                    if (strtolower($object->attribute) == 'filename') {
                                        $attachments[$i]['is_attachment'] = true;
                                        $attachments[$i]['filename'] = $object->value;
                                    }
                                }
                            }

                            if ($structure->parts[$i]->ifparameters) {
                                foreach ($structure->parts[$i]->parameters as $object) {
                                    if (strtolower($object->attribute) == 'name') {
                                        $attachments[$i]['is_attachment'] = true;
                                        $attachments[$i]['name'] = $object->value;
                                    }
                                }
                            }
                            if ($attachments[$i]['is_attachment']) {// if adjunto
                                $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i + 1);
                                if ($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                                    if (strpos($attachments[$i]['filename'], "zip") || strpos($attachments[$i]['filename'], "ZIP") || strpos($attachments[$i]['name'], "ZIP") || strpos($attachments[$i]['name'], "zip") || strpos($attachments[$i]['name'], "pdf") || strpos($attachments[$i]['name'], "PDF") || strpos($attachments[$i]['filename'], "pdf") || strpos($attachments[$i]['filename'], "PDF")) {
                                        if (base64_decode($attachments[$i]['attachment'], true)) {
                                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                        } else {
                                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                        }
                                    }
                                }//fin encode 3 
                                else { // 4 = QUOTED-PRINTABLE
                                    if (strpos($attachments[$i]['filename'], "zip") || strpos($attachments[$i]['filename'], "ZIP") || strpos($attachments[$i]['name'], "ZIP") || strpos($attachments[$i]['name'], "zip") || strpos($attachments[$i]['name'], "pdf") || strpos($attachments[$i]['name'], "PDF") || strpos($attachments[$i]['filename'], "pdf") || strpos($attachments[$i]['filename'], "PDF")) {
                                        if (base64_decode($attachments[$i]['attachment'], true)) {
                                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                        } else {
                                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                        }
                                    }
                                }// fin encode 4
                            }//fin if adjunto
                            if (strpos($attachments[$i]['name'], "xml") || strpos($attachments[$i]['name'], "XML") || strpos($attachments[$i]['filename'], "xml") || strpos($attachments[$i]['filename'], "XML")) {
                                if (base64_decode($attachments[$i]['attachment'], true)) {
                                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                } else {
                                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                }
                                libxml_use_internal_errors(true);
                                $attachments[$i]['attachment'] = str_replace("o;?", "", $attachments[$i]['attachment']);
                                $attachments[$i]['attachment'] = str_replace("C", "O", $attachments[$i]['attachment']);
                                $attachments[$i]['attachment'] = str_replace("E", "O", $attachments[$i]['attachment']);
                                $attachments[$i]['attachment'] = str_replace("o?=", "O", $attachments[$i]['attachment']);
                                if ($xml = simplexml_load_string($attachments[$i]['attachment'])) {
                                    $claveXML = $xml->Clave;
                                    if (strrpos($attachments[$i]['attachment'], 'mensajeHacienda') || strrpos($attachments[$i]['attachment'], 'MensajeHacienda')) {
                                        echo "mensaje " . $xml->Clave;
                                        $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $xml->Clave;
                                        if (!file_exists($carpeta)) {
                                            mkdir($carpeta, 0777, true);
                                        }
                                        $nombre_fichero = $carpeta . '/' . $xml->Clave . '-R.xml';
                                        $xml->asXML($nombre_fichero);
                                        echo "Guardado <br>";
                                    }
                                    if (strrpos($attachments[$i]['attachment'], 'facturaElectronica') || strrpos($attachments[$i]['attachment'], 'FacturaElectronica') || strrpos($attachments[$i]['attachment'], 'tiqueteElectronico') || strrpos($attachments[$i]['attachment'], 'TiqueteElectronico') ||
                                            strrpos($attachments[$i]['attachment'], 'NotaCreditoElectronica') || strrpos($attachments[$i]['attachment'], 'notaCreditoElectronica')) {
                                        $claveXML = $xml->Clave;
                                        echo "factura " . $xml->Clave;
                                        $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $xml->Clave;
                                        if (!file_exists($carpeta)) {
                                            mkdir($carpeta, 0777, true);
                                        }
                                        $nombre_fichero = $carpeta . '/' . $xml->Clave . '.xml';
                                        $xml->asXML($nombre_fichero);
                                        echo "Guardado <br>";
                                    }
                                } else {
                                    echo "error al abrir <br>";
                                }
                            }//fin if xml
                            if (strpos($attachments[$i]['name'], "pdf") || strpos($attachments[$i]['name'], "PDF") || strpos($attachments[$i]['filename'], "pdf") || strpos($attachments[$i]['filename'], "PDF")) {
                                if ($claveXML != '') {
                                    echo "PDF " . $claveXML;
                                    $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $claveXML;
                                    if (!file_exists($carpeta)) {
                                        mkdir($carpeta, 0777, true);
                                    }
                                    $nombre_fichero = $carpeta . '/' . $claveXML . '.pdf';

                                    if (file_put_contents($nombre_fichero, $attachments[$i]['attachment'])) {
                                        echo "Guardado <br>";
                                    }
                                    //$xml->asXML($nombre_fichero);
                                }
                            }//fin if pdf
                            if (strpos($attachments[$i]['name'], "zip") || strpos($attachments[$i]['name'], "ZIP") || strpos($attachments[$i]['filename'], "zip") || strpos($attachments[$i]['filename'], "ZIP")) {
                                echo "ZIP <br>";
                                $zip = new ZipArchive;
                                $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']);
                                if (!file_exists($carpeta)) {
                                    mkdir($carpeta, 0777, true);
                                }
                                $nombre_fichero = $carpeta . '/' . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']) . '.zip';
                                file_put_contents($nombre_fichero, $attachments[$i]['attachment']);
                                if ($zip->open($nombre_fichero) === TRUE) {
                                    $zip->extractTo($carpeta . '/');
                                    $zip->close();
                                    rename($carpeta . "/" . str_replace(".zip", "", $attachments[$i]['name']) . ".xml", $carpeta . "/" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']) . ".xml");
                                    rename($carpeta . "/ATV_eFAC_Respuesta_" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']) . ".xml", $carpeta . "/" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']) . "-R.xml");
                                    rename($carpeta . "/" . str_replace(".zip", "", $attachments[$i]['name']) . ".pdf", $carpeta . "/" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']) . ".pdf");
                                    echo 'ok';
                                } else {
                                    echo 'failed';
                                }
                            }//fin if pdf
                        }// fin if tipo de archivo
                    }//fin for partes
                    $claveXML = "";
                }//fin if partes email
            }//fin recorre emails
            // echo $output;
        }

        /* close the connection */
        imap_close($inbox);
    }

}
