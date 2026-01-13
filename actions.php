<?php
// actions.php
require_once 'config.php';

function redirect($url, $msg) {
    echo "<script>alert('$msg'); window.location.href='$url';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    try {

        if ($_POST['action'] == 'add_receipt') {
            $iid = $_POST['invoice_id'];
            
            // 1. Generate Receipt No
            $stmtCount = $pdo->query("SELECT COUNT(*) FROM receipts");
            $count = $stmtCount->fetchColumn() + 1;
            $rec_no = "REC-" . str_pad($count, 5, "0", STR_PAD_LEFT);

            // 2. Handle File Upload
            $file_path = null;
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true); // Create dir if not exists

                $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
                $filename = "proof_" . $rec_no . "_" . time() . "." . $ext;
                $target = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target)) {
                    $file_path = $target;
                }
            }

            // 3. Insert into Database
            $stmt = $pdo->prepare("INSERT INTO receipts (invoice_id, receipt_number, receipt_date, amount_paid, payment_mode, transaction_ref, receipt_file) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $iid, 
                $rec_no, 
                $_POST['pay_date'], 
                $_POST['pay_amount'], 
                $_POST['pay_mode'], 
                $_POST['pay_ref'],
                $file_path
            ]);

            $origin = isset($_POST['origin']) ? $_POST['origin'] : 'INHOUSE';
            redirect("invoice_list.php?origin=$origin", "Payment Receipt Generated Successfully!");
        }

        // --- EXISTING ACTIONS (Keep these as they were) ---
        
        elseif ($_POST['action'] == 'add_full_entry') {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO clients (client_name, address, contact_number, alt_contact_number, client_origin) VALUES (?,?,?,?,?)");
            $stmt->execute([$_POST['client_name'], $_POST['address'], $_POST['contact'], $_POST['alt_contact'], $_POST['origin']]);
            $cid = $pdo->lastInsertId();
            if (isset($_POST['include_project'])) {
                $stmtP = $pdo->prepare("INSERT INTO projects (client_id, project_name, project_type, amc_base_amount, next_renewal_date, manager_name, manager_contact_no, tech_name, current_version) VALUES (?,?,?,?,?,?,?,?,?)");
                $stmtP->execute([$cid, $_POST['p_name'], $_POST['p_type'], $_POST['p_amc'], $_POST['p_renewal'], $_POST['p_manager'], $_POST['p_manager_contact'], $_POST['p_tech_name'], $_POST['p_version']]);
            }
            if (isset($_POST['include_smm'])) {
                $stmtS = $pdo->prepare("INSERT INTO smm_services (client_id, base_charge, ad_description, next_renewal_date) VALUES (?, ?, ?, LAST_DAY(CURDATE()))");
                $stmtS->execute([$cid, $_POST['s_mgmt'], $_POST['s_desc']]);
            }
            $pdo->commit();
            redirect("client_view.php?id=$cid", "Entry saved successfully!");
        }
        elseif ($_POST['action'] == 'add_project') {
            $cid = $_POST['client_id'];
            $stmt = $pdo->prepare("INSERT INTO projects (client_id, project_name, project_type, amc_base_amount, next_renewal_date, manager_name, manager_contact_no, tech_name, current_version) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$cid, $_POST['p_name'], $_POST['p_type'], $_POST['p_amc'], $_POST['p_renewal'], $_POST['p_manager'], $_POST['p_manager_contact'], $_POST['p_tech_name'], $_POST['p_version']]);
            redirect("client_view.php?id=$cid", "Project added successfully!");
        }
        elseif ($_POST['action'] == 'add_smm') {
            $cid = $_POST['client_id'];
            $stmt = $pdo->prepare("INSERT INTO smm_services (client_id, base_charge, ad_description, next_renewal_date) VALUES (?, ?, ?, LAST_DAY(CURDATE()))");
            $stmt->execute([$cid, $_POST['s_mgmt'], $_POST['s_desc']]);
            redirect("client_view.php?id=$cid", "SMM Service started!");
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        redirect($_SERVER['HTTP_REFERER'], "Error: " . $e->getMessage());
    }
}
?>