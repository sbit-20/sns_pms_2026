<?php
require 'config.php';
include 'layout_header.php';

// --- DATA FETCHING ---

// 1. Statistics Counts
$stats = [
    'clients'  => $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn(),
    'projects' => $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
    'smm'      => $pdo->query("SELECT COUNT(*) FROM smm_services")->fetchColumn()
];

// 2. Fetch Active Reminders
$reminders = $pdo->query("SELECT * FROM reminders 
                          WHERE status = 'PENDING' 
                          AND reminder_date <= DATE_ADD(CURDATE(), INTERVAL 5 DAY) 
                          ORDER BY reminder_date ASC")->fetchAll();

// 3. Logic: Find Clients with pending items
$sql = "SELECT 
            c.client_id, 
            c.client_name, 
            SUM(CASE WHEN type = 'project' THEN 1 ELSE 0 END) as pending_projects,
            SUM(CASE WHEN type = 'smm' THEN 1 ELSE 0 END) as pending_smm
        FROM clients c 
        JOIN (
            SELECT client_id, 'project' as type FROM projects WHERE next_renewal_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION ALL 
            SELECT client_id, 'smm' as type FROM smm_services WHERE next_renewal_date <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)
        ) as due_items ON c.client_id = due_items.client_id 
        GROUP BY c.client_id";

$pending = $pdo->query($sql)->fetchAll();

$count_p = 0; $count_s = 0;
foreach($pending as $p) {
    if($p['pending_projects'] > 0) $count_p++;
    if($p['pending_smm'] > 0) $count_s++;
}

// 4. Recent Invoices
$sql_inv = "SELECT i.*, c.client_name,
            (SELECT COUNT(*) FROM invoice_items ii WHERE ii.invoice_id = i.invoice_id AND ii.description LIKE '%AMC%') as has_project,
            (SELECT COUNT(*) FROM invoice_items ii WHERE ii.invoice_id = i.invoice_id AND ii.description LIKE '%SMM%') as has_smm
            FROM invoices i 
            JOIN clients c ON i.client_id=c.client_id 
            ORDER BY i.invoice_id DESC LIMIT 10";

$all_invoices = $pdo->query($sql_inv)->fetchAll();

$inv_projects = [];
$inv_smm = [];

foreach($all_invoices as $inv) {
    if($inv['has_project'] > 0 || ($inv['has_project'] == 0 && $inv['has_smm'] == 0)) {
        $inv_projects[] = $inv;
    }
    if($inv['has_smm'] > 0) {
        $inv_smm[] = $inv;
    }
}
$inv_projects = array_slice($inv_projects, 0, 5);
$inv_smm = array_slice($inv_smm, 0, 5);
?>

<div class="w-full space-y-6">

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-3 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm h-full flex flex-col justify-center">
                <h3 class="font-bold text-slate-800 mb-5 px-1">Business Overview</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3.5 bg-blue-50/50 rounded-xl border border-blue-100 hover:border-blue-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white text-blue-600 flex items-center justify-center shadow-sm"><i class="fa-solid fa-users"></i></div>
                            <span class="text-sm font-medium text-slate-600">Clients</span>
                        </div>
                        <span class="text-xl font-bold text-slate-800"><?= $stats['clients'] ?></span>
                    </div>
                    <div class="flex items-center justify-between p-3.5 bg-emerald-50/50 rounded-xl border border-emerald-100 hover:border-emerald-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white text-emerald-600 flex items-center justify-center shadow-sm"><i class="fa-solid fa-code"></i></div>
                            <span class="text-sm font-medium text-slate-600">Projects</span>
                        </div>
                        <span class="text-xl font-bold text-slate-800"><?= $stats['projects'] ?></span>
                    </div>
                    <div class="flex items-center justify-between p-3.5 bg-pink-50/50 rounded-xl border border-pink-100 hover:border-pink-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white text-pink-600 flex items-center justify-center shadow-sm"><i class="fa-solid fa-bullhorn"></i></div>
                            <span class="text-sm font-medium text-slate-600">Social Media</span>
                        </div>
                        <span class="text-xl font-bold text-slate-800"><?= $stats['smm'] ?></span>
                    </div>
                </div>
                
                <a href="add_reminder.php" class="mt-5 block bg-slate-900 rounded-xl p-4 shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all text-center group cursor-pointer border border-slate-900">
                    <div class="flex items-center justify-center gap-3">
                        <div class="w-6 h-6 bg-white/10 rounded-full flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                        </div>
                        <span class="text-white font-bold text-sm">Create New Reminder</span>
                    </div>
                </a>
            </div>
        </div>

        <div class="xl:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[500px]">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50 to-white rounded-t-2xl">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                    <h3 class="font-bold text-slate-800">Priority Tasks</h3>
                </div>
                <?php if(count($reminders) > 0): ?>
                    <span class="bg-amber-100 text-amber-700 text-[10px] px-2.5 py-1 rounded-md font-bold"><?= count($reminders) ?> Pending</span>
                <?php endif; ?>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-3">
                <?php if(empty($reminders)): ?>
                    <div class="h-full flex flex-col items-center justify-center text-center">
                        <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center text-emerald-400 text-xl mb-3">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">All caught up!</p>
                        <p class="text-slate-400 text-xs mt-1">No pending tasks for now.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach($reminders as $r): 
                            $r_date = strtotime($r['reminder_date']);
                            $today = strtotime(date('Y-m-d'));
                            $diff = ($r_date - $today) / 86400;

                            if ($diff < 0) { $color = 'red'; $text = 'Overdue'; } 
                            elseif ($diff == 0) { $color = 'amber'; $text = 'Today'; } 
                            else { $color = 'blue'; $text = 'Upcoming'; }
                        ?>
                        <div class="p-3.5 rounded-xl border border-<?= $color ?>-100 bg-<?= $color ?>-50/10 hover:bg-white hover:shadow-sm transition-all flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <div class="flex flex-col items-center justify-center w-11 h-11 bg-white border border-<?= $color ?>-100 rounded-lg shadow-sm">
                                    <span class="text-[9px] font-bold text-<?= $color ?>-500 uppercase"><?= date('M', $r_date) ?></span>
                                    <span class="text-sm font-bold text-slate-700 leading-none"><?= date('d', $r_date) ?></span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-slate-800 text-sm"><?= $r['title'] ?></h4>
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-<?= $color ?>-100 text-<?= $color ?>-700 uppercase tracking-wide"><?= $text ?></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-1">
                                        <i class="fa-solid fa-repeat text-[10px]"></i> <?= ucfirst(strtolower($r['reminder_type'])) ?>
                                        <?php if(!empty($r['remark'])): ?> &bull; <span class="truncate max-w-[180px]"><?= $r['remark'] ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <a href="reminders.php?toggle=<?= $r['reminder_id'] ?>&from=dashboard" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-100 text-slate-300 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all bg-white shadow-sm">
                                <i class="fa-solid fa-check text-xs"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if(count($reminders) > 0): ?>
            <div class="p-3 border-t border-slate-100 bg-slate-50 text-center rounded-b-2xl">
                <a href="reminders.php" class="text-[11px] font-bold text-slate-500 hover:text-slate-800 transition-colors uppercase tracking-wide">View All Tasks</a>
            </div>
            <?php endif; ?>
        </div>

        <div class="xl:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[500px]">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Renewals</h3>
                <div class="bg-slate-100 p-1 rounded-lg flex text-[10px] font-bold">
                    <button onclick="switchTab('tab_project', this)" class="tab-btn active px-3 py-1.5 rounded-md transition-all shadow-sm bg-white text-slate-800" data-target="tab_project">Projects (<?= $count_p ?>)</button>
                    <button onclick="switchTab('tab_smm', this)" class="tab-btn px-3 py-1.5 rounded-md transition-all text-slate-500 hover:text-slate-700" data-target="tab_smm">SMM (<?= $count_s ?>)</button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-3">
                <div id="tab_project" class="tab-content space-y-3">
                    <?php $has_p = false; foreach($pending as $pc): if($pc['pending_projects'] > 0): $has_p = true; ?>
                    <div class="p-3.5 bg-white border border-slate-100 rounded-xl hover:border-blue-200 hover:shadow-sm transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs"><i class="fa-solid fa-code"></i></div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-700"><?= $pc['client_name'] ?></h4>
                                <p class="text-[11px] text-amber-600 font-medium bg-amber-50 px-1.5 rounded w-fit mt-0.5"><?= $pc['pending_projects'] ?> due</p>
                            </div>
                        </div>
                        <a href="generate_invoice.php?client_id=<?= $pc['client_id'] ?>&type=project" class="opacity-0 group-hover:opacity-100 text-[10px] font-bold bg-slate-900 text-white px-3 py-1.5 rounded-lg transition-all shadow-sm hover:bg-black">Bill</a>
                    </div>
                    <?php endif; endforeach; if(!$has_p): ?>
                        <div class="h-full flex flex-col items-center justify-center text-center opacity-60">
                            <i class="fa-regular fa-folder-open text-2xl text-slate-300 mb-2"></i>
                            <span class="text-slate-400 text-xs">No pending project renewals.</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="tab_smm" class="tab-content hidden space-y-3">
                    <?php $has_s = false; foreach($pending as $pc): if($pc['pending_smm'] > 0): $has_s = true; ?>
                    <div class="p-3.5 bg-white border border-slate-100 rounded-xl hover:border-pink-200 hover:shadow-sm transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-xs"><i class="fa-solid fa-hashtag"></i></div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-700"><?= $pc['client_name'] ?></h4>
                                <p class="text-[11px] text-pink-600 font-medium bg-pink-50 px-1.5 rounded w-fit mt-0.5"><?= $pc['pending_smm'] ?> due</p>
                            </div>
                        </div>
                        <a href="generate_invoice.php?client_id=<?= $pc['client_id'] ?>&type=smm" class="opacity-0 group-hover:opacity-100 text-[10px] font-bold bg-slate-900 text-white px-3 py-1.5 rounded-lg transition-all shadow-sm hover:bg-black">Bill</a>
                    </div>
                    <?php endif; endforeach; if(!$has_s): ?>
                        <div class="h-full flex flex-col items-center justify-center text-center opacity-60">
                            <i class="fa-regular fa-folder-open text-2xl text-slate-300 mb-2"></i>
                            <span class="text-slate-400 text-xs">No pending SMM renewals.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Recent Invoices</h3>
            <div class="bg-slate-100 p-1 rounded-lg flex text-xs font-bold">
                <button onclick="switchInvTab('tab_inv_project', this)" class="tab-inv-btn active px-3 py-1.5 rounded-md transition-all shadow-sm bg-white text-slate-800" data-target="tab_inv_project">Projects</button>
                <button onclick="switchInvTab('tab_inv_smm', this)" class="tab-inv-btn px-3 py-1.5 rounded-md transition-all text-slate-500 hover:text-slate-700" data-target="tab_inv_smm">Social Media</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <div id="tab_inv_project" class="tab-inv-content">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/50 text-slate-500 text-[11px] uppercase font-bold border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4">Client</th>
                            <th class="px-6 py-4">Invoice</th>
                            <th class="px-6 py-4 text-right">Amount</th>
                            <th class="px-6 py-4 text-right">Date</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($inv_projects as $i): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 font-bold text-slate-700"><?= $i['client_name'] ?></td>
                            <td class="px-6 py-3.5 font-bold text-slate-700 "><?= $i['invoice_number'] ?></td>
                            <td class="px-6 py-3.5 text-right font-bold text-slate-800">₹<?= number_format($i['total_amount']) ?></td>
                            <td class="px-6 py-3.5 text-right text-slate-700"><?= date('M d, Y', strtotime($i['invoice_date'])) ?></td>
                            <td class="px-6 py-3.5 text-center">
                                <a href="invoice_print.php?id=<?= $i['invoice_id'] ?>" target="_blank" class="text-slate-400 hover:text-blue-600 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-blue-50 mx-auto"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; if(empty($inv_projects)): ?>
                            <tr><td colspan="5" class="p-8 text-center text-slate-400 italic">No project invoices found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="tab_inv_smm" class="tab-inv-content hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/50 text-slate-500 text-[11px] uppercase font-bold border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4">Client</th>
                            <th class="px-6 py-4">Invoice #</th>
                            <th class="px-6 py-4 text-right">Amount</th>
                            <th class="px-6 py-4 text-right">Date</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($inv_smm as $i): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 font-bold text-slate-700"><?= $i['client_name'] ?></td>
                            <td class="px-6 py-3.5 font-mono text-xs text-slate-500 font-medium">#<?= $i['invoice_number'] ?></td>
                            <td class="px-6 py-3.5 text-right font-bold text-slate-800">₹<?= number_format($i['total_amount']) ?></td>
                            <td class="px-6 py-3.5 text-right text-xs text-slate-500"><?= date('M d, Y', strtotime($i['invoice_date'])) ?></td>
                            <td class="px-6 py-3.5 text-center">
                                <a href="invoice_print.php?id=<?= $i['invoice_id'] ?>" target="_blank" class="text-slate-400 hover:text-blue-600 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-blue-50 mx-auto"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; if(empty($inv_smm)): ?>
                            <tr><td colspan="5" class="p-8 text-center text-slate-400 italic">No SMM invoices found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    function toggleTabClasses(btn, allBtns) {
        allBtns.forEach(el => {
            el.classList.remove('bg-white', 'text-slate-800', 'shadow-sm', 'active');
            el.classList.add('text-slate-500', 'hover:text-slate-700');
        });
        btn.classList.add('bg-white', 'text-slate-800', 'shadow-sm', 'active');
        btn.classList.remove('text-slate-500', 'hover:text-slate-700');
    }

    function switchTab(tabId, btn) {
        const container = btn.closest('.rounded-2xl'); 
        container.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        container.querySelector('#' + tabId).classList.remove('hidden');
        toggleTabClasses(btn, container.querySelectorAll('.tab-btn'));
    }

    function switchInvTab(tabId, btn) {
        const container = btn.closest('.rounded-2xl');
        container.querySelectorAll('.tab-inv-content').forEach(el => el.classList.add('hidden'));
        container.querySelector('#' + tabId).classList.remove('hidden');
        toggleTabClasses(btn, container.querySelectorAll('.tab-inv-btn'));
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>

<?php include 'layout_footer.php'; ?>