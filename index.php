<?php
// index.php (Project Root)

// 1. Update Path to config: Point to the config folder
require_once 'config/config.php';

if(isset($_POST['login'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id']; 
        header("Location: dashboard.php"); 
        exit;
    } else { 
        $error = "Invalid Login"; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login - SNS PMS</title>
</head>
<body class="bg-slate-900 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-96">
        <div class="flex justify-center mb-6">
            <img src="<?= BASE_URL ?>assets/img/snss.png" alt="Sun & Sun Solutions" class="h-16 w-auto object-contain">
        </div>
        
        <?php if(isset($error)) echo "<p class='text-red-500 text-center mb-4 text-sm font-medium'>$error</p>"; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 ml-1">Username</label>
                <input type="text" name="username" placeholder="Username" class="w-full border border-slate-200 p-3 rounded-xl text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" required>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 ml-1">Password</label>
                <input type="password" name="password" placeholder="Password" class="w-full border border-slate-200 p-3 rounded-xl text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" required>
            </div>
            <button type="submit" name="login" class="w-full bg-blue-600 text-white p-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-500/20 active:scale-[0.98]">
                Login
            </button>
        </form>
    </div>
</body>
</html>