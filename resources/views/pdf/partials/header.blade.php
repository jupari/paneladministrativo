<header>
    <div style="margin-top:10px;">
        <table class="border">
            <tr>
                <td width="30%" class="center">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" width="200" height="70">
                    @endif
                </td>
                <td width="45%">
                    <strong>MINDUVAL</strong><br>
                    CRA 6 A # 31-53 BR PORVENIR<br>
                    CALI - COLOMBIA<br>
                    CEL: 319 640 2798<br>
                    EMAIL: gerencia@minduval.com.co<br>
                    www.minduval.com
                </td>
                <td width="25%">
                    <strong>REALIZADO POR:</strong><br>
                    {{ $cotizacion->usuario->name ?? 'Administrador' }}<br><br>
                    <strong>VERSIÓN:</strong> 1<br>
                    <strong>FECHA:</strong><br>
                    {{ \Carbon\Carbon::parse($cotizacion->fecha)->format('d/m/Y') }}
                </td>
            </tr>
        </table>
        <table class="border">
            <tr>
                <td width="15%" class="bold gray">CLIENTE</td>
                <td width="35%">{{
                    trim($cotizacion->tercero->nombre_establecimiento) !== '' || $cotizacion->tercero->nombre_establecimiento != null
                        ? $cotizacion->tercero->nombre_establecimiento
                        : trim($cotizacion->tercero->nombres . ' ' . $cotizacion->tercero->apellidos)
                }}</td>
                <td width="15%" class="bold gray">CONTACTO</td>
                <td width="35%">
                    {{ $cotizacion->terceroContacto
                        ? $cotizacion->terceroContacto->nombres . ' ' . $cotizacion->terceroContacto->apellidos .
                          ' (' . $cotizacion->terceroContacto->cargo . ')'
                        : 'Sin contacto'
                    }}
                </td>
            </tr>
            <tr>
                <td class="bold gray">PROYECTO</td>
                <td colspan="3">{{ $cotizacion->proyecto ?? '' }}</td>
            </tr>
        </table>
    </div>

</header>
