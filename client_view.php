<?php
require 'config.php';
$id = isset($_GET['id']) ? $_GET['id'] : 0;
if (!$id) {
    header("Location: clients.php");
    exit;
}

// 1. Fetch Basic Client & Service Data
$client = $pdo->query("SELECT * FROM clients WHERE client_id = $id")->fetch();
$projects = $pdo->query("SELECT * FROM projects WHERE client_id = $id")->fetchAll();
$smm = $pdo->query("SELECT * FROM smm_services WHERE client_id = $id")->fetchAll();

// 2. Fetch Invoices & Check Payment Status
$sql_inv = "SELECT i.*, r.receipt_id 
            FROM invoices i 
            LEFT JOIN receipts r ON i.invoice_id = r.invoice_id
            WHERE i.client_id = $id 
            ORDER BY i.invoice_id DESC";
$invoices = $pdo->query($sql_inv)->fetchAll();

include 'layout_header.php';
?>

<div class="max-w-6xl mx-auto space-y-8 mb-12">
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-8 py-6">
            <div class="flex flex-col md:flex-row gap-6 items-start">
                
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3 mb-1">
                        <h1 class="text-2xl font-bold text-slate-800"><?= $client['client_name'] ?></h1>
                        
                        <?php if($client['client_origin'] == 'INHOUSE'): ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 tracking-wide">INHOUSE</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100 tracking-wide">OUTHOUSE</span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-sm text-slate-500 flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-location-dot text-slate-400 text-xs"></i> 
                        <?= $client['address'] ?: 'No Address Provided' ?>
                    </p>

                    <div class="flex flex-wrap gap-4 md:gap-8 border-t border-slate-100 pt-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Primary Contact</div>
                                <div class="font-bold text-slate-700 text-sm"><?= $client['contact_number'] ?></div>
                            </div>
                        </div>
                        
                        <?php if (!empty($client['alt_contact_number'])): ?>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500">
                                <i class="fa-solid fa-mobile-screen text-xs"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alt Contact</div>
                                <div class="font-bold text-slate-700 text-sm"><?= $client['alt_contact_number'] ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto mt-2">
                    <a href="generate_invoice.php?client_id=<?= $id ?>" class="bg-slate-900 text-white text-xs px-5 py-2.5 rounded-xl font-bold hover:bg-black shadow-lg shadow-slate-900/10 transition-transform active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-invoice"></i> Generate Bill
                    </a>
                    
                    <div class="flex gap-2">
                        <a href="add_project.php?client_id=<?= $id ?>" class="flex-1 bg-white border border-slate-200 text-slate-600 text-xs px-4 py-2.5 rounded-xl font-bold hover:bg-slate-50 hover:text-blue-600 hover:border-blue-200 transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus"></i> Project
                        </a>
                        <?php if (!$smm): ?>
                        <a href="add_smm.php?client_id=<?= $id ?>" class="flex-1 bg-white border border-slate-200 text-slate-600 text-xs px-4 py-2.5 rounded-xl font-bold hover:bg-slate-50 hover:text-pink-600 hover:border-pink-200 transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus"></i> SMM
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-blue-500"></i> Active Projects
            </h2>
            
            <?php if (empty($projects)): ?>
                <div class="bg-slate-50 rounded-xl p-8 text-center border border-dashed border-slate-200">
                    <p class="text-slate-400 text-sm italic">No projects found.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($projects as $p): ?>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-blue-50 to-transparent -mr-8 -mt-8 rounded-full"></div>
                    
                    <div class="flex justify-between items-start mb-4 relative">
                        <div>
                            <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-1 block"><?= $p['project_type'] ?></span>
                            <h3 class="font-bold text-slate-800 text-base"><?= $p['project_name'] ?></h3>
                        </div>
                        <div class="text-right">
                             <div class="text-xs text-slate-400 font-medium">Renewal Date</div>
                             <div class="text-sm font-bold text-slate-700"><?= date('d M, Y', strtotime($p['next_renewal_date'])) ?></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-y-3 gap-x-6 text-xs border-t border-slate-50 pt-4 relative">
                        <div>
                            <span class="text-slate-400 block mb-0.5">AMC Amount</span>
                            <span class="font-bold text-slate-700">₹<?= number_format($p['amc_base_amount']) ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Tech Stack</span>
                            <span class="font-bold text-slate-700"><?= $p['tech_name'] ?: 'N/A' ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Current Ver.</span>
                            <span class="font-bold text-slate-700"><?= $p['current_version'] ?: '1.0' ?></span>
                        </div>
                        
                        <div>
                            <span class="text-slate-400 block mb-0.5">Manager</span>
                            <div class="font-bold text-slate-700"><?= $p['manager_name'] ?></div>
                            <?php if (!empty($p['manager_contact_no'])): ?>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <i class="fa-brands fa-whatsapp text-emerald-500 text-[10px]"></i>
                                    <a href="https://wa.me/91<?= $p['manager_contact_no'] ?>" target="_blank" class="text-xs text-slate-500 hover:text-emerald-600 hover:underline">
                                        <?= $p['manager_contact_no'] ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-hashtag text-pink-500"></i> Social Media
            </h2>
            
            <?php if (empty($smm)): ?>
                <div class="bg-slate-50 rounded-xl p-8 text-center border border-dashed border-slate-200">
                    <p class="text-slate-400 text-sm italic">No social media services.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($smm as $s): ?>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-pink-50 to-transparent -mr-8 -mt-8 rounded-full"></div>

                    <div class="flex justify-between items-start mb-4 relative">
                        <div>
                            <span class="text-[10px] font-bold text-pink-500 uppercase tracking-wider mb-1 block">Monthly Plan</span>
                            <h3 class="font-bold text-slate-800 text-base"><?= $s['ad_description'] ?></h3>
                        </div>
                        <div class="text-right">
                             <div class="text-xs text-slate-400 font-medium">Next Renewal</div>
                             <div class="text-sm font-bold text-pink-600"><?= date('d M, Y', strtotime($s['next_renewal_date'])) ?></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-50 pt-4 relative">
                        <div>
                            <span class="text-xs text-slate-400 block mb-0.5">Management Charge</span>
                            <span class="text-sm font-bold text-slate-700">₹<?= number_format($s['base_charge']) ?></span>
                        </div>
                        <span class="px-2 py-1 bg-pink-50 text-pink-600 text-[10px] font-bold rounded uppercase tracking-wide">Active</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Billing History
        </h2>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="p-5 w-16 text-center">S.No.</th>
                            <th class="p-5 w-48">Invoice No</th>
                            <th class="p-5 w-32">Date</th>
                            <th class="p-5 text-right w-32">Amount</th>
                            <th class="p-5 text-center w-32">Status</th>
                            <th class="p-5 text-right pr-6 w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-50">
                        <?php 
                        $cnt = 1;
                        foreach ($invoices as $inv): 
                        ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-5 text-center font-bold text-slate-400 text-xs"><?= $cnt++ ?></td>
                            
                            <td class="p-5">
                                <span class="font-bold text-slate-800 text-xs"><?= $inv['invoice_number'] ?></span>
                            </td>
                            
                            <td class="p-5 text-slate-500 text-xs font-medium">
                                <?= date('d M Y', strtotime($inv['invoice_date'])) ?>
                            </td>
                            
                            <td class="p-5 text-right font-bold text-slate-700">
                                ₹<?= number_format($inv['total_amount']) ?>
                            </td>
                            
                            <td class="p-5 text-center">
                                <?php if($inv['receipt_id']): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <i class="fa-solid fa-check-circle text-[9px]"></i> PAID
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100">
                                        PENDING
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="p-5 text-right pr-6">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="generate_invoice.php?edit_id=<?= $inv['invoice_id'] ?>" class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-600 hover:border-amber-200 hover:bg-amber-50 transition-all shadow-sm" title="Edit Invoice">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>

                                    <a href="invoice_print.php?id=<?= $inv['invoice_id'] ?>" target="_blank" class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm" title="View Invoice">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </a>

                                    <?php if($inv['receipt_id']): ?>
                                        <a href="receipt_print.php?id=<?= $inv['receipt_id'] ?>" target="_blank" class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-emerald-200 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 transition-all shadow-sm" title="View Receipt">
                                            <i class="fa-solid fa-receipt text-xs"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="add_receipt.php?invoice_id=<?= $inv['invoice_id'] ?>&origin=<?= $client['client_origin'] ?>" class="px-2 py-1 rounded-md bg-slate-800 text-white text-[10px] font-bold hover:bg-black transition-colors shadow-sm">
                                            Pay Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if(empty($invoices)): ?>
                            <tr><td colspan="6" class="p-8 text-center text-slate-400 italic text-sm">No billing history found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'layout_footer.php'; ?>