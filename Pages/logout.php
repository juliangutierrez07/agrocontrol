<?php
require_once("../Config/conexion.php");
start_secure_session();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();
header("Location:../Login/iniciar_sesion.php");
exit();
?>
