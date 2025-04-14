@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools">
                <button onclick="modalAction('{{ url('/user/import') }}')" class="btn btn-info">Import user</button>
                {{-- <a class="btn btn-sm btn-primary mt-1" href="{{ url('user/create') }}">Tambah</a> --}}
                <a href="{{ url('/user/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export user</a>
                <a href="{{ url('/user/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export user</a>
                <button onclick="modalAction('{{ url('user/create_ajax') }}')" class="btn btn-success" >Tambah ajax</button>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="row">
                <div class="col-ms-12">
                    <div class="form-group row">
                        <label class="col-4 control-label col-form-label">Filter :</label>
                        <div class="col-8">
                            <select name="level_id" id="level_id" class="form-control" required>
                                <option value="">- Semua -</option>
                                @foreach ($level as $item)
                                    <option value="{{ $item->level_id }}">{{ $item->level_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Level Pengguna</small>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table table-bordered table-sm table-striped table-hover" id="table-user"> 
              <thead> 
                <tr><th>ID</th><th>Username</th><th>Nama</th><th>Level Pengguna</th><th>Aksi</th></tr> 
              </thead> 
          </table> 
        </div> 
      </div> 
      <div id="myModal" class="modal fade animate shake" tabindex="-1" data-backdrop="static"data-keyboard="false" data-width="75%"></div>
@endsection

@push('css')
@endpush

@push('js')
    <script>
        function modalAction(url = ''){
                $('#myModal').load(url,function(){
                $('#myModal').modal('show');
            });
        }

        var tableUser;
        $(document).ready(function() {
            tableUser = $('#table-user').DataTable({
                processing: true,
                serverSide: true, // serverSide: true, jika ingin menggunakan server side processing
                ajax: {
                    "url": "{{ url('user/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        d.level_id = $('#level_id').val();
                    }
                },
                columns: [{
                    data: "DT_RowIndex", // nomor urut dari laravel datatable addIndexColumn()
                    className: "text-center",
                    orderable: false,
                    searchable: false
                }, {
                    data: "username",
                    className: "",
                    orderable: true, // orderable: true, jika ingin kolom ini bisa diurutkan
                    searchable: true // searchable: true, jika ingin kolom ini bisa dicari
                }, {
                    data: "nama",
                    className: "",
                    orderable: true, 
                    searchable: true 
                }, {
                    data: "level.level_nama",
                    className: "",
                    orderable: false, 
                    searchable: false
                }, {
                    data: "aksi",
                    className: "text-center",
                    width: "12%",
                    orderable: false, 
                    searchable: false 
                }
            ]
        });

        $('#level_id').on('change', function() {
            tableUser.ajax.reload();
        });
    });
    </script>
@endpush