<?php
// dashboard.php (Project Root)
require_once 'config/config.php';
include_once 'includes/layout_header.php';

// Execute the Stored Procedure
$stmt = $pdo->query("CALL GetDashboardData()");

// 1. Fetch Stats
$stats_raw = $stmt->fetch(PDO::FETCH_ASSOC);

$stats = array(
    'clients'  => (isset($stats_raw['total_clients'])) ? $stats_raw['total_clients'] : 0,
    'projects' => (isset($stats_raw['total_projects'])) ? $stats_raw['total_projects'] : 0,
    'smm'      => (isset($stats_raw['total_smm'])) ? $stats_raw['total_smm'] : 0
);

// 2. Fetch Active Reminders
$stmt->nextRowset();
$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Project Renewals
$stmt->nextRowset();
$projects_due = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. SMM Renewals
$stmt->nextRowset();
$smm_due = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Recent Project Invoices
$stmt->nextRowset();
$inv_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Recent SMM Invoices
$stmt->nextRowset();
$inv_smm = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt->closeCursor();
?>

<div class="w-full pb-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        <div class="lg:col-span-3 space-y-3 lg:sticky lg:top-6">
            <h2 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] px-2 mb-1">Overview</h2>
            
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-blue-300 transition-all group overflow-hidden relative">
                <div class="flex items-center gap-3 relative">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fa-solid fa-users-viewfinder"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Total Clients</p>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight"><?= number_format($stats['clients']); ?></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-emerald-300 transition-all group overflow-hidden relative">
                <div class="flex items-center gap-3 relative">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fa-solid fa-laptop-code"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Projects</p>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight"><?= number_format($stats['projects']); ?></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-pink-300 transition-all group overflow-hidden relative">
                <div class="flex items-center gap-3 relative">
                    <div class="w-10 h-10 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-lg group-hover:bg-pink-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">SMM Services</p>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight"><?= number_format($stats['smm']); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-9 space-y-5">
            
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
                <div class="xl:col-span-7">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[480px]">
                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                            <h3 class="font-bold text-slate-800 text-base tracking-tight">Priority Tasks</h3>
                            <a href="<?= BASE_URL; ?>modules/reminders/add_reminder.php" class="w-8 h-8 flex items-center justify-center bg-slate-900 text-white rounded-lg hover:bg-black transition-all">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                            </a>
                        </div>

                        <div class="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-2.5">
                            <?php if(empty($reminders)): ?>
                                <div class="h-full flex flex-col items-center justify-center text-center opacity-50">
                                    <i class="fa-solid fa-thumbs-up text-3xl text-emerald-500 mb-2"></i>
                                    <p class="text-slate-400 font-bold text-xs">All tasks completed!</p>
                                </div>
                            <?php else: foreach($reminders as $r): 
                                $diff = (strtotime($r['reminder_date']) - strtotime(date('Y-m-d'))) / 86400;
                                if ($diff < 0) { $cls = 'border-red-100 bg-red-50/20'; $badge = 'bg-red-100 text-red-600'; $status = 'Overdue'; } 
                                elseif ($diff == 0) { $cls = 'border-amber-100 bg-amber-50/20'; $badge = 'bg-amber-100 text-amber-600'; $status = 'Today'; } 
                                else { $cls = 'border-slate-100 bg-slate-50/50'; $badge = 'bg-blue-100 text-blue-600'; $status = 'Upcoming'; }
                            ?>
                                <div class="group flex items-center justify-between p-3 rounded-xl border <?= $cls; ?> hover:bg-white hover:shadow-sm transition-all duration-300">
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col items-center justify-center w-9 h-9 bg-white rounded-lg shadow-sm border border-slate-100">
                                            <span class="text-[8px] font-black text-slate-400 uppercase"><?= date('M', strtotime($r['reminder_date'])); ?></span>
                                            <span class="text-sm font-black text-slate-800 leading-none"><?= date('d', strtotime($r['reminder_date'])); ?></span>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-bold text-slate-800 text-xs"><?= htmlspecialchars($r['title']); ?></h4>
                                                <span class="px-1.5 py-0.5 rounded text-[7px] font-black uppercase <?= $badge; ?>"><?= $status; ?></span>
                                            </div>
                                            <p class="text-[10px] text-slate-500 line-clamp-1 italic"><?= ($r['remark']) ? htmlspecialchars($r['remark']) : 'No notes.'; ?></p>
                                        </div>
                                    </div>
                                    <a href="<?= BASE_URL; ?>modules/reminders/reminders.php?toggle=<?= $r['reminder_id']; ?>&from=dashboard" class="w-8 h-8 rounded-full border border-slate-200 bg-white flex items-center justify-center text-slate-300 hover:text-emerald-500 hover:border-emerald-500 transition-all">
                                        <i class="fa-solid fa-check text-xs"></i>
                                    </a>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-5">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[480px]">
                        <div class="p-5 border-b border-slate-100">
                            <h3 class="font-bold text-slate-800 text-center mb-3 text-sm tracking-tight">Upcoming Renewals</h3>
                            <div class="flex bg-slate-100 p-1 rounded-xl">
                                <button onclick="switchTab('tab_project', this)" class="tab-btn flex-1 py-2 rounded-lg text-[9px] font-black transition-all bg-white text-slate-800 shadow-sm" data-target="tab_project">
                                    PROJECTS (<?= count($projects_due) ?>)
                                </button>
                                <button onclick="switchTab('tab_smm', this)" class="tab-btn flex-1 py-2 rounded-lg text-[9px] font-black transition-all text-slate-500" data-target="tab_smm">
                                    SMM (<?= count($smm_due) ?>)
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto custom-scrollbar p-5">
                            <div id="tab_project" class="tab-content space-y-2.5">
                                <?php if(empty($projects_due)): ?>
                                    <p class="text-center text-slate-400 text-[10px] py-10 font-bold uppercase">No projects due</p>
                                <?php else: foreach($projects_due as $p): ?>
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between group hover:bg-white transition-all">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded bg-blue-600 text-white flex items-center justify-center text-[10px]"><i class="fa-solid fa-code"></i></div>
                                            <div>
                                                <h4 class="text-[11px] font-bold text-slate-800 leading-tight"><?= htmlspecialchars($p['project_name']); ?></h4>
                                                <p class="text-[9px] text-slate-500 font-bold uppercase tracking-tighter"><?= htmlspecialchars($p['client_name']); ?></p>
                                                <span class="text-[10px] font-bold text-amber-600 mt-0.5 block"><?= date('d M, Y', strtotime($p['next_renewal_date'])); ?></span>
                                            </div>
                                        </div>
                                        <a href="<?= BASE_URL; ?>modules/invoices/generate_invoice.php?client_id=<?= $p['client_id']; ?>&type=project" class="opacity-0 group-hover:opacity-100 px-2.5 py-1 bg-slate-900 text-white text-[9px] font-black rounded transition-all">Bill</a>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>

                            <div id="tab_smm" class="tab-content hidden space-y-2.5">
                                <?php if(empty($smm_due)): ?>
                                    <p class="text-center text-slate-400 text-[10px] py-10 font-bold uppercase">No SMM due</p>
                                <?php else: foreach($smm_due as $s): ?>
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between group hover:bg-white transition-all">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded bg-pink-600 text-white flex items-center justify-center text-[10px]"><i class="fa-solid fa-hashtag"></i></div>
                                            <div>
                                                <h4 class="text-[11px] font-bold text-slate-800 leading-tight"><?= htmlspecialchars(($s['ad_description']) ? $s['ad_description'] : 'SMM Service'); ?></h4>
                                                <p class="text-[9px] text-slate-500 font-bold uppercase tracking-tighter"><?= htmlspecialchars($s['client_name']); ?></p>
                                                <span class="text-[10px] font-bold text-pink-600 mt-0.5 block"><?= date('d M, Y', strtotime($s['next_renewal_date'])); ?></span>
                                            </div>
                                        </div>
                                        <a href="<?= BASE_URL; ?>modules/invoices/generate_invoice.php?client_id=<?= $s['client_id']; ?>&type=smm" class="opacity-0 group-hover:opacity-100 px-2.5 py-1 bg-slate-900 text-white text-[9px] font-black rounded transition-all">Bill</a>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <h3 class="font-bold text-slate-800 text-sm tracking-tight">Recent Invoices</h3>
                    <div class="flex bg-slate-100 p-1 rounded-lg">
                        <button onclick="switchInvTab('tab_inv_project', this)" class="tab-inv-btn px-4 py-1.5 rounded-md text-[9px] font-black transition-all bg-white text-slate-800 shadow-sm" data-target="tab_inv_project">Projects</button>
                        <button onclick="switchInvTab('tab_inv_smm', this)" class="tab-inv-btn px-4 py-1.5 rounded-md text-[9px] font-black transition-all text-slate-500" data-target="tab_inv_smm">SMM</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div id="tab_inv_project" class="tab-inv-content">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50 text-slate-400 text-[8px] uppercase font-black border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-3">Client</th>
                                    <th class="px-6 py-3">Invoice #</th>
                                    <th class="px-6 py-3 text-right">Date</th>
                                    <th class="px-6 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($inv_projects as $i): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-3 font-bold text-slate-700 text-xs"><?= htmlspecialchars($i['client_name']); ?></td>
                                    <td class="px-6 py-3 font-mono text-[9px] text-slate-500"><?= $i['invoice_number']; ?></td>
                                    <td class="px-6 py-3 text-right text-[9px] font-bold text-slate-500"><?= date('M d, Y', strtotime($i['invoice_date'])); ?></td>
                                    <td class="px-6 py-3 text-center">
                                        <a href="<?= BASE_URL; ?>modules/invoices/invoice_print.php?id=<?= $i['invoice_id']; ?>" target="_blank" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 text-slate-400 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <i class="fa-solid fa-eye text-[9px]"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="tab_inv_smm" class="tab-inv-content hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50 text-slate-400 text-[8px] uppercase font-black border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-3">Client</th>
                                    <th class="px-6 py-3">Invoice #</th>
                                    <th class="px-6 py-3 text-right">Date</th>
                                    <th class="px-6 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($inv_smm as $i): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-3 font-bold text-slate-700 text-xs"><?= htmlspecialchars($i['client_name']); ?></td>
                                    <td class="px-6 py-3 font-mono text-[9px] text-slate-500"><?= $i['invoice_number']; ?></td>
                                    <td class="px-6 py-3 text-right text-[9px] font-bold text-slate-500"><?= date('M d, Y', strtotime($i['invoice_date'])); ?></td>
                                    <td class="px-6 py-3 text-center">
                                        <a href="<?= BASE_URL; ?>modules/invoices/invoice_print.php?id=<?= $i['invoice_id']; ?>" target="_blank" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 text-slate-400 hover:bg-pink-600 hover:text-white transition-all shadow-sm">
                                            <i class="fa-solid fa-eye text-[9px]"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function applyTabStyle(btn, allBtnClass) {
        document.querySelectorAll('.' + allBtnClass).forEach(el => {
            el.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
            el.classList.add('text-slate-500');
        });
        btn.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
        btn.classList.remove('text-slate-500');
    }

    function switchTab(tabId, btn) {
        const parent = btn.closest('.rounded-2xl');
        parent.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        parent.querySelector('#' + tabId).classList.remove('hidden');
        applyTabStyle(btn, 'tab-btn');
    }

    function switchInvTab(tabId, btn) {
        const parent = btn.closest('.rounded-2xl');
        parent.querySelectorAll('.tab-inv-content').forEach(el => el.classList.add('hidden'));
        parent.querySelector('#' + tabId).classList.remove('hidden');
        applyTabStyle(btn, 'tab-inv-btn');
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<?php include_once 'includes/layout_footer.php'; ?>