@extends('layout.app')
@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Add Account</h5>
                        </div>
                        <form id="filterForm" action="{{ route('account') }}" method="POST"> 
                            @csrf
                            <div class="row clearfix">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <dt>Username</dt>
                                            <dd>
                                                <select name="username" class="form-control pegawai" style="width: 100%;" required>
                                                    <option value="" selected>- Select Pegawai -</option>
                                                    @foreach ($pilihan_pegawai as $pegawai)
                                                        <option value="{{ $pegawai->nik }}">{{ $pegawai->nama }} [{{ $pegawai->nik }}]</option>
                                                    @endforeach
                                                </select>
                                            </dd>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <dt>Password</dt>
                                            <dd>
                                                <input type="password" value="" class="form-control" name="password" placeholder="password" required>
                                            </dd>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <dt>Kategori</dt>
                                            <dd>
                                                <input type="text" value="" class="form-control" name="level" placeholder="Kategori/Level" required>
                                            </dd>
                                        </div>
                                    </div>
                                </div>
                                <div class="row clearfix">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <dd><button type="submit" name="tombol" value="Submit" class="btn btn-primary">Submit</button> </dd>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex align-items-center justify-content-between pb-0">
            <div class="card-body">
                <button class="btn btn-success" onclick="window.location.href='{{ route('copy_access') }}'">
                    <i class="tf-icons bx bx-copy-alt"></i> Copy Hak Access
                </button><br><br>
                <h3>Data User</h3>
                <table id="user" class="table table-bordered table-responsive table-striped table-hover display " style="width:100%;">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th style='text-align: center;'>Username</th>
                            <th style='text-align: center;'>Jabatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list_user as $a)
                        <tr>
                            <td style='text-align: left;'> {{ $a->nama }}  </td>
                            <td style='text-align: center;'> {{ $a->nik }}</td>
                            <td style='text-align: center;'> {{ $a->jabatan }} </td>
                            <td style='text-align: center;'><a href="{{ route('hakacc', ['userId' => $a->nik]) }}" class="btn btn-success waves-effect m-t-15 m-b-15" data-toggle="modal" data-target="#detail" ><i class="bx bx-door-open"></i> Hak Akses</a> |  <a href="#" onclick="showConfirmationPopup('{{ $a->nik }}')" class="btn btn-danger"><i class="bx bx-x-circle"></i> Delete</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
          </div>
        </div>
      </div>
    </div>
</div> 

 <!-- Modal Structure -->
 <div class="modal fade" id="detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 90%; width: 90vw;">
        <div class="modal-content">
            <div class="modal-header">
                {{-- <h5 class="modal-title">Detail Data Pegawai</h5> 
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> --}}
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Modal Body Content Will Be Loaded Dynamically Here -->
            </div>
            <div class="modal-footer">
                {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button> --}}
            </div>
        </div>
    </div>
</div>


<script>
    $('#user').dataTable( {
	    responsive: true,
        order: [[ 2, 'desc' ]],
        lengthMenu: [10, 25, 50, 100],
        pageLength: 1000,
    });
    $(document).ready(function () {
            $('a[data-toggle="modal"]').on('click', function (e) {
                e.preventDefault();
                var target_modal = $(this).data('target');
                var remote_content = $(this).attr('href');

                $(target_modal).modal('show');

                // Load content dynamically
                $('#modalContent').load(remote_content);
            });
        });
</script>
 <!-- JavaScript code to trigger the popup -->
<script>
    function showConfirmationPopup(userId) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });

        swalWithBootstrapButtons.fire({
            title: "Are you sure?",
            text: "Anda Akan Menghapus Akun " + userId + "!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to a specific route with parameter
                window.location.href = "{{ route('deleteacc', ['userId' => ':userId']) }}".replace(':userId', userId);
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Do nothing
            }
        });
    }
</script>

<script>
    $(document).ready(function() {
        $('.pegawai').select2();
    });
</script>

@endsection