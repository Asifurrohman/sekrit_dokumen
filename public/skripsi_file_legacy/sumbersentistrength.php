<?php 
require_once 'koneksi.php';




$columns = array( 'id', 'username', 'tanggal', 'pra', 'sentistrength');

$querycount = $koneksi->query("SELECT count(id) as jumlah FROM dataset WHERE jenis='umum' AND sentistrength !=''");
$datacount = $querycount->fetch_assoc();
$totalData = $datacount['jumlah'];
$totalFiltered = $totalData; 

$limit = $_POST['length'];
$start = $_POST['start'];
$order = $columns[$_POST['order']['0']['column']];
$dir = $_POST['order']['0']['dir'];

if(empty($_POST['search']['value']))
{            
   $query = $koneksi->query("SELECT * FROM dataset WHERE jenis ='umum' AND sentistrength !='' order by $order $dir
      LIMIT $limit
      OFFSET $start");
}
else {
    $search = $_POST['search']['value']; 
    $query = $koneksi->query("SELECT * FROM dataset WHERE pra LIKE '%$search%' AND jenis='umum'
       AND sentistrength !='' order by $order $dir
       LIMIT $limit
       OFFSET $start");


    $querycount = $koneksi->query("SELECT count(id) as jumlah FROM dataset WHERE jenis='umum' AND pra LIKE '%$search%' AND sentistrength !=''");
    $datacount = $querycount->fetch_assoc();
    $totalFiltered = $datacount['jumlah'];
}

$data = array();
if(!empty($query))
{
    $no = $start + 1;
    while ($r = $query->fetch_assoc())
    {
        $nestedData['no'] = $no;
        $nestedData['username'] = $r['username'];
        $nestedData['tanggal'] = $r['tanggal'];
        $nestedData['pra'] = $r['pra'];
        $nestedData['sentistrength'] = $r['sentistrength'];

        if (!empty($r['sentistrength'])) {
            // 1 line di bawah ini untuk mengubah petik 1 menjadi petik 2 pada json
            $r['sentistrength'] = str_replace("'", '"', $r['sentistrength']);

            $ss = json_decode($r['sentistrength'], TRUE);
            if (isset($ss['kelas'])) {
            $nestedData['klasifikasi'] = $ss['kelas'];
            } else {
                $nestedData['klasifikasi'] = '';
            }
        }
        
        $data[] = $nestedData;
        $no++;
    }
}

$json_data = array(
    "draw"            => intval($_POST['draw']),  
    "recordsTotal"    => intval($totalData),  
    "recordsFiltered" => intval($totalFiltered), 
    "data"            => $data  
);

echo json_encode($json_data); 
?>