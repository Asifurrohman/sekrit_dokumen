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

<a href="pilih_tweet_random.php" class="btn btn-primary">Pilih tweet random</a>

<script>
       
      $(function(){
 
           $('#tableData').DataTable({
              "processing": true,
              "serverSide": true,
              "ajax":{
                       "url": "sumberakhir.php",
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