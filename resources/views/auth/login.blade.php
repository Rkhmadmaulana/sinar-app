<!DOCTYPE html>
<!-- Coding By CodingNepal - www.codingnepalweb.com -->
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SINAR - APP</title>
    <link rel="stylesheet" href="{{asset('css/style-login.css')}}" />

    <style>
      body {
      background-image: url('{{asset('public/img/background.jpg')}}'); /* Ganti dengan path gambar Anda */
      background-size: cover; 
      background-position: center center; 
      background-attachment: fixed; 
    }
    </style>
  </head>
  <body>
    <div class="wrapper">
      <form action="{{ route('login-proses') }}" method="POST">
        @csrf
        <div class="logo-container">
          <img src="{{asset('public/img/logo-rs.png')}}" alt="Logo" class="logo" />
        </div>
        <h2>Login</h2>
        <div class="input-field">
          @error('username')
          <small style="color: red">{{ $message }}</small><br />
          @enderror
          <input type="text" id="email" name="username" required />
          <label>Enter Your Username</label>
        </div>
        <div class="input-field">
          @error('password')
          <small style="color: red">{{ $message }}</small><br />
          @enderror
          <input type="password" name="password" required />
          <label>Enter Your password</label>
        </div>
        <div class="forget">
          <label for="remember">
            <input type="checkbox" id="remember" />
            <p>Remember me</p>
          </label>
        </div>
        <button type="submit">Log In</button>
        <div class="register"></div>
      </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if($message = Session::get('failed'))
    <script>
      Swal.fire({
        icon: "error",
        title: "{!! addslashes($message) !!}",
        showConfirmButton: false,
        timer: 1500,
      });
    </script>
    @endif @if($message = Session::get('success'))
    <script>
      Swal.fire({
        icon: "success",
        title: "{!! addslashes($message) !!}",
        showConfirmButton: false,
        timer: 1500,
      });
    </script>
    @endif
  </body>
</html>
