<?php
session_start(); 
include("../Config/conexion.php");
$con = conexion();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $codigo      = trim($_POST['codigo'] ?? '');
    $nombre      = trim($_POST['nombre'] ?? '');
    $raza        = trim($_POST['raza'] ?? '');
    $edad        = ($_POST['edad'] ?? '') !== '' ? (int) $_POST['edad'] : null;
    $estado      = trim($_POST['estado'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $vacunasInfo = trim($_POST['vacunas_info'] ?? '');
    $partos      = ($_POST['partos'] ?? '') !== '' ? (int) $_POST['partos'] : 0;
    $usuario_id  = (int) $_SESSION['id'];
    $fotoRuta    = null;

    // 🔍 VALIDAR SI EL CÓDIGO YA EXISTE PARA ESTE USUARIO
    $stmt = mysqli_prepare($con, "SELECT id FROM vacas WHERE codigo = ? AND usuario_id = ?");
    mysqli_stmt_bind_param($stmt, "si", $codigo, $usuario_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        echo "<script>
                alert('⚠️ Ya existe una vaca con ese código');
                window.location='Registro_Vacas.php';
              </script>";
        exit();
    }
    mysqli_stmt_close($stmt);

    // ✅ INSERTAR SI NO EXISTE
    if (isset($_FILES['foto']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            echo "<script>
                    alert('No se pudo subir la foto de la vaca');
                    window.location='Registro_Vacas.php';
                  </script>";
            exit();
        }

        $permitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/avif' => 'avif'
        ];
        $mime = mime_content_type($_FILES['foto']['tmp_name']);

        if (!isset($permitidos[$mime])) {
            echo "<script>
                    alert('La foto debe estar en formato JPG, PNG, WEBP o AVIF');
                    window.location='Registro_Vacas.php';
                  </script>";
            exit();
        }

        $directorioFisico = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'Imagenes' . DIRECTORY_SEPARATOR . 'vacas';
        if (!is_dir($directorioFisico)) {
            mkdir($directorioFisico, 0777, true);
        }

        $nombreArchivo = 'vaca_' . $usuario_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $permitidos[$mime];
        $rutaFisica = $directorioFisico . DIRECTORY_SEPARATOR . $nombreArchivo;

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFisica)) {
            echo "<script>
                    alert('No se pudo guardar la foto de la vaca');
                    window.location='Registro_Vacas.php';
                  </script>";
            exit();
        }

        $fotoRuta = '../Assets/Imagenes/vacas/' . $nombreArchivo;
    }

    $stmt = mysqli_prepare($con, "INSERT INTO vacas (codigo, nombre, raza, edad, estado, foto, descripcion, vacunas_info, partos, usuario_id) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssissssii", $codigo, $nombre, $raza, $edad, $estado, $fotoRuta, $descripcion, $vacunasInfo, $partos, $usuario_id);
    $resultado = mysqli_stmt_execute($stmt);

    if ($resultado) {
        mysqli_stmt_close($stmt);
        header("Location: Registro_Vacas.php");
        exit();
    } else {
        echo "Error al registrar: " . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);
}
?>
