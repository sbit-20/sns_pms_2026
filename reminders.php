<?php
require 'config.php';

// --- LOGIC: HANDLE ACTIONS BEFORE HTML OUTPUT ---

// 1. Delete Reminder
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM reminders WHERE reminder_id = ?")->execute([$_GET['delete']]);
    // Redirect back to the same page (or dashboard if 'from' is set)
    $redirect = isset($_GET['from']) && $_GET['from'] == 'dashboard' ? 'dashboard.php' : 'reminders.php';
    header("Location: $redirect");
    exit;
}

// 2. Toggle Status (Complete / Undo) - STRICT DATE LOGIC HERE
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    
    // Fetch the task details
    $stmt = $pdo->prepare("SELECT * FROM reminders WHERE reminder_id = ?");
    $stmt->execute([$id]);
    $task = $stmt->fetch();

    if ($task) {
        if ($task['status'] == 'PENDING') {
            // --- MARK AS COMPLETED ---
            // 1. Update the CURRENT task status to COMPLETED (keeps history)
            $pdo->prepare("UPDATE reminders SET status = 'COMPLETED' WHERE reminder_id = ?")->execute([$id]);

            // 2. CREATE THE NEXT TASK (If Recurring)
            if ($task['reminder_type'] != 'ONETIME') {
                
                // Determine SQL Interval
                $interval = '';
                if ($task['reminder_type'] == 'WEEKLY')  $interval = '1 WEEK';
                if ($task['reminder_type'] == 'MONTHLY') $interval = '1 MONTH';
                if ($task['reminder_type'] == 'YEARLY')  $interval = '1 YEAR';

                // --- BEST LOGIC: STRICT CALENDAR CALCULATION ---
                // We calculate the new date using the OLD 'reminder_date'.
                // Example: Old = 01/10/2025 -> New = 08/10/2025
                // We do NOT use CURDATE() here, so the schedule never drifts.
                
                $date_query = $pdo->prepare("SELECT DATE_ADD(?, INTERVAL $interval)");
                $date_query->execute([$task['reminder_date']]);
                $next_date = $date_query->fetchColumn();

                // Insert the NEW Pending Task
                $insert = $pdo->prepare("INSERT INTO reminders (title, remark, reminder_date, reminder_type, status) VALUES (?, ?, ?, ?, 'PENDING')");
                $insert->execute([$task['title'], $task['remark'], $next_date, $task['reminder_type']]);
            }

        } else {
            // --- UNDO (If accidentally clicked) ---
            // If it was COMPLETED, set it back to PENDING.
            // Note: This does not delete the "Next" task if already created, to avoid data loss.
            $pdo->prepare("UPDATE reminders SET status = 'PENDING' WHERE reminder_id = ?")->execute([$id]);
        }
    }
    
    // Redirect back to where the user clicked
    $redirect = isset($_GET['from']) && $_GET['from'] == 'dashboard' ? 'dashboard.php' : 'reminders.php';
    header("Location: $redirect");
    exit;
}

// --- HTML OUTPUT STARTS HERE ---
include 'layout_header.php';

// Fetch Reminders for List View
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'ALL';
$sql = "SELECT * FROM reminders";
if ($filter == 'PENDING') $sql .= " WHERE status = 'PENDING'";
if ($filter == 'COMPLETED') $sql .= " WHERE status = 'COMPLETED'";
$sql .= " ORDER BY reminder_date ASC, status DESC"; // Sort by Date (Oldest first)

$reminders = $pdo->query($sql)->fetchAll();
?>

<div class="max-w-5xl mx-auto space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">All Reminders</h1>
            <p class="text-xs text-slate-500 mt-1">History and upcoming schedule.</p>
        </div>
        <a href="add_reminder.php" class="bg-slate-900 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-lg hover:bg-black transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Set Reminder
        </a>
    </div>

    <div class="flex gap-2 border-b border-slate-200 pb-4">
        <a href="reminders.php?filter=ALL" class="px-4 py-1.5 rounded-full text-xs font-bold transition-all <?= $filter=='ALL' ? 'bg-slate-800 text-white' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200' ?>">All</a>
        <a href="reminders.php?filter=PENDING" class="px-4 py-1.5 rounded-full text-xs font-bold transition-all <?= $filter=='PENDING' ? 'bg-amber-500 text-white' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200' ?>">Pending</a>
        <a href="reminders.php?filter=COMPLETED" class="px-4 py-1.5 rounded-full text-xs font-bold transition-all <?= $filter=='COMPLETED' ? 'bg-emerald-500 text-white' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200' ?>">Completed</a>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <?php if(empty($reminders)): ?>
            <div class="p-12 text-center text-slate-400 italic">No reminders found.</div>
        <?php else: ?>
            <div class="divide-y divide-slate-50">
                <?php foreach($reminders as $r): 
                    $is_done = $r['status'] == 'COMPLETED';
                    $row_opacity = $is_done ? 'opacity-60 bg-slate-50/50' : '';
                    $strike = $is_done ? 'line-through text-slate-400' : 'text-slate-800';
                    
                    // Visuals
                    $is_overdue = ($r['status']=='PENDING' && strtotime($r['reminder_date']) < strtotime(date('Y-m-d')));
                    $date_color = $is_overdue ? 'text-red-500 font-bold' : ($is_done ? 'text-slate-400' : 'text-slate-500');
                    
                    // Icons
                    $type_icon = 'fa-thumbtack';
                    if($r['reminder_type'] == 'WEEKLY') $type_icon = 'fa-calendar-week';
                    if($r['reminder_type'] == 'MONTHLY') $type_icon = 'fa-calendar-days';
                    if($r['reminder_type'] == 'YEARLY') $type_icon = 'fa-calendar';
                ?>
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50 transition group <?= $row_opacity ?>">
                    <div class="flex items-start gap-4">
                        <a href="reminders.php?toggle=<?= $r['reminder_id'] ?>" 
                           title="<?= $is_done ? 'Mark as Pending' : 'Mark Complete' ?>" 
                           class="mt-1 w-6 h-6 rounded-md border-2 flex items-center justify-center transition-all shadow-sm <?= $is_done ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 hover:border-emerald-400 text-transparent hover:text-emerald-300 bg-white' ?>">
                            <i class="fa-solid fa-check text-xs"></i>
                        </a>
                        
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-sm <?= $strike ?>"><?= $r['title'] ?></h3>
                                <?php if($is_done): ?>
                                    <span class="text-[9px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded font-bold uppercase tracking-wide">Done</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if(!empty($r['remark'])): ?>
                                <p class="text-xs text-slate-500 mt-0.5"><?= $r['remark'] ?></p>
                            <?php endif; ?>
                            
                            <div class="text-[10px] mt-1 <?= $date_color ?> flex items-center gap-2">
                                <span><i class="fa-regular fa-calendar mr-1"></i> <?= date('d M Y', strtotime($r['reminder_date'])) ?></span>
                                <span class="bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded uppercase tracking-wider flex items-center gap-1">
                                    <i class="fa-solid <?= $type_icon ?> text-[9px]"></i> <?= $r['reminder_type'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                         <a href="reminders.php?delete=<?= $r['reminder_id'] ?>" onclick="return confirm('Delete this reminder?')" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors" title="Delete">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'layout_footer.php'; ?>