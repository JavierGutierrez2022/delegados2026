<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Control Delegados | Login</title>

  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}">

  <style>
    html, body { height: 100%; margin: 0; }
    body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
    .input-group-text {
      background: #f4f7fb;
      border: 1px solid #dbe4f0;
      color: #5d7088;
      border-radius: 12px 0 0 12px;
    }
    .form-control {
      border-color: #dbe4f0;
      border-radius: 0 12px 12px 0;
      height: 46px;
      font-size: 18px;
    }
    .form-control:focus { border-color: #5ea4ff; box-shadow: 0 0 0 .2rem rgba(22,119,214,.15); }
    .form-group > label {
      display: inline-block;
      margin-bottom: 10px;
      padding: 6px 12px;
      border-radius: 999px;
      background: #eaf3ff;
      color: #1c4d80;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: .2px;
    }
    .login-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; }
    .login-actions label { margin: 0; font-weight: 600; color: #24496f; }
    .btn-login { border: none; font-weight: 700; letter-spacing: .6px; padding: .8rem 1rem; }
    .mini-foot { font-size: 12px; color: #6e8198; margin-top: 18px; text-align: center; }

    .theme-1 {
      min-height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      background:
        linear-gradient(120deg, rgba(5, 157, 86, 0.92), rgba(16, 108, 13, 0.92)),
        url("{{ asset('imagenes/fondo.jpg') }}") center/cover no-repeat fixed;
    }
    .theme-1 .login-card {
      width: 100%;
      max-width: 1080px;
      border-radius: 22px;
      overflow: hidden;
      box-shadow: 0 30px 70px rgba(0,0,0,.35);
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.16);
      backdrop-filter: blur(4px);
    }
    .theme-1 .grid {
      display: grid;
      grid-template-columns: 1.25fr 1fr;
    }
    .theme-1 .brand {
      color: #fff;
      padding: 56px 48px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background:
        radial-gradient(900px 500px at -10% -20%, rgba(255,255,255,.16), transparent 55%),
        radial-gradient(700px 400px at 120% 120%, rgba(255,255,255,.12), transparent 60%);
    }
    .theme-1 .brand h1 { margin: 0 0 10px; font-size: 42px; font-weight: 800; line-height: 1.05; }
    .theme-1 .brand p { margin: 0; opacity: .95; font-size: 20px; }
    .theme-1 .brand .badge-info {
      display: inline-block;
      margin-top: 22px;
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(255,255,255,.18);
      font-size: 14px;
      font-weight: 700;
    }
    .theme-1 .form {
      background: rgba(248,251,255,.97);
      color: #12345a;
      padding: 44px 42px;
    }
    .theme-1 .form h2 { margin: 0; font-size: 34px; font-weight: 800; }
    .theme-1 .form .sub { margin: 6px 0 26px; color: #557295; font-weight: 600; }
    .theme-1 .btn-login {
      margin-top: 16px;
      background: linear-gradient(90deg, #1de0bf, #105fb3);
      box-shadow: 0 10px 22px rgba(16,95,179,.35);
    }

    @media (max-width: 992px) {
      .theme-1 .grid { grid-template-columns: 1fr; }
      .theme-1 .brand { padding: 34px 26px 24px; }
      .theme-1 .brand h1 { font-size: 30px; }
      .theme-1 .brand p { font-size: 16px; }
      .theme-1 .form { padding: 30px 24px; }
    }
  </style>
</head>
<body>
  <div class="theme-1">
    <div class="login-card">
      <div class="grid">
        <section class="brand">
          <h1>Plataforma<br>Control Delegados</h1>
          <p>Gestion operativa y seguimiento en tiempo real.</p>
          <span class="badge-info">Acceso seguro del sistema</span>
        </section>
        <section class="form">
          <h2>Login</h2>
          <div class="sub">Ingrese sus credenciales para continuar.</div>

          <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group mb-3">
              <label for="email">Correo</label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                  name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="usuario@dominio.com">
                @error('email')
                  <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
              </div>
            </div>

            <div class="form-group mb-2">
              <label for="password">Contrasena</label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                  name="password" required autocomplete="current-password" placeholder="********">
                @error('password')
                  <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
              </div>
            </div>

            <div class="login-actions">
              <div class="icheck-primary">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Recordarme</label>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-login">LOGIN</button>
            <div class="mini-foot">© {{ date('Y') }} DJM V2.0 - Todos los derechos reservados</div>
          </form>
        </section>
      </div>
    </div>
  </div>

  <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
