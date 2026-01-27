<?php
// modules/clients/client_view.php
require_once '../../config/config.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;
if (!$id) {
    header("Location: " . BASE_URL . "modules/clients/clients.php");
    exit;
}

// 1. Fetch Client Details
$client = $pdo->query("SELECT * FROM clients WHERE client_id = $id")->fetch();

// 2. Fetch Projects (Now includes documentation columns directly)
$projects = $pdo->query("SELECT * FROM projects WHERE client_id = $id")->fetchAll();

// 3. Fetch SMM Services
$smm = $pdo->query("SELECT * FROM smm_services WHERE client_id = $id")->fetchAll();

// 4. Fetch Billing History
$sql_inv = "SELECT i.*, r.receipt_id 
            FROM invoices i 
            LEFT JOIN receipts r ON i.invoice_id = r.invoice_id
            WHERE i.client_id = $id 
            ORDER BY i.invoice_id DESC";
$invoices = $pdo->query($sql_inv)->fetchAll();

include_once '../../includes/layout_header.php';
?>

<div class="max-w-6xl mx-auto space-y-6 mb-12 animate-in fade-in duration-500">
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex flex-col lg:flex-row justify-between items-start gap-6">
                <div class="flex-1 space-y-3">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight"><?= $client['client_name'] ?></h1>
                        <button onclick="document.getElementById('edit_client_modal').classList.remove('hidden')" class="text-slate-400 hover:text-blue-600 transition-all">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $client['client_origin'] == 'INHOUSE' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-blue-50 text-blue-600 border border-blue-100' ?>">
                            <?= $client['client_origin'] ?>
                        </span>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-slate-500">
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-location-dot text-slate-300"></i>
                            <span class="truncate max-w-xs"><?= $client['address'] ?: 'No Address' ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-phone text-slate-300"></i>
                            <span class="font-semibold text-slate-700"><?= $client['contact_number'] ?></span>
                        </div>
                        <?php if (!empty($client['alt_contact_number'])): ?>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-mobile-screen text-slate-300"></i>
                            <span class="font-semibold text-slate-700"><?= $client['alt_contact_number'] ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 w-full lg:w-auto">
                    <a href="<?= BASE_URL ?>modules/invoices/generate_invoice.php?client_id=<?= $id ?>" class="flex-1 lg:flex-none bg-slate-900 text-white text-xs px-5 py-2.5 rounded-xl font-bold hover:bg-black shadow-md shadow-slate-200 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-invoice opacity-70"></i> Generate Bill
                    </a>
                    <a href="<?= BASE_URL ?>modules/projects/add_project.php?client_id=<?= $id ?>" class="flex-1 lg:flex-none bg-white border border-slate-200 text-slate-600 text-xs px-5 py-2.5 rounded-xl font-bold hover:border-blue-300 hover:text-blue-600 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus opacity-70"></i> Project
                    </a>
                    <?php if (!$smm): ?>
                    <a href="<?= BASE_URL ?>modules/projects/add_smm.php?client_id=<?= $id ?>" class="flex-1 lg:flex-none bg-white border border-slate-200 text-slate-600 text-xs px-5 py-2.5 rounded-xl font-bold hover:border-pink-300 hover:text-pink-600 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus opacity-70"></i> SMM
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2 px-1">
                <i class="fa-solid fa-layer-group text-blue-500"></i> Active Projects
            </h2>
            <?php if (empty($projects)): ?>
                <div class="bg-slate-50/50 rounded-2xl p-8 text-center border-2 border-dashed border-slate-100">
                    <p class="text-slate-400 text-sm italic font-medium">No projects found.</p>
                </div>
            <?php endif; ?>
            <?php foreach ($projects as $p): ?>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-blue-50/50 -mr-10 -mt-10 rounded-full blur-2xl group-hover:bg-blue-100/50 transition-colors"></div>
                    
                    <div class="flex justify-between items-start mb-4 relative">
                        <div>
                            <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-1 block"><?= $p['project_type'] ?></span>
                            <h3 class="font-bold text-slate-800 text-base group-hover:text-blue-600 transition-colors"><?= $p['project_name'] ?></h3>
                        </div>
                        <div class="flex gap-2">
                            <a href="../projects/edit_project.php?project_id=<?= $p['project_id'] ?>" class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-400 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all border border-transparent hover:border-blue-100">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </a>
                            <div class="text-right">
                                 <div class="text-[10px] text-slate-400 font-bold uppercase">Renewal</div>
                                 <div class="text-sm font-bold text-slate-700"><?= date('d M, Y', strtotime($p['next_renewal_date'])) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs border-t border-slate-50 pt-4 relative">
                        <div class="space-y-1">
                            <span class="text-slate-400 block font-medium">AMC Amount</span>
                            <span class="font-bold text-slate-700 text-sm">₹<?= number_format($p['amc_base_amount']) ?></span>
                        </div>
                        <div class="space-y-1 text-right">
                            <span class="text-slate-400 block font-medium">Technology</span>
                            <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-600 font-bold"><?= $p['tech_name'] ?: 'N/A' ?></span>
                        </div>
                        <div class="col-span-2 flex justify-between items-end border-t border-slate-50 pt-3">
                            <div>
                                <span class="text-slate-400 block mb-1 font-medium">Manage By</span>
                                <div class="font-bold text-slate-700"><?= $p['manager_name'] ?></div>
                                <?php if (!empty($p['manager_contact_no'])): 
                                    $c_list = explode(',', $p['manager_contact_no']);
                                    foreach($c_list as $single_no):
                                        $single_no = trim($single_no); if(empty($single_no)) continue;
                                        $clean_no = preg_replace('/\D/', '', $single_no);
                                ?>
                                    <a href="https://wa.me/91<?= $clean_no ?>" target="_blank" class="inline-flex items-center gap-1.5 mt-1 text-[11px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full hover:bg-emerald-100 transition-colors font-bold">
                                        <i class="fa-brands fa-whatsapp"></i> <?= $single_no ?>
                                    </a>
                                <?php endforeach; endif; ?>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-400 block mb-1 font-medium">Version</span>
                                <span class="font-bold text-slate-700 italic"><?= $p['current_version'] ?: '1.0' ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-50 relative">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Documentation</span>
                            <button onclick="document.getElementById('doc_modal_<?= $p['project_id'] ?>').classList.remove('hidden')" class="text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1 rounded-lg font-bold transition-all">
                                <i class="fa-solid <?= !empty($p['doc_title']) ? 'fa-pen-to-square' : 'fa-plus' ?> mr-1"></i> <?= !empty($p['doc_title']) ? 'Edit Docs' : 'Add Docs' ?>
                            </button>
                        </div>

                        <?php if (!empty($p['doc_title'])): ?>
                            <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm border border-slate-100">
                                        <i class="fa-solid fa-file-lines text-blue-400 text-sm"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700 leading-none mb-1"><?= $p['doc_title'] ?></span>
                                        <div class="flex gap-3">
                                            <?php if(!empty($p['doc_file_path'])): ?>
                                                <a href="<?= BASE_URL . $p['doc_file_path'] ?>" target="_blank" class="text-[10px] font-bold text-blue-500 hover:underline">View PDF</a>
                                            <?php endif; ?>
                                            <?php if(!empty($p['doc_link'])): ?>
                                                <a href="<?= $p['doc_link'] ?>" target="_blank" class="text-[10px] font-bold text-emerald-500 hover:underline">Drive Link</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <form action="../../src/actions.php" method="POST" onsubmit="return confirm('Remove documentation?');">
                                    <input type="hidden" name="action" value="delete_document">
                                    <input type="hidden" name="project_id" value="<?= $p['project_id'] ?>">
                                    <input type="hidden" name="client_id" value="<?= $id ?>">
                                    <button class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="doc_modal_<?= $p['project_id'] ?>" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
                        <h3 class="font-bold text-slate-800 text-lg mb-1"><?= !empty($p['doc_title']) ? 'Update' : 'Add' ?> Documentation</h3>
                        <p class="text-xs text-slate-400 mb-5">Attach project links or upload PDF files.</p>
                        <form action="../../src/actions.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="action" value="add_project_document">
                            <input type="hidden" name="project_id" value="<?= $p['project_id'] ?>">
                            <input type="hidden" name="client_id" value="<?= $id ?>">
                            
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Title</label>
                                <input type="text" name="doc_title" value="<?= !empty($p['doc_title']) ? $p['doc_title'] : '' ?>" placeholder="e.g. Requirement Doc" required class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-blue-500 transition-all">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Link (Drive/Web)</label>
                                <input type="url" name="doc_link" value="<?= !empty($p['doc_link']) ? $p['doc_link'] : '' ?>" placeholder="https://..." class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-blue-500 transition-all">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Upload File</label>
                                <input type="file" name="doc_file" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            </div>

                            <div class="flex gap-2 pt-2">
                                <button type="button" onclick="document.getElementById('doc_modal_<?= $p['project_id'] ?>').classList.add('hidden')" class="flex-1 px-4 py-2.5 bg-slate-50 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition-all">Cancel</button>
                                <button type="submit" class="flex-1 px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-black shadow-lg shadow-slate-200 transition-all">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2 px-1">
                <i class="fa-solid fa-hashtag text-pink-500"></i> Social Media
            </h2>
            
            <?php if (empty($smm)): ?>
                <div class="bg-slate-50/50 rounded-2xl p-8 text-center border-2 border-dashed border-slate-100">
                    <p class="text-slate-400 text-sm italic font-medium">No services active.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($smm as $s): ?>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-pink-50/50 -mr-10 -mt-10 rounded-full blur-2xl group-hover:bg-pink-100/50 transition-colors"></div>
                    
                    <div class="flex justify-between items-start mb-4 relative">
                        <div class="text-right flex items-center gap-3">
                            <button onclick="document.getElementById('edit_smm_modal_<?= $s['smm_id'] ?>').classList.remove('hidden')" class="text-slate-400 hover:text-pink-600 transition-all">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">Renewal</div>
                                <div class="text-sm font-bold text-pink-600">
                                    <?= date('d M, Y', strtotime($s['next_renewal_date'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 text-xs border-t border-slate-50 pt-4 relative">
                        <div class="flex justify-between items-center bg-slate-50/50 p-3 rounded-xl border border-slate-100">
                            <div>
                                <span class="text-slate-400 block mb-0.5 font-medium">Monthly Charge</span>
                                <span class="font-bold text-slate-700 text-sm">₹<?= number_format($s['base_charge']) ?></span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Service Details</span>
                            <p class="text-xs text-slate-600 bg-white italic border-l-2 border-pink-100 pl-3 py-1">
                                <?= !empty($s['ad_description']) ? htmlspecialchars($s['ad_description']) : 'Standard SMM Plan' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div id="edit_smm_modal_<?= $s['smm_id'] ?>" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
                        <h3 class="font-bold text-slate-800 text-lg mb-4">Edit SMM Service</h3>
                        <form action="../../src/actions.php" method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="edit_smm">
                            <input type="hidden" name="smm_id" value="<?= $s['smm_id'] ?>">
                            <input type="hidden" name="client_id" value="<?= $id ?>">
                            
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Monthly Charge</label>
                                <input type="number" name="s_mgmt" value="<?= $s['base_charge'] ?>" required class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-pink-500 transition-all">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Renewal Date</label>
                                <input type="date" name="s_renewal" value="<?= $s['next_renewal_date'] ?>" required class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-pink-500 transition-all">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Details</label>
                                <textarea name="s_desc" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-pink-500 transition-all"><?= $s['ad_description'] ?></textarea>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="flex-1 py-2.5 bg-slate-50 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition-all">Cancel</button>
                                <button type="submit" class="flex-1 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-black shadow-lg shadow-slate-200 transition-all">Update Service</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2 px-1">
            <i class="fa-solid fa-clock-rotate-left"></i> Billing History
        </h2>
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <tr>
                        <th class="py-4 px-6">Invoice</th>
                        <th class="py-4 px-6">Date</th>
                        <th class="py-4 px-6 text-right">Amount</th>
                        <th class="py-4 px-6 text-center">Payment Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-50">
                    <?php foreach ($invoices as $inv): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="py-4 px-6 font-bold text-slate-800"><?= $inv['invoice_number'] ?></td>
                        <td class="py-4 px-6 text-slate-500 font-medium"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                        <td class="py-4 px-6 text-right font-bold text-slate-700">₹<?= number_format($inv['total_amount']) ?></td>
                        <td class="py-4 px-6 text-center">
                            <?= $inv['receipt_id'] 
                                ? '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">PAID</span>' 
                                : '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100 font-mono tracking-tighter">PENDING</span>' ?>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="<?= BASE_URL ?>modules/invoices/invoice_print.php?id=<?= $inv['invoice_id'] ?>" target="_blank" class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:shadow-sm transition-all shadow-sm">
                                <i class="fa-solid fa-print text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($invoices)): ?>
                        <tr><td colspan="5" class="p-12 text-center text-slate-400 italic text-sm font-medium">No billing history found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="edit_client_modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
        <h3 class="font-bold text-slate-800 text-lg mb-4">Edit Client Profile</h3>
        <form action="../../src/actions.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="edit_client">
            <input type="hidden" name="client_id" value="<?= $id ?>">
            
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Client Name</label>
                <input type="text" name="client_name" value="<?= $client['client_name'] ?>" required class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-blue-500 transition-all">
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Full Address</label>
                <input type="text" name="address" value="<?= $client['address'] ?>" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-blue-500 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Primary Phone</label>
                    <input type="text" name="contact" value="<?= $client['contact_number'] ?>" required class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-blue-500 transition-all">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Alt Phone</label>
                    <input type="text" name="alt_contact" value="<?= $client['alt_contact_number'] ?>" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-blue-500 transition-all">
                </div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Origin</label>
                <select name="origin" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-blue-500 transition-all">
                    <option value="INHOUSE" <?= $client['client_origin'] == 'INHOUSE' ? 'selected' : '' ?>>INHOUSE</option>
                    <option value="OUTHOUSE" <?= $client['client_origin'] == 'OUTHOUSE' ? 'selected' : '' ?>>OUTHOUSE</option>
                </select>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('edit_client_modal').classList.add('hidden')" class="flex-1 py-2.5 bg-slate-50 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition-all">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-black shadow-lg shadow-slate-200 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>      

<?php 
include_once '../../includes/layout_footer.php'; 
?>