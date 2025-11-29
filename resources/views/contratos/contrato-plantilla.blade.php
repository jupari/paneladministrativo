<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato de Trabajo</title>
    <style>
        /* Formato de la página para PDF */
        @page {
            size: A4; /* Papel A4 */
            /*margin: 2.5cm 2cm 3cm 2cm;  Márgenes: Superior, Derecha, Inferior, Izquierda */
            margin: 2cm 1cm 1cm 1cm; /* Márgenes: Superior, Derecha, Inferior, Izquierda */
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.0;
            text-align: justify;
            margin: 0; /* Evita márgenes extra al imprimir */
        }

        .container {
            width: 100%;
            padding: 20px;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .table-container {
            margin-top: 20px;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid black;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        @page {
            margin: 2cm;
            size: A4;
            counter-increment: pages;
        }
        /* Pie de página */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            font-size: 12px;
            text-align: center;
            border-top: 3px solid blue;
            padding-top: 5px;
            display: flex;
            justify-content: space-between;
        }

        /* Contador de páginas en el PDF */
        /* .footer::after {
            content: "Página " counter(page) " de [TOTAL_PAGES]";
        } */
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo de la empresa -->
        <div style="text-align: right;">
            <img src="{{ public_path('images/logo-minduval.png') }}" alt="Logo Minduval" style="width: 180px;">
        </div>

        <!-- Título del contrato -->
        <div class="title">CONTRATO DE TRABAJO POR DURACIÓN DE LA OBRA O LABOR CONTRATADA</div>

        <!-- Tabla de datos del empleador y trabajador -->
        <div class="table-container">
            <table>
                <tr><th colspan="2">EMPLEADOR</th></tr>
                <tr><td><b>Nombre:</b></td> <td>MINDUVAL - MONTAJES INDUSTRIALES Y NAVALES S.A.S.</td></tr>
                <tr><td><b>NIT:</b></td> <td>900.519.812-8</td></tr>
                <tr><td><b>Representante Legal:</b></td> <td>RUBEN DARÍO RODRÍGUEZ SATIZABAL</td></tr>
            </table>

            <table>
                <tr><th colspan="2">TRABAJADOR</th></tr>
                <tr><td><b>Nombre:</b></td> <td>{{ $datos['NOMBRES'] }}  {{ $datos['APELLIDOS'] }}</td></tr>
                <tr><td><b>Cédula:</b></td> <td>{{ $datos['CEDULA'] ?? 'N/A' }}</td></tr>
                <tr><td><b>Ciudad Expedición:</b></td> <td>{{ $datos['EXPEDIDA_EN'] ?? 'N/A' }}</td></tr>
                <tr><td><b>Dirección:</b></td> <td>{{ $datos['DIRECCION'] ?? 'N/A' }}</td></tr>
                <tr><td><b>Fecha Nacimiento:</b></td> <td>{{ $datos['FECHA_NACIMIENTO'] ?? 'N/A' }}</td></tr>
                <tr><td><b>Cargo:</b></td> <td>{{ $datos['CARGO'] ?? 'N/A' }}</td></tr>
                <tr><td><b>Fecha Inicio Labores:</b></td> <td>{{ $datos['FECHA_INICIO_LABOR'] ?? 'N/A' }}</td></tr>
                <tr><td><b>Descripción Servicio:</b></td> <td>{{ $datos['DESCRIPCION_SERVICIO'] ?? 'N/A' }}</td></tr>
            </table>
        </div>

        {{-- <div class="section">
            <p>
                Entre la <b>MINDUVAL - MONTAJES INDUSTRIALES Y NAVALES S.A.S.</b>, sociedad legalmente constituida mediante documento privado del 20 de abril de 2012, inscrita en la Cámara de Comercio de Cali, el 27 de abril de 2012 con el No. 5191 del Libro IX, identificada con NIT No. 900.519.812-8, con domicilio principal en la ciudad de Cali, Valle del Cauca, representada en este acto por el señor <b>RUBEN DARÍO RODRÍGUEZ SATIZABAL</b>, mayor de edad, vecino de la ciudad de Cali – Valle del Cauca, identificado con la cédula de ciudadanía No. 16.460.416 expedida en Yumbo, en su calidad de Representante Legal, quien en adelante se denominará <b>EL EMPLEADOR</b> por una parte.
            </p>

            <p>
                Por otra parte, <b>{{ $datos['NOMBRES'] }}  {{ $datos['APELLIDOS'] }}</b>, mayor de edad, vecino de la ciudad de <b>CERRITO</b>, identificado con cédula de ciudadanía No. <b>{{ $datos['CEDULA'] ?? 'N/A' }}</b> expedida en <b>{{ $datos['EXPEDIDA_EN'] ?? 'N/A' }}</b>, quien en adelante se denominará <b>EL TRABAJADOR</b>. Ambas partes en conjunto se denominarán <b>LAS PARTES</b>, y han decidido suscribir el presente <b>CONTRATO INDIVIDUAL DE TRABAJO</b>, bajo la tipología de duración de la obra o labor contratada, el cual se rige por las normas contenidas en el Código Sustantivo del Trabajo, la Ley 50 de 1990, la Ley 789 de 2002 y por las cláusulas que en adelante se establecen.
            </p>
        </div>

        <div class="section">
            <h3>PRIMERA. PRESTACIÓN DEL SERVICIO.</h3>
            <p>
                <b>EL TRABAJADOR</b> se obliga con <b>EL EMPLEADOR</b> a incorporar su capacidad normal de trabajo en el desempeño de todas las funciones propias del empleo u oficio estipulado en el contrato, así como en la ejecución de las tareas ordinarias y anexas al mencionado cargo, de conformidad con los reglamentos, órdenes e instrucciones impartidas por <b>EL EMPLEADOR</b>, observando en su cumplimiento la diligencia y el cuidado necesario.
            </p>
            <p>
                LAS PARTES acuerdan que la obra o labor a la que se sujeta el presente contrato es la relativa a la ejecución del proyecto denominado <b>"{{ $datos['DESCRIPCION_SERVICIO'] ?? 'N/A' }}"</b> en la planta <b>{{$datos['UBICACION'] ?? 'N/A' }}</b>, ubicada en la vía Yumbo – Aeropuerto – Palmira, departamento del Valle del Cauca, proyecto contratado por la sociedad <b>UNILEVER ANDINA COLOMBIA LTDA</b>, con la que la sociedad <b>MINDUVAL - MONTAJES INDUSTRIALES Y NAVALES S.A.S.</b> mantiene relaciones comerciales.
            </p>
        </div>

        <div class="section">
            <h3>SEGUNDA. CARGO.</h3>
            <p>
                El cargo que desempeñará <b>EL TRABAJADOR</b> es el de <b>{{ $datos['CARGO'] ?? 'N/A' }}</b> de la sociedad <b>MINDUVAL - MONTAJES INDUSTRIALES Y NAVALES S.A.S.</b>, en el proyecto denominado <b>"{{ $datos['DESCRIPCION_SERVICIO'] ?? 'N/A' }}"</b> en la planta <b>HPC PALMIRA DE UNILEVER</b>, contratado por la sociedad <b>UNILEVER ANDINA COLOMBIA LTDA</b>.
            </p>
        </div>

        <div class="section">
            <h3>TERCERA. SALARIO.</h3>
            <p>
                <b>EL EMPLEADOR</b> pagará al trabajador un salario equivalente a <b>UN MILLÓN SETECIENTOS MIL PESOS M/CTE (${{ $datos['SALARIO'] ?? 'N/A' }})</b>, el cual será pagado mediante consignación bancaria en periodos quincenales los días cinco (5) y veinte (20), o el proporcional a dicha suma por los días trabajados.
            </p>
        </div>

        <div class="section">
            <h3>CUARTA. JORNADA LABORAL.</h3>
            <p>
                <b>EL TRABAJADOR</b> se encuentra sujeto a lo establecido en el artículo 161 del Código Sustantivo del Trabajo, relativo a la jornada máxima legal.
            </p>
        </div>

        <div class="section">
            <h3>QUINTA. LUGAR DE TRABAJO.</h3>
            <p>
                Las labores se desarrollarán en el establecimiento de comercio denominado <b>UNILEVER ANDINA COLOMBIA LTDA</b>, ubicado en la dirección <b>Km 13 Cencar - Aeropuerto, Palmira, Valle del Cauca</b>.
            </p>
        </div>

        <div class="section">
            <h3>SÉPTIMA. PERIODO DE PRUEBA.</h3>
            <p>
                El periodo de prueba será de <b>SESENTA (60) DÍAS</b>, durante el cual el contrato podrá darse por terminado sin que se cause pago de indemnización alguna, en forma unilateral, conforme al artículo 80 del Código Sustantivo del Trabajo.
            </p>
        </div>

        <div class="section">
            <h3>OCTAVA. OBLIGACIONES DEL EMPLEADOR.</h3>
            <p>Serán obligaciones del <b>EMPLEADOR</b>:</p>
            <ul>
                <li>Poner a disposición del <b>TRABAJADOR</b> los instrumentos, equipos y materias primas necesarios para el desarrollo de sus labores.</li>
                <li>Entregar al <b>TRABAJADOR</b> el equipo de protección personal necesario.</li>
                <li>Realizar exámenes médicos ocupacionales de ingreso, periódicos y de egreso.</li>
                <li>Pagar los viáticos o gastos de desplazamiento debidamente justificados.</li>
            </ul>
        </div>

        <div class="section">
            <table style="width: 100%; margin-top: 80px;border:none">
                <tr>
                    <!-- Firma Representante Legal -->
                    <td style="width: 50%; text-align: center; vertical-align: bottom; padding-bottom: 30px; border:none">
                        <hr style="width: 80%; border: 1px solid black;">
                        <p style="margin:0px">Representante Legal</p>
                    </td>

                    <!-- Firma Trabajador -->
                    <td style="width: 50%; text-align: center; vertical-align: bottom; padding-bottom: 6px;border:none; margin-top:20%">
                        <hr style="width: 80%; border: 1px solid black;">
                        <p style="margin:0px">{{ $datos['NOMBRES'] }}  {{ $datos['APELLIDOS'] }}</p>
                        <p style="margin:0px">C.C. {{ $datos['CEDULA'] ?? 'N/A' }}</p>
                    </td>
                </tr>
            </table>
        </div>
    </div> --}}

    <!-- Pie de página con numeración -->
    {{-- <div class="footer">
        gerencia@minduval.com.co - <span>Página <span class="pagenum"></span> de <span class="total-pages"></span></span>
    </div> --}}
    {{-- <div class="footer">
        <span>gerencia@minduval.com.co</span>
        <span class="page-number"></span>
    </div> --}}


    {{-- pruebaaa  --}}
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><span style="position:absolute; z-index:-1895825408;left:0px;margin-left:75px;margin-top:118px;width:560px; height:51px;"><img width="560" height="51" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAjAAAAAzAQMAAACzNKCrAAAAAXNSR0ICQMB9xQAAAAZQTFRFAAAA//8AiNtwUAAAAAF0Uk5TAEDm2GYAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAAZdEVYdFNvZnR3YXJlAE1pY3Jvc29mdCBPZmZpY2V/7TVxAAAARUlEQVRIx+3UsQkAIAxE0escy9UdyQ20CgghRART/V+Hd12koLYumsqCgYGB+czYwUh31A8PBgYGpozxL00vwcDAwBQxG4XG9bq1gQrmAAAAAElFTkSuQmCC" alt="image"></span>Entre la <strong>MINDUVAL - MONTAJES INDUSTRIALES Y NAVALES S.A.S.,</strong> sociedad legalmente constituida mediante documento privado del 20 de abril de 2012, inscrita en la C&aacute;mara de Comercio de Cali, el 27 de abril de 2012 con el No. 5191 del Libro IX, identificada con NIT No. 900.519.812-8, con domicilio principal en la ciudad de Cali, Valle del Cauca, representada en este acto por el se&ntilde;or <strong>RUBEN DAR&Iacute;O RODR&Iacute;GUEZ SATIZABAL,</strong> mayor de edad, vecino de la ciudad de Cali &ndash; Valle del Cauca, identificado con la c&eacute;dula de ciudadan&iacute;a No. 16.460.416 expedida en Yumbo, en su calidad de Representante Legal, quien en adelante se denominar&aacute; <strong>EL EMPLEADOR</strong> por una parte; por la otra, <strong>FRANCISCO JAVIER CORCHUELO L&Oacute;PEZ</strong>, mayor de edad, vecino de la ciudad de <strong>CERRITO</strong>, identificado con c&eacute;dula de ciudadan&iacute;a No. <strong>1.111.111.111</strong> expedida en La Cumbre, Valle quien en adelante se denominar&aacute; <strong>EL TRABAJADOR,</strong> y en conjunto se denominar&aacute;n <strong>LAS PARTES</strong>, hemos decidido suscribir el presente <strong>CONTRATO INDIVIDUAL DE TRABAJO,</strong> bajo la tipolog&iacute;a de duraci&oacute;n de la obra o labor contratada, el cual se rige por las normas contenidas en el C&oacute;digo Sustantivo del Trabajo, la Ley 50 de 1990, la Ley 789 de 2002 y por las cl&aacute;usulas que en adelante se denotan.&nbsp;</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>PRIMERA. PRESTACI&Oacute;N DEL SERVICIO.</u></strong> <strong>EL TRABAJADOR</strong> se obliga con <strong>EL EMPLEADOR</strong> a incorporar su capacidad normal de trabajo a su servicio en el desempe&ntilde;o de todas las funciones propias del empleo u oficio en el cargo estipulado en el encabezado del contrato, as&iacute; como en la ejecuci&oacute;n de las tareas ordinarias y anexas al mencionado cargo, de conformidad con los reglamentos, &oacute;rdenes e instrucciones que le imparta <strong>EL EMPLEADOR</strong>, observando en su cumplimiento la diligencia y el cuidado necesario. As&iacute; mismo, <strong>LAS PARTES</strong> concuerdan que la obra o labor a la que se sujeta el presente contrato es la relativa a la ejecuci&oacute;n del proyecto denominado <strong><span style="background:yellow;">&quot;FABRICACI&Oacute;N E INSTALACI&Oacute;N SERPENTINES PAILAS Y LIMPIEZA ORDEN</span> <span style="background:yellow;">DE COMPRA: PO15862080&quot;</span></strong> en la planta <strong><span style="background:yellow;">HPC PALMIRA DE UNILEVER</span></strong> que se desarrolla en la v&iacute;a Yumbo &ndash; Aeropuerto &ndash; Palmira, departamento del Valle del Cauca, proyecto contratado por la sociedad <strong><span style="background:yellow;">UNILEVER ANDINA COLOMBIA LTDA,</span></strong> con la que la sociedad <strong>MINDUVAL - MONTAJES INDUSTRIALES Y NAVALES S.A.S.</strong> sostiene relaciones comerciales.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:.05pt;margin-bottom:.25pt; margin-left:5.65pt;"><span style="position:absolute;z-index:-1895824384; left:0px;margin-left:8px;margin-top:33px;width:627px;height:51px;"><img width="627" height="51" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnMAAAAzAQMAAADGuRwIAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAGUExURQAAAP//AIjbcFAAAAABdFJOUwBA5thmAAAACXBIWXMAAA7DAAAOwwHHb6hkAAAARElEQVRYw+3NsQ0AIAhEUYgDOIKrOjYdWlIYjaHk/+6ad/JZ82uCh4eHV9bzVBau+t6Kh4eHhxebcmw8eTw8PLyinukCoZb3h3EwsGsAAAAASUVORK5CYII=" alt="image"></span><strong><u>SEGUNDA.</u></strong> <u><strong>CARGO.</strong></u> El cargo que desempe&ntilde;ar&aacute; <strong>EL TRABAJADOR</strong> es el de <strong><span style="background:yellow;">&iexcl;Error! No se</span> <span style="background:yellow;">encuentra el origen de la referencia.</span></strong> de la sociedad <strong>MINDUVAL - MONTAJES INDUSTRIALES Y NAVALES S.A.S.,</strong> en el proyecto denominado <strong>&quot;FABRICACI&Oacute;N E INSTALACI&Oacute;N SERPENTINES PAILAS Y LIMPIEZA ORDEN DE COMPRA: PO15862080&quot;</strong> en la planta <strong>HPC PALMIRA DE UNILEVER</strong>, contratado por la sociedad <strong>UNILEVER ANDINA COLOMBIA LTDA</strong>.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 1. Funciones generales del cargo. EL TRABAJADOR</strong> deber&aacute; llevar cabo todas las actividades que sean delegadas por parte de su <strong>EMPLEADOR</strong>, con el &uacute;nico fin de llevar hasta su culminaci&oacute;n la obra se&ntilde;alada en la cl&aacute;usula primera, que principalmente se encuentran expuestas en el Manual de Cargo el cual es entregado junto con el contrato y que es parte integral del mismo.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>TERCERA.</u></strong> <u><strong>SALARIO.</strong></u> <strong>EL EMPLEADOR</strong> pagar&aacute; al trabajador por la prestaci&oacute;n de sus servicios el salario equivalente al valor de <strong><span style="background:yellow;">UN MILL&Oacute;N SETECIENTOS&nbsp; MIL PESOS M/CTE ($ 1.700.000)</span></strong>, el cual ser&aacute; pagado mediante consignaci&oacute;n bancaria en periodos quincenales los d&iacute;as cinco (5) y veinte (20), o el proporcional a dicha suma por los d&iacute;as trabajados.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 1. EL EMPLEADOR</strong> podr&aacute; establecer en forma transitoria y por mera liberalidad bonificaciones, primas extralegales, auxilios, p&oacute;lizas de seguro, auxilio de transporte intermunicipal, vivienda o auxilio para vivienda, obsequios, gastos por transporte, gastos de representaci&oacute;n, la alimentaci&oacute;n, los auxilios de productividad y dem&aacute;s conceptos diferentes al salario estipulado, que en forma ocasional y teniendo en cuenta las urgencias de producci&oacute;n, ventas, actividad, intensidad de trabajo, precios de los productos y calidad se reconozcan al <strong>TRABAJADOR</strong>, los cuales no constituir&aacute;n salario, por lo que no ser&aacute;n tenidos en cuenta para liquidar las prestaciones sociales, indemnizaciones, aportes a la Seguridad Social o aportes a parafiscales.&nbsp;</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 2.</strong> El factor salarial comprende el pago de horas extra diurnas, nocturnas, dominicales, d&iacute;as festivos y recargos nocturnos si se llegasen a causar.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 3.</strong> En caso de que el monto salarial convenido sea modificado, a este contrato se le incluir&aacute; el correspondiente anexo, que remplazar&aacute; el acuerdo anterior sobre ese aspecto.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 4. EL TRABAJADOR</strong> recibir&aacute; el salario liquidado en proporci&oacute;n a las horas trabajadas en caso de que labore medio tiempo o en jornadas inferiores a la m&aacute;xima legal.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 5. EL EMPLEADOR</strong> no reconocer&aacute; ninguna remuneraci&oacute;n por el excedente del tiempo de trabajo que haya sido necesario para efectuar por culpa del trabajador alguna de las labores encomendadas a &eacute;l por raz&oacute;n de descuido, lentitud en el desempe&ntilde;o y errores u omisiones de &eacute;ste.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>CUARTA.</u></strong> <u><strong>JORNADA LABORAL.</strong></u> <strong>EL TRABAJADOR</strong> se encuentra sujeto a lo establecido en el art&iacute;culo 161 del C&oacute;digo Sustantivo del Trabajo, relativo a que se ejecutar&aacute;n las labores en la jornada m&aacute;xima legal.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>QUINTA.</u></strong> <u><strong>LUGAR DE TRABAJO.</strong></u> Las labores se desarrollar&aacute;n en el establecimiento de comercio denominado <strong><span style="background:yellow;">UNILEVER ANDINA COLOMBIA LTDA</span>,</strong> ubicado en la direcci&oacute;n <span style="background:yellow;">Km 13 Cencar -</span> <span style="background:yellow;">Aeropuerto</span>, en la ciudad de Palmira, departamento de Valle del Cauca.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 1.</strong> Este lugar de trabajo podr&aacute; ser modificado sin desmejorar las condiciones laborales del <strong>TRABAJADOR.</strong></p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 2.</strong> En aplicaci&oacute;n de la facultad del <strong>EMPLEADOR</strong> denominada ius variandi, &eacute;ste podr&aacute; remitir al <strong>TRABAJADOR</strong> a ejercer sus funciones a establecimientos de comercio diferentes al antedicho, de manera que el cargo tiene como requisito esencial la movilidad entre distintas sedes de trabajo, ciudades y/o dependencias. Por consiguiente, <strong>EL TRABAJADOR</strong> se compromete a asumir los eventuales cambios y/o traslados transitorios que se lleguen a requerir por parte del <strong>EMPLEADOR,</strong> sin restricci&oacute;n geogr&aacute;fica, para lo cual bastar&aacute; la notificaci&oacute;n verbal o escrita por parte del <strong>EMPLEADOR.</strong></p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>SEXTA. DURACI&Oacute;N DEL CONTRATO.</u></strong> Como el encabezado de este documento lo se&ntilde;ala, la duraci&oacute;n del contrato es por duraci&oacute;n de la obra o labor contratada, la cual est&aacute; supeditada a la terminaci&oacute;n de la obra o labor contratada<strong>.</strong></p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>S&Eacute;PTIMA.</u></strong> <u><strong>PERIODO DE PRUEBA.</strong></u> El periodo de prueba ser&aacute; de <strong>SESENTA (60)</strong> <strong>DIAS</strong>, en el cual se podr&aacute; dar por terminado por <strong>LAS PARTES</strong> el contrato, sin que se cause el pago de indemnizaci&oacute;n alguna, en forma unilateral, de conformidad con el art&iacute;culo 80 del C&oacute;digo Sustantivo del Trabajo, con sus respectivas modificaciones. Este periodo se entiende iniciado simult&aacute;neamente a la suscripci&oacute;n del presente contrato.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>OCTAVA. OBLIGACIONES DEL EMPLEADOR.</u></strong> Ser&aacute;n obligaciones del <strong>EMPLEADOR</strong> las estipuladas en el Articulo 56 y 57 del C&oacute;digo Sustantivo del Trabajo y dem&aacute;s normas que lo sustituyan o modifiquen y las pactadas por ambas partes en este contrato</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:41.9pt;text-indent:-.25in;"><strong><span style="line-height:103%;">1.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Poner a disposici&oacute;n del <strong>TRABAJADOR</strong> los instrumentos, equipos y materias primas para el desarrollo de sus labores.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:41.9pt;text-indent:-.25in;"><strong><span style="line-height:103%;">2.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Entregar al <strong>TRABAJADOR</strong> el equipo de protecci&oacute;n personal, para asegurar de ese modo la seguridad y salud en el trabajo.</p>
    <p class="MsoNormal" style="margin-left:41.9pt;text-indent:-.25in;"><strong><span style="line-height:103%;">3.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Realizar al <strong>TRABAJADOR</strong> de forma peri&oacute;dica o aleatoria prueba de toxicolog&iacute;a y alcoholimetr&iacute;a.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:41.9pt;text-indent:-.25in;"><strong><span style="line-height:103%;">4.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Pagar la remuneraci&oacute;n pactada en las condiciones, periodos y lugares convenidos.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:41.9pt;text-indent:-.25in;"><strong><span style="line-height:103%;">5.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Realizar los ex&aacute;menes m&eacute;dicos ocupacionales, es decir, el de ingreso, peri&oacute;dicos y el de egreso.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:41.9pt;text-indent:-.25in;"><strong><span style="line-height:103%;">6.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Pagar los vi&aacute;ticos o gastos de desplazamiento del trabajador, debidamente justificados.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>NOVENA. OBLIGACIONES DEL TRABAJADOR.</u></strong> <strong>EL TRABAJADOR</strong> deber&aacute; desempe&ntilde;ar las funciones inherentes al cargo establecido en la cl&aacute;usula segunda y en las labores complementarias del mismo, de conformidad con las &oacute;rdenes e instrucciones que le imparta <strong>EL EMPLEADOR</strong>, las estipuladas en el Articulo 56 y 58 del C&oacute;digo Sustantivo del Trabajo y dem&aacute;s normas que lo sustituyan o modifiquen, as&iacute; como las pactadas por ambas partes en este contrato;&nbsp;</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">I.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Cumplir con estrictos niveles de calidad, puntualidad, diligencia, cuidado y pericia a los oficios que se le impongan.</p>
    <p class="MsoNormal" style="margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">II.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Llevar a cabo todas las funciones que le sean delegadas mediante el Manual de Funciones del cargo asignado.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.65pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">III.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Obrar con plena observancia de los principios &eacute;ticos que exige su labor.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.6pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">IV.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Planificar la forma m&aacute;s eficiente y eficaz a trav&eacute;s de la cual se realizar&aacute; el trabajo de la empresa.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.7pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">V.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Procurar un adecuado ambiente de trabajo.</p>
    <p class="MsoNormal" style="margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">VI.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> No prestar sus servicios a ning&uacute;n otro patrono, ni dedicarse a negocios propios relacionados con su actividad.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">VII.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> No presentarse al lugar de trabajo bajo los efectos de embriaguez o de sustancias psicoactivo o alucin&oacute;genas</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">VIII.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> No utilizar los dispositivos proporcionados como herramientas de trabajo para su uso personal; se le proh&iacute;be la instalaci&oacute;n de aplicaciones o modificaci&oacute;n de su configuraci&oacute;n, para ello requerir&aacute; previa autorizaci&oacute;n del <strong>EMPLEADOR</strong>.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">IX.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> No ingresar, guardar, custodiar ni suministrar en su lugar de trabajo sustancias alucin&oacute;genas, explosivos, armas de fuego o traum&aacute;ticas sin los debidos permisos otorgados por las autoridades competentes, objetos o bienes muebles de mala procedencia (Receptaci&oacute;n) y todo tipo de cosa que se le asemeje y que este estrictamente tipificada como conducta punible en el C&oacute;digo Penal Colombiano.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.65pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">X.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> No consumir en el lugar de trabajo sustancias psicoactivas ni bebidas embriagantes.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">XI.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Resolver los inconvenientes que puedan surgir durante la ejecuci&oacute;n del contrato con la empresa cliente del <strong>EMPLEADOR,</strong> generando un trato respetuoso a quienes la conforman.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.5pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">XII.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Atender sugerencias, comentarios o recomendaciones de los clientes en caso de ser necesario.</p>
    <p class="MsoNormal" style="margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">XIII.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Informar de manera constante al <strong>EMPLEADOR</strong> o a sus representantes de las observaciones que estime conducentes para la correcta ejecuci&oacute;n de la labor encomendada, con la finalidad de sostener una comunicaci&oacute;n asertiva y constructiva entre <strong>LAS PARTES</strong>, evitando que surjan da&ntilde;os y perjuicios.</p>
    <p class="MsoNormal" style="margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">XIV.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Exhibir sus art&iacute;culos personales en caso de que sea requerido por <strong>EL EMPLEADOR</strong> o por el personal de la empresa en la que se desarrolle la obra o labor contratada.</p>
    <p class="MsoNormal" style="margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">XV.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> El <strong>TRABAJADOR</strong> se obliga a conservar todos los archivos digitales y f&iacute;sicos que produzca, genere, le sean entregados o consiga en el desarrollo de sus funciones, debiendo adem&aacute;s entregarlos en el momento que se le requiera y al finalizar la relaci&oacute;n laboral.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.5pt; margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">XVI.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Ser emp&aacute;tico con los clientes, superiores y compa&ntilde;eros de trabajo, e igualmente trabajar en equipo y ser proactivo.&nbsp;</p>
    <p class="MsoNormal" style="margin-left:55.6pt;text-indent:-41.9pt;"><strong><span style="line-height:103%;">XVII.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Avisar oportunamente a la empresa todas las novedades que se puedan presentar frente a su estado de salud, traslados de ubicaci&oacute;n, entre otros que puedan eventualmente afectar el cumplimiento de sus obligaciones.&nbsp;</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:.8pt;margin-left:41.9pt;text-align:left;text-indent:0in; line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 1.</strong> En el caso del Numeral VII de esta cl&aacute;usula <strong>EL TRABAJADOR</strong> se practicar&aacute; de forma libre y voluntaria prueba toxicol&oacute;gica y de alcoholimetr&iacute;a, la cual podr&aacute; ser practicada en el lugar de trabajo por <strong>EL EMPLEADOR</strong> o ser remitido a un laboratorio designado por <strong>EL EMPLEADOR.</strong></p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>D&Eacute;CIMA. TERMINACI&Oacute;N DEL CONTRATO.</u></strong> El presente contrato de trabajo ser&aacute; finalizado conforme a lo estipulado por el C&oacute;digo Sustantivo del Trabajo en su art&iacute;culo 61 S.S., dem&aacute;s normas que lo sustituyan o modifiquen y las pactadas por ambas partes en este contrato destac&aacute;ndose entonces:</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:41.9pt;text-indent:-.25in;"><strong><span style="line-height:103%;">1.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Por muerte del trabajador.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:.8pt;margin-left:41.9pt;text-align:left;text-indent:0in; line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:41.9pt;text-indent:-.25in;"><strong><span style="line-height:103%;">2.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Por mutuo consentimiento.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:41.9pt;text-align:left;text-indent:0in; line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:.8pt; margin-left:41.9pt;text-indent:-.25in;line-height:107%;"><strong><span style="line-height:107%;">3.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Por la liquidaci&oacute;n o clausura definitiva de la empresa o establecimiento de comercio.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:41.9pt;text-align:left;text-indent:0in; line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.4pt; margin-left:41.9pt;text-indent:-.25in;"><strong><span style="line-height:103%;">4.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Por la terminaci&oacute;n de la obra o labor contratada.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:41.9pt;text-align:left;text-indent:0in; line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 1.</strong> Las partes acuerdan que, a la terminaci&oacute;n del contrato de trabajo, el trabajador otorga un plazo de diez (10) d&iacute;as h&aacute;biles a la empresa para el pago de su liquidaci&oacute;n definitiva, periodo durante el cual <strong>EL TRABAJADOR</strong> renuncia expresamente a reclamar indemnizaci&oacute;n moratoria por retardo en el pago de sus prestaciones sociales.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 2.</strong> Al terminar el contrato el trabajador debe presentarse a recibir las &oacute;rdenes para el examen m&eacute;dico de retiro y realizar dentro de los cinco (5) d&iacute;as siguientes. Si no lo hace o manifiesta alg&uacute;n reclamo se considera que elude o dilata el examen, relevando al <strong>EMPLEADOR</strong> de cualquier obligaci&oacute;n al respecto.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:41.9pt;text-align:left;text-indent:0in; line-height:107%;">&nbsp;</p>
    <h4 style="margin-top:0in;margin-right:-.65pt;margin-bottom:0in;margin-left: 5.65pt;margin-bottom:.0001pt;">D&Eacute;CIMA PRIMERA. TERMINACI&Oacute;N DEL CONTRATO POR JUSTA CAUSA POR PARTE DEL</h1>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.25pt; margin-left:6.1pt;"><strong><u>EMPLEADOR.</u></strong> Ser&aacute;n justas causas para poner t&eacute;rmino al contrato de trabajo unilateralmente por parte del <strong>EMPLEADOR</strong>, las enumeradas en el literal a del art&iacute;culo 62 del C&oacute;digo Sustantivo del Trabajo, y las pactadas por ambas partes en este contrato:</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:41.9pt;text-align:left;text-indent:0in; line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">a.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> La violaci&oacute;n del <strong>TRABAJADOR</strong> de cualquiera de sus obligaciones legales, contractuales o reglamentarias.</p>
    <p class="MsoNormal" style="margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">b.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> El incumplimiento de las funciones que le son asignadas mediante el Manual de Funciones del cargo a desarrollar.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">c.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> La falta de asistencia puntual al trabajo, sin excusa suficiente a juicio del empleador, por tres (3) veces dentro de un mismo mes calendario.</p>
    <p class="MsoNormal" style="margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">d.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> La ejecuci&oacute;n por parte del <strong>TRABAJADOR</strong> de labores remuneradas al servicio de terceros.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.4pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">e.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> La revelaci&oacute;n de secretos y datos reservados de la empresa.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">f.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Las desavenencias frecuentes con sus compa&ntilde;eros de trabajo.</p>
    <p class="MsoNormal" style="margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">g.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Presentarse al lugar de trabajo en condici&oacute;n de embriaguez o bajo el efecto de sustancias psicoactivas.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.4pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">h.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> El abandono del lugar de trabajo sin el permiso de sus superiores.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">i.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> La falta de asistencia a una jornada de trabajo, sin justificaci&oacute;n alguna.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">j.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Enmendar, borrar o adulterar cualquier documento propio de la empresa.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.35pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">k.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Consumir o sacar del lugar del trabajo implementos, informaci&oacute;n o mercanc&iacute;a de propiedad del <strong>EMPLEADOR</strong> sin el debido consentimiento por escrito del superior inmediato.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">l.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Almacenar, suministrar, expender sustancias psicoactivas o bebidas embriagantes</p>
    <p class="MsoNormal" style="margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">m.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;</span></span></strong> Presentar descuadres por faltante de dinero, equipos, herramientas, mercanc&iacute;as o insumos en las labores que le han sido encomendadas.</p>
    <p class="MsoNormal" style="margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">n.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Permitir, bien sea por descuido, negligencia, falta de cuidado o de prevenci&oacute;n que sean sustra&iacute;dos de las instalaciones de la empresa dineros, equipos, herramientas, mercanc&iacute;as o insumos, sin que se cuente con la autorizaci&oacute;n debida.</p>
    <p class="MsoNormal" style="margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">o.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Valerse del <em>Good Will</em> del empleador para emprender, respaldar o acreditar negocios particulares o actividades comerciales personales.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">p.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Presentar cuentas de gastos ficticias o reportar como cumplidas tareas no efectuadas.</p>
    <p class="MsoNormal" style="margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">q.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Irrespetar a sus superiores, compa&ntilde;eros de trabajo, subalternos, clientes, visitantes o personas que de una u otra forma est&eacute;n relacionados con la empresa.</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">r.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> Ocultar o encubrir cualquier acto o hecho que cause o pueda causar perjuicio al</p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:39.4pt;margin-bottom: 1.4pt;margin-left:74.05pt;"><strong>EMPLEADOR.</strong></p>
    <p class="MsoNormal" style="margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">s.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Cualquier falta moral y/o las buenas costumbres, que se realice dentro de las instalaciones de la empresa, en la jornada de trabajo o fuera de ella.</p>
    <p class="MsoNormal" style="margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">t.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> No presentar las incapacidades m&eacute;dicas que le sean otorgadas ante el <strong>EMPLEADOR</strong> dentro de los tres (3) d&iacute;as h&aacute;biles siguientes a que le hayan sido prescritas.</p>
    <p class="MsoNormal" style="margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">u.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> La realizaci&oacute;n de actividades que se configuren como actos de competencia desleal en contra del <strong>EMPLEADOR.</strong></p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.25pt; margin-left:73.55pt;text-indent:-.25in;"><strong><span style="line-height:103%;">v.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> No superar el examen de conocimientos t&eacute;cnicos que efect&uacute;a el cliente del <strong>EMPLEADOR</strong> previo ingreso a la planta o espacio f&iacute;sico donde se desarrolla el proyecto.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <h4 style="margin-top:0in;margin-right:-.65pt;margin-bottom:0in;margin-left: 5.65pt;margin-bottom:.0001pt;">DECIMA SEGUNDA TERMINACI&Oacute;N DEL CONTRATO POR JUSTA CAUSA POR PARTE DEL</h1>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>TRABAJADOR.</u></strong> Ser&aacute;n justas causas para poner t&eacute;rmino al contrato de trabajo unilateralmente por parte del <strong>TRABAJADOR</strong>, las enumeradas en el literal b del art&iacute;culo 62 del C&oacute;digo Sustantivo del Trabajo, y las pactadas por ambas partes en este contrato:</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:44.65pt;text-indent:-.25in;"><strong><span style="line-height:103%;">a.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> El haber sufrido enga&ntilde;o por parte del <strong>EMPLEADOR</strong>, respecto de las condiciones de trabajo.</p>
    <p class="MsoNormal" style="margin-left:44.65pt;text-indent:-.25in;"><strong><span style="line-height:103%;">b.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Todo acto de violencia, malos tratamientos o amenazas graves inferidas por el <strong>EMPLEADOR</strong> contra el trabajador o los miembros de su familia, dentro o fuera del servicio, o inferidas dentro del servicio por los parientes, representantes o dependientes del empleador con el consentimiento o la tolerancia de &eacute;ste.</p>
    <p class="MsoNormal" style="margin-left:44.65pt;text-indent:-.25in;"><strong><span style="line-height:103%;">c.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Cualquier acto del <strong>EMPLEADOR</strong> o de sus representantes que induzca al <strong>TRABAJADOR</strong> a cometer un acto il&iacute;cito o contrario a sus convicciones pol&iacute;ticas o religiosas.</p>
    <p class="MsoNormal" style="margin-left:44.65pt;text-indent:-.25in;"><strong><span style="line-height:103%;">d.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Todas las circunstancias que el <strong>TRABAJADOR</strong> no pueda prever al celebrar el contrato, y que pongan en peligro su seguridad o su salud, y que el empleador no se allane a modificar.</p>
    <p class="MsoNormal" style="margin-left:44.65pt;text-indent:-.25in;"><strong><span style="line-height:103%;">e.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> Todo perjuicio causado maliciosamente por el <strong>EMPLEADOR</strong> al <strong>TRABAJADOR</strong> en la prestaci&oacute;n del servicio.</p>
    <p class="MsoNormal" style="margin-left:44.65pt;text-indent:-.25in;"><strong><span style="line-height:103%;">f.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;</span></span></strong> El incumplimiento sistem&aacute;tico sin razones v&aacute;lidas por parte del <strong>EMPLEADOR</strong>, de sus obligaciones convencionales o legales.&nbsp;</p>
    <p class="MsoNormal" style="margin-left:44.65pt;text-indent:-.25in;"><strong><span style="line-height:103%;">g.<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;</span></span></strong> La exigencia del <strong>EMPLEADOR</strong>, sin razones v&aacute;lidas, de la prestaci&oacute;n de un servicio distinto, o en lugares diversos de aqu&eacute;l para el cual se le contrat&oacute;.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>D&Eacute;CIMA TERCERA. SUSPENSI&Oacute;N.</u></strong> El contrato de trabajo se suspender&aacute; por las causales establecidas en el art&iacute;culo 51 del C&oacute;digo Sustantivo del Trabajo. Durante el periodo de suspensi&oacute;n se interrumpe la obligaci&oacute;n del <strong>TRABAJADOR</strong> de prestar el servicio contratado y la obligaci&oacute;n del <strong>EMPLEADOR</strong> de pagar el salario pactado.&nbsp;</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:.8pt;margin-left:5.9pt;text-align:left;text-indent:0in; line-height:107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 1. EL EMPLEADOR</strong> podr&aacute; descontar el periodo de suspensi&oacute;n para efectos de liquidaci&oacute;n de las prestaciones sociales.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>D&Eacute;CIMA CUARTA. MODIFICACIONES AL CONTRATO.</u></strong> <strong>EL TRABAJADOR</strong> acepta desde ahora expresamente todas las modificaciones determinadas por <strong>EL EMPLEADOR</strong>, en ejercicio del poder subordinante, de sus condiciones laborales, tales como la jornada de trabajo, el lugar de prestaci&oacute;n de servicios, el cargo u oficio y/o funciones y la forma de remuneraci&oacute;n.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <h4 style="margin-top:0in;margin-right:-.65pt;margin-bottom:0in;margin-left: 5.65pt;margin-bottom:.0001pt;">D&Eacute;CIMA QUINTA. RECONOCIMIENTO DE LA NORMATIVA INTERNA. <span style="text-decoration:none;">EL TRABAJADOR</span></h1>
    <p class="MsoNormal" style="margin-left:6.1pt;">mediante la aceptaci&oacute;n y firma del presente contrato manifiesta que tiene conocimiento de la existencia y contenido del Reglamento Interno de Trabajo de la empresa, que se pone a disposici&oacute;n del trabajador y hace parte integral del presente contrato.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>D&Eacute;CIMA SEXTA. AUTORIZACI&Oacute;N DE DEDUCCIONES.</u> EL TRABAJADOR</strong> autoriza expresamente a la empresa para que deduzca de los salarios, bonificaciones, comisiones o cualquier saldo que a su favor pudiera existir en el momento de su retiro, por concepto de pr&eacute;stamos otorgados, deudas por mercanc&iacute;as retiradas o elementos de trabajo extraviados puestos bajo su responsabilidad y cuidado.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>D&Eacute;CIMA SEPTIMA. SEGURIDAD SOCIAL.</u> EL TRABAJADOR</strong> autoriza los descuentos de las sumas que legalmente corresponden como cotizaci&oacute;n para las entidades del Sistema General de Seguridad Social (Salud y Pensi&oacute;n).</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 1.</strong> En virtud de la ley en caso de incapacidad superior a tres (3) d&iacute;as, <strong>EL EMPLEADOR</strong>, solo podr&aacute; tener como v&aacute;lidas las incapacidades provenientes de la <strong>EPS</strong> o la <strong>ARL</strong> a la que se encuentre afiliado <strong>EL TRABAJADOR</strong>, en consecuencia, si el trabajador no se presenta a prestar sus servicios posteriormente a esos d&iacute;as y no acude a la <strong>EPS</strong> o a la <strong>ARL</strong> correspondiente por la correspondiente incapacidad, se entender&aacute; como ausencia no justificada y dar&aacute; lugar a la terminaci&oacute;n del contrato por justa causa por parte de EL EMPLEADOR.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 2.</strong> Hace parte de las obligaciones del <strong>TRABAJADOR</strong> remitir las incapacidades, y tratamientos m&eacute;dicos ordenados, f&iacute;sicamente en las instalaciones de la empresa, dentro de los <strong>tres (3) d&iacute;as h&aacute;biles siguientes</strong> de haber sido otorgadas y as&iacute; mismo, remitirlas por correo electr&oacute;nico <strong>simult&aacute;neamente</strong> a la direcci&oacute;n electr&oacute;nica: <u><span style="color:#0563C1;">recursoshumanos@minduval.com.co</span></u> o a los WhatsApp <u><span style="color:#0563C1;">3187305015 - 3167445178.</span></u></p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>D&Eacute;CIMA OCTAVA. CONFIDENCIALIDAD.</u></strong> De conformidad con el cargo que desarrollar&aacute; <strong>EL TRABAJADOR</strong>, es necesario que &eacute;ste maneje informaci&oacute;n confidencial y/o sujeta a propiedad industrial, por tal raz&oacute;n se hace necesario mantener la confidencialidad y responsabilidad en el manejo de los datos e informaci&oacute;n de la empresa, clientes, proveedores y colaboradores.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;">Por consiguiente, <strong>EL</strong> <strong>TRABAJADOR</strong> tiene prohibido durante la vigencia del presente contrato individual de trabajo y posterior a la finalizaci&oacute;n de este: divulgar, comunicar, copiar, distribuir, publicar, disponer, reproducir, explotar, proporcionar, facilitar o colocar a disposici&oacute;n de personas naturales o jur&iacute;dicas externas el uso de aplicaciones, c&oacute;digos, m&eacute;todos de negocio, patentes, pol&iacute;ticas comerciales, procesos, estrategias de mercadeo, documentaci&oacute;n y dem&aacute;s informaci&oacute;n que obtenga, considerando todo lo anterior como secreto empresarial.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;">Acept&aacute;ndose que, la transgresi&oacute;n de lo expuesto dar&aacute; lugar al <strong>EMPLEADOR</strong> para iniciar las acciones legales y judiciales pertinentes para obtener la correspondiente indemnizaci&oacute;n de perjuicios a que haya lugar.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>D&Eacute;CIMA NOVENA. PROPIEDAD INTELECTUAL.</u></strong> Las partes acuerdan que todas las invenciones, descubrimientos y trabajos originales concebidos o hechos por <strong>EL TRABAJADOR</strong> en vigencia del presente contrato pertenecer&aacute;n a <strong>EL EMPLEADOR,</strong> por el cual <strong>EL TRABAJADOR</strong> se obliga a informar a <strong>EL EMPLEADOR</strong> de forma inmediata sobre la existencia de dichas invenciones y/o trabajos originales. <strong>EL TRABAJADOR</strong> acceder&aacute; a facilitar el cumplimiento oportuno de las correspondientes formalidades y dar&aacute; su firma o extender&aacute; los poderes y documentos necesarios para transferir la propiedad intelectual a <strong>EL EMPLEADOR</strong> cuando as&iacute; se lo solicite. Teniendo en cuenta lo dispuesto en la normatividad vigente y lo estipulado anteriormente, las partes acuerdan que el salario devengado contiene la remuneraci&oacute;n por la transferencia de todo tipo de propiedad intelectual, raz&oacute;n por la cual no se causar&aacute; ninguna compensaci&oacute;n adicional.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>VIGESIMA. DERECHOS DE AUTOR.</u></strong> Los derechos patrimoniales de autor sobre las obras art&iacute;sticas, cient&iacute;ficas o literarias creadas por el <strong>TRABAJADOR</strong> en ejercicio de sus funciones o con ocasi&oacute;n ellas pertenecen al <strong>EMPLEADOR</strong>. Todo lo anterior sin perjuicio de los derechos morales del autor que pertenecer&aacute;n en cabeza del creador de la obra, de acuerdo con la Ley 23 de 1982 y la Decisi&oacute;n 351 de 1993 de la Comisi&oacute;n de la Comunidad Andina.&nbsp;</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:.8pt;margin-left:10.9pt;text-align:left;text-indent:0in; line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>VIGESIMA PRIMERA. BUENA FE.</u></strong> El presente contrato est&aacute; redactado de acuerdo con la Ley y la Jurisprudencia, en consonancia del C&oacute;digo Sustantivo del Trabajo, de modo que sea interpretado de buena fe y as&iacute; tambi&eacute;n las relaciones que de &eacute;l se derivan.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>VIG&Eacute;SIMA SEGUNDA. LEY DE ACOSO LABORAL.</u></strong> Adicionalmente a que el presente contrato se encuentra sujeto a las normas legales del trabajo, tambi&eacute;n lo est&aacute; respecto de las normas internacionales de trabajo como la expedidas por la Organizaci&oacute;n Internacional del Trabajo (OIT), de modo que <strong>EL EMPLEADOR Y EL TRABAJADOR</strong> declaran que conocen y aceptan los lineamientos planteados en la Ley 1010 del 23 de enero de 2006 denominada &ldquo;Ley de Acoso Laboral&rdquo; y por tanto ser&aacute; incorporado al presente las disposiciones de la misma.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>VIG&Eacute;SIMA TERCERA. PROHIBICI&Oacute;N DE CESI&Oacute;N.</u></strong> Ninguna de <strong>LAS PARTES</strong> podr&aacute; ceder total ni parcialmente la ejecuci&oacute;n del presente contrato (derechos u obligaciones), salvo autorizaci&oacute;n expresa de <strong>LA OTRA PARTE.</strong></p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>VIG&Eacute;SIMA CUARTA. INDIVISIBILIDAD.</u></strong> Si una o varias de las cl&aacute;usulas de este contrato se declaran invalidas o si la autoridad competente les otorga aplicaci&oacute;n o interpretaci&oacute;n diferente a la pretendida, seguir&aacute; vigente el resto del negocio jur&iacute;dico, a menos que la cl&aacute;usula o cl&aacute;usulas invalidadas lo hagan ineficaz, caso en el cual terminar&aacute; inmediatamente con la simple notificaci&oacute;n escrita que se env&iacute;e por alguna de las <strong>PARTES.</strong></p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>VIG&Eacute;SIMA QUINTA. ESTIPULACIONES ANTERIORES.</u></strong> Las <strong>PARTES</strong> manifiestan que no reconocer&aacute;n validez a estipulaciones verbales o escritas anteriormente relacionadas con los servicios objeto del presente contrato, el cual constituye un acuerdo completo y total acerca de su objeto y remplaza o deja sin efecto cualquier estipulaci&oacute;n, contrato de trabajo o de prestaci&oacute;n de servicios celebrado entre <strong>LAS PARTES</strong> con anterioridad.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>VIG&Eacute;SIMA SEXTA. MODIFICACIONES.</u></strong> Cualquier modificaci&oacute;n de las estipulaciones contenidas en el presente contrato deber&aacute; realizarse por medio de un otros&iacute;, el cual deber&aacute; constar por escrito, ser firmado por ambas <strong>PARTES</strong> y anexado a este documento.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>VIG&Eacute;SIMA SEPTIMA. T&Iacute;TULO DE LAS CL&Aacute;USULAS.</u></strong> Los t&iacute;tulos de las cl&aacute;usulas que aparecen en el presente documento se han propuesto con el prop&oacute;sito de facilitar su lectura, por lo tanto, no definen, ni limitan el contenido de estas. Para efectos de interpretaci&oacute;n de cada cl&aacute;usula deber&aacute; entenderse exclusivamente a su contenido y de ninguna manera al t&iacute;tulo.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;"><strong>&nbsp;</strong></p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong><u>VIG&Eacute;SIMA OCTAVA. AUTORIZACI&Oacute;N PARA EL TRATAMIENTO DE DATOS PERSONALES.</u> EL TRABAJADOR,</strong> quien es la persona natural respecto de la cual se obtengan datos personales, autoriza de manera previa, expresa, informada y expl&iacute;cita a <strong>EL EMPLEADOR</strong> para que la informaci&oacute;n que este almacene en sus bases de datos con ocasi&oacute;n del presente <strong>CONTRATO</strong> sea utilizada espec&iacute;ficamente para finalidades relacionadas con su ejecuci&oacute;n; de igual manera, autoriza al <strong>EMPLEADOR</strong> el tratamiento de los datos personales de sus hijos menores y/o mayores de edad, de su c&oacute;nyuge o compa&ntilde;ero(a) permanente, o de quienes se configuren en sus beneficiarios, que reposen en documentos, bases de datos o sistemas de informaci&oacute;n del <strong>EMPLEADOR</strong>, quedando facultado para utilizar, transferir, transmitir, almacenar, consultar, procesar y en general realizar tratamiento a dichos datos, con el fin de poder cumplir con las obligaciones propias de la relaci&oacute;n laboral.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:5.9pt;text-align:left;text-indent:0in;line-height: 107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:6.1pt;"><strong>Par&aacute;grafo 1.</strong> El uso de estos datos por parte del <strong>EMPLEADOR</strong> se har&aacute; con fines de contrataci&oacute;n, formaci&oacute;n, mediciones internas, entrega de elementos de trabajo, gesti&oacute;n y pago de n&oacute;mina, prestaciones sociales, vacaciones, sanciones, cesant&iacute;as, evaluaciones de desempe&ntilde;o, procedimientos administrativos, respuestas y procedimientos judiciales, tr&aacute;mites del Sistema</p>
</div>
<p><span style='font-size:11.0pt;line-height:103%;font-family:"Arial",sans-serif; color:black;'><br style="clear: both;page-break-before:always;"></span></p>
<div class="WordSection2">
    <p class="MsoNormal" style="margin-left:.5pt;">General de Seguridad y Salud en el Trabajo, actividades culturales y deportivas, recursos de investigaci&oacute;n, afiliaci&oacute;n al Sistema General de Seguridad Social Integral, procedimientos m&eacute;dicos ocupacionales, tr&aacute;mite de incapacidades, ejecuci&oacute;n de auditor&iacute;as y en general, para el tratamiento de todas las actividades derivadas de la relaci&oacute;n laboral.</p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;"><strong>Par&aacute;grafo 2.</strong> <strong>EL TRABAJADOR</strong> autoriza de manera voluntaria el tratamiento de sus datos personales sensibles, como la huella dactilar, la firma y, particularmente el tratamiento de su imagen en formato de fotograf&iacute;a y video, por lo que autoriza tambi&eacute;n el tratamiento del audio correspondiente al video, para las finalidades relativas a la seguridad de las personas y de los bienes al interior de las sedes u oficinas del <strong>EMPLEADOR</strong>, as&iacute; como para la ejecuci&oacute;n de todas las obligaciones y actividades que recaigan en &eacute;l.</p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;"><strong>Par&aacute;grafo 3.</strong> De conformidad con la Ley 1581 de 2012, la actualizaci&oacute;n, rectificaci&oacute;n, cancelaci&oacute;n y oposici&oacute;n sobre los datos personales podr&aacute; realizarse a trav&eacute;s del correo <u><span style="color:#0563C1;">auxiliar.contable@minduval.com.co</span></u>.</p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;"><strong>Par&aacute;grafo 4.</strong> <strong>EL TRABAJADOR</strong> autoriza al <strong>EMPLEADOR</strong> para que investigue en las bases de datos publicas informaci&oacute;n relacionada con antecedentes penales, disciplinarios, fiscales y los que se le asemeje, los cuales ser&aacute;n tratados de acuerdo a ley 1581 del 2012.&nbsp;</p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;"><strong>Par&aacute;grafo 5. EL TRABAJADOR</strong> se compromete a que al momento de realizar alg&uacute;n cambio a sus datos personales los notificar&aacute; al <strong>EMPLEADOR</strong>, as&iacute; como tambi&eacute;n se compromete a diligenciar los formularios de actualizaci&oacute;n de datos y antecedentes de forma peri&oacute;dica, cuando sea requerido por <strong>EL EMPLEADOR.</strong></p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;"><strong><u>VIG&Eacute;SIMA NOVENA. AUTORIZACI&Oacute;N DE VISITAS DOMICILIARIAS Y ESTUDIOS DE</u> <u>SEGURIDAD.</u></strong> <strong>EL TRABAJADOR</strong> autoriza al <strong>EMPLEADOR</strong> a evaluar si el cargo para el cual est&aacute; siendo contratado requiere realizar las visitas domiciliarias y/o estudios de seguridad, para que consecuentemente efect&uacute;e dichos procedimientos. En ese sentido, si el cargo para el que se contrata requiere visitas domiciliarias y los estudios de seguridad, estos ser&aacute;n efectuadas por parte del <strong>EMPLEADOR</strong>, con la frecuencia que &eacute;ste considere pertinente para ambos casos, debido a las certificaciones que &eacute;ste pueda requerir para el ejercicio de su objeto social. De otra parte, <strong>EL TRABAJADOR</strong> hace constar que ha sido informado sobre la plena facultad que le asiste de suministrar informaci&oacute;n de este tipo, as&iacute; como del mecanismo que tiene a disposici&oacute;n para solicitar la rectificaci&oacute;n de la informaci&oacute;n.</p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;"><strong><u>TRIGESIMA. LEGISLACI&Oacute;N APLICABLE.</u></strong> En lo no previsto en las cl&aacute;usulas estipuladas, el contrato se regir&aacute; por lo dispuesto en el C&oacute;digo Sustantivo de Trabajo y dem&aacute;s normas complementarias de la Rep&uacute;blica de Colombia.</p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;"><strong><u>TRIGESIMA PRIMERA. DOMICILIO CONTRACTUAL.</u></strong> Para todos los efectos legales se tendr&aacute; como domicilio contractual la ciudad de Cali.</p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;"><strong><u>TRIG&Eacute;SIMA SEGUNDA. NOTIFICACIONES.</u> LAS PARTES</strong> declaran como direcciones oficiales en donde pueden ser notificadas o recibir correspondencia, las indicadas a continuaci&oacute;n.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:59.15pt;margin-left:0in;text-align:left;text-indent:0in; line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" align="right" style="margin-top:0in;margin-right:41.2pt; margin-bottom:17.65pt;margin-left:.5pt;text-align:right;line-height:107%;"><span style="font-size:10.0pt;line-height:107%;">P&aacute;gina</span></p>
    <p class="MsoNormal" style="margin-top:0in;margin-right:0in;margin-bottom:1.3pt; margin-left:59.05pt;text-indent:-.25in;"><span style="line-height:103%;">&bull;<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span> <strong>EL EMPLEADOR: MINDUVAL - MONTAJES INDUSTRIALES Y NAVALES S.A.S.</strong> recibir&aacute; notificaciones en la siguiente direcci&oacute;n Carrera 6 A No. 31 &ndash; 53 en Cali o al correo electr&oacute;nico: <u><span style="color:#0563C1;">minduval@gmail.com</span></u>.</p>
    <p class="MsoNormal" align="left" style="margin-top:0in;margin-right:0in; margin-bottom:0in;margin-left:59.05pt;text-align:left;text-indent:0in; line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:59.05pt;text-indent:-.25in;"><span style="line-height:103%;">&bull;<span style='font:7.0pt "Times New Roman";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span> <strong>EL TRABAJADOR: <span style="background:yellow;">OLA OLA</span></strong> recibir&aacute; notificaciones en la siguiente direcci&oacute;n: <strong><span style="background:yellow;">CARRERA 1 ESTE CALLE 4</span></strong> <span style="background:yellow;"><strong>D SUR - 45</strong></span>, en <strong><span style="background:yellow;">CERRITO, VALLE</span></strong> o al correo electr&oacute;nico: <strong><u><span style="color:#4472C4;background:yellow;">PACHODCC@HOTMAIL.COM</span></u></strong> y al celular: <strong><span style="background:yellow;">3210000000</span></strong></p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;"><strong>Par&aacute;grafo 1.</strong> Cualquier notificaci&oacute;n hacia el <strong>TRABAJADOR</strong> se entender&aacute; recibida y perfeccionada si se dirige a las mencionadas direcciones, por lo tanto, cualquier cambio de &eacute;stas deber&aacute; previamente comunicarse al <strong>EMPLEADOR</strong>, a efectos de generar la actualizaci&oacute;n de datos de notificaci&oacute;n.</p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;">Le&iacute;do el presente instrumento <strong>LAS PARTES</strong> lo aceptan y suscriben como aparecen en se&ntilde;al de conformidad, en dos originales, en la ciudad de Cali, a los <span style="background:yellow;">seis (06)</span> d&iacute;a del mes de <span style="background:yellow;">noviembre</span> del a&ntilde;o dos mil <span style="background:yellow;">veinticuatro (2024),</span> entendi&eacute;ndose perfeccionado con la firma del mismo.</p>
    <p class="MsoNormal" align="left" style="margin:0in;text-align:left;text-indent: 0in;line-height:107%;">&nbsp;</p>
    <p class="MsoNormal" style="margin-left:.5pt;">Lo suscriben,</p>


    <div class="section">
        <table style="width: 100%; margin-top: 80px;border:none">
            <tr>
                <!-- Firma Representante Legal -->
                <td style="width: 50%; text-align: center; vertical-align: bottom; padding-bottom: 30px; border:none">
                    <hr style="width: 80%; border: 1px solid black;">
                    <p style="margin:0px">Representante Legal</p>
                </td>

                <!-- Firma Trabajador -->
                <td style="width: 50%; text-align: center; vertical-align: bottom; padding-bottom: 6px;border:none; margin-top:20%">
                    <hr style="width: 80%; border: 1px solid black;">
                    <p style="margin:0px">{{ $datos['NOMBRES'] }}  {{ $datos['APELLIDOS'] }}</p>
                    <p style="margin:0px">C.C. {{ $datos['CEDULA'] ?? 'N/A' }}</p>
                </td>
            </tr>
        </table>
    </div>

     <!-- Pie de página con numeración -->
    <div class="footer">
        gerencia@minduval.com.co - <span>Página <span class="pagenum"></span> de <span class="total-pages"></span></span>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var totalPages = document.querySelectorAll('.pagenum').length;
            document.querySelectorAll('.total-pages').forEach(el => el.innerText = totalPages);
        });
    </script>
</body>
</html>
