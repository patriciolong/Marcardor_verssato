<?php
// includes/header.php
// Asegúrate de que las funciones de sesión ya estén cargadas
// y que la sesión esté iniciada ANTES de incluir este header.
// Por ejemplo, en dashboard.php, index.php (después de login), etc.

// No iniciar sesión aquí directamente si ya se hace en otros archivos para evitar errores.
// Asumo que verificarAutenticacion() ya se encargó de iniciar la sesión y verificar al usuario.

$nombre_empleado_menu = "Usuario"; // Valor por defecto
$userRol = "";
if (isset($_SESSION['id_empleado'])) {
    // Si la conexión a la base de datos no está disponible globalmente,
    // es posible que necesites incluir 'config.php' aquí o pasar $mysqli como parámetro.
    // Por simplicidad, asumiré que $mysqli ya está disponible si estás logueado
    // (porque `verificarAutenticacion()` debería estar en la página principal).
    global $mysqli; // Acceder a la conexión global $mysqli

    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $stmt = $mysqli->prepare("SELECT nombre,rol  FROM empleados WHERE id_empleado = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['id_empleado']);
            $stmt->execute();
            $stmt->bind_result($nombre_db,$rol_empleado);
            $stmt->fetch();
            $stmt->close();
            if ($nombre_db) {
                $nombre_empleado_menu = htmlspecialchars($nombre_db);
                $userRol = htmlspecialchars($rol_empleado);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClockIn</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;
            background: rgba(199, 218, 247, 0.38);
            color: #334155;
            line-height: 1.6;
            font-size: 14px;
            overflow-x: hidden;
        }

        .page-wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* === SIDEBAR === */
        .sidebar {
            width: 280px;
            background:rgb(15, 23, 42);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
            box-shadow: 
                4px 0 24px rgba(15, 23, 42, 0.12),
                2px 0 8px rgba(15, 23, 42, 0.08);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 30% 30%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 70% 70%, rgba(99, 102, 241, 0.06) 0%, transparent 50%);
            pointer-events: none;
        }

        .sidebar-header {
            padding: 32px 24px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
        }

        .app-title {
            font-size: 1.5rem;
            font-weight: 500;
            color: #ffffff;
            letter-spacing: -0.02em;
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .app-title::before {
            content: '';
            width: 8px;
            height: 32px;
            background: linear-gradient(180deg, #3b82f6, #1d4ed8);
            border-radius: 4px;
            box-shadow: 0 0 12px rgba(59, 130, 246, 0.4);
        }

        .main-nav {
            padding: 24px 0;
            position: relative;
            z-index: 1;
        }

        .main-nav ul {
            list-style: none;
        }

        .main-nav li {
            margin: 4px 16px;
        }

        .main-nav a {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            color:rgb(255, 255, 255);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            font-size: 15px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.01em;
        }

        .main-nav a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg rgba(255, 255, 255, 0.05));
        }

        .main-nav a:hover {
            color: #ffffff;
            background: rgba(59, 130, 246, 0.15);
            
            
            box-shadow: 
                0 4px 16px rgba(59, 130, 246, 0.1),
                inset 0 1px 2px rgba(255, 255, 255, 0.1);
        }

        .main-nav a:hover::before {
            left: 100%;
        }

        .main-nav a:active {
            transform: translateX(-2px);
            background: rgba(59, 130, 246, 0.2);
        }

        /* Estilo especial para el enlace de cerrar sesión */
        .main-nav li:last-child {
            margin-top: auto;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .main-nav li:last-child a {
            color: #f1f5f9;
            background: rgba(15, 23, 42, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
            line-height: 1.4;
        }

        .main-nav li:last-child a:hover {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fef2f2;
        }

        /* === CONTENT AREA === */
        .content-area {
            margin-left: 280px;
            flex: 1;
            background: rgba(199, 218, 247, 0.38);
            min-height: 100vh;
            position: relative;
            display: flex;
            justify-content: center; /* Centra horizontalmente */
            align-items: center; /* Centra verticalmente */
        }

        .content-area::before {
            content: '';
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.2), transparent);
            z-index: 100;
        }

        /* Contenido de ejemplo para mostrar el layout */
       

        .content-header {
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #f1f5f9;
        }

        .content-title {
            font-size: 32px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .content-subtitle {
            color: #64748b;
            font-size: 16px;
            font-weight: 400;
        }

        /* === RESPONSIVE === */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .content-area {
                margin-left: 0;
            }
            
            .main-content {
                padding: 24px 20px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }
            
            .content-area::before {
                left: 0;
            }
            
            .main-content {
                padding: 20px 16px;
            }
            
            .content-title {
                font-size: 24px;
            }
            
            .app-title {
                font-size: 24px;
            }
            
            .main-nav a {
                font-size: 14px;
                padding: 14px 16px;
            }
        }

        /* === MOBILE MENU BUTTON === */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #0f172a;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.3);
        }

        @media (max-width: 1024px) {
            .mobile-menu-btn {
                display: block;
            }
        }

        /* === OVERLAY === */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(4px);
        }

        @media (max-width: 1024px) {
            .sidebar-overlay.active {
                display: block;
            }
        }

        /* === LOADING STATE === */
        .sidebar.loading {
            pointer-events: none;
        }

        .sidebar.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 32px;
            height: 32px;
            margin: -16px 0 0 -16px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
        }

       

        /* === FOCUS STATES === */
        .main-nav a:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        .mobile-menu-btn:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <button class="mobile-menu-btn"></button>
    <div class="sidebar-overlay"></div>
    
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="app-title">ClockIn</div>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="dashboard.php">Inicio</a></li>
                    <?php if ($userRol == 'Administrador'): ?>
                    <li><a href="ver_marcaciones.php">Reporteria</a></li>
                    <li><a href="registrar_empleado.php">Registrar Empleado</a></li>
                    <li><a href="ver_usuarios.php">Usuarios</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </aside>

        

     <main class="content-area">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const navLinks = document.querySelectorAll('.main-nav a');

            // Toggle mobile menu
            mobileMenuBtn.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
            });

            // Close menu when clicking overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            });

            // Add loading state to navigation links
         

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                }
            });

            // Auto-close mobile menu on resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                }
            });
        });
    </script>

