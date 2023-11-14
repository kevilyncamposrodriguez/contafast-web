<?php 
$session_id = session_id();
if (empty($session_id)) {
    session_start();
}
/**
 * Description of InvoiceController
 *
 * @author Kevin Campos
 */
//require 'libs/SPDO.php';
require 'model/IModel.php';
require 'controller/IController.php';
require 'libs/FrontController.php';
FrontController::main();
