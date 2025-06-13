<?php 
include 'koneksi.php';

$ambil = $koneksi->query("SELECT * FROM dataset WHERE jenis='umum' ORDER BY RAND() LIMIT 500");

while($tiap = $ambil->fetch_assoc()){
	$id = $tiap['id'];

	$koneksi->query("UPDATE dataset SET status='pakai' WHERE id='$id'");
}

echo "<script>alert('data terpilih');</script>";
echo "<script>location='pilih.php';</script>";


 ?>