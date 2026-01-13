<?php
require 'config.php';
include 'layout_header.php';

// Fetch Clients
$sql = "SELECT * FROM client_view";
$clients = $pdo->query($sql)->fetchAll();

// Calculate Stats for the Top Bar
$total_clients = count($clients);
$inhouse = 0;
$outhouse = 0;
foreach ($clients as $c) {
    if ($c['client_origin'] == 'INHOUSE') $inhouse++;
    else $outhouse++;
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css">

<div class="max-w-7xl mx-auto space-y-8 mb-12">

    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Client Directory</h1>
            <p class="text-xs text-slate-500 mt-1">View and manage your client relationships.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            
            <div class="relative min-w-[140px]">
                <select id="originFilter" class="w-full bg-white border border-slate-200 text-sm pl-4 pr-8 py-2.5 rounded-xl outline-none focus:border-slate-400 cursor-pointer shadow-sm appearance-none font-medium text-slate-600">
                    <option value="">All Origins</option>
                    <option value="INHOUSE">Inhouse</option>
                    <option value="OUTHOUSE">Outhouse</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 pointer-events-none"></i>
            </div>

            <div class="relative group flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors text-xs"></i>
                <input type="text" id="customSearch" placeholder="Search clients..." class="w-full sm:w-64 bg-white border border-slate-200 text-sm pl-9 pr-4 py-2.5 rounded-xl outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
            </div>

            <a href="add_entry.php" class="bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-slate-900/20 transition-all transform active:scale-95 flex items-center justify-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-plus"></i> <span class="hidden sm:inline">Add Client</span>
            </a>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table id="clientTable" class="w-full text-left border-collapse" style="width:100%">
                <thead class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="p-5 w-14 text-center">S.No.</th>
                        <th class="p-5 pl-6">Client Name</th>
                        <th class="p-5">Contact</th>
                        <th class="p-5 text-center">Services</th>
                        <th class="p-5 text-center">Origin</th>
                        <th class="p-5 text-right pr-6">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-50">
                    <?php $i = 1; foreach ($clients as $c): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        
                        <td class="p-5 text-center font-bold text-slate-400 text-xs">
                            <?= $i++ ?>
                        </td>

                        <td class="p-5 pl-6">
                            <div>
                                <div class="font-bold text-slate-800 text-[15px]"><?= $c['client_name'] ?></div>
                                <div class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]"><?= $c['address'] ?: 'No address provided' ?></div>
                            </div>
                        </td>

                        <td class="p-5">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2 text-slate-600 font-medium text-xs">
                                    <i class="fa-solid fa-phone text-[10px] text-slate-400"></i> <?= $c['contact_number'] ?>
                                </div>
                                <?php if($c['alt_contact_number']): ?>
                                <div class="flex items-center gap-2 text-slate-500 text-xs">
                                    <i class="fa-solid fa-mobile-screen text-[10px] text-slate-400"></i> <?= $c['alt_contact_number'] ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="p-5 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <div title="Projects" class="<?= $c['no_of_project'] > 0 ? 'text-slate-700' : 'text-slate-300' ?> flex flex-col items-center">
                                    <span class="font-bold text-sm"><?= str_pad($c['no_of_project'], 2, '0', STR_PAD_LEFT) ?></span>
                                    <span class="text-[9px] uppercase font-bold tracking-wide">Proj</span>
                                </div>
                                <div class="w-px h-6 bg-slate-100"></div>
                                <div title="Social Media" class="<?= $c['no_of_sm'] > 0 ? 'text-pink-600' : 'text-slate-300' ?> flex flex-col items-center">
                                    <span class="font-bold text-sm"><?= str_pad($c['no_of_sm'], 2, '0', STR_PAD_LEFT) ?></span>
                                    <span class="text-[9px] uppercase font-bold tracking-wide">SMM</span>
                                </div>
                            </div>
                        </td>

                        <td class="p-5 text-center">
                            <?php if ($c['client_origin'] == 'INHOUSE'): ?>
                                <span class="origin-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                    INHOUSE
                                </span>
                            <?php else: ?>
                                <span class="origin-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                    OUTHOUSE
                                </span>
                            <?php endif; ?>
                            <span class="hidden"><?= $c['client_origin'] ?></span>
                        </td>

                        <td class="p-5 text-right pr-6">
                            <a href="client_view.php?id=<?= $c['client_id'] ?>" class="group/btn inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-blue-600 transition-colors bg-slate-50 hover:bg-blue-50 px-3 py-2 rounded-lg border border-slate-100 hover:border-blue-100">
                                View Profile <i class="fa-solid fa-arrow-right -rotate-45 group-hover/btn:rotate-0 transition-transform"></i>
                            </a>
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
    /* Completely hide default DataTables controls */
    .dataTables_wrapper .dataTables_paginate, 
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_length { display: none !important; }
    
    table.dataTable.no-footer { border-bottom: 0 !important; }
    
    /* Active Page Number Style */
    .page-num-btn.active { background-color: #0f172a; color: white; border-color: #0f172a; }
</style>

<script>
$(document).ready(function() {
    var table = $('#clientTable').DataTable({
        pageLength: 10,
        order: [], 
        columnDefs: [ { orderable: false, targets: [5] } ], // Disable sort on Action (index 5)
        language: { emptyTable: "No clients found." },
        
        // Update Custom Pagination on Draw
        drawCallback: function(settings) {
            var api = this.api();
            var info = api.page.info();
            
            // 1. Update Info Text (Showing 1 to 5 of 12)
            var start = info.recordsTotal > 0 ? info.start + 1 : 0;
            var end = info.end;
            var total = info.recordsDisplay;
            
            $('#paginationInfo').html(
                `Showing <span class="font-bold text-slate-700">${start}</span> to <span class="font-bold text-slate-700">${end}</span> of <span class="font-bold text-slate-700">${total}</span> clients`
            );

            // 2. LOGIC: Hide Pagination if only 1 page (or 0 pages)
            if (info.pages <= 1) {
                $('#paginationControls').hide();
            } else {
                $('#paginationControls').show();
                
                // 3. LOGIC: Prev Button Visibility
                if (info.page === 0) {
                    $('#btnPrev').hide();
                } else {
                    $('#btnPrev').show();
                    $('#btnPrev').off('click').on('click', function() { api.page('previous').draw('page'); });
                }

                // 4. LOGIC: Next Button Visibility
                if (info.page === info.pages - 1) {
                    $('#btnNext').hide();
                } else {
                    $('#btnNext').show();
                    $('#btnNext').off('click').on('click', function() { api.page('next').draw('page'); });
                }

                // 5. Generate Page Numbers (1, 2, 3...)
                var pageHtml = '';
                for (var i = 0; i < info.pages; i++) {
                    var activeClass = (i === info.page) ? 'active' : 'bg-white text-slate-500 hover:bg-slate-50';
                    pageHtml += `<button class="page-num-btn w-8 h-8 rounded-lg border border-slate-200 text-xs font-bold transition-all ${activeClass}" data-page="${i}">${i + 1}</button>`;
                }
                $('#pageNumbers').html(pageHtml);
                
                // Bind Page Number Clicks
                $('.page-num-btn').on('click', function() {
                    var page = $(this).data('page');
                    api.page(page).draw('page');
                });
            }
        }
    });

    // Link Custom Search
    $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
    // Link Custom Filter (Column 4 is Origin)
    $('#originFilter').on('change', function() { table.column(4).search(this.value).draw(); });
});
</script>

<?php include 'layout_footer.php'; ?>