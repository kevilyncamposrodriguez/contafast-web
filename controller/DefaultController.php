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


require 'model/DefaultModel.php';
require 'model/ExchangeRateModel.php';

class DefaultController implements IController {

    private $view;
    private $defaultModel;
    private $exchangeRate;

    //cointructor
    public function __construct() {
        $this->view = new View();
        $this->defaultModel = new DefaultModel();
        $this->exchangeRate = new ExchangeRateModel();
    }

    //carga pagina principal
    public function index() {
        $data = array();
        $this->view->show("indexView.php", $data);
    }

    //inicio de session a la app
    public function login() {
        $data = array();
        $data['user'] = $_POST['user'];
        $data['pass'] = $_POST['pass'];
        $result = $this->defaultModel->login($data);
       
    }

    //inicio de session a la app
    public function logout() {
        $this->defaultModel->logout();
    }
    //Muestra el tipo de cambio del dolar (compra/venta)
    public function dolar(){
        
    }

    public function all() {
        
    }

    public function create() {
        
    }

    public function delete() {
        
    }

    public function search() {
        
    }

    public function update() {
        
    }
    public function syncGMAIL() {
        $clients = $this->clientModel->all();
        $clients = json_decode($clients, true);
        foreach ($clients as $client) {
            echo "Cargando facturas de: " . $client["emailUser"] . " - " . $client["idcard"] . "<br>";
            $clients = $this->defaultModel->downloadMails($client["emailUser"], $client["emailPass"], $client["idcard"]);
            echo "<br>";
        }
    }

}

// fin clase
?>