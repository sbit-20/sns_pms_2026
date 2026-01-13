<?php
require 'config.php';
if(isset($_POST['login'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id']; header("Location: dashboard.php"); exit;
    } else { $error = "Invalid Login"; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><script src="https://cdn.tailwindcss.com"></script><title>Login</title></head>
<body class="bg-slate-900 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded shadow-lg w-96">
        <div class="flex justify-center mb-6">
            <img src="snss.png" alt="Sun & Sun Solutions" class="h-16 w-auto object-contain">
        </div>
        
        <?php if(isset($error)) echo "<p class='text-red-500 text-center mb-4 text-sm'>$error</p>"; ?>
        <form method="POST" class="space-y-4">
            <input type="text" name="username" placeholder="Username" class="w-full border p-2 rounded" required>
            <input type="password" name="password" placeholder="Password" class="w-full border p-2 rounded" required>
            <button type="submit" name="login" class="w-full bg-blue-600 text-white p-2 rounded font-bold hover:bg-blue-700 transition">Login</button>
        </form>
    </div>
</body>
</html>