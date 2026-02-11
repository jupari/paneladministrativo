/**
 * Company Branding Verification Script
 * Verifica que el logo y branding de la empresa se esté aplicando correctamente
 */

document.addEventListener('DOMContentLoaded', function() {
    // Verificar si hay datos de empresa
    const brandLink = document.querySelector('.brand-link');
    if (brandLink && brandLink.hasAttribute('data-company')) {
        const companyName = brandLink.getAttribute('data-company');
        console.log('✅ Company branding loaded:', companyName);

        // Verificar logo de empresa
        const brandImage = brandLink.querySelector('.brand-image');
        if (brandImage) {
            console.log('✅ Company logo loaded:', brandImage.src);

            // Verificar si es un logo de empresa (no el default de AdminLTE)
            if (!brandImage.src.includes('AdminLTELogo.png')) {
                console.log('✅ Custom company logo detected');

                // Añadir indicador visual de éxito
                const indicator = document.createElement('div');
                indicator.style.cssText = `
                    position: fixed;
                    top: 10px;
                    right: 10px;
                    background: #28a745;
                    color: white;
                    padding: 8px 12px;
                    border-radius: 4px;
                    font-size: 12px;
                    z-index: 9999;
                    opacity: 0.9;
                `;
                indicator.textContent = '✅ Logo empresarial cargado';
                document.body.appendChild(indicator);

                // Ocultar después de 3 segundos
                setTimeout(() => {
                    indicator.remove();
                }, 3000);
            }
        }

        // Verificar colores personalizados
        const customStyles = document.querySelector('style:has([data-company])') ||
                           document.querySelector('style');
        if (customStyles && customStyles.textContent.includes('--company-primary')) {
            console.log('✅ Company colors applied');
        }
    } else {
        console.log('ℹ️ No company branding data found - using default AdminLTE branding');
    }
});