<?php
require 'config.php';

// HANDLE LOGIC FIRST (Before including the header/HTML)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $date = $_POST['reminder_date'];
    $type = $_POST['reminder_type'];
    $remark = $_POST['remark'];

    $stmt = $pdo->prepare("INSERT INTO reminders (title, reminder_date, reminder_type, remark) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$title, $date, $type, $remark])) {
        // Now this redirect will work because no HTML has been output yet
        header("Location: dashboard.php");
        exit;
    }
}

// OUTPUT HTML SECOND
include 'layout_header.php';
?>

<div class="max-w-2xl mx-auto mt-10">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Set New Reminder</h1>
            <p class="text-xs text-slate-500 mt-1">Create a one-time or recurring task.</p>
        </div>

        <form method="POST" class="space-y-6">
            
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Reminder Title</label>
                <input type="text" name="title" required placeholder="e.g., Call Client regarding Renewal" 
                       class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Reminder Date</label>
                    <input type="date" name="reminder_date" required value="<?= date('Y-m-d') ?>" 
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Recurrence Type</label>
                    <select name="reminder_type" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer">
                        <option value="ONETIME">One Time</option>
                        <option value="WEEKLY">Weekly</option>
                        <option value="MONTHLY">Monthly</option>
                        <option value="YEARLY">Yearly</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Remark</label>
                <textarea name="remark" rows="3" placeholder="Add any specific details here..." 
                          class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"></textarea>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="flex-1 bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-black transition-all shadow-lg shadow-slate-900/20 active:scale-95">
                    Save Reminder
                </button>
                <a href="dashboard.php" class="px-6 py-3 font-bold text-slate-500 hover:text-slate-800 transition-colors">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

<?php include 'layout_footer.php'; ?>