<?php include 'koneksi.php' ?>
<?php include 'header.php' ?>

<?php 



?>

<div class="container-fluid">
    <h5>Dataset</h5>
    <table class="table table-sm" id="tableData">
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Tanggal</th>
                <th>Isi</th>
                <th>Sentistrength</th>
                <th>Klasifikasi</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
</div>

<?php 
$jumlahPositive = 0;
$jumlahNegative = 0;
$jumlahNeutral  = 0;

$ambil = $koneksi->query("SELECT * FROM dataset WHERE sentistrength !='' ORDER BY id ASC");
while ($r = $ambil->fetch_assoc()) {

    if (!empty($r['sentistrength'])) {
        $r['sentistrength'] = str_replace("'", '"', $r['sentistrength']);
        $ss = json_decode($r['sentistrength'], TRUE);

        if (isset($ss['kelas'])) {
            $jumlahPositive+=$ss['kelas']=='positive' ? 1 : 0;
            $jumlahNegative+=$ss['kelas']=='negative' ? 1 : 0;
            $jumlahNeutral+=$ss['kelas']=='neutral' ? 1 : 0;
        }
    }
}

 ?>

<div class="my-5" id="jumlah">
    
</div>

<a href="jalankan_sentistrength.php" class="btn btn-primary">Jalankan sentistrength</a>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
    // Data retrieved from https://netmarketshare.com
Highcharts.chart('jumlah', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: 'Browser market shares in May, 2020',
        align: 'left'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
        point: {
            valueSuffix: '%'
        }
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.y}'
            }
        }
    },
    series: [{
        name: 'jumlah',
        colorByPoint: true,
        data: [{
            name: 'positive',
            y: <?php echo $jumlahPositive; ?>,
            sliced: true,
            selected: true
        }, {
            name: 'negative',
            y: <?php echo $jumlahNegative; ?>
        },  {
            name: 'neutral',
            y: <?php echo $jumlahNeutral; ?>
        }]
    }]
});

</script>
<script>
       
      $(function(){
 
           $('#tableData').DataTable({
              "processing": true,
              "serverSide": true,
              "ajax":{
                       "url": "sumbersentistrength.php",
                       "dataType": "json",
                       "type": "POST"
                     },
              "columns": [
                  { "data": "no" },
                  { "data": "username" },
                  { "data": "tanggal" },
                  { "data": "pra" },
                  { "data": "sentistrength" },
                  { "data": "klasifikasi" }
              ]  
 
          });
        });
 
     
 
</script>
<?php include 'footer.php' ?>