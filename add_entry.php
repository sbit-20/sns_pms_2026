<?php 
require 'config.php'; 
include 'layout_header.php'; 

// Fetch Service Types from Database
$services = $pdo->query("SELECT * FROM service_type_tbl ORDER BY service_name ASC")->fetchAll();
?>

<div class="max-w-4xl mx-auto mt-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-lg text-slate-800">New Client Entry</h3>
        </div>

        <form action="actions.php" method="POST" class="p-8 space-y-8">
            <input type="hidden" name="action" value="add_full_entry">

            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-6">Client Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Client Name</label>
                        <input type="text" name="client_name" required placeholder="Enter Client Name"
                               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Origin</label>
                        <select name="origin" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer">
                            <option value="INHOUSE">INHOUSE</option>
                            <option value="OUTHOUSE">OUTHOUSE</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Primary Contact</label>
                        <input type="text" name="contact" placeholder="Mobile Number"
                               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Alternate Contact</label>
                        <input type="text" name="alt_contact" placeholder="Alt Mobile Number"
                               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Address</label>
                        <input type="text" name="address" placeholder="Full Address"
                               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <div>
                <label class="flex items-center gap-3 cursor-pointer mb-6 select-none w-fit">
                    <input type="checkbox" name="include_project" id="check_project" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition">
                    <span class="font-bold text-sm text-blue-700 uppercase">Add Project</span>
                </label>
                
                <div id="project_section" class="hidden p-6 bg-blue-50/30 rounded-xl border border-blue-100 transition-all">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Project Name</label>
                            <input type="text" name="p_name" placeholder="e.g. Inventory System"
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
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Manager Contact</label>
                            <input type="text" name="p_manager_contact" placeholder="Contact No"
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
                </div>
            </div>

            <div>
                <label class="flex items-center gap-3 cursor-pointer mb-6 select-none w-fit">
                    <input type="checkbox" name="include_smm" id="check_smm" class="w-5 h-5 rounded border-gray-300 text-pink-600 focus:ring-pink-500 transition">
                    <span class="font-bold text-sm text-pink-700 uppercase">Add Social Media</span>
                </label>
                
                <div id="smm_section" class="hidden p-6 bg-pink-50/30 rounded-xl border border-pink-100 transition-all">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Management Charge (₹)</label>
                            <input type="number" name="s_mgmt" placeholder="0.00"
                                   class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 outline-none transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Remark / Description</label>
                            <input type="text" name="s_desc" placeholder="e.g. FB, Insta, Youtube"
                                   class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 outline-none transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl hover:bg-slate-800 transition text-sm shadow-lg shadow-slate-900/10 transform active:scale-[0.99]">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const toggle = (chk, sec) => {
        const c = document.getElementById(chk), s = document.getElementById(sec);
        const up = () => s.classList.toggle('hidden', !c.checked);
        c.addEventListener('change', up); up();
    };
    toggle('check_project', 'project_section'); toggle('check_smm', 'smm_section');
</script>

<?php include 'layout_footer.php'; ?>