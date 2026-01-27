<?php
// sns_pms/src/actions.php

// 1. Update config path: Move up one level to root, then into config folder
require_once '../config/config.php';

/**
 * Redirects to a specific URL with an alert message
 */
function redirect($url, $msg) {
    echo "<script>alert('$msg'); window.location.href='$url';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    try {

        // --- 1. EDIT CLIENT DETAILS ---
        if ($_POST['action'] == 'edit_client') {
            $cid = $_POST['client_id'];
            $stmt = $pdo->prepare("UPDATE clients SET client_name = ?, address = ?, contact_number = ?, alt_contact_number = ?, client_origin = ? WHERE client_id = ?");
            $stmt->execute([
                $_POST['client_name'], 
                $_POST['address'], 
                $_POST['contact'], 
                $_POST['alt_contact'], 
                $_POST['origin'], 
                $cid
            ]);
            redirect("../modules/clients/client_view.php?id=$cid", "Client profile updated successfully!");
        }

        // --- 2. EDIT SMM SERVICE ---
        elseif ($_POST['action'] == 'edit_smm') {
            $sid = $_POST['smm_id'];
            $cid = $_POST['client_id'];
            $stmt = $pdo->prepare("UPDATE smm_services SET base_charge = ?, ad_description = ?, next_renewal_date = ? WHERE smm_id = ?");
            $stmt->execute([
                $_POST['s_mgmt'], 
                $_POST['s_desc'], 
                $_POST['s_renewal'], 
                $sid
            ]);
            redirect("../modules/clients/client_view.php?id=$cid", "SMM service updated successfully!");
        }

        // --- 3. EDIT PROJECT (Full Details + Documentation) ---
        elseif ($_POST['action'] == 'edit_project') {
            $pid = $_POST['project_id'];
            $cid = $_POST['client_id'];
            
            // Fetch current record to handle file replacement
            $stmtOld = $pdo->prepare("SELECT doc_file_path FROM projects WHERE project_id = ?");
            $stmtOld->execute([$pid]);
            $oldProject = $stmtOld->fetch();
           $file_path = isset($oldProject['doc_file_path']) ? $oldProject['doc_file_path'] : null;


            // Handle New File Upload
            if (isset($_FILES['doc_file']) && $_FILES['doc_file']['error'] == 0) {
                $upload_dir = '../uploads/docs/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                // Delete the old physical file if it exists
                if ($file_path && file_exists('../' . $file_path)) {
                    unlink('../' . $file_path);
                }

                $ext = pathinfo($_FILES['doc_file']['name'], PATHINFO_EXTENSION);
                $filename = "doc_" . $pid . "_" . time() . "." . $ext;
                if(move_uploaded_file($_FILES['doc_file']['tmp_name'], $upload_dir . $filename)) {
                    $file_path = 'uploads/docs/' . $filename;
                }
            }

            // Update all fields in the projects table
            $sql = "UPDATE projects SET 
                    project_name = ?, 
                    project_type = ?, 
                    amc_base_amount = ?, 
                    next_renewal_date = ?, 
                    manager_name = ?, 
                    manager_contact_no = ?, 
                    tech_name = ?, 
                    current_version = ?, 
                    doc_title = ?, 
                    doc_file_path = ?, 
                    doc_link = ? 
                    WHERE project_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['p_name'], 
                $_POST['p_type'], 
                $_POST['p_amc'], 
                $_POST['p_renewal'], 
                $_POST['p_manager'], 
                $_POST['p_manager_contact'], 
                $_POST['p_tech_name'], 
                $_POST['p_version'], 
                $_POST['doc_title'], 
                $file_path, 
                $_POST['doc_link'], 
                $pid
            ]);

            redirect("../modules/clients/client_view.php?id=$cid", "Project updated successfully!");
        }

        // --- 4. ADD PROJECT (Integrated Single Table) ---
        elseif ($_POST['action'] == 'add_project') {
            $cid = $_POST['client_id'];
            $doc_title = $_POST['doc_title'];
            $doc_link = $_POST['doc_link'];
            $file_path = null;

            if (isset($_FILES['doc_file']) && $_FILES['doc_file']['error'] == 0) {
                $upload_dir = '../uploads/docs/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $ext = pathinfo($_FILES['doc_file']['name'], PATHINFO_EXTENSION);
                $filename = "doc_" . time() . "_" . uniqid() . "." . $ext;
                if(move_uploaded_file($_FILES['doc_file']['tmp_name'], $upload_dir . $filename)) {
                    $file_path = 'uploads/docs/' . $filename;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO projects (client_id, project_name, project_type, amc_base_amount, next_renewal_date, manager_name, manager_contact_no, tech_name, current_version, doc_title, doc_file_path, doc_link) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $cid, 
                $_POST['p_name'], 
                $_POST['p_type'], 
                $_POST['p_amc'], 
                $_POST['p_renewal'], 
                $_POST['p_manager'], 
                $_POST['p_manager_contact'], 
                $_POST['p_tech_name'], 
                $_POST['p_version'],
                $doc_title,
                $file_path,
                $doc_link
            ]);
            
            redirect("../modules/clients/client_view.php?id=$cid", "Project added successfully!");
        }

        // --- 5. ADD RECEIPT (Payment Proof) ---
        elseif ($_POST['action'] == 'add_receipt') {
            $iid = $_POST['invoice_id'];
            
            $stmtCount = $pdo->query("SELECT COUNT(*) FROM receipts");
            $count = $stmtCount->fetchColumn() + 1;
            $rec_no = "REC-" . str_pad($count, 5, "0", STR_PAD_LEFT);

            $file_path = null;
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
                $upload_dir = '../uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
                $filename = "proof_" . $rec_no . "_" . time() . "." . $ext;
                $target = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target)) {
                    $file_path = 'uploads/' . $filename;
                }
            }

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
            redirect("../modules/invoices/invoice_list.php?origin=$origin", "Payment Receipt Generated Successfully!");
        }

        // --- 6. ADD SMM SERVICE ---
        elseif ($_POST['action'] == 'add_smm') {
            $cid = $_POST['client_id'];
            $stmt = $pdo->prepare("INSERT INTO smm_services (client_id, base_charge, ad_description, next_renewal_date) VALUES (?, ?, ?, LAST_DAY(CURDATE()))");
            $stmt->execute([$cid, $_POST['s_mgmt'], $_POST['s_desc']]);
            redirect("../modules/clients/client_view.php?id=$cid", "SMM Service started!");
        }

        // --- 7. ADD FULL ENTRY (Transactional) ---
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
            redirect("../modules/clients/client_view.php?id=$cid", "Entry saved successfully!");
        }

        // --- 8. DELETE DOCUMENTATION (Clear Fields) ---
        elseif ($_POST['action'] == 'delete_document') {
            $pid = $_POST['project_id']; 
            $cid = $_POST['client_id'];

            $stmt = $pdo->prepare("SELECT doc_file_path FROM projects WHERE project_id = ?");
            $stmt->execute([$pid]);
            $doc = $stmt->fetch();

            if ($doc && !empty($doc['doc_file_path']) && file_exists('../' . $doc['doc_file_path'])) {
                unlink('../' . $doc['doc_file_path']);
            }

            $pdo->prepare("UPDATE projects SET doc_title = NULL, doc_file_path = NULL, doc_link = NULL WHERE project_id = ?")->execute([$pid]);
            redirect("../modules/clients/client_view.php?id=$cid", "Documentation removed.");
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        redirect($_SERVER['HTTP_REFERER'], "Error: " . $e->getMessage());
    }
}
?>