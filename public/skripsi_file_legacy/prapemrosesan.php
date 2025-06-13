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
</div>

<a href="buang_duplikat.php" class="btn btn-primary">Hapus tweet yang duplikat</a>

<script>
       
      $(function(){
 
           $('#tableData').DataTable({
              "processing": true,
              "serverSide": true,
              "ajax":{
                       "url": "sumberpra.php",
                       "dataType": "json",
                       "type": "POST"
                     },
              "columns": [
                  { "data": "no" },
                  { "data": "username" },
                  { "data": "tanggal" },
                  { "data": "pra" },
              ]  
 
          });
        });
 
     
 
</script>
<?php include 'footer.php' ?>