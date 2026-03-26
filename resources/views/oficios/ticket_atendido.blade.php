<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Oficio de Atención</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 40px;
            line-height: 1.4;
            position: relative;
            min-height: 100vh;
        }

        .bottom-left-date {
            position: absolute;
            bottom: -40px;
            /* para salir del margen del body */
            left: -40px;
            font-weight: normal;
            font-size: 12px;
            white-space: nowrap;
        }

        .bottom-right-logo {
            position: absolute;
            bottom: -40px;
            right: -40px;
            max-height: 90px;
            max-width: 180px;
            object-fit: contain;
        }

        /* Otros estilos que ya tenías */
        .bold {
            font-weight: bold;
        }

        .nombre-firma {
            max-width: 100%;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .firma {
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            word-break: normal;
            white-space: normal;
            vertical-align: top;
        }
    </style>
</head>

<body>

    <!-- Logos en esquinas superiores -->
    <img src="{{ public_path('assets/images/escudo.png') }}" alt="Escudo" style="position:absolute; top: -50px; left: -40px; max-height: 120px; max-width: 150px; object-fit: contain;">
    <img src="{{ public_path('assets/images/logo1.png') }}" alt="Logo" style="position:absolute; top: -40px; right: -40px; max-height: 120px; max-width: 150px; object-fit: contain;">

    <!-- Texto centrado -->
    <div style="max-width: 600px; margin: 0 auto; text-align: center; padding-top: 10px; font-weight: bold;">
        Gobierno del Estado de Quintana Roo<br />
        SECRETARÍA DE GOBIERNO<br />
        DIRECCIÓN DE ARCHIVO GENERAL DEL ESTADO<br />
        DEPARTAMENTO DE TECNOLOGÍAS DE LA INFORMACIÓN Y COMUNICACIONES<br />
        Cédula de registro de servicios y soporte
    </div>


    <!-- Datos del solicitante -->
    <hr style="margin-top: 40px; border: 1px solid #000;">
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="font-weight: bold; font-size: 14px;">Datos Generales del Solicitante:</td>
        </tr>
    </table>
    <table style="width: 100%;">
        <tr>
            <td>
                <span style="font-weight: bold; font-size: 12px;">Unidad Administrativa:</span>
                <span style="font-size: 12px;">{{ $ticket->unidad_administrativa ?? 'N/A' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span style="font-weight: bold; font-size: 12px;">Solicitante:</span>
                <span style="font-size: 12px;">{{ $ticket->capturador ?? 'N/A' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span style="font-weight: bold; font-size: 12px;">Cargo:</span>
                <span style="font-size: 12px;">{{ $ticket->cargo ?? 'N/A' }}</span>
            </td>
        </tr>
    </table>

    <!-- Datos del servicio -->
    <hr style="margin-top: 20px; border: 1px solid #000;">
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="font-weight: bold; font-size: 14px;">Datos Generales del Servicio o Soporte:</td>
        </tr>
    </table>
    <table style="width: 100%;">
        <tr>
            <td>
                <span style="font-weight: bold; font-size: 12px;">Denominación del Servicio o Soporte:</span>
                <span style="font-size: 12px;">{{ $ticket->asunto }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span style="font-weight: bold; font-size: 12px;">Fecha de la solicitud:</span>
                <span style="font-size: 12px;">{{ $ticket->created_at->format('d/m/Y') }}</span> &nbsp;
                <span style="font-weight: bold; font-size: 12px;">Fecha de Finalización:</span>
                <span style="font-size: 12px;">{{ $ticket->fecha_cierre ? $ticket->fecha_cierre->format('d/m/Y') : 'Sin cerrar' }}</span>
            </td>
        </tr>
    </table>

    <!-- Problemática -->
    <hr style="margin-top: 20px; border: 1px solid #000;">
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="font-weight: bold; font-size: 12px;">Problemática:</td>
        </tr>
        <tr>
            <td style="font-size: 12px;">{!! $ticket->contenido !!}</td>
        </tr>
    </table>

    <!-- Solución -->
    <hr style="margin-top: 20px; border: 1px solid #000;">
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="font-weight: bold; font-size: 12px;">Solución y Observaciones:</td>
        </tr>
        <tr>
            <td style="font-size: 12px;">{{ $ticket->solucion }}</td>
        </tr>
    </table>



    <!-- Firmas -->
    <table style="margin-top: 150px; text-align: center; table-layout: fixed; width: 100%; border-collapse: collapse;">
        <colgroup>
            <col style="width: 33.33%;" />
            <col style="width: 33.33%;" />
            <col style="width: 33.33%;" />
        </colgroup>

        <!-- Títulos -->
        <tr>
            <td class="bold">Atendió</td>
            <td class="bold">Vo. Bo.</td>
            <td class="bold">Solicitante</td>
        </tr>

        <!-- Nombres -->
        <tr>
            <td class="nombre-firma">{{ $ticket->quien_atendio }}</td>
            <td class="nombre-firma">Mtro. José Alberto Díaz López</td>
            <td class="nombre-firma">
                {{ $ticket->user ? $ticket->user->nombre_con_prefijo : $ticket->capturador }}
            </td>
        </tr>

        <!-- Espacio para firma -->
        <tr>
            <td style="height: 40px;"></td>
            <td style="height: 40px;"></td>
            <td style="height: 40px;"></td>
        </tr>

        <!-- Línea de nombre y firma -->
        <tr>
            <td class="firma">Nombre y Firma</td>
            <td class="firma">Nombre y Firma</td>
            <td class="firma">Nombre y Firma</td>
        </tr>
    </table>

    <!-- Fecha abajo izquierda -->
    <div class="bottom-left-date">
        Chetumal, Quintana Roo<br />
        {{ $ticket->fecha_cierre ? $ticket->fecha_cierre->format('d/m/Y') : date('d/m/Y') }}
    </div>

    <!-- Logo abajo derecha -->
    <img src="{{ public_path('assets/images/agqroo.png') }}" alt="AGQROO Logo" class="bottom-right-logo" />

</body>

</html>