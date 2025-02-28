<!-- Style -->
<style media="screen">
    table td,
    table th {
      padding: 5px;
    }
  </style>
<!-- End Style -->
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span> Tutup</button>
    <h4 class="modal-title" style="color:green">Elektronik Rekam Medis</h4>
</div>
<div class="modal-body">
  <!-- Data Pasien -->
   aaaaa
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-primary" data-dismiss="modal">Tutup</button>
</div>
            <script>
              $('#erm').dataTable( {
                responsive: true,
                order: [[ 0, 'desc' ]],
                "lengthMenu": [[1, 5, 10, -1], [1, 5, 10, "All"]],
                "dom": '<l><p><f>rt<ip><"clear">',
                "pagingType": "full_numbers"
              } );
            </script>