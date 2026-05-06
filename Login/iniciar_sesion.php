<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AgroControl | Iniciar Sesión</title>

<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Sora:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../Css/iniciarsesion.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <h1>AGRO <span>CONTROL</span></h1>
            <p>Sistema inteligente para gestión ganadera</p>
            <div class="stats">
                <div class="stat">
                    <h2>248</h2>
                    <p>Fincas</p>
                </div>
                <div class="stat">
                    <h2>12K</h2>
                    <p>Animales</p>
                </div>
                <div class="stat">
                    <h2>99%</h2>
                    <p>Uptime</p>
                </div>
            </div>
        </div>
        <div class="right">
            <div class="login-box">
                <div class="logo">AGRO<span>CONTROL</span></div>
                <h2>INICIAR SESIÓN</h2>
                <p class="subtitle">Ingrese sus credenciales</p>
                <form action="iniciar_sesion.php" method="POST">
                    <div class="input-group">
                        <label>Correo</label>
                        <input type="email" name="correo" required>
                    </div>
                    <div class="input-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" required>
                    </div>

                    <button type="submit" name="ingresar">INGRESAR</button>
                </form>
            </div>
        </div>
    </div>
<?php
session_start();
include("../Config/conexion.php");
$con = Conexion();

$mensaje = "";
$tipo = "";
$redirigir = false;

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $correo = $_POST['correo'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE correo='$correo'";
    $query = mysqli_query($con, $sql);
    $usuario = mysqli_fetch_assoc($query);

    if($usuario && $usuario['password'] == $password){

        $_SESSION['id'] = $usuario['id'];
$_SESSION['nombre'] = $usuario['nombre'];

        $mensaje = "Bienvenido al sistema";
        $tipo = "success";
        $redirigir = true;

    }else{

        $mensaje = "Correo o contraseña incorrectos";
        $tipo = "error";

    }
}
?>
    <div id="toast" class="toast"></div>
    <script>

function mostrarToast(mensaje, tipo){

    const toast = document.getElementById("toast");

    toast.innerText = mensaje;
    toast.className = "toast " + tipo;

    setTimeout(()=>{
        toast.classList.add("show");
    },50);

    setTimeout(()=>{
        toast.classList.remove("show");
    },2500);
}

<?php if($mensaje != ""){ ?>

document.addEventListener("DOMContentLoaded", function(){

    mostrarToast("<?php echo $mensaje ?>","<?php echo $tipo ?>");

    <?php if($redirigir){ ?>

    setTimeout(function(){
        window.location="../Pages/Dashboard.php";
    },1800);

    <?php } ?>

});

<?php } ?>

</script>
</body>
</html>