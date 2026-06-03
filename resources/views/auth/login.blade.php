<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolApp - Connexion</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <h1>SchoolApp</h1>
            <p>Connexion à votre espace</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">
                    Adresse Email
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    required
                >
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label class="form-label">
                    Mot de passe
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required
                >
            </div>

            <div style="margin-top:12px;">
                <a href="#" class="login-link">
                    Mot de passe oublié ?
                </a>
            </div>

            <div class="login-actions">
                <button type="reset" class="btn btn-ghost">
                    Annuler
                </button>

                <button type="submit" class="btn btn-primary">
                    Se connecter
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>