<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AdminLTE 3 | Log in</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <!-- Animate.css -->
  <link rel="stylesheet" href="{{asset('css/animate.css')}}">

</head>
<style>
  @font-face {
    font-family: 'TT Octosquares Trial Compressed Bold';
    src:url('{{asset('font/TT Octosquares Trial Compressed Bold.ttf')}}');

  }
  .titulo1{
    font-family: 'TT Octosquares Trial Compressed Bold';
    font-size: 32pt;
    color: rgb(255, 255, 255);
    text-shadow: 0.1em 0.1em 0.3em black;
    }
</style>
<body class="hold-transition login-page" 
            style="background: url('{{asset('imagenes/fondo.jpg')}}'); 
            background-repeat: no-repeat;
            background-size: 100vw 100vh;
            z-inde: -3x;
            background-attachment: fixed">
<div class="login-box">
  <div class="login-logo">
    <a href="{{url('/')}}"><b class="titulo1 animated zoomInLeft delay-2s ">
      Plataforma Listas Control Electoral "Alianza Unidad"</b></a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Ingrese sus credenciales</p>

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="row mb-3">
            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Correo') }}</label>

            <div class="col-md-6">
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Contraseña') }}</label>

            <div class="col-md-6">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

     
        <div class="row mb-0">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-block">
                    {{ __('Ingresar') }}
                </button>               
            </div>            
        </div>

        <div class="row mb-0">
          <div class="col-md-12">
            <br>
              <a href="{{ url('/registro') }}">¿No tienes una cuenta? Regístrate</a>
          </div>
      </div>

    </form>

      
      <!-- /.social-auth-links -->

      
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
</body>
</html>



