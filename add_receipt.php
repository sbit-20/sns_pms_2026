<?php 
require 'config.php';
include 'layout_header.php'; 

$iid = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : 0;
// We pass origin so we know where to redirect back to (optional, defaults to INHOUSE)
$origin = isset($_GET['origin']) ? $_GET['origin'] : 'INHOUSE';

$inv = $pdo->query("SELECT * FROM invoices WHERE invoice_id = $iid")->fetch();
if(!$inv) {
    echo "<script>alert('Invalid Invoice ID'); window.location.href='dashboard.php';</script>";
    exit;
}

$client = $pdo->query("SELECT * FROM clients WHERE client_id = ".$inv['client_id'])->fetch();
?>

<div class="max-w-xl mx-auto mt-10">
    <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-lg text-slate-800">Add Payment Receipt</h3>
            <p class="text-xs text-slate-500 mt-1">Invoice: <b><?= $inv['invoice_number'] ?></b> | Client: <b><?= $client['client_name'] ?></b></p>
        </div>
        
        <form action="actions.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            <input type="hidden" name="action" value="add_receipt">
            <input type="hidden" name="invoice_id" value="<?= $iid ?>">
            <input type="hidden" name="origin" value="<?= $origin ?>">

            <div class="input-group">
                <input type="date" name="pay_date" value="<?= date('Y-m-d') ?>" class="flutter-input" placeholder=" " required>
                <label class="flutter-label">Payment Date</label>
            </div>

            <div class="input-group">
                <input type="number" name="pay_amount" value="<?= $inv['total_amount'] ?>" class="flutter-input" placeholder=" " readonly>
                <label class="flutter-label">Amount Received (₹)</label>
            </div>

            <div class="input-group">
                <select name="pay_mode" class="flutter-input bg-white">
                    <option value="Cash">Cash</option>
                    <option value="UPI">UPI / PhonePe / GPay</option>
                    <option value="Bank Transfer">Bank Transfer / NEFT</option>
                    <option value="Cheque">Cheque</option>
                </select>
                <label class="flutter-label">Payment Mode</label>
            </div>

            <div class="input-group">
                <input type="text" name="pay_ref" class="flutter-input" placeholder=" ">
                <label class="flutter-label">Transaction Ref / Cheque No (Optional)</label>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Payment Proof (Screenshot/PDF)</label>
                <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-sm text-slate-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-xs file:font-semibold
                    file:bg-emerald-50 file:text-emerald-700
                    hover:file:bg-emerald-100 transition
                "/>
                <p class="text-[10px] text-slate-400 mt-1">Allowed: JPG, PNG, PDF</p>
            </div>

            <button class="w-full bg-emerald-600 text-white font-bold py-3.5 rounded-lg text-sm shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition transform active:scale-[0.99]">
                Save Receipt
            </button>
        </form>
    </div>
</div>
<?php include 'layout_footer.php'; ?>