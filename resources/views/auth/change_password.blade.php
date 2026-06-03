<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SchoolApp - Sécurisation du compte</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

<div style="min-height:100vh; display:flex; justify-content:center; align-items:center;">

    <div class="card" style="max-width:420px; width:100%; padding:25px;">

        <h3 style="text-align:center; font-weight:900; color:var(--primary);">
            🔒 Première Connexion
        </h3>

        <p style="text-align:center; color:var(--text-muted); font-size:13px; margin-bottom:20px;">
            Définissez votre mot de passe pour pouvoir continuer
        </p>

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group" style="margin-top:12px;">
                <label class="form-label">Confirmation</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="login-actions">
                <a href="{{ route('login') }}" class="btn btn-ghost">
                    Annuler
                </a>

                <button type="submit" class="btn btn-primary">
                    Se connecter
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>