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
			</tr>
		</thead>
		<tbody>
			
		</tbody>
	</table>
	<a href="jalankan_prapemrosesan.php" class="btn btn-outline-primary">Jalankan Pra Pemrosesan</a>
</div>

<script>
       
      $(function(){
 
           $('#tableData').DataTable({
              "processing": true,
              "serverSide": true,
              "ajax":{
                       "url": "sumberdataset.php",
                       "dataType": "json",
                       "type": "POST"
                     },
              "columns": [
                  { "data": "no" },
                  { "data": "username" },
                  { "data": "tanggal" },
                  { "data": "isi" },
              ]  
 
          });
        });
 
     
 
</script>
<?php include 'footer.php' ?>