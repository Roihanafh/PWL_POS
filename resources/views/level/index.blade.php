@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools">
                <button onclick="modalAction('{{ url('/level/import') }}')" class="btn btn-info">Import Level</button>
                {{-- <a href="{{ url('/level/create') }}" class="btn btn-primary">Tambah Data</a> --}}
                <a href="{{ url('/level/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Level</a>
                <a href="{{ url('/level/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Level</a>
                <button onclick="modalAction('{{ url('level/create_ajax') }}')" class="btn btn-success" >Tambah ajax</button>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <table class="table table-bordered table-sm table-striped table-hover" id="table-level"> 
              <thead> 
                <tr><th>ID</th><th>Level Kode</th><th>Level Pengguna</th><th>Aksi</th></tr> 
              </thead> 
          </table> 
        </div> 
      </div> 
      <div id="myModal" class="modal fade animate shake" tabindex="-1" role="dialog" databackdrop="static" data-keyboard="false" data-width="75%" aria-hidden="true"></div>
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

        var tableLevel;
        $(document).ready(function() {
            tableLevel = $('#table-level').DataTable({
                serverSide: true, 
                processing: true,
                ajax: {
                    "url": "{{ url('level/list') }}",
                    "dataType": "json",
                    "type": "POST"
                },
                columns: [{
                    data: "DT_RowIndex", 
                    className: "text-center",
                    orderable: false,
                    searchable: false
                },{
                    data: "level_kode", 
                    orderable: false,
                    searchable: false
                },{
                    data: "level_nama",
                    className: "",
                    orderable: true,
                    searchable: true 
                }, {
                    data: "aksi",
                    className: "text-center",
                    width: "12%",
                    orderable: false,
                    searchable: false 
                }
            ]
        });
    });
    </script>
@endpush
