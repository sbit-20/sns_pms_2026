<?php
// modules/invoices/generate_invoice.php

// 1. Update Path to config: Go up two levels to root, then into config folder
require_once '../../config/config.php';

// Check if we are editing an existing invoice
$edit_id = isset($_GET['edit_id']) ? $_GET['edit_id'] : 0;
$is_edit = ($edit_id > 0);

// Get the bill type from URL (passed from dashboard)
$bill_type = isset($_GET['type']) ? $_GET['type'] : 'all';

$is_project_invoice = false;
$is_smm_invoice = false;

if ($is_edit) {
    // --- EDIT MODE ---
    $inv = $pdo->query("SELECT * FROM invoices WHERE invoice_id = $edit_id")->fetch();
    if (!$inv) die("Invoice not found.");
    $client_id = $inv['client_id'];
    
    // Detection logic
    if ((isset($inv['service_type']) && $inv['service_type'] == 'P') || stripos($inv['invoice_number'], '/P/') !== false) {
        $is_project_invoice = true;
    } elseif ((isset($inv['service_type']) && $inv['service_type'] == 'SM') || stripos($inv['invoice_number'], '/SM/') !== false) {
        $is_smm_invoice = true;
    }
    
    $invoice_items = $pdo->query("SELECT * FROM invoice_items WHERE invoice_id = $edit_id ORDER BY item_id ASC")->fetchAll();
    $title = "Edit Invoice";
    $btn_text = "Update Invoice";
} else {
    // --- CREATE MODE ---
    $client_id = isset($_GET['client_id']) ? $_GET['client_id'] : 0;
    // 2. Update Redirect: Point to root dashboard
    if (!$client_id) {
        header("Location: " . BASE_URL . "dashboard.php");
        exit;
    }
    $title = "Generate Invoice";
    $btn_text = "Create Invoice";
    $invoice_items = [];
}

$client = $pdo->query("SELECT * FROM clients WHERE client_id = $client_id")->fetch();

// --- FILTERED FETCHING ---
$projects = (!$is_edit && ($bill_type == 'all' || $bill_type == 'project')) 
    ? $pdo->query("SELECT * FROM projects WHERE client_id = $client_id AND next_renewal_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchAll() 
    : [];

$smm = (!$is_edit && ($bill_type == 'all' || $bill_type == 'smm')) 
    ? $pdo->query("SELECT * FROM smm_services WHERE client_id = $client_id AND next_renewal_date <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)")->fetchAll() 
    : [];

if (!$is_edit) {
    if ($bill_type == 'project' || (count($projects) > 0 && count($smm) == 0)) {
        $is_project_invoice = true;
    } elseif ($bill_type == 'smm') {
        $is_smm_invoice = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        $final_invoice_date = date('Y-m-d'); 
        
        if(!$is_edit) {
            if(isset($_POST['p_ids'])) {
                $pid = $_POST['p_ids'][0];
                $p_data = $pdo->query("SELECT next_renewal_date FROM projects WHERE project_id = $pid")->fetch();
                $final_invoice_date = $p_data['next_renewal_date'];
            } elseif(isset($_POST['s_ids'])) {
                $sid = $_POST['s_ids'][0];
                $s_data = $pdo->query("SELECT next_renewal_date FROM smm_services WHERE smm_id = $sid")->fetch();
                $final_invoice_date = $s_data['next_renewal_date'];
            }
        } else {
            $final_invoice_date = $inv['invoice_date']; 
        }

        $inv_year = date('Y', strtotime($final_invoice_date));
        $inv_month = date('n', strtotime($final_invoice_date));
        $billing_period = ($inv_month >= 4) ? $inv_year . "-" . ($inv_year + 1) : ($inv_year - 1) . "-" . $inv_year;

        $type_flag = 'GEN';
        if (isset($_POST['p_ids'])) $type_flag = 'P';
        elseif (isset($_POST['s_ids'])) $type_flag = 'SM';
        elseif ($is_edit && isset($inv['service_type'])) $type_flag = $inv['service_type'];

        if ($is_edit) {
            $iid = $edit_id;
            $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$iid]);
            $inv_no = $_POST['invoice_number'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO invoices (invoice_number, client_id, invoice_date, total_amount, service_type, billing_period) VALUES ('TEMP', ?, ?, 0, ?, ?)");
            $stmt->execute([$client_id, $final_invoice_date, $type_flag, $billing_period]);
            $iid = $pdo->lastInsertId(); 
            
            $origin_code = ($client['client_origin'] == 'INHOUSE') ? 'IH' : 'OH';
            $type_code = $type_flag; 

            $cur_m = date('n', strtotime($final_invoice_date)); 
            $cur_y = date('Y', strtotime($final_invoice_date));
            $fy_start_date = ($cur_m >= 4) ? $cur_y . "-04-01" : ($cur_y - 1) . "-04-01";

            $search_pattern = "SNSS/$origin_code/$type_code/%";
            $stmtSeq = $pdo->prepare("SELECT invoice_number FROM invoices WHERE invoice_number LIKE ? AND invoice_date >= ? ORDER BY invoice_id DESC LIMIT 1");
            $stmtSeq->execute([$search_pattern, $fy_start_date]);
            $lastInvStr = $stmtSeq->fetchColumn();

            $next_seq = 1; 
            if ($lastInvStr) {
                $parts = explode('/', $lastInvStr);
                $next_seq = intval(end($parts)) + 1; 
            }
            $inv_no = sprintf("SNSS/%s/%s/%03d", $origin_code, $type_code, $next_seq);
        }

        $total_invoice_amount = 0;
        
        if(isset($_POST['p_ids'])) foreach($_POST['p_ids'] as $id) { 
            $qty = 1; 
            $unit_price = floatval($_POST['p_price'][$id]);
            $line_total = $qty * $unit_price;
            $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, amount, qty) VALUES (?,?,?,?)");
            $stmt->execute([$iid, $_POST['p_desc'][$id], $line_total, $qty]); 
            
            if (!$is_edit) {
                $stmtUpdate = $pdo->prepare("UPDATE projects SET amc_base_amount = ?, next_renewal_date = DATE_ADD(next_renewal_date, INTERVAL 1 YEAR) WHERE project_id = ?");
                $stmtUpdate->execute([$unit_price, $id]);
            }
            $total_invoice_amount += $line_total; 
        }
        
        if(isset($_POST['s_ids'])) foreach($_POST['s_ids'] as $id) { 
            $qty = isset($_POST['s_qty'][$id]) ? floatval($_POST['s_qty'][$id]) : 1;
            $unit_price = floatval($_POST['s_price'][$id]);
            $line_total = $qty * $unit_price;
            $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, amount, qty) VALUES (?,?,?,?)");
            $stmt->execute([$iid, $_POST['s_desc'][$id], $line_total, $qty]); 
            
            if (!$is_edit) {
                $stmtUpdate = $pdo->prepare("UPDATE smm_services SET base_charge = ?, next_renewal_date = DATE_ADD(next_renewal_date, INTERVAL 1 MONTH) WHERE smm_id = ?");
                $stmtUpdate->execute([$unit_price, $id]);
            }
            $total_invoice_amount += $line_total; 
        }

        if(isset($_POST['addon_desc'])) {
            foreach($_POST['addon_desc'] as $k => $desc) {
                $unit_price = floatval($_POST['addon_price'][$k]); 
                $qty = isset($_POST['addon_qty'][$k]) ? floatval($_POST['addon_qty'][$k]) : 1;          
                if(!empty($desc) && $unit_price > 0) {
                    $line_total = $unit_price * $qty; 
                    $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, amount, qty) VALUES (?,?,?,?)");
                    $stmt->execute([$iid, $desc, $line_total, $qty]);
                    $total_invoice_amount += $line_total;
                }
            }
        }
        
        $stmtUpdateFinal = $pdo->prepare("UPDATE invoices SET invoice_number = ?, total_amount = ?, service_type = ?, billing_period = ? WHERE invoice_id = ?");
        $stmtUpdateFinal->execute([$inv_no, $total_invoice_amount, $type_flag, $billing_period, $iid]);
        
        $pdo->commit(); 
        // 3. Update Redirect: Point to print page in current folder
        header("Location: invoice_print.php?id=$iid");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}

// 4. Update Path to Header
include_once '../../includes/layout_header.php';
$cnt = 1; 
?>

<div class="max-w-6xl mx-auto space-y-6 mb-20">
    <form method="POST" id="invoiceForm">
        <div class="flex items-end justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold text-stone-800 tracking-tight"><?= $title ?></h1>
                <p class="text-[10px] text-stone-400 uppercase tracking-widest font-bold">Client: <span class="text-stone-700"><?= $client['client_name'] ?></span></p>
            </div>
            
            <div class="flex items-center gap-4">
                <?php if($is_edit): ?>
                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-stone-400 uppercase mb-1">Invoice Number</label>
                    <input type="text" name="invoice_number" value="<?= $inv['invoice_number'] ?>" class="bg-stone-100 border border-stone-200 rounded-xl px-4 py-2 text-sm font-bold text-stone-800 outline-none focus:border-amber-500 focus:bg-white transition-all">
                </div>
                <a href="invoice_list.php" class="bg-white border border-stone-200 text-stone-400 px-4 py-2.5 rounded-xl text-xs font-bold hover:text-red-500 hover:border-red-200 transition-all shadow-sm h-fit">Cancel</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-stone-200 overflow-hidden mt-6">
            <div class="overflow-x-auto min-h-[250px]"> 
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-stone-50/80 border-b border-stone-100 text-stone-400 uppercase text-[11px] tracking-wider font-bold">
                            <th class="p-5 w-16 text-center">S.No.</th>
                            <th class="p-5">Description of Goods</th>
                            <?php if (!$is_project_invoice): ?><th class="p-5 w-24 text-center">Qty</th><?php endif; ?>
                            <th class="p-5 w-40 text-right">Unit Amount</th>
                            <?php if (!$is_project_invoice): ?><th class="p-5 w-40 text-right">Total</th><?php endif; ?>
                            <th class="p-5 w-16 text-center">
                                <?php if (!$is_project_invoice): ?>
                                <button type="button" onclick="addManualItem()" class="bg-amber-500 text-white w-8 h-8 rounded-full shadow-lg shadow-amber-500/30 flex items-center justify-center mx-auto hover:bg-amber-600 transition-all active:scale-90"><i class="fa-solid fa-plus text-xs"></i></button>
                                <?php endif; ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-50" id="invoiceTableBody">
                        
                        <?php foreach($projects as $p): ?>
                        <tr class="hover:bg-amber-50/20 transition-colors item-row">
                            <td class="p-5 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <input type="checkbox" name="p_ids[]" value="<?= $p['project_id'] ?>" checked onchange="calculateGrandTotal()" class="row-checkbox w-4 h-4 text-amber-600 rounded border-stone-300">
                                    <span class="font-bold text-stone-400 sno-label"><?= $cnt++ ?></span>
                                </div>
                            </td>
                            <td class="p-5">
                                <span class="text-[10px] font-bold text-stone-400 uppercase block mb-1">Project Charge (Renewal: <?= date('d M Y', strtotime($p['next_renewal_date'])) ?>)</span>
                                <input value="<?= $p['project_name'] ?>" name="p_desc[<?= $p['project_id'] ?>]" class="w-full bg-transparent font-bold text-stone-800 outline-none">
                            </td>
                            <?php if (!$is_project_invoice): ?>
                            <td class="p-5"><input type="number" name="p_qty[<?= $p['project_id'] ?>]" value="1" oninput="calculateRowTotal(this)" class="qty-input w-full bg-stone-50 border border-stone-200 rounded-lg px-2 py-2 text-center font-bold"></td>
                            <?php endif; ?>
                            <td class="p-5">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 font-bold">₹</span>
                                    <input type="number" name="p_price[<?= $p['project_id'] ?>]" value="<?= $p['amc_base_amount'] ?>" oninput="calculateRowTotal(this)" class="price-input w-full pl-7 pr-3 py-2 bg-stone-50 border border-stone-200 rounded-lg font-bold text-stone-800 text-right outline-none">
                                </div>
                            </td>
                            <?php if (!$is_project_invoice): ?>
                            <td class="p-5 text-right font-bold text-stone-800"><span class="row-total-display">₹ <?= number_format($p['amc_base_amount'], 2) ?></span><input type="hidden" class="row-total-value" value="<?= $p['amc_base_amount'] ?>"></td>
                            <?php else: ?>
                            <input type="hidden" class="row-total-value" value="<?= $p['amc_base_amount'] ?>">
                            <?php endif; ?>
                            <td class="p-5"></td> 
                        </tr>
                        <?php endforeach; ?>

                        <?php foreach($smm as $s): ?>
                        <tr class="hover:bg-amber-50/20 transition-colors item-row">
                            <td class="p-5 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <input type="checkbox" name="s_ids[]" value="<?= $s['smm_id'] ?>" checked onchange="calculateGrandTotal()" class="row-checkbox w-4 h-4 text-amber-600 rounded border-stone-300">
                                    <span class="font-bold text-stone-400 sno-label"><?= $cnt++ ?></span>
                                </div>
                            </td>
                            <td class="p-5">
                                <span class="text-[10px] font-bold text-pink-400 uppercase block mb-1">Social Media (Renewal: <?= date('d M Y', strtotime($s['next_renewal_date'])) ?>)</span>
                                <input value="Social Media Management Charge" name="s_desc[<?= $s['smm_id'] ?>]" class="w-full bg-transparent font-bold text-stone-800 outline-none">
                            </td>
                            <td class="p-5"><input type="number" name="s_qty[<?= $s['smm_id'] ?>]" value="1" oninput="calculateRowTotal(this)" class="qty-input w-full bg-stone-50 border border-stone-200 rounded-lg px-2 py-2 text-center font-bold"></td>
                            <td class="p-5">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 font-bold">₹</span>
                                    <input type="number" name="s_price[<?= $s['smm_id'] ?>]" value="<?= $s['base_charge'] ?>" oninput="calculateRowTotal(this)" class="price-input w-full pl-7 pr-3 py-2 bg-stone-50 border border-stone-200 rounded-lg font-bold text-stone-800 text-right outline-none">
                                </div>
                            </td>
                            <td class="p-5 text-right font-bold text-stone-800"><span class="row-total-display">₹ <?= number_format($s['base_charge'], 2) ?></span><input type="hidden" class="row-total-value" value="<?= $s['base_charge'] ?>"></td>
                            <td class="p-5"></td> 
                        </tr>
                        <?php endforeach; ?>

                        <?php foreach($invoice_items as $item): ?>
                        <tr class="bg-stone-50/30 item-row">
                            <td class="p-5 text-center font-bold text-stone-400 sno-label"><?= $cnt++ ?></td>
                            <td class="p-5"><input type="text" name="addon_desc[]" value="<?= $item['description'] ?>" class="w-full bg-white border border-stone-200 rounded-lg px-3 py-2 text-sm font-bold text-stone-800 outline-none focus:border-amber-400"></td>
                            <?php if (!$is_project_invoice): ?>
                            <td class="p-5"><input type="number" name="addon_qty[]" value="<?= $item['qty'] ?>" oninput="calculateRowTotal(this)" class="qty-input w-full bg-white border border-stone-200 rounded-lg px-2 py-2 text-center font-bold outline-none"></td>
                            <?php endif; ?>
                            <td class="p-5">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 font-bold">₹</span>
                                    <input type="number" name="addon_price[]" value="<?= ($item['qty'] > 0) ? $item['amount']/$item['qty'] : $item['amount'] ?>" oninput="calculateRowTotal(this)" class="price-input w-full pl-7 pr-3 py-2 bg-white border border-stone-200 rounded-lg font-bold text-stone-800 text-right outline-none">
                                </div>
                            </td>
                            <?php if (!$is_project_invoice): ?>
                            <td class="p-5 text-right font-bold text-stone-800"><span class="row-total-display">₹ <?= number_format($item['amount'], 2) ?></span><input type="hidden" class="row-total-value" value="<?= $item['amount'] ?>"></td>
                            <?php else: ?>
                            <input type="hidden" class="row-total-value" value="<?= $item['amount'] ?>">
                            <?php endif; ?>
                            <td class="p-5 text-center">
                                <?php if (!$is_project_invoice): ?>
                                <button type="button" onclick="removeRow(this)" class="text-stone-300 hover:text-red-500 transition-colors"><i class="fa-solid fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-stone-100 p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-stone-50/30">
                <div class="text-stone-400 text-xs font-bold uppercase tracking-[2px]">
                    Confirm and click save to generate the final bill.
                </div>
                
                <div class="flex flex-col items-end w-full md:w-auto">
                    <?php if (!$is_project_invoice): ?>
                    <div class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">Total Amount Payable</div>
                    <div class="text-4xl font-black text-stone-900 tracking-tighter" id="grandTotalDisplay">₹ <?= $is_edit ? number_format($inv['total_amount'], 2) : '0.00' ?></div>
                    <?php endif; ?>
                    
                    <button type="submit" class="mt-6 w-full md:w-auto bg-stone-900 text-white px-12 py-4 rounded-2xl font-bold text-sm hover:bg-black hover:shadow-2xl transition-all flex items-center justify-center gap-3 active:scale-95">
                        <i class="fa-solid fa-file-circle-check"></i> <?= $btn_text ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function updateSerialNumbers() {
    const rows = document.querySelectorAll('#invoiceTableBody tr');
    rows.forEach((row, index) => {
        const snoLabel = row.querySelector('.sno-label');
        if(snoLabel) snoLabel.innerText = index + 1;
    });
}

function calculateRowTotal(el) {
    const row = el.closest('tr');
    const qtyInput = row.querySelector('.qty-input');
    const qty = qtyInput ? parseFloat(qtyInput.value) : 1;
    const priceInput = row.querySelector('.price-input');
    const price = priceInput ? parseFloat(priceInput.value) : 0;
    const total = qty * price;
    
    const rowVal = row.querySelector('.row-total-value');
    if(rowVal) rowVal.value = total;
    
    const rowDisplay = row.querySelector('.row-total-display');
    if(rowDisplay) rowDisplay.innerText = '₹ ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
    
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const cb = row.querySelector('.row-checkbox');
        if (!cb || cb.checked) {
            const val = row.querySelector('.row-total-value');
            grandTotal += val ? parseFloat(val.value) : 0;
        }
    });
    
    const gtDisplay = document.getElementById('grandTotalDisplay');
    if(gtDisplay) gtDisplay.innerText = '₹ ' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
}

function addManualItem() {
    const tbody = document.getElementById('invoiceTableBody');
    const newRow = document.createElement('tr');
    newRow.className = "bg-white item-row border-b border-stone-50";
    newRow.innerHTML = `
        <td class="p-5 text-center font-bold text-stone-400 sno-label">...</td>
        <td class="p-5"><input type="text" name="addon_desc[]" placeholder="Description" class="w-full bg-white border border-stone-200 rounded-lg px-3 py-2 text-sm font-bold text-stone-800 outline-none"></td>
        <td class="p-5"><input type="number" name="addon_qty[]" value="1" min="1" oninput="calculateRowTotal(this)" class="qty-input w-full bg-white border border-stone-200 rounded-lg px-2 py-2 text-center font-bold"></td>
        <td class="p-5 text-right">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 font-bold">₹</span>
                <input type="number" name="addon_price[]" placeholder="0.00" oninput="calculateRowTotal(this)" class="price-input w-full pl-7 pr-3 py-2 bg-white border border-stone-200 rounded-lg font-bold text-stone-800 text-right outline-none">
            </div>
        </td>
        <td class="p-5 text-right font-bold text-stone-800"><span class="row-total-display">₹ 0.00</span><input type="hidden" class="row-total-value" value="0"></td>
        <td class="p-5 text-center"><button type="button" onclick="removeRow(this)" class="text-stone-300 hover:text-red-500 transition-colors"><i class="fa-solid fa-trash"></i></button></td>`;
    tbody.appendChild(newRow);
    updateSerialNumbers();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    updateSerialNumbers();
    calculateGrandTotal();
}

window.onload = function() {
    updateSerialNumbers();
    calculateGrandTotal();
};
</script>
<?php 
// 6. Update Path to Footer
include_once '../../includes/layout_footer.php'; 
?>