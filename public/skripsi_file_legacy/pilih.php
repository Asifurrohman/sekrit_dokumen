<?php include 'koneksi.php' ?>
<?php include 'header.php' ?>

<?php 



?>

<div class="container-fluid">
    <h5>Tweet terpilih</h5>
    <table class="table table-sm" id="tableData">
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Tanggal</th>
                <th>Pra</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
</div>

<script>
       
      $(function(){
 
           $('#tableData').DataTable({
              "processing": true,
              "serverSide": true,
              "ajax":{
                       "url": "sumberpilih.php",
                       "dataType": "json",
                       "type": "POST"
                     },
              "columns": [
                  { "data": "no" },
                  { "data": "username" },
                  { "data": "tanggal" },
                  { "data": "pra" },
                  { "data": "aksi" }
              ]  
 
          });
        });
 
     
 
</script>
<?php include 'footer.php' ?>