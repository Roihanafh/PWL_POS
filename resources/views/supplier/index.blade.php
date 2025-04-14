@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools">
                <button onclick="modalAction('{{ url('/supplier/import') }}')" class="btn btn-info">Import supplier</button>
                {{-- <a class="btn btn-sm btn-primary mt-1" href="{{ url('supplier/create') }}">Tambah</a> --}}
                <a href="{{ url('/supplier/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export supplier</a>
                <a href="{{ url('/supplier/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export supplier</a>
                <button onclick="modalAction('{{ url('/supplier/create_ajax') }}')" class="btn btn-success" >Tambah ajax</button>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <table class="table table-bordered table-sm table-striped table-hover" id="table-supplier"> 
              <thead> 
                <tr>
                    <th>ID</th>
                    <th>Kode Supplier</th>
                    <th>Nama Supplier</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr> 
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

        var tableSupplier;
        $(document).ready(function() {
            tableSupplier = $('#table-supplier').DataTable({
                serverSide: true, 
                ajax: {
                    "url": "{{ url('supplier/list') }}",
                    "dataType": "json",
                    "type": "POST"
                },
                columns: [
                    {
                        data: "supplier_id", 
                        className: "",
                        orderable: true,
                        searchable: true
                    }, 
                    {
                        data: "supplier_kode",
                        className: "",
                        orderable: true,
                        searchable: true 
                    }, 
                    {
                        data: "supplier_nama",
                        className: "",
                        orderable: true,
                        searchable: true 
                    }, 
                    {
                        data: "supplier_alamat",
                        className: "",
                        orderable: true,
                        searchable: true 
                    }, 
                    {
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
