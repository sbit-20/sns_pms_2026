<?php 
// modules/projects/edit_project.php
require_once '../../config/config.php';

$pid = isset($_GET['project_id']) ? $_GET['project_id'] : 0;
$project = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
$project->execute([$pid]);
$p = $project->fetch();

if (!$p) {
    header("Location: " . BASE_URL . "modules/clients/clients.php");
    exit;
}

$services = $pdo->query("SELECT * FROM service_type_tbl ORDER BY service_name ASC")->fetchAll();
include_once '../../includes/layout_header.php'; 
?>

<div class="max-w-4xl mx-auto mt-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="font-bold text-lg text-slate-800">Edit Project: <?= $p['project_name'] ?></h3>
            <a href="../clients/client_view.php?id=<?= $p['client_id'] ?>" class="text-xs font-bold text-slate-500 hover:text-slate-800">Cancel</a>
        </div>
        
        <form action="../../src/actions.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
            <input type="hidden" name="action" value="edit_project">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <input type="hidden" name="client_id" value="<?= $p['client_id'] ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Project Name</label>
                    <input type="text" name="p_name" value="<?= $p['project_name'] ?>" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Project Type</label>
                    <select name="p_type" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                        <?php foreach($services as $svc): ?>
                            <option value="<?= $svc['service_name'] ?>" <?= $p['project_type'] == $svc['service_name'] ? 'selected' : '' ?>><?= $svc['service_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">AMC Amount (₹)</label>
                    <input type="number" name="p_amc" value="<?= $p['amc_base_amount'] ?>" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">AMC Renewal Date</label>
                    <input type="date" name="p_renewal" value="<?= $p['next_renewal_date'] ?>" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Managed By</label>
                    <input type="text" name="p_manager" value="<?= $p['manager_name'] ?>" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Contact No</label>
                    <input type="text" name="p_manager_contact" value="<?= $p['manager_contact_no'] ?>" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Technology</label>
                    <input type="text" name="p_tech_name" value="<?= $p['tech_name'] ?>" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Tech Version</label>
                    <input type="text" name="p_version" value="<?= $p['current_version'] ?>" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>

                <div class="md:col-span-2 pt-6 border-t border-slate-100">
                    <h4 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wide">Update Documentation</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Document Title</label>
                            <input type="text" name="doc_title" value="<?= $p['doc_title'] ?>" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Google Drive Link</label>
                            <input type="url" name="doc_link" value="<?= $p['doc_link'] ?>" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Replace PDF File</label>
                            <input type="file" name="doc_file" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-blue-50 file:text-blue-700 cursor-pointer">
                            <?php if($p['doc_file_path']): ?>
                                <p class="text-[10px] mt-2 text-emerald-600 font-bold italic">* Current file exists. Uploading new one will replace it.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl text-sm shadow-lg hover:bg-blue-700 transition transform active:scale-[0.99]">
                    Update Project Details
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/layout_footer.php'; ?>