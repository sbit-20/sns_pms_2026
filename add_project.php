<?php 
require 'config.php';
include 'layout_header.php'; 
$c = $pdo->query("SELECT * FROM clients ORDER BY client_name ASC")->fetchAll();
$sel = isset($_GET['client_id']) ? $_GET['client_id'] : '';
$services = $pdo->query("SELECT * FROM service_type_tbl ORDER BY service_name ASC")->fetchAll();
?>

<div class="max-w-4xl mx-auto mt-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-lg text-slate-800">Add Project</h3>
        </div>
        
        <form action="actions.php" method="POST" class="p-8 space-y-8">
            <input type="hidden" name="action" value="add_project">
            <input type="hidden" name="client_id" value="<?= $sel ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Project Name</label>
                    <input type="text" name="p_name" required placeholder="Enter Project Name"
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Project Type</label>
                    <select name="p_type" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer">
                        <option value="">Select Type</option>
                        <?php foreach($services as $svc): ?>
                            <option value="<?= $svc['service_name'] ?>"><?= $svc['service_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">AMC Amount (₹)</label>
                    <input type="number" name="p_amc" placeholder="0.00"
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">AMC Renewal Date</label>
                    <input type="date" name="p_renewal" value="<?= date('Y-m-d') ?>"
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Managed By</label>
                    <input type="text" name="p_manager" placeholder="Manager Name"
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Contact No</label>
                    <input type="text" name="p_manager_contact" placeholder="Manager Contact"
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Technology</label>
                    <input type="text" name="p_tech_name" placeholder="e.g. PHP, Flutter"
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Tech Version</label>
                    <input type="text" name="p_version" placeholder="e.g. 1.0"
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>
            </div>

            <div class="pt-4">
                <button class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl text-sm shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition transform active:scale-[0.99]">
                    Save Project
                </button>
            </div>
        </form>
    </div>
</div>
<?php include 'layout_footer.php'; ?>