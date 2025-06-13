<?php 
include 'koneksi.php';

$file = fopen("lagihasil.csv","r");

while(!feof($file)){
	// echo "<pre>";
	// print_r(fgetcsv($file));
	// echo "</pre>";

	$baris = fgetcsv($file);

	if (!empty($baris[0])) {
		$tanggal = $baris[1];
		$id = $baris[2];
		$isi = $baris[3];
		$username = $baris[4];

		$ambil = $koneksi->query("SELECT * FROM dataset WHERE id = '$id'");
		$cek = $ambil->fetch_assoc();

		if (empty($cek)) {
			$koneksi->query("INSERT INTO dataset(id, tanggal, username, isi) VALUES('$id', '$tanggal', '$username', '$isi')");
		}
	}
}

fclose($file);
?>