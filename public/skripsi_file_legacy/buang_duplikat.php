<?php 
include 'koneksi.php';

// dataset yang hasil pra pemrosesannya sama, maka salah satunya ditandai sebagai jenis data duplikat
$sql = "UPDATE dataset AS t1 INNER JOIN dataset AS t2 SET t1.jenis='duplikat' WHERE t1.id < t2.id AND t1.pra = t2.pra";
$koneksi->query($sql);

echo "<script>alert('tweet yang duplikat sudah dibuang');</script>";
echo "<script>location='akhir.php';</script>";


 ?>