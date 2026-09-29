<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Exámenes - Laboratorio Clínico UEB</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1cm 1.2cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
            font-size: 7.5px;
            line-height: 1.15;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-title-center {
            text-align: center;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .header-title-right {
            text-align: right;
            font-size: 10px;
            font-weight: 800;
            color: #0c3866;
            line-height: 1.25;
            letter-spacing: 0.3px;
        }
        .section-header {
            font-size: 9.5px;
            font-weight: bold;
            color: #0c3866;
            border-bottom: 1.5px solid #0c3866;
            padding-bottom: 2px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .patient-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8px;
        }
        .patient-table td {
            padding: 2px 4px;
            vertical-align: middle;
        }
        .patient-data-line {
            border-bottom: 1px dotted #64748b;
            font-weight: bold;
            color: #0f172a;
            padding: 0 4px;
        }
        .columns-table {
            width: 100%;
            border-collapse: collapse;
        }
        .column-td {
            width: 33.33%;
            vertical-align: top;
            padding: 0 4px;
        }
        .category-box {
            margin-bottom: 8px;
        }
        .category-header {
            background-color: #0c3866;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            padding: 2.5px 4px;
            margin-bottom: 2px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            border-radius: 1px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            line-height: 1.15;
        }
        .items-table td {
            padding: 0.8px 1px;
            vertical-align: middle;
        }
        .check-box-checked {
            width: 26px;
            white-space: nowrap;
            font-weight: bold;
            color: #0c3866;
            font-size: 7.5px;
            text-align: left;
            padding-right: 2px;
        }
        .check-box-unchecked {
            width: 26px;
            white-space: nowrap;
            color: #94a3b8;
            font-size: 7.5px;
            text-align: left;
            padding-right: 2px;
        }
        .item-text-checked {
            font-weight: bold;
            color: #0f172a;
        }
        .item-text-unchecked {
            color: #475569;
        }
        .otros-title {
            color: #0c3866;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #0c3866;
            padding-bottom: 2px;
            margin-top: 10px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .otros-line {
            border-bottom: 1px dotted #64748b;
            min-height: 14px;
            margin-bottom: 5px;
            padding: 1px 4px;
            font-size: 7px;
            color: #0f172a;
            font-weight: bold;
        }
        .observaciones-box {
            margin-top: 10px;
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 4px;
            font-size: 7.5px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        .signature-box {
            text-align: center;
            width: 240px;
            margin: 0 auto;
            border-top: 1px solid #334155;
            padding-top: 4px;
            font-size: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO OFICIAL UEB -->
    <table class="header-table">
        <tr>
            <td style="width: 25%;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="max-height: 44px; width: auto;" alt="Logo UEB">
                @else
                    <strong style="font-size: 13px; color: #0c3866;">UNIVERSIDAD ESTATAL DE BOLÍVAR</strong>
                @endif
            </td>
            <td style="width: 45%;" class="header-title-center">
                SALUD OCUPACIONAL
            </td>
            <td style="width: 30%;" class="header-title-right">
                ORDEN DE EXÁMENES<br>
                <span style="font-size: 9px; font-weight: normal; color: #334155;">LABORATORIO CLÍNICO</span>
            </td>
        </tr>
    </table>

    <!-- DATOS DEL PACIENTE -->
    <div class="section-header">DATOS DEL PACIENTE</div>
    <table class="patient-table">
        <tr>
            <td style="width: 70%;">
                <span style="color: #64748b; font-size: 7.5px;">Nombres - Apellidos:</span>
                <span class="patient-data-line">{{ $pacienteNombre ?? '—' }}</span>
            </td>
            <td style="width: 30%;">
                <span style="color: #64748b; font-size: 7.5px;">Fecha:</span>
                <span class="patient-data-line">{{ $fecha ?? date('Y-m-d') }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 70%;">
                <span style="color: #64748b; font-size: 7.5px;">Cédula:</span>
                <span class="patient-data-line" style="margin-right: 15px;">{{ $pacienteCedula ?? '—' }}</span>

                <span style="color: #64748b; font-size: 7.5px;">Edad:</span>
                <span class="patient-data-line">{{ $pacienteEdad ? $pacienteEdad . ' años' : '—' }}</span>
            </td>
            <td style="width: 30%;">
                <span style="color: #64748b; font-size: 7.5px;">Médico Solicitante:</span>
                <span class="patient-data-line">{{ $doctorNombre ?? '—' }}</span>
            </td>
        </tr>
    </table>

    <!-- MATRIZ DE EXÁMENES EN 3 COLUMNAS -->
    <table class="columns-table">
        <tr>
            <!-- COLUMNA 1 -->
            <td class="column-td">
                @foreach($columna1 as $grupo)
                    <div class="category-box">
                        <div class="category-header">{{ $grupo['nombre'] }}</div>
                        <table class="items-table">
                            @foreach($grupo['items'] as $item)
                                @php $isChecked = in_array($item['id'], $selectedIds); @endphp
                                <tr>
                                    <td class="{{ $isChecked ? 'check-box-checked' : 'check-box-unchecked' }}">
                                        {{ $isChecked ? '[X]' : '( )' }}
                                    </td>
                                    <td class="{{ $isChecked ? 'item-text-checked' : 'item-text-unchecked' }}">
                                        {{ $item['nombre'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endforeach
            </td>

            <!-- COLUMNA 2 -->
            <td class="column-td">
                @foreach($columna2 as $grupo)
                    <div class="category-box">
                        <div class="category-header">{{ $grupo['nombre'] }}</div>
                        <table class="items-table">
                            @foreach($grupo['items'] as $item)
                                @php $isChecked = in_array($item['id'], $selectedIds); @endphp
                                <tr>
                                    <td class="{{ $isChecked ? 'check-box-checked' : 'check-box-unchecked' }}">
                                        {{ $isChecked ? '[X]' : '( )' }}
                                    </td>
                                    <td class="{{ $isChecked ? 'item-text-checked' : 'item-text-unchecked' }}">
                                        {{ $item['nombre'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endforeach
            </td>

            <!-- COLUMNA 3 -->
            <td class="column-td">
                @foreach($columna3 as $grupo)
                    <div class="category-box">
                        <div class="category-header">{{ $grupo['nombre'] }}</div>
                        <table class="items-table">
                            @foreach($grupo['items'] as $item)
                                @php $isChecked = in_array($item['id'], $selectedIds); @endphp
                                <tr>
                                    <td class="{{ $isChecked ? 'check-box-checked' : 'check-box-unchecked' }}">
                                        {{ $isChecked ? '[X]' : '( )' }}
                                    </td>
                                    <td class="{{ $isChecked ? 'item-text-checked' : 'item-text-unchecked' }}">
                                        {{ $item['nombre'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endforeach

                <!-- OTROS EXÁMENES -->
                <div class="category-box">
                    <div class="otros-title">OTROS EXÁMENES</div>
                    @if(!empty($otrosExamenes) && count($otrosExamenes) > 0)
                        @foreach($otrosExamenes as $otro)
                            <div class="otros-line">[ X ] {{ $otro }}</div>
                        @endforeach
                    @else
                        <div class="otros-line" style="color: #94a3b8; font-weight: normal;">( &nbsp; ) Ninguno especificado</div>
                    @endif
                    <div class="otros-line">&nbsp;</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- OBSERVACIONES E INDICACIONES -->
    @if(!empty($observaciones))
        <div class="observaciones-box">
            <strong>Indicaciones / Observaciones Clínicas:</strong> {{ $observaciones }}
        </div>
    @endif

    <!-- FIRMA Y SELLO -->
    <table class="signature-table">
        <tr>
            <td style="text-align: center;">
                <div class="signature-box">
                    <span>{{ $doctorNombre ?? 'Médico Ocupacional' }}</span><br>
                    <span style="font-size: 7.5px; font-weight: normal; color: #475569;">Firma y Sello del Médico Solicitante</span>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
