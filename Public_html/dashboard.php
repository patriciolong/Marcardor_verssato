<?php
// dashboard.php

include 'config.php'; //
include 'includes/funciones.php'; //
verificarAutenticacion(); // Verifica si el usuario está logueado, si no, redirige

// Opcional: Obtener información del empleado logueado para mostrar un saludo
$nombre_empleado = "Empleado";
$userRol = "Empleado";
if (isset($_SESSION['id_empleado'])) {
    $id_empleado_sesion = $_SESSION['id_empleado'];
    $stmt = $mysqli->prepare("SELECT nombre, rol FROM empleados WHERE id_empleado = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_empleado_sesion);
        $stmt->execute();
        $stmt->bind_result($nombre_empleado_db,$rol_empleado);
        $stmt->fetch();
        $stmt->close();
        if ($nombre_empleado_db) {
            $nombre_empleado = $nombre_empleado_db;
            $userRol = $rol_empleado;
        }
    }
}

// Manejar mensaje de error si viene de marcar.php
$error_message = '';
if (isset($_GET['error']) && $_GET['error'] === 'no_face_data') {
    $error_message = "No se encontraron datos faciales registrados para tu cuenta. Por favor, contacta a tu administrador.";
}
if (isset($_GET['success_message'])) {
    $success_message = htmlspecialchars($_GET['success_message']);
}
if (isset($_GET['error_message'])) {
    $error_message = htmlspecialchars($_GET['error_message']);
}


?>


<?php include 'includes/header.php'; ?>

<style>
    .main-content {
        padding: 48px 40px;
        max-width: 1200px;
        margin: 0 auto;
        background:rgb(213, 206, 255);
        min-height: calc(100vh - 120px);
    }

    /* === HEADER SECTION === */
    .dashboard-header {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 40px;
        margin-bottom: 40px;
        box-shadow: 
            0 4px 20px rgba(15, 23, 42, 0.23),
            0 1px 4px rgba(15, 23, 42, 0.02);
        position: relative;
        overflow: hidden;
        width: 100%;
        max-width: none;
    }   

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #1d4ed8, #0f172a);
        border-radius: 20px 20px 0 0;
    }

    .welcome-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .user-info h2:first-child {
        font-size: 3rem;
        font-weight: 500;
        color: #0f172a;
        margin-bottom: 10%;
        letter-spacing: -0.02em;
        line-height: 1.1;
    }

    .user-info h2:last-child {
        font-size: 16px;
        font-weight: 600;
        color:rgb(13, 32, 61);
        background: rgb(209, 225, 246);
        padding: 8px 20px;
        border-radius: 25px;
        display: inline-block;
        margin: 0;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .current-time {
        text-align: right;
        font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
    }

    .time-display {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
        letter-spacing: -0.01em;
    }

    .date-display {
        font-size: 14px;
        color: #64748b;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .welcome-message {
        font-size: 18px;
        color: #475569;
        font-weight: 500;
        margin: 0;
        letter-spacing: 0.01em;
    }

    /* === MESSAGES === */
    .message-container {
        margin-bottom: 32px;
    }

    .error-message {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #dc2626;
        padding: 20px 24px;
        border-radius: 16px;
        margin-bottom: 20px;
        border: 1px solid #fecaca;
        font-size: 15px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
        animation: slideInFromTop 0.5s ease-out;
    }

    .error-message::before {
        content: '⚠️';
        font-size: 20px;
    }

    .success-message {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        color: #16a34a;
        padding: 20px 24px;
        border-radius: 16px;
        margin-bottom: 20px;
        border: 1px solid #bbf7d0;
        font-size: 15px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.1);
        animation: slideInFromTop 0.5s ease-out;
    }

    .success-message::before {
        content: '✅';
        font-size: 20px;
    }

    @keyframes slideInFromTop {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* === MAIN ACTION SECTION === */
    .action-section {
        background: #ffffff;
        border: 2px solid #f1f5f9;
        border-radius: 24px;
        padding: 48px;
        margin-bottom: 32px;
        box-shadow: 
            0 8px 32px rgba(15, 23, 42, 0.04),
            0 2px 8px rgba(15, 23, 42, 0.02);
        position: relative;
    }

    .action-section::before {
        content: '';
        position: absolute;
        top: -1px;
        left: -1px;
        right: -1px;
        height: 2px;
      
        border-radius: 24px 24px 0 0;
        background-size: 200% 100%;
        animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    .section-title {
        text-align: center;
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 32px;
        letter-spacing: -0.01em;
    }

    .button-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 40px;
        max-width: 550px;
        margin-left: auto;
        margin-right: auto;
    }

    .btn-entrada,
    .btn-salida {
        padding: 15px 25px;
        border: none;
        border-radius: 16px;
        font-size: 18px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    .btn-entrada::before,
    .btn-salida::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        transition: left 0.5s;
    }

    .btn-entrada {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: 2px solid transparent;
    }

    .btn-entrada:hover {
        background: linear-gradient(135deg, #059669, #047857);
        transform: translateY(-4px) scale(1.02);
        box-shadow: 
            0 12px 32px rgba(16, 185, 129, 0.3),
          s  0 4px 12px rgba(16, 185, 129, 0.2);
    }

    .btn-salida {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        border: 2px solid transparent;
    }

    .btn-salida:hover {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        transform: translateY(-4px) scale(1.02);
        box-shadow: 
            0 12px 32px rgba(239, 68, 68, 0.3),
            0 4px 12px rgba(239, 68, 68, 0.2);
    }
    .btn-entrada:hover::before,
    .btn-salida:hover::before {
        left: 100%;
    }

    .btn-entrada:active,
    .btn-salida:active {
        transform: translateY(-2px) scale(1.01);
    }

    /* === LOGOUT SECTION === */
    .logout-section {
        text-align: center;
        padding: 32px;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    .logout-link {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 16px 32px;
        background: linear-gradient(135deg, #64748b, #475569);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        letter-spacing: 0.01em;
    }

    .logout-link::before {
        content: '🚪';
        font-size: 18px;
    }

    .logout-link:hover {
        background: linear-gradient(135deg, #475569, #334155);
        transform: translateY(-2px);
        box-shadow: 
            0 8px 24px rgba(100, 116, 139, 0.3),
            0 4px 12px rgba(100, 116, 139, 0.2);
        border-color: #94a3b8;
    }

    .logout-link:active {
        transform: translateY(0);
    }

    /* === STATUS INDICATORS === */
    .status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .status-card {
        background: #ffffff;
        border: 2px solid #f1f5f9;
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        transition: all 0.3s ease;
    }

    .status-card:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.1);
    }

    .status-title {
        font-size: 14px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }

    .status-value {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
    }

    /* === RESPONSIVE === */
    @media (max-width: 768px) {
        .main-content {
            padding: 24px 20px;
        }

        .dashboard-header {
            padding: 24px;
        }

        .welcome-section {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }

        .user-info h2:first-child {
            font-size: 28px;
        }

        .current-time {
            text-align: center;
        }

        .action-section {
            padding: 32px 24px;
        }

        .button-group {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .btn-entrada,
        .btn-salida {
            padding: 20px 24px;
            font-size: 16px;
            min-height: 100px;
        }

        .section-title {
            font-size: 20px;
        }

        .status-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .dashboard-header {
            padding: 20px;
        }

        .user-info h2:first-child {
            font-size: 24px;
        }

        .time-display {
            font-size: 24px;
        }

        .action-section {
            padding: 24px 20px;
        }

        .welcome-message {
            font-size: 16px;
        }
    }

    /* === LOADING STATES === */
    .btn-entrada.loading,
    .btn-salida.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-entrada.loading::after,
    .btn-salida.loading::after {
        content: '';
        width: 20px;
        height: 20px;
        border: 2px solid transparent;
        border-top: 2px solid #ffffff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* === ACCESSIBILITY === */
    .btn-entrada:focus,
    .btn-salida:focus,
    .logout-link:focus {
        outline: 3px solid #3b82f6;
        outline-offset: 2px;
    }

    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.1s !important;
            transition-duration: 0.1s !important;
        }
    }
</style>


<div class="main-content">
    <div class="dashboard-header">
        <div class="welcome-section">
            <div class="user-info">
                <h2>Bienvenido, <?php echo htmlspecialchars($nombre_empleado); ?></h2>
                <h2><?php echo htmlspecialchars($userRol); ?></h2>
            </div>
            <div class="current-time">
                <div class="time-display" id="current-time">--:--:--</div>
                <div class="date-display" id="current-date">-- -- ----</div>
            </div>
        </div>

        <p class="welcome-message">Sistema de Control de Asistencia</p>
    </div>


    <div class="message-container">
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
    </div>

    <?php if ($userRol == 'Empleado'): ?>
    <div class="dashboard-header">
        <h3 class="section-title">Registrar Asistencia</h3>
        <div class="button-group">
            <button class="btn-entrada" onclick="handleTimeAction('entrada')">
                Marcar Entrada
            </button>
            <button class="btn-salida" onclick="handleTimeAction('salida')">
                Marcar Salida
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar reloj en tiempo real
    function updateClock() {
        const now = new Date();
        
        // Formatear hora
        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        const timeString = now.toLocaleTimeString('es-ES', timeOptions);
        
        // Formatear fecha
        const dateOptions = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const dateString = now.toLocaleDateString('es-ES', dateOptions);
        
        // Actualizar elementos
        document.getElementById('current-time').textContent = timeString;
        document.getElementById('current-date').textContent = dateString;

    }
    
    // Actualizar cada segundo
    updateClock();
    setInterval(updateClock, 1000);
    
    // Función para manejar acciones de marcación
    window.handleTimeAction = function(tipo) {
        const button = document.querySelector(`.btn-${tipo}`);
        
        // Confirmar acción
        const message = tipo === 'entrada' ? 
            '¿Confirmas que deseas marcar entrada?' : 
            '¿Confirmas que deseas marcar salida?';
            
        if (confirm(message)) {
            // Redirect a la página de marcación
            window.location.href = `marcar.php?tipo=${tipo}`;
        } else {
            // Remover estado de carga si se cancela
            setTimeout(() => {
                button.classList.remove('loading');
            }, 300);
        }
    };
    
    // Animaciones de entrada
    const cards = document.querySelectorAll('.status-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.style.animation = 'slideInFromTop 0.6s ease-out forwards';
    });
    
    // Efectos de hover mejorados
    const buttons = document.querySelectorAll('.btn-entrada, .btn-salida');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px) scale(1.02)';
        });
        
        button.addEventListener('mouseleave', function() {
            if (!this.classList.contains('loading')) {
                this.style.transform = 'translateY(0) scale(1)';
            }
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'e':
                case 'E':
                    e.preventDefault();
                    handleTimeAction('entrada');
                    break;
                case 's':
                case 'S':
                    e.preventDefault();
                    handleTimeAction('salida');
                    break;
            }
        }
    });
});
</script>
