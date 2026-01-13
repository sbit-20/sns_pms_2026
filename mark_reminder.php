<?php
require 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 1. Fetch the reminder you just clicked
    $stmt = $pdo->prepare("SELECT * FROM reminders WHERE reminder_id = ?");
    $stmt->execute([$id]);
    $current_task = $stmt->fetch();

    if ($current_task) {
        // 2. Mark this specific task as COMPLETED (History)
        $pdo->prepare("UPDATE reminders SET status = 'COMPLETED' WHERE reminder_id = ?")->execute([$id]);

        // 3. GENERATE NEXT TASK (If it's recurring)
        if ($current_task['reminder_type'] != 'ONETIME') {
            
            // Set the Interval based on type
            $interval = '';
            if ($current_task['reminder_type'] == 'WEEKLY')  $interval = '1 WEEK';
            if ($current_task['reminder_type'] == 'MONTHLY') $interval = '1 MONTH';
            if ($current_task['reminder_type'] == 'YEARLY')  $interval = '1 YEAR';
            
            // --- STRICT CALENDAR LOGIC ---
            // New Date = OLD Reminder Date + Interval
            // This ignores the current date to prevent "drifting"
            
            $sql_date_calc = "SELECT DATE_ADD(?, INTERVAL $interval)";
            $stmt_date = $pdo->prepare($sql_date_calc);
            $stmt_date->execute([$current_task['reminder_date']]); 
            $next_due_date = $stmt_date->fetchColumn();

            // 4. Create the NEW Task in the database
            $insert_sql = "INSERT INTO reminders (title, remark, reminder_date, reminder_type, status) 
                           VALUES (?, ?, ?, ?, 'PENDING')";
            $stmt_insert = $pdo->prepare($insert_sql);
            $stmt_insert->execute([
                $current_task['title'], 
                $current_task['remark'], 
                $next_due_date, 
                $current_task['reminder_type']
            ]);
        }
    }
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit;