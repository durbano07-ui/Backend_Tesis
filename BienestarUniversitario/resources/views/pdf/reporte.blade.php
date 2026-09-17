<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Estadístico</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2c5282;
        }

        .header h1 {
            font-size: 16px;
            color: #2c5282;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14px;
            color: #4a5568;
            font-weight: normal;
        }

        .info-section {
            background-color: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            color: #4a5568;
            width: 40%;
            padding: 3px 0;
        }

        .info-value {
            display: table-cell;
            color: #2d3748;
            width: 60%;
            padding: 3px 0;
        }

        .summary-box {
            background-color: #2c5282;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .summary-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: bold;
        }

        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        thead {
            background-color: #edf2f7;
        }

        th {
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            color: #4a5568;
            border-bottom: 2px solid #cbd5e0;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        tr:nth-child(even) {
            background-color: #f7fafc;
        }

        tr:hover {
            background-color: #edf2f7;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .cantidad {
            font-weight: bold;
            color: #2c5282;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #718096;
            padding: 10px;
            border-top: 1px solid #e2e8f0;
        }

        .page-number {
            text-align: center;
        }

        .no-data {
            text-align: center;
            color: #718096;
            padding: 20px;
            font-style: italic;
        }

        @page {
            margin: 20mm 15mm 25mm 15mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Universidad Nacional de Educación (UNAE)</h1>
        <h2>Centro de Bienestar Estudiantil</h2>
        <h1 style="margin-top: 10px;">Reporte Estadístico - {{ $tipoLabel }}</h1>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Doctor:</span>
                <span class="info-value">{{ $doctorName }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Desde:</span>
                <span class="info-value">{{ $fechaDesde }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Hasta:</span>
                <span class="info-value">{{ $fechaHasta }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Generado:</span>
                <span class="info-value">{{ $generatedAt }}</span>
            </div>
        </div>
    </div>

    <div class="summary-box">
        <div class="summary-title">Resumen de Atenciones</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $reporte['totales']['total_atenciones'] }}</div>
                <div class="summary-label">Total Atenciones</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $reporte['totales']['pacientes_unicos'] }}</div>
                <div class="summary-label">Pacientes Únicos</div>
            </div>
        </div>
    </div>

    @if(count($reporte['por_facultad']) > 0)
    <div class="section">
        <div class="section-title">Atenciones por Facultad</div>
        <table>
            <thead>
                <tr>
                    <th>Facultad</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reporte['por_facultad'] as $item)
                <tr>
                    <td>{{ $item['facultad'] }}</td>
                    <td class="text-right cantidad">{{ $item['cantidad'] }}</td>
                    <td class="text-right">{{ $reporte['totales']['total_atenciones'] > 0 ? round(($item['cantidad'] / $reporte['totales']['total_atenciones']) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(count($reporte['por_carrera']) > 0)
    <div class="section">
        <div class="section-title">Atenciones por Carrera</div>
        <table>
            <thead>
                <tr>
                    <th>Carrera</th>
                    <th>Facultad</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reporte['por_carrera'] as $item)
                <tr>
                    <td>{{ $item['carrera'] }}</td>
                    <td>{{ $item['facultad'] }}</td>
                    <td class="text-right cantidad">{{ $item['cantidad'] }}</td>
                    <td class="text-right">{{ $reporte['totales']['total_atenciones'] > 0 ? round(($item['cantidad'] / $reporte['totales']['total_atenciones']) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(count($reporte['por_genero']) > 0)
    <div class="section">
        <div class="section-title">Atenciones por Género</div>
        <table>
            <thead>
                <tr>
                    <th>Género</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reporte['por_genero'] as $item)
                <tr>
                    <td>{{ $item['genero'] }}</td>
                    <td class="text-right cantidad">{{ $item['cantidad'] }}</td>
                    <td class="text-right">{{ $reporte['totales']['total_atenciones'] > 0 ? round(($item['cantidad'] / $reporte['totales']['total_atenciones']) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(count($reporte['por_tipo_usuario']) > 0)
    <div class="section">
        <div class="section-title">Atenciones por Tipo de Usuario</div>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Usuario</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reporte['por_tipo_usuario'] as $item)
                <tr>
                    <td>{{ $item['tipo_usuario'] }}</td>
                    <td class="text-right cantidad">{{ $item['cantidad'] }}</td>
                    <td class="text-right">{{ $reporte['totales']['total_atenciones'] > 0 ? round(($item['cantidad'] / $reporte['totales']['total_atenciones']) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(count($reporte['por_mes']) > 0)
    <div class="section">
        <div class="section-title">Atenciones por Mes</div>
        <table>
            <thead>
                <tr>
                    <th>Mes</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reporte['por_mes'] as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $item['mes'])->format('F Y') }}</td>
                    <td class="text-right cantidad">{{ $item['cantidad'] }}</td>
                    <td class="text-right">{{ $reporte['totales']['total_atenciones'] > 0 ? round(($item['cantidad'] / $reporte['totales']['total_atenciones']) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <div class="page-number">Página 1 de {total_pages}</div>
        <div>Generado el {{ $generatedAt }} - Centro de Bienestar Estudiantil</div>
    </div>
</body>
</html>
