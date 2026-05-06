<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador-AgroControl</title>
    <link rel="stylesheet" href="../Css/administrador.css">
</head>
<body>
    <div class="contenedor1">
        <form action="CrearR.php" method="POST">
            <img src="../Assets/Imagenes/logo.png" alt="Logo-AgroControl" class="imagenLogo">
            <h2>Sistema De Gestion</h2>
            <p>Administrador</p>
            <label>Nombre:</label>
            <input type="text" name="nombre" placeholder="Nombre A Registrar" required>
            <label>Correo Electronico:</label>
            <input type="text" name="correo" placeholder="Correo A Registrar" required>
            <label>Contraseña</label>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Ingrese Contraseña A Registrar" required>
                <span class="toggle-password" onclick="togglePassword()">👁</span>
            </div>
            <button type="submit">Registrar </button>
        </form>
    </div>
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
    },3000);
}
</script>
<?php
if(isset($_GET['registro']) && $_GET['registro']=="ok"){
echo "<script>
window.onload = function(){
mostrarToast('Usuario registrado correctamente','success');
}
</script>";
}

if(isset($_GET['registro']) && $_GET['registro']=="correo_existente"){
echo "<script>
window.onload = function(){
mostrarToast('El correo ya se encuentra registrado','error');
}
</script>";
}
?>
<script>

function togglePassword(){

let input = document.getElementById("password");

if(input.type === "password"){
input.type = "text";
}else{
input.type = "password";
}

}

</script>
</body>
</html>
