<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account activation notice</title>
</head>
<body>
    <h2>Hello {{ $user->full_name ?? 'there' }},</h2>
    <p>Your account activation is approaching its end date.</p>
    <p><strong>Stage:</strong> {{ $stage }}</p>
    <p><strong>Activation end date:</strong> {{ $activationEndDate ?
        \\Illuminate\\Support\\Carbon::parse($activationEndDate)->format('F j, Y') : 'Not set' }}</p>
    <p>Please contact support if you need to extend your access.</p>
</body>
</html>
