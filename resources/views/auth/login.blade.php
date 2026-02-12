@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="login-container">
        <div class="container login">
            <div class="form-section">
                <div class="row">
                    <div class="col-md-12 form-0">
                        <p class="company-logo">
                            <img src="{{ asset('images/SPLOGO.png')}}" class="img-fluid" alt="">
                        </p>
                        <p class="section-title">
                            <span>Welcome Back!</span>
                        </p>
                        <div class="login-form">
                            <div class="form-1">
                                <p class="form-label">Email Address</p>
                                <div>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                </div>
                            </div>
                            <div class="form-1">
                                <p class="form-label">Password</p>
                                <div>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                </div>
                            </div>
                            <div class="form-1 submit-btn">
                                <p class="form-btn">
                                    <button type="submit" class="btn btn-primary">LOGIN</button>
                                </p>
                            </div>
                            <div class="form-1">
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}">
                                    <p class="form-label forgot-password">Forgot password?</p>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</form>
@section('styles')
<style>
html, body {
    height: 100%;
    margin: 0;
}
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}
div#app {
    height: 100% !important;
}
main.py-4 {
    height: 100%;
}
form {
    height: 100%;
}
span.invalid-feedback {
    text-align: center;
    color: #ffb100;
}
.form-control.is-invalid {
    border-color: #ffb100;
}
</style>
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script>
    </script>
@endsection
@stop

