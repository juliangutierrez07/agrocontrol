<?php
require_once("../Config/conexion.php");
require_role(['administrador']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador-AgroControl</title>
    <link rel="stylesheet" href="../Css/administrador.css">
</head>
<body>
    <div class="contenedor1">
        <form action="CrearR.php" method="POST">
            <?php echo csrf_field(); ?>
            <img src="../Assets/Imagenes/logo.png" alt="Logo-AgroControl" class="imagenLogo">
            <h2>Sistema De Gestion</h2>
            <p>Administrador</p>
            <label>Nombre:</label>
            <input type="text" name="nombre" placeholder="Nombre A Registrar" maxlength="100" required>
            <label>Correo Electronico:</label>
            <input type="email" name="correo" placeholder="Correo A Registrar" maxlength="255" required>
            <label>Contrasena</label>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Ingrese Contrasena A Registrar" minlength="8" maxlength="255" required>
                <span class="toggle-password" onclick="togglePassword()">Ver</span>
            </div>
            <button type="submit">Registrar</button>
        </form>
    </div>
    <div id="toast" class="toast"></div>

<script>
function mostrarToast(mensaje, tipo){
    const toast = document.getElementById("toast");
    toast.innerText = mensaje;
    toast.className = "toast " + tipo;
    setTimeout(()=>{ toast.classList.add("show"); },50);
    setTimeout(()=>{ toast.classList.remove("show"); },3000);
}

function togglePassword(){
    let input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}
</script>
<?php
$mensajes = [
    'ok' => ['Usuario registrado correctamente', 'success'],
    'correo_existente' => ['El correo ya se encuentra registrado', 'error'],
    'password_debil' => ['La contrasena debe tener minimo 8 caracteres', 'error'],
    'error' => ['No fue posible registrar el usuario', 'error'],
];
$registro = $_GET['registro'] ?? '';
if (isset($mensajes[$registro])) {
    [$mensaje, $tipo] = $mensajes[$registro];
    echo "<script>window.onload = function(){ mostrarToast(" . json_encode($mensaje) . "," . json_encode($tipo) . "); }</script>";
}
?>
</body>
</html>
