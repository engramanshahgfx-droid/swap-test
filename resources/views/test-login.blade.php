<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <style>
        body { font-family: Arial; margin: 50px; }
        form { max-width: 400px; }
        input { display: block; margin: 10px 0; padding: 8px; width: 100%; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Test Login</h1>
    <form method="POST">
        @csrf
        <input type="email" name="email" placeholder="Email" value="admin@crewswap.com" required>
        <input type="password" name="password" placeholder="Password" value="password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>

