<?php 
require 'config.php';
include 'layout_header.php'; 
$c = $pdo->query("SELECT * FROM clients ORDER BY client_name ASC")->fetchAll();
$sel = isset($_GET['client_id']) ? $_GET['client_id'] : '';
?>

<div class="max-w-4xl mx-auto mt-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-lg text-slate-800">Add SMM Service</h3>
        </div>
        
        <form action="actions.php" method="POST" class="p-8 space-y-8">
            <input type="hidden" name="action" value="add_smm">
            
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Select Client</label>
                <select name="client_id" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer" required>
                    <option value="">-- Choose Client --</option>
                    <?php foreach($c as $cl): ?>
                        <option value="<?= $cl['client_id'] ?>" <?= ($cl['client_id']==$sel)?'selected':'' ?>><?= $cl['client_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Management Charge (₹)</label>
                    <input type="number" name="s_mgmt" placeholder="0.00"
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Remark</label>
                    <input type="text" name="s_desc" placeholder="Service Details"
                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>
            </div>

            <div class="pt-4">
                <button class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl text-sm shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition transform active:scale-[0.99]">
                    Start Service
                </button>
            </div>
        </form>
    </div>
</div>
<?php include 'layout_footer.php'; ?>