<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Bienestar Universitario</title>
    <style>
        body {
            font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f7fa;
            color: #002040;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 32, 64, 0.05);
            border: 1px solid rgba(0, 32, 64, 0.05);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-text {
            font-size: 24px;
            font-weight: 800;
            color: #b71a34;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .subtitle {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #475467;
            margin-top: 5px;
            font-weight: 600;
        }
        h2 {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
            color: #002040;
            letter-spacing: -0.5px;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            color: #475467;
            margin-top: 0;
            margin-bottom: 24px;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            background-color: #b71a34;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 30px;
            font-size: 15px;
            font-weight: 700;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(183, 26, 52, 0.2);
            transition: all 0.2s ease;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #98a2b3;
            line-height: 1.5;
        }
        .footer a {
            color: #b71a34;
            text-decoration: none;
            font-weight: 600;
        }
        .divider {
            border: 0;
            border-top: 1px solid #e7ebf0;
            margin: 30px 0;
        }
        .expiration-note {
            font-size: 13px;
            color: #667085;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(file_exists(public_path('images/ueb.png')))
                <img src="{{ $message->embed(public_path('images/ueb.png')) }}" alt="Logo UEB" style="height: 70px; width: auto; margin-bottom: 12px;">
            @endif
            <div class="logo-text">Bienestar Universitario</div>
            <div class="subtitle">Universidad Estatal de Bolívar</div>
        </div>
        <div class="card">
            <h2>Hola, {{ $name }}!</h2>
            <p>Recibiste este correo electrónico porque solicitaste un restablecimiento de contraseña para acceder a tu cuenta institucional en el Sistema de Bienestar Universitario.</p>
            
            <div class="btn-container">
                <a href="{{ $resetUrl }}" class="btn">Restablecer contraseña</a>
            </div>

            <div class="expiration-note">
                <strong>Importante:</strong> Este enlace de recuperación expirará en 60 minutos por razones de seguridad.
            </div>

            <hr class="divider">

            <p style="font-size: 13px; margin-bottom: 0;">Si no solicitaste este cambio, puedes ignorar este correo de forma segura. Tu contraseña actual no se modificará.</p>
        </div>
        <div class="footer">
            <p style="margin-bottom: 4px;">© 2026 Bienestar Universitario · UEB</p>
            <p style="margin: 0;">Si tienes dudas o inconvenientes, contáctanos a <a href="mailto:soporte@ueb.edu.ec">soporte@ueb.edu.ec</a></p>
        </div>
    </div>
</body>
</html>
