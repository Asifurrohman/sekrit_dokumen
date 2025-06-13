<?php 
include 'koneksi.php';
include 'stopword.php';

$dataset = array();
$ambil = $koneksi->query("SELECT * FROM dataset");

while($tiap = $ambil->fetch_assoc()){
	// ambil isi
	$isi = $tiap['isi'];

	// pecah isi
	$banyak_kata = explode(" ", $isi);

	$kalimat = "";

	foreach($banyak_kata as $key => $kata){
		// membuang tanda baca yang dianggap tidak perlu (tidak bermakna untuk machine learning)
		$kata = str_replace(['.', ',', ':', '?', '!', '&', '-', '"', '(', ')', 'amp', '+', '_', ';', '/', '[', ']', '$'], " ", $kata);

		// buang angka
		$kata = preg_replace('/\d+/u', ' ', $kata);

		// buang mention
		if (strpos($kata, '@') !== FALSE) {
			$kata = ' ';
		}

		// buang link
		if (strpos($kata, 'http') !== FALSE) {
			$kata = ' ';
		}

		// buang hashtag
		if (strpos($kata, '#') !== FALSE) {
			$kata = ' ';
		}

		// buang stopword (kata yang dianggap tidak perlu untuk tujuan klasifikasi (kata sambung, kata ganti))
		$kata = str_replace($stopword, " ", $kata);

		// konvert ke huruf kecil agar seragam. karena dalam ML kemunculan kata yang sama dapat mempengaruhi hasil
		$kata = strtolower($kata);
		$kalimat .= $kata." ";
	}
	// echo "<pre>";
	// print_r($banyak_kata);
	// echo "</pre>";

	// echo $kalimat;
	// echo '<hr>';

	// buang spasi di awal dan akhir
	$kalimat = rtrim(trim($kalimat));

	// buang whitespace
	$kalimat = preg_replace('/\s+/', ' ', $kalimat);

	$id = $tiap['id'];
	$koneksi->query("UPDATE dataset SET pra = '$kalimat' WHERE id='$id'");
}

echo "<script>location='prapemrosesan.php'</script>";

 ?>