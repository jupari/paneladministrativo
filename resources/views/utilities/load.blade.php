<!-- Pantalla de Carga (Spinner Profesional) -->
<div id="loadingScreen" class="loading-overlay d-none">
    <div class="loading-content">
        <div class="spinner-container">
            <div class="spinner"></div>
        </div>
        <p id="loadingMessage">Procesando, por favor espere...</p>
    </div>
</div>

<!-- Botón de prueba -->
<button id="startProcess" class="btn btn-primary">Iniciar Acción</button>

<!-- Estilos del Spinner -->
<style>
    /* Fondo oscuro con transparencia */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.3s ease-in-out;
    }

    /* Contenido centrado */
    .loading-content {
        text-align: center;
        color: white;
        font-size: 18px;
        font-weight: bold;
    }

    /* Contenedor del Spinner */
    .spinner-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 10px;
    }

    /* Diseño del Spinner */
    .spinner {
        width: 50px;
        height: 50px;
        border: 6px solid rgba(255, 255, 255, 0.3);
        border-top: 6px solid #ffffff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    /* Animación de giro */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
