<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Activation compte admin</title>
</head>
<body>
    <p>Bonjour {{ $admin->prenom }} {{ $admin->nom }},</p>

    <p>Votre compte administrateur a été créé. Voici vos identifiants :</p>

    <ul>
        <li>Matricule : {{ $admin->matricule }}</li>
        <li>Login : {{ $login }}</li>
        <li>Mot de passe temporaire : {{ $plainPassword }}</li>
    </ul>

    <p>Veuillez activer votre compte via ce lien (valable 24h) :</p>
    <p><a href="{{ $activationLink }}">{{ $activationLink }}</a></p>

    <p>Merci.</p>
</body>
</html>
