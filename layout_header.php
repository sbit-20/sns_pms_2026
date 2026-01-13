<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'index.php') {
    header("Location: index.php"); exit;
}

// Helper to check active state
function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Dashboard</title>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#fffbeb', 100: '#fef3c7', 500: '#f59e0b', 600: '#d97706', 900: '#78350f' }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f5f5f4; /* Stone-100 */ }
        
        /* Sidebar Transition */
        #sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* Collapsed State Details */
        .is-collapsed { width: 5rem !important; }
        .is-collapsed .nav-label, 
        .is-collapsed .group-label, 
        .is-collapsed .chevron-icon,
        .is-collapsed .logo-full { display: none !important; opacity: 0; }
        .is-collapsed .logo-icon { display: flex !important; }
        .is-collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
        .is-collapsed .submenu-container { display: none !important; }
        
        /* Floating Input Styles (kept for children pages) */
        .flutter-input-group { position: relative; margin-bottom: 1rem; }
        .flutter-input { width: 100%; background: white; border: 1px solid #e7e5e4; border-radius: 0.75rem; padding: 0.85rem 1rem; font-size: 0.95rem; transition: all 0.2s; color: #1c1917; outline: none; }
        .flutter-input:focus { border-color: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); }
        .flutter-label { position: absolute; left: 1rem; top: 0.9rem; color: #a8a29e; pointer-events: none; transition: 0.2s ease all; background-color: white; padding: 0 0.25rem; font-size: 0.95rem; }
        .flutter-input:focus ~ .flutter-label, .flutter-input:not(:placeholder-shown) ~ .flutter-label { top: -0.6rem; left: 0.8rem; font-size: 0.75rem; font-weight: 600; color: #d97706; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a29e; }
    </style>
</head>
<body class="text-stone-600 antialiased flex h-screen overflow-hidden selection:bg-amber-100 selection:text-amber-900">

    <aside id="sidebar" class="bg-white hidden md:flex flex-col relative w-64 h-full border-r border-stone-200 z-30 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
        
        <button id="toggleBtn" class="absolute -right-3 top-9 bg-white text-stone-400 hover:text-amber-600 w-6 h-6 rounded-full flex items-center justify-center shadow-md border border-stone-100 cursor-pointer transition-all hover:scale-110 z-50">
            <i class="fa-solid fa-chevron-left text-[10px]"></i>
        </button>

        <div class="h-24 flex items-center justify-center border-b border-stone-100 shrink-0 relative bg-white/50">
            
            <div class="logo-full items-center justify-center">
                <img src="snss.png" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" alt="Logo" class="h-12 w-auto object-contain drop-shadow-sm hover:scale-105 transition-transform">
                <div class="hidden px-4 py-2 bg-stone-800 rounded-lg items-center justify-center text-white font-bold tracking-widest shadow-lg">
                    SUN & SUN
                </div>
            </div>

            <div class="logo-icon hidden w-full h-full items-center justify-center">
                <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-amber-500/30">
                    S
                </div>
            </div>
        </div>
        
        <nav class="flex-1 py-6 px-3 space-y-1.5 overflow-y-auto">
            
            <div class="px-3 mb-2 mt-1 text-[10px] font-bold uppercase tracking-wider text-stone-400 group-label">Overview</div>

            <a href="dashboard.php" class="nav-item flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group font-medium text-sm <?= isActive('dashboard.php') ? 'bg-stone-900 text-white shadow-lg shadow-stone-200' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' ?>">
                <i class="fa-solid fa-chart-pie w-6 text-center transition-colors <?= isActive('dashboard.php') ? 'text-amber-400' : 'group-hover:text-stone-800' ?>"></i>
                <span class="ml-3 nav-label">Dashboard</span>
            </a>

            <a href="reminders.php" class="nav-item flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group font-medium text-sm <?= isActive('reminders.php') ? 'bg-stone-900 text-white shadow-lg shadow-stone-200' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' ?>">
                <i class="fa-regular fa-bell w-6 text-center transition-colors <?= isActive('reminders.php') ? 'text-amber-400' : 'group-hover:text-stone-800' ?>"></i>
                <span class="ml-3 nav-label">Reminders</span>
            </a>

            <div class="px-3 mb-2 mt-6 text-[10px] font-bold uppercase tracking-wider text-stone-400 group-label">Business</div>
            
            <a href="clients.php" class="nav-item flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group font-medium text-sm <?= isActive('clients.php') ? 'bg-stone-900 text-white shadow-lg shadow-stone-200' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' ?>">
                <i class="fa-solid fa-users w-6 text-center transition-colors <?= isActive('clients.php') ? 'text-amber-400' : 'group-hover:text-stone-800' ?>"></i>
                <span class="ml-3 nav-label">Client Directory</span>
            </a>
            
            <?php $is_inv = basename($_SERVER['PHP_SELF']) == 'invoice_list.php'; ?>
            <div class="relative">
                <button onclick="toggleSubmenu('invoiceSubmenu')" class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group font-medium text-sm <?= $is_inv ? 'bg-stone-100 text-stone-900' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' ?>">
                    <div class="flex items-center">
                        <i class="fa-solid fa-file-invoice-dollar w-6 text-center transition-colors <?= $is_inv ? 'text-amber-500' : 'group-hover:text-stone-800' ?>"></i>
                        <span class="ml-3 nav-label">Invoices</span>
                    </div>
                    <i id="invoiceArrow" class="fa-solid fa-chevron-down text-[10px] chevron-icon transition-transform duration-300 opacity-50"></i>
                </button>
                
                <div id="invoiceSubmenu" class="submenu-container hidden pl-9 pr-2 mt-1 space-y-1 relative">
                    <div class="absolute left-[1.65rem] top-0 bottom-4 w-px bg-stone-200"></div>
                    
                    <a href="invoice_list.php?origin=INHOUSE" class="relative block py-2 pl-4 rounded-lg text-xs font-medium transition-colors hover:text-stone-900 <?= ($is_inv && isset($_GET['origin']) && $_GET['origin']=='INHOUSE') ? 'text-amber-600 bg-amber-50' : 'text-stone-500' ?>">
                         Inhouse Records
                    </a>
                    <a href="invoice_list.php?origin=OUTHOUSE" class="relative block py-2 pl-4 rounded-lg text-xs font-medium transition-colors hover:text-stone-900 <?= ($is_inv && isset($_GET['origin']) && $_GET['origin']=='OUTHOUSE') ? 'text-amber-600 bg-amber-50' : 'text-stone-500' ?>">
                         Outhouse Records
                    </a>
                </div>
            </div>

            <a href="add_entry.php" class="nav-item flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group font-medium text-sm <?= isActive('add_entry.php') ? 'bg-stone-900 text-white shadow-lg shadow-stone-200' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' ?>">
                <i class="fa-solid fa-plus-circle w-6 text-center transition-colors <?= isActive('add_entry.php') ? 'text-amber-400' : 'group-hover:text-stone-800' ?>"></i>
                <span class="ml-3 nav-label">New Entry</span>
            </a>
        </nav>

        <div class="p-4 border-t border-stone-100 bg-stone-50/50">
            <a href="logout.php" class="nav-item flex items-center px-3 py-2 text-stone-500 hover:bg-white hover:text-red-600 hover:shadow-sm hover:ring-1 hover:ring-stone-200 rounded-xl transition-all group">
                <i class="fa-solid fa-arrow-right-from-bracket w-6 text-center text-sm"></i>
                <span class="ml-3 font-medium text-xs nav-label">Sign Out</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <header class="h-16 bg-white/80 backdrop-blur-md border-b border-stone-200/60 flex justify-between items-center px-4 sm:px-8 z-20 sticky top-0">
            
            <div class="flex items-center gap-4">
                <button class="md:hidden text-stone-500 w-10 h-10 hover:bg-stone-100 rounded-xl transition flex items-center justify-center" onclick="toggleMobileMenu()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="flex flex-col">
                    <h2 class="font-bold text-stone-800 text-lg leading-tight capitalize">
                        <?php 
                            $page_clean = str_replace(['_', '.php'], [' ', ''], basename($_SERVER['PHP_SELF']));
                            echo $page_clean == 'index' ? 'Dashboard' : $page_clean; 
                        ?>
                    </h2>
                    <div class="flex items-center gap-2 text-[10px] text-stone-400 font-medium">
                        <span>Overview</span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="hidden sm:flex items-center gap-2 bg-stone-100 px-3 py-1.5 rounded-full border border-stone-200">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-stone-600 uppercase tracking-wide">System Online</span>
                </div>

                <div class="h-9 w-9 bg-stone-800 rounded-full flex items-center justify-center text-white font-bold text-xs ring-4 ring-stone-100 cursor-pointer hover:ring-amber-100 transition-all">
                    AD
                </div>
            </div>
        </header>

        <div id="mobileMenu" class="fixed inset-0 z-50 hidden md:hidden">
            <div class="absolute inset-0 bg-stone-900/50 backdrop-blur-sm" onclick="toggleMobileMenu()"></div>
            <div class="absolute left-0 top-0 bottom-0 w-64 bg-white shadow-2xl p-4 flex flex-col gap-2 animate-slide-in">
                <div class="h-20 flex items-center justify-center px-2 mb-4 border-b border-stone-100">
                    <img src="snss.png" onerror="this.style.display='none'" alt="Logo" class="h-10 w-auto object-contain">
                </div>
                <a href="dashboard.php" class="p-3 rounded-lg hover:bg-stone-50 font-medium text-stone-600">Dashboard</a>
                <a href="reminders.php" class="p-3 rounded-lg hover:bg-stone-50 font-medium text-stone-600">Reminders</a>
                <a href="clients.php" class="p-3 rounded-lg hover:bg-stone-50 font-medium text-stone-600">Client Directory</a>
                <a href="invoice_list.php" class="p-3 rounded-lg hover:bg-stone-50 font-medium text-stone-600">Invoices</a>
                <div class="mt-auto border-t border-stone-100 pt-4">
                    <a href="logout.php" class="p-3 rounded-lg text-red-600 hover:bg-red-50 font-medium flex items-center gap-2">
                        <i class="fa-solid fa-power-off"></i> Sign Out
                    </a>
                </div>
            </div>
        </div>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 scroll-smooth">

<script>
    const sidebar = document.getElementById('sidebar'); 
    const toggleBtn = document.getElementById('toggleBtn'); 
    const icon = toggleBtn.querySelector('i'); 
    const mobileMenu = document.getElementById('mobileMenu');
    
    // Sidebar Logic
    const savedState = localStorage.getItem('sidebarState');
    if (savedState === 'collapsed') { 
        sidebar.classList.add('is-collapsed'); 
        icon.classList.add('rotate-180'); 
    }
    
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('is-collapsed'); 
        icon.classList.toggle('rotate-180');
        localStorage.setItem('sidebarState', sidebar.classList.contains('is-collapsed') ? 'collapsed' : 'expanded');
    });

    function toggleMobileMenu() { 
        mobileMenu.classList.toggle('hidden'); 
    }

    // Submenu Logic
    function toggleSubmenu(id) {
        const submenu = document.getElementById(id);
        const arrow = document.getElementById('invoiceArrow');
        submenu.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
        localStorage.setItem(id, submenu.classList.contains('hidden') ? 'closed' : 'open');
    }

    // Restore Submenu State
    if(localStorage.getItem('invoiceSubmenu') === 'open') {
        document.getElementById('invoiceSubmenu').classList.remove('hidden');
        document.getElementById('invoiceArrow').classList.add('rotate-180');
    }
</script>