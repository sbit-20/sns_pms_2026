<?php
// modules/receipts/receipt_print.php

// 1. Update Path to config: Go up two levels to root, then into config folder
require_once '../../config/config.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$rec = $pdo->query("SELECT * FROM receipts WHERE receipt_id = $id")->fetch();
if(!$rec) die("Receipt not found.");

$inv = $pdo->query("SELECT * FROM invoices WHERE invoice_id = ".$rec['invoice_id'])->fetch();
$client = $pdo->query("SELECT client_name FROM clients WHERE client_id = ".$inv['client_id'])->fetch();

function getIndianCurrency($number) {
    $no = floor($number);
    $point = round($number - $no, 2) * 100;
    $digits_1 = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
        } else $str[] = null;
    }
    $str = array_reverse($str);
    return "INR " . strtoupper(implode('', $str)) . " ONLY";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Receipt #<?= $rec['receipt_number'] ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap');
        body { font-family: 'Arimo', sans-serif; font-size: 14px; -webkit-print-color-adjust: exact; }
        .box-border { border: 1px solid #333; }
        .border-r-black { border-right: 1px solid #333; }
        .border-b-black { border-bottom: 1px solid #333; }
        .watermark { position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%); width: 400px; opacity: 0.08; z-index: 0; pointer-events: none; }
        @media print { .no-print { display: none; } body { padding: 0; margin: 0; } .print-container { box-shadow: none; border: none; width: 100%; max-width: 100%; } }
    </style>
</head>
<body class="bg-gray-200 p-8 flex justify-center min-h-screen">

<div class="print-container bg-white w-[210mm] shadow-xl relative text-slate-900 box-border mx-auto flex flex-col">
    <img src="<?= BASE_URL ?>assets/img/group_bg.png" class="watermark" alt="">

    <div class="text-center font-bold text-lg py-1 border-b-black bg-emerald-50 relative z-10 text-emerald-800">PAYMENT RECEIPT</div>

    <div class="flex border-b-black relative z-10">
        <div class="w-full flex flex-col">
            <div class="flex border-b-black h-full">
                <div class="w-1/2 p-2 border-r-black"><span class="block text-sm font-bold text-slate-600">Receipt No.</span><span class="block font-bold text-md mt-1"><?= $rec['receipt_number'] ?></span></div>
                <div class="w-1/2 p-2"><span class="block text-sm font-bold text-slate-600">Date</span><span class="block font-bold text-md mt-1"><?= date('d-M-Y', strtotime($rec['receipt_date'])) ?></span></div>
            </div>
            <div class="flex-1 p-2">
                <span class="block text-sm font-bold text-slate-600">Ref Invoice No.</span>
                <span class="block font-bold text-md mt-1"><?= $inv['invoice_number'] ?></span>
            </div>
        </div>
    </div>

    <div class="pt-8 pl-5 relative z-10">
        <div class="w-full flex flex-col">
            <div class="flex h-full">
                <div class="w-1/2 p-2"><span class="block text-sm font-bold text-slate-600">Client Name</span><span class="block font-bold text-md mt-1"><?= strtoupper($client['client_name']) ?></span></div>
                <div class="w-1/2 p-2"><span class="block text-sm font-bold text-slate-600">Amount</span><span class="block font-bold text-md mt-1">₹ <?= number_format($rec['amount_paid'],2) ?></span></div>
            </div>
            
            <div class="flex h-full">
                <div class="w-1/2 p-2"><span class="block text-sm font-bold text-slate-600">Payment Mode</span><span class="block font-bold text-md mt-1"><?= strtoupper($rec['payment_mode']) ?></span></div>
                <div class="w-1/2 p-2"><span class="block text-sm font-bold text-slate-600">Transaction Ref / Cheque No</span><span class="block font-bold text-md mt-1"><?= $rec['transaction_ref'] ?></span></div>
            </div>
        </div>
    </div>
   
    <?php 
    // 2. Update File Path Logic: The database stores paths like 'uploads/proof_...'
    // Since this file is in modules/receipts/, we check relative to root.
    if (!empty($rec['receipt_file']) && file_exists('../../' . $rec['receipt_file'])): ?>
    <div class="border-t-black p-4 mt-4 page-break-before">
        <a href="<?= BASE_URL . $rec['receipt_file'] ?>" target="_blank" class="bg-slate-800 text-white px-4 py-2 rounded text-xs font-bold hover:bg-black">View Acknowledgement</a>
    </div>
    <?php endif; ?>

</div>

<div class="fixed bottom-8 right-8 flex gap-4 no-print">
    <button onclick="window.print()" class="bg-blue-600 text-white px-8 py-3 rounded-full shadow-lg font-bold hover:bg-blue-700 transition">Print Receipt</button>
</div>
</body>
</html>