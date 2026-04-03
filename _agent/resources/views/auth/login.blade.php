<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f5f5f7; display: flex; align-items: center; justify-content: center;
            min-height: 100vh; -webkit-font-smoothing: antialiased;
        }
        .login-container { width: 100%; max-width: 380px; padding: 20px; }
        .login-header { text-align: center; margin-bottom: 32px; }
        .login-header h1 { font-size: 28px; font-weight: 600; letter-spacing: -0.5px; color: #1d1d1f; margin-bottom: 6px; }
        .login-header p { font-size: 14px; color: #86868b; }
        .login-card { background: #fff; border-radius: 16px; padding: 32px; border: 1px solid #e5e5e7; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 12px; font-weight: 500; color: #1d1d1f; margin-bottom: 6px; }
        .form-input {
            width: 100%; padding: 11px 14px; border: 1px solid #e5e5e7; border-radius: 10px;
            font-size: 14px; font-family: inherit; outline: none; transition: border-color 0.15s;
            background: #fff; color: #1d1d1f;
        }
        .form-input:focus { border-color: #1d1d1f; box-shadow: 0 0 0 3px rgba(0,0,0,0.04); }
        .form-error { font-size: 12px; color: #ff3b30; margin-top: 4px; }
        .remember-row { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        .remember-row input { width: 15px; height: 15px; accent-color: #1d1d1f; }
        .remember-row label { font-size: 13px; color: #86868b; cursor: pointer; }
        .btn-login {
            width: 100%; padding: 12px; background: #1d1d1f; color: #fff; border: none;
            border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer;
            font-family: inherit; transition: opacity 0.15s;
        }
        .btn-login:hover { opacity: 0.85; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>PropertyAgent</h1>
            <p>Sign in to your CMS account</p>
        </div>
        <div class="login-card">
            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" value="{{ old('username') }}" placeholder="Enter your username" autofocus required>
                    @error('username')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                </div>
                <div class="remember-row">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn-login">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>
