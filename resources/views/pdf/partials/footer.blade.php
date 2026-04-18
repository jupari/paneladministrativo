<footer>
    <table class="border">
        <tr>
            <td width="70%">
                @php $cc = $cotizacion->condicionesComerciales->first(); @endphp
                @if($cc)
                    @if(!empty($cc->tiempo_entrega))<strong>TIEMPO DE ENTREGA:</strong> {{ $cc->tiempo_entrega }}<br>@endif
                    @if(!empty($cc->lugar_obra))<strong>SITIO DE ENTREGA:</strong> {{ $cc->lugar_obra }}<br>@endif
                    @if(!empty($cc->duracion_oferta))<strong>DURACIÓN DE LA OFERTA:</strong> {{ $cc->duracion_oferta }}<br>@endif
                    @if(!empty($cc->garantia))<strong>GARANTÍA:</strong> {{ $cc->garantia }}<br>@endif
                    @if(!empty($cc->forma_pago))<strong>FORMA DE PAGO:</strong> {{ $cc->forma_pago }}<br>@endif
                @else
                    TIEMPO DE ENTREGA: A convenir<br>
                    DURACIÓN DE LA OFERTA: {{ $diasVencimiento ?? 30 }} días<br>
                    FORMA DE PAGO: Pendiente por acordar
                @endif
            </td>
            <td width="30%" class="center">
                <strong>ELABORADO POR</strong><br>
                {{ $cotizacion->usuario->name ?? 'Administrador' }}<br><br>
                Página <span class="pagenum"></span> de <span class="pagecount"></span>
            </td>
        </tr>
    </table>
</footer>

