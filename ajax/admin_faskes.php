<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    
    // Get filter parameters to persist
    $search = urlencode($_POST['current_search'] ?? '');
    $wilayah = urlencode($_POST['current_wilayah'] ?? '');
    $sort = urlencode($_POST['current_sort'] ?? '');
    
    $query_string = "";
    if ($search || $wilayah || $sort) {
        $query_string = "&search=$search&wilayah=$wilayah&sort=$sort";
    }

    // File upload handling function
    function uploadImage() {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $newFilename = uniqid() . '.' . $ext;
                $destination = '../uploads/faskes/' . $newFilename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    return '/sehati/uploads/faskes/' . $newFilename;
                }
            }
        }
        return false;
    }

    if ($action === 'create') {
        $nama = $conn->real_escape_string($_POST['nama']);
        $kategori = $conn->real_escape_string($_POST['kategori']);
        $status = $conn->real_escape_string($_POST['status']);
        $telepon = $conn->real_escape_string($_POST['telepon']);
        $wilayah = $conn->real_escape_string($_POST['wilayah']);
        $alamat = $conn->real_escape_string($_POST['alamat']);
        $jam = $conn->real_escape_string($_POST['jam_pelayanan']);
        $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
        
        $image_url = '';
        $uploaded = uploadImage();
        if ($uploaded) {
            $image_url = $uploaded;
        }

        $sql = "INSERT INTO faskes (nama, kategori, status, telepon, wilayah, alamat, jam_pelayanan, deskripsi, image_url) 
                VALUES ('$nama', '$kategori', '$status', '$telepon', '$wilayah', '$alamat', '$jam', '$deskripsi', '$image_url')";
        
        if ($conn->query($sql)) {
            $faskes_id = $conn->insert_id;
            
            // Insert polis if any
            if (isset($_POST['polis']) && is_array($_POST['polis'])) {
                foreach ($_POST['polis'] as $poli_id) {
                    $poli_id = (int)$poli_id;
                    $conn->query("INSERT INTO faskes_layanan (faskes_id, poli_id) VALUES ($faskes_id, $poli_id)");
                }
            }
        }
        
        header("Location: ../admin/facilities.php?success=add" . $query_string);
        exit;

    } elseif ($action === 'update' && !empty($id)) {
        $id = $conn->real_escape_string($id);
        $nama = $conn->real_escape_string($_POST['nama']);
        $kategori = $conn->real_escape_string($_POST['kategori']);
        $status = $conn->real_escape_string($_POST['status']);
        $telepon = $conn->real_escape_string($_POST['telepon']);
        $wilayah = $conn->real_escape_string($_POST['wilayah']);
        $alamat = $conn->real_escape_string($_POST['alamat']);
        $jam = $conn->real_escape_string($_POST['jam_pelayanan']);
        $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
        
        $uploaded = uploadImage();
        
        if ($uploaded) {
            $image_url = $uploaded;
            $sql = "UPDATE faskes SET nama='$nama', kategori='$kategori', status='$status', telepon='$telepon', wilayah='$wilayah', alamat='$alamat', jam_pelayanan='$jam', deskripsi='$deskripsi', image_url='$image_url' WHERE id='$id'";
        } else {
            $sql = "UPDATE faskes SET nama='$nama', kategori='$kategori', status='$status', telepon='$telepon', wilayah='$wilayah', alamat='$alamat', jam_pelayanan='$jam', deskripsi='$deskripsi' WHERE id='$id'";
        }
        
        if ($conn->query($sql)) {
            // Update polis: Delete all existing then re-insert
            $conn->query("DELETE FROM faskes_layanan WHERE faskes_id = '$id'");
            
            if (isset($_POST['polis']) && is_array($_POST['polis'])) {
                foreach ($_POST['polis'] as $poli_id) {
                    $poli_id = (int)$poli_id;
                    $conn->query("INSERT INTO faskes_layanan (faskes_id, poli_id) VALUES ('$id', $poli_id)");
                }
            }
        }
        
        header("Location: ../admin/facilities.php?success=edit" . $query_string);
        exit;

    } elseif ($action === 'delete' && !empty($id)) {
        $id = $conn->real_escape_string($id);
        
        $conn->query("DELETE FROM faskes_layanan WHERE faskes_id='$id'");
        $sql = "DELETE FROM faskes WHERE id='$id'";
        $conn->query($sql);
        header("Location: ../admin/facilities.php?success=delete" . $query_string);
        exit;
    }
}
header("Location: ../admin/facilities.php");
exit;
