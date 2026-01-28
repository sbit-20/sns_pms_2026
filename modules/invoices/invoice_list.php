<?php
// modules/invoices/invoice_list.php

// 1. Update Path to config: Go up two levels to root, then into config folder
require_once '../../config/config.php';

$origin = isset($_GET['origin']) ? $_GET['origin'] : 'INHOUSE';
$origin_title = ($origin == 'INHOUSE') ? 'Inhouse Invoices' : 'Outhouse Invoices';

// Theme Colors based on Origin
$theme_color = ($origin == 'INHOUSE') ? 'emerald' : 'blue';
$theme_text = ($origin == 'INHOUSE') ? 'text-emerald-600' : 'text-blue-600';
$theme_bg_light = ($origin == 'INHOUSE') ? 'bg-emerald-50' : 'bg-blue-50';
$theme_border = ($origin == 'INHOUSE') ? 'focus:border-emerald-500' : 'focus:border-blue-500';
$theme_ring = ($origin == 'INHOUSE') ? 'focus:ring-emerald-500/10' : 'focus:ring-blue-500/10';

// SQL: LEFT JOIN receipts to check if payment exists
// $sql = "SELECT i.*, c.client_name, c.client_origin, r.receipt_id 
//         FROM invoices i 
//         JOIN clients c ON i.client_id = c.client_id 
//         LEFT JOIN receipts r ON i.invoice_id = r.invoice_id
//         WHERE c.client_origin = ? 
//         ORDER BY i.invoice_id DESC";
// $stmt = $pdo->prepare($sql);
// $stmt->execute([$origin]);
// $invoices = $stmt->fetchAll();


$sql = "SELECT i.*, c.client_name, c.client_origin, r.receipt_id 
        FROM invoices i 
        JOIN clients c ON i.client_id = c.client_id 
        LEFT JOIN receipts r ON i.invoice_id = r.invoice_id
        WHERE c.client_origin = ? 
        ORDER BY i.invoice_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$origin]);
$invoices = $stmt->fetchAll();
// Calculate Totals for quick stats
$total_inv = count($invoices);
$total_amt = 0;
$pending_count = 0;
foreach($invoices as $inv) {
    $total_amt += $inv['total_amount'];
    if(!$inv['receipt_id']) $pending_count++;
}

// 2. Update Path to Header
include_once '../../includes/layout_header.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css">

<div class="max-w-7xl mx-auto space-y-6 mb-12">

    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-3">
                <?= $origin_title ?>
                <span class="text-xs font-bold px-2 py-1 rounded-md bg-slate-100 text-slate-500 border border-slate-200"><?= $total_inv ?> Records</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Manage billing and track payments for <?= strtolower($origin) ?> clients.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <div class="relative group flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:<?= $theme_text ?> transition-colors text-xs"></i>
                <input type="text" id="customSearch" placeholder="Search Invoice No or Client..." class="w-full sm:w-72 bg-white border border-slate-200 text-sm pl-9 pr-4 py-2.5 rounded-xl outline-none <?= $theme_border ?> focus:ring-4 <?= $theme_ring ?> transition-all shadow-sm">
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-4 border-b border-slate-100 pb-4">
        <div class="bg-slate-100 p-1 rounded-xl inline-flex">
            <button onclick="filterType('all', this)" class="filter-tab active px-5 py-2 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-800">All</button>
            <button onclick="filterType('project', this)" class="filter-tab px-5 py-2 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-800">Projects</button>
            <button onclick="filterType('smm', this)" class="filter-tab px-5 py-2 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-800">Social Media</button>
        </div>
        
        <div class="text-xs text-slate-400 font-medium ml-auto hidden sm:block">
            Total Pending: <span class="text-slate-800 font-bold"><?= $pending_count ?></span>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table id="invoiceTable" class="w-full text-left border-collapse" style="width:100%">
                <thead class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="p-5 pl-6 w-16 text-center">S.No.</th>
                        <th class="p-5 w-64">Invoice No</th>
                        <th class="p-5">Client Details</th>
                        <th class="p-5 w-32">Date</th>
                        <th class="p-5 text-right w-32">Amount</th>
                        <th class="p-5 text-center w-32">Status</th>
                        <th class="p-5 text-right pr-6 w-24">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-50">
                    <?php $cnt = 1; foreach($invoices as $inv): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        
                        <td class="p-5 pl-6 text-center font-bold text-slate-400 text-xs">
                            <?= $cnt++ ?>
                        </td>

                        <td class="p-5">
                            <span class="font-bold text-slate-800 text-[14px]"><?= $inv['invoice_number'] ?></span>
                        </td>

                        <td class="p-5">
                            <div class="font-bold text-slate-800 text-[14px]"><?= $inv['client_name'] ?></div>
                        </td>

                        <td class="p-5">
                            <div class="text-xs font-medium text-slate-500">
                                <?= date('d M Y', strtotime($inv['invoice_date'])) ?>
                            </div>
                        </td>

                        <td class="p-5 text-right">
                            <div class="font-bold text-slate-700">₹<?= number_format($inv['total_amount']) ?></div>
                        </td>
                        
                        <td class="p-5 text-center">
                            <?php if($inv['receipt_id']): ?>
                                <div class="inline-flex flex-col items-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <i class="fa-solid fa-check-circle text-[9px]"></i> PAID
                                    </span>
                                    <a href="<?= BASE_URL ?>modules/receipts/receipt_print.php?id=<?= $inv['receipt_id'] ?>" target="_blank" class="mt-1 text-[10px] font-bold text-slate-400 hover:text-blue-600 hover:underline transition-colors">
                                        View Receipt
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="inline-flex flex-col items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100">
                                        PENDING
                                    </span>
                                    <a href="<?= BASE_URL ?>modules/receipts/add_receipt.php?invoice_id=<?= $inv['invoice_id'] ?>&origin=<?= $origin ?>" class="text-[10px] font-bold bg-slate-800 hover:bg-black text-white px-3 py-1 rounded-md shadow-sm transition-transform active:scale-95">
                                        Pay Now
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td class="p-5 text-right pr-6">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= BASE_URL ?>modules/invoices/generate_invoice.php?edit_id=<?= $inv['invoice_id'] ?>" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-amber-600 hover:border-amber-200 hover:bg-amber-50 transition-all shadow-sm" title="Edit Invoice">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </a>

                                <a href="<?= BASE_URL ?>modules/invoices/invoice_print.php?id=<?= $inv['invoice_id'] ?>" target="_blank" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm" title="Print Invoice">
                                    <i class="fa-solid fa-print text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="customFooter" class="bg-white border-t border-slate-100 p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div id="paginationInfo" class="text-xs text-slate-500 font-medium"></div>
            <div id="paginationControls" class="flex items-center gap-2">
                <button id="btnPrev" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-500 hover:bg-slate-50 hover:text-slate-800 disabled:opacity-50 transition-colors">
                    <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                </button>
                <div id="pageNumbers" class="flex items-center gap-1"></div>
                <button id="btnNext" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-500 hover:bg-slate-50 hover:text-slate-800 disabled:opacity-50 transition-colors">
                    Next <i class="fa-solid fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<style>
    /* Clean up DataTables defaults */
    .dataTables_wrapper .dataTables_paginate, 
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_length { display: none !important; }
    
    table.dataTable.no-footer { border-bottom: 0 !important; }

    /* Filter Tab Active State */
    .filter-tab.active { background-color: white; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); color: #0f172a; }
    
    /* Pagination Active State */
    .page-num-btn.active { background-color: #0f172a; color: white; border-color: #0f172a; }
</style>

<script>
$(document).ready(function() {
    var table = $('#invoiceTable').DataTable({
        pageLength: 10,
        order: [], 
        columnDefs: [ 
            { orderable: false, targets: [6] } 
        ],
        language: { emptyTable: "No invoices found." },

        drawCallback: function(settings) {
            var api = this.api();
            var info = api.page.info();
            
            var start = info.recordsTotal > 0 ? info.start + 1 : 0;
            var end = info.end;
            var total = info.recordsDisplay;
            
            $('#paginationInfo').html(`Showing <span class="font-bold text-slate-700">${start}</span> to <span class="font-bold text-slate-700">${end}</span> of <span class="font-bold text-slate-700">${total}</span> invoices`);

            if (info.pages <= 1) {
                $('#paginationControls').hide();
            } else {
                $('#paginationControls').show();
                
                if (info.page === 0) $('#btnPrev').hide();
                else {
                    $('#btnPrev').show().off('click').on('click', function() { api.page('previous').draw('page'); });
                }

                if (info.page === info.pages - 1) $('#btnNext').hide();
                else {
                    $('#btnNext').show().off('click').on('click', function() { api.page('next').draw('page'); });
                }

                var pageHtml = '';
                for (var i = 0; i < info.pages; i++) {
                    var activeClass = (i === info.page) ? 'active' : 'bg-white text-slate-500 hover:bg-slate-50';
                    pageHtml += `<button class="page-num-btn w-8 h-8 rounded-lg border border-slate-200 text-xs font-bold transition-all ${activeClass}" data-page="${i}">${i + 1}</button>`;
                }
                $('#pageNumbers').html(pageHtml);
                
                $('.page-num-btn').on('click', function() {
                    api.page($(this).data('page')).draw('page');
                });
            }
        }
    });

    $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
});

function filterType(type, btn) {
    document.querySelectorAll('.filter-tab').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
    
    var table = $('#invoiceTable').DataTable();
    if (type === 'all') table.column(1).search('').draw();
    else if (type === 'project') table.column(1).search('/P/').draw();
    else if (type === 'smm') table.column(1).search('/SM/').draw();
}
</script>

<?php 
// 7. Update Path to Footer
include_once '../../includes/layout_footer.php'; 
?>