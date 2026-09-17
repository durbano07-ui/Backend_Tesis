<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Bienvenido a Bienestar Universitario!</title>
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
            font-size: 22px;
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
            margin-bottom: 20px;
        }
        .highlight-box {
            background-color: #f8fafc;
            border-left: 4px solid #b71a34;
            padding: 20px;
            border-radius: 12px;
            margin: 24px 0;
        }
        .highlight-box strong {
            display: block;
            margin-bottom: 8px;
            color: #002040;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .highlight-box p {
            margin: 0;
            font-size: 14px;
            color: #475467;
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
            <h2>¡Hola, {{ $name }}!</h2>
            <p>Queremos darte la más cálida bienvenida al Sistema de Bienestar Universitario de la Universidad Estatal de Bolívar. Tu cuenta institucional ha sido registrada con éxito.</p>
            
            <p>A través de esta plataforma podrás gestionar tus citas médicas, registrar tus antecedentes de salud y acceder a los servicios de medicina general, enfermería, psicología y odontología que ofrece la institución.</p>

            <div class="highlight-box">
                <strong>¿Qué debes hacer ahora?</strong>
                <p>Para poder brindarte una atención personalizada, el primer paso es ingresar a tu cuenta y completar tu perfil con tus datos personales y tu ficha de antecedentes médicos.</p>
            </div>

            <div class="btn-container">
                <a href="{{ url('/') }}" class="btn">Ingresar al Sistema</a>
            </div>

            <hr class="divider">

            <p style="font-size: 13px; margin-bottom: 0;">Este es un correo automático de bienvenida. Si consideras que se trata de un error o no has solicitado este registro, por favor ponte en contacto con nuestro equipo de soporte.</p>
        </div>
        <div class="footer">
            <p style="margin-bottom: 4px;">© 2026 Bienestar Universitario · UEB</p>
            <p style="margin: 0;">¿Necesitas ayuda? Escríbenos a <a href="mailto:soporte@ueb.edu.ec">soporte@ueb.edu.ec</a></p>
        </div>
    </div>
</body>
</html>
