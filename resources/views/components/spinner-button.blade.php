<button type="submit" id="{{ $id }}" class="{{ $class }} btn-spinner" onclick="showSpinner('{{ $id }}')" {{ $attributes }}>
    <span class="spinner-border spinner-border-sm d-none" id="spinner-{{ $id }}" role="status" aria-hidden="true"></span>
    <span id="text-{{ $id }}">{{ $slot }}</span>
</button>

<script>
    function showSpinner(buttonId) {
        let btn = document.getElementById(buttonId);
        let spinner = document.getElementById('spinner-' + buttonId);
        let text = document.getElementById('text-' + buttonId);

        if (btn && spinner && text) {
            btn.disabled = true; // Deshabilita el botón
            spinner.classList.remove('d-none'); // Muestra el spinner
            text.style.display = 'none'; // Oculta el texto
        }
    }
</script>
