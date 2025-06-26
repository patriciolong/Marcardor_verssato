<?php
// index.php

include 'config.php'; // Incluye el archivo de conexión a la base de datos
include 'includes/funciones.php'; // Incluye tus funciones de sesión
iniciarSesionSegura(); // Inicia o reanuda la sesión de forma segura

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error_message = "Por favor, ingresa tu email y contraseña.";   
    } else {
        // Preparar la consulta para evitar inyecciones SQL y seleccionar también el estado
        $stmt = $mysqli->prepare("SELECT id_empleado, password, estado, rol FROM empleados WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email); // 's' porque email es un string
            $stmt->execute();
            $stmt->store_result(); // Almacenar resultados para poder usar num_rows y bind_result
            $stmt->bind_result($id_empleado, $hashed_password, $estado, $rol); // Asocia las columnas seleccionadas a estas variables
            $stmt->fetch(); // Obtiene los valores

            // Verificar si se encontró un usuario y si la contraseña coincide con el hash
            if ($stmt->num_rows == 1 && password_verify($password, $hashed_password)) {
                // Verificar el estado del empleado
                if ($estado === 'Activo') { // Asumiendo que 'Activo' es el valor para habilitado
                    // Autenticación exitosa
                    $_SESSION['id_empleado'] = $id_empleado;
                    $_SESSION['rol'] = $rol;
                    $_SESSION['loggedin'] = true;
                    // Redirigir al dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    // Estado inactivo
                    $error_message = "Tu cuenta está inactiva. Por favor, contacta a tu administrador.";
                }
            } else {
                // Credenciales inválidas
                $error_message = "Email o contraseña inválidos.";
            }
            $stmt->close();
        } else {
            $error_message = "Error en la preparación de la consulta.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg,rgb(9, 11, 14) 0%,rgb(4, 23, 60) 25%,rgb(4, 19, 48) 50%,rgb(5, 25, 62) 75%, #1a365d 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animación del fondo */
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Elementos decorativos del fondo */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(29, 78, 216, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(37, 99, 235, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.36),
                0 8px 16px rgba(0, 0, 0, 0.05),
                inset 0 1px 2px rgba(255, 255, 255, 0.8);
            position: relative;
            z-index: 1;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 24px 24px 0 0;
        }

        h2 {
            color:rgb(15, 29, 74);
            font-size: 32px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 32px;
            letter-spacing: -0.02em;
            position: relative;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg,rgb(15, 39, 78),rgb(24, 38, 76));
            border-radius: 2px;
        }

        .error-message {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #dc2626;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid #fecaca;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            color:rgb(24, 27, 32);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.02em;
            margin-bottom: 4px;
        }

        input[type="text"],
        input[type="password"] {
            padding: 16px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 400;
            color: #374151;
            background: #fafafa;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color:rgb(17, 35, 63);
            background: #ffffff;
            box-shadow: 
                0 0 0 4px rgba(59, 130, 246, 0.1),
                0 4px 12px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }

        input[type="text"]:hover,
        input[type="password"]:hover {
            border-color:rgb(121, 126, 135);
            background:rgb(223, 221, 221);
        }

        button {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 18px 24px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        button:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            transform: translateY(-2px);
            box-shadow: 
                0 8px 24px rgba(59, 130, 246, 0.3),
                0 4px 12px rgba(59, 130, 246, 0.2);
        }

        button:hover::before {
            left: 100%;
        }

        button:active {
            transform: translateY(0);
            box-shadow: 
                0 4px 12px rgba(59, 130, 246, 0.2),
                0 2px 6px rgba(59, 130, 246, 0.1);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 32px 24px;
                border-radius: 20px;
            }

            h2 {
                font-size: 28px;
                margin-bottom: 24px;
            }

            form {
                gap: 20px;
            }

            input[type="text"],
            input[type="password"] {
                padding: 14px 16px;
                font-size: 16px;
            }

            button {
                padding: 16px 20px;
                font-size: 16px;
            }
        }

        /* Animación de carga */
        .login-container.loading {
            pointer-events: none;
        }

        .login-container.loading button {
            background: linear-gradient(135deg, #9ca3af, #6b7280);
            cursor: not-allowed;
        }

        .login-container.loading button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>

    <script>
        // Agregar efectos interactivos
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const container = document.querySelector('.login-container');
            
            form.addEventListener('submit', function(e) {
                container.classList.add('loading');
                
                // Simular carga (remover en producción)
                setTimeout(() => {
                    container.classList.remove('loading');
                }, 2000);
            });

            // Efecto de enfoque mejorado
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>