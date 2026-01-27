<?php
// modules/invoices/invoice_print.php

// 1. Update Path to config: Go up two levels to root, then into config folder
require_once '../../config/config.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$inv = $pdo->query("SELECT * FROM invoices WHERE invoice_id = $id")->fetch();
if (!$inv) die("Invoice not found.");

$client = $pdo->query("SELECT * FROM clients WHERE client_id = " . $inv['client_id'])->fetch();
$items = $pdo->query("SELECT * FROM invoice_items WHERE invoice_id = $id ORDER BY item_id ASC")->fetchAll();

// Detection logic
$is_smm_invoice = (isset($inv['service_type']) && $inv['service_type'] == 'SM') || (strpos($inv['invoice_number'], '/SM/') !== false);
$is_project_invoice = (isset($inv['service_type']) && $inv['service_type'] == 'P') || (strpos($inv['invoice_number'], '/P/') !== false);

function getIndianCurrency($number)
{
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
            $str[] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
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
    <title>Invoice #<?= $inv['invoice_number'] ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            -webkit-print-color-adjust: exact;
        }
        .hr { background: red; min-height: 1px; }
        .print-footer { background-color: red; color: #fff; width: 100%; height: 25px; padding: 2px; text-align: center; }
        .box-border { margin: 10px; border: 1px solid #000; }
        .border-r-black { border-right: 1px solid #000; }
        .border-b-black { border-bottom: 1px solid #000; }
        .border-t-black { border-top: 1px solid #000; }
        .watermark { position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%); width: 400px; opacity: 0.4; z-index: 0; pointer-events: none; }
        .print-header, .print-footer { display: none; }

        @media print {
            @page { size: A4; margin: 10mm; }
            .print-header, .print-footer { display: block; position: fixed; left: 0; right: 0; }
            .print-header { top: 0; }
            .print-footer { bottom: 0; }
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; position: relative; }
            .print-container { box-shadow: none !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>

<body class="p-4 flex justify-center min-h-screen">
    <div class="print-container w-full relative text-slate-900">
        <div class="watermark flex flex-col items-center justify-center">
            <img src="<?= BASE_URL ?>assets/img/group_bg.png" alt="Sun & Sun Group">
        </div>

        <div class="flex relative print-header">
            <div class="w-3/4 pt-3 pb-3">
                <img src="<?= BASE_URL ?>assets/img/snss.png" alt="Sun & Sun Solutions" style="height: 80px">
            </div>
            <div class="text-sm leading-5 text-black-700">
                First Floor, Laxmi Bhavan,<br>Opp. Sun & Sun Jewellers Parking, Sadar Bazar, Raipur (CG) 492001<br>Email: snssraipur@gmail.com | Web: snssolutions.in <br> Mob. & Whatsapp 95755 93333
            </div>
        </div>
        <div class="hr"></div>

        <div class="text-center font-bold text-lg pt-4 relative z-10">INVOICE</div>

        <div class="box-border">
            <div class="flex relative z-10">
                <div class="w-1/2 p-2 border-b-black border-r-black">
                    <h1 class="font-bold text-lg uppercase">Sun And Sun Solutions</h1>
                    <div class="text-xs leading-5 mt-1 text-black-700">
                        First Floor, Laxmi Bhavan,<br>Opp. Sun & Sun Jewellers Parking,<br>Sadar Bazar, Raipur (CG) 492001<br>Email: snssraipur@gmail.com | Web: snssolutions.in<br>Mob. 95755 93333
                    </div>   
                </div>
                <div class="w-1/2 flex flex-col">
                    <div class="flex border-b-black">
                        <div class="w-1/2 p-2 border-r-black">
                            <span class="block text-sm text-black-600">Invoice No.</span>
                            <span class="block font-bold text-md mt-1"><?= $inv['invoice_number'] ?></span>
                        </div>
                        <div class="w-1/2 p-2">
                            <span class="block text-sm text-black-600">Dated</span>
                            <span class="block font-bold text-md mt-1"><?= strtoupper(date('d-M-Y', strtotime($inv['invoice_date']))) ?></span></div>
                    </div>
                </div>
            </div>

            <div class="flex border-b-black relative z-10">
                <div class="w-1/2 border-r-black p-2">
                    <span class="text-sm text-black-600">To</span>
                    <div class="font-bold text-lg"><?= strtoupper($client['client_name']) ?></div>
                    <div class="text-sm text-black-700 w-2/3"><?= $client['address'] ?><br><?= $client['contact_number'] ?></div>
                </div>
            </div>

            <div class="relative z-10">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-black text-sm">
                            <th class="border-r-black p-2 w-12 text-center font-bold">S.No.</th>
                            <th class="border-r-black p-2 font-bold">Description of Goods</th>
                            <?php if ($is_smm_invoice): ?>
                                <th class="border-r-black p-2 w-16 font-bold">Qty</th>
                                <th class="border-r-black p-2 w-28 font-bold">Amount</th>
                            <?php endif; ?>
                            <th class="p-2 w-28 font-bold text-right"><?= $is_smm_invoice ? 'Total' : 'Amount' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($is_project_invoice): ?>
                            <tr class="text-sm">
                                <td class="border-r-black pt-2 text-center align-top font-bold">1.</td>
                                <td class="border-r-black pt-2 pl-2 align-top font-bold text-black-800 text-[15px]">Annual Maintenance Charge</td>
                                <td class="pt-2 pr-2 text-right align-top font-bold"></td>
                            </tr>

                            <?php
                            $sub_count = 1;
                            $total_items = count($items);
                            foreach ($items as $item):
                            ?>
                                <tr class="text-sm">
                                    <td class="border-r-black pt-1 text-center align-top"></td>
                                    <td class="border-r-black pt-1 pl-8 align-top text-black-800">
                                        <?php if ($total_items > 1) echo $sub_count++ . ". "; ?>
                                        <?= $item['description'] ?>
                                    </td>
                                    <td class="pt-1 pr-2 text-right align-top font-medium">₹ <?= number_format($item['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>

                            <?php $count = 1;
                            foreach ($items as $item):
                                $qty = isset($item['qty']) && $item['qty'] > 0 ? $item['qty'] : 1;
                                $unit_price = $item['amount'] / $qty;
                            ?>
                                <tr class="text-sm">
                                    <td class="border-r-black pt-2 text-center align-top"><?= $count++ ?>.</td>
                                    <td class="border-r-black pt-2 pl-2 align-top font-bold text-black-800"><?= $item['description'] ?></td>
                                    <?php if ($is_smm_invoice): ?>
                                        <td class="border-r-black pt-2 text-center align-top"><?= str_pad($qty, 2, '0', STR_PAD_LEFT) ?></td>
                                        <td class="border-r-black pt-2 pr-2 text-right align-top">₹ <?= number_format($unit_price) ?></td>
                                    <?php endif; ?>
                                    <td class="pt-2 pr-2 text-right align-top font-bold">₹ <?= number_format($item['amount']) ?></td>
                                </tr>
                            <?php endforeach; ?>

                        <?php endif; ?>

                        <?php
                        $rows_to_fill = 8 - ($is_project_invoice ? (count($items) + 1) : count($items));
                        if ($rows_to_fill < 0) $rows_to_fill = 0;
                        for ($i = 0; $i < $rows_to_fill; $i++):
                        ?>
                            <tr>
                                <td class="border-r-black pt-2">&nbsp;</td>
                                <td class="border-r-black pt-2">&nbsp;</td>
                                <?php if ($is_smm_invoice): ?>
                                    <td class="border-r-black pt-2">&nbsp;</td>
                                    <td class="border-r-black pt-2">&nbsp;</td>
                                <?php endif; ?>
                                <td class="pt-2">&nbsp;</td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                    <tfoot class="border-t-black border-b-black">
                        <tr>
                            <td colspan="<?= $is_smm_invoice ? '4' : '2' ?>" class="p-2 text-right font-bold border-r-black">Total</td>
                            <td class="p-2 text-right font-bold">₹ <?= number_format($inv['total_amount'], 2) ?> /-</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="border-b-black p-3 relative z-10">
                <div class="flex w-full">
                    <div class="text-sm w-1/2 text-slate-600 block">Amount Chargeable (in words)</div>
                    <div class="text-right w-1/2 text-xs pr-2 pt-1 pb-2">E.& O.E</div>
                </div>
                <span class="font-bold uppercase text-slate-800"><?= getIndianCurrency($inv['total_amount']) ?></span>
            </div>

            <div class="flex relative z-10">
                <div class="w-3/5 pl-4 pt-3 border-r-black ">
                    <div class="text-sm mb-3">
                        <span class="block mb-2 text-slate-600">Company Bank Details</span>
                        <div class="flex mb-1"><span class="w-32 font-medium">Bank Name</span><span class="font-bold">: YES BANK</span></div>
                        <div class="flex mb-1"><span class="w-32 font-medium">A/c No.</span><span class="font-bold">: 004763700001292</span></div>
                        <div class="flex"><span class="w-32 font-medium">Branch & IFS Code</span><span class="font-bold">: YESB0000047</span></div>
                    </div>
                </div>
                <div class="w-2/5 flex flex-col justify-between">
                    <div class="p-2 text-right font-bold text-sm">for SUN AND SUN SOLUTIONS</div>
                    <div class="h-10"></div>
                    <div class="p-2 text-right text-sm font-bold mb-2 pr-4">Authorized Signatory</div>
                </div>
            </div>
        </div>

        <div class="text-center text-xs mt-5 text-slate-500">This is a Computer Generated Invoice Dosen't Required Any Signature</div>
        <div class="print-footer">Application Development | Web designing | Mobile App | Social Media Marketing | IT Consultancy</div>
    </div>

    <script>
        window.onload = function() { window.print(); }
        window.onafterprint = function() { window.history.back(); }
    </script>
</body>
</html>