@extends('layouts.template')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar barang</h3>
        <div class="card-tools">
            <button onclick="modalAction('{{ url('/barang/import') }}')" class="btn btn-info">Import Barang</button>
            {{-- <a href="{{ url('/barang/create') }}" class="btn btn-primary">Tambah Data</a> --}}
            <a href="{{ url('/barang/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Barang</a>
            <a href="{{ url('/barang/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Barang</a>
            <button onclick="modalAction('{{ url('/barang/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
        </div>
    </div>
    <div class="card-body">
        <!-- untuk Filter data -->
        <div class="row">
            <div class="col-ms-12">
                <div class="form-group row">
                    <label class="col-4 control-label col-form-label">Filter :</label>
                    <div class="col-8">
                        <select name="kategori_id" id="kategori_id" class="form-control" required>
                            <option value="">- Semua -</option>
                            @foreach($kategori as $l)
                                <option value="{{ $l->kategori_id }}">{{ $l->kategori_nama }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Kategori Barang</small>
                    </div>
                </div>
            </div>
        </div>
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <table class="table table-bordered table-sm table-striped table-hover" id="table-barang">
        <thead>
        <tr><th>No</th><th>Kode Barang</th><th>Nama Barang</th><th>Harga Beli</th><th>Harga Jual</th><th>Kategori</th><th>Aksi</th></tr>
        </thead>
        <tbody></tbody>
        </table>
    </div>
</div>
<div id="myModal" class="modal fade animate shake" tabindex="-1" data-backdrop="static"data-keyboard="false" data-width="75%"></div>
@endsection
@push('js')
<script>
    function modalAction(url = ''){
        $('#myModal').load(url,function(){
            $('#myModal').modal('show');
        });
    }
    var tableBarang;
        $(document).ready(function(){
            tableBarang = $('#table-barang').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                "url": "{{ url('barang/list') }}",
                "dataType": "json",
                "type": "POST",
                "data": function (d) {
                    d.kategori_id = $('#kategori_id').val();
                }
            },
            columns: [{
                    data: "barang_id",
                    className: "text-center",
                    width: "5%",
                    orderable: true,
                    searchable: false
                },{
                    data: "barang_kode",
                    className: "",
                    width: "10%",
                    orderable: true,
                    searchable: true
                },{
                    data: "barang_nama",    
                    className: "",
                    width: "37%",
                    orderable: true,
                    searchable: true,
                },{
                    data: "harga_beli",
                    className: "",
                    width: "10%",
                    orderable: true,
                    searchable: false,
                    render: function(data, type, row){
                    return new Intl.NumberFormat('id-ID').format(data);
                }
                },{
                    data: "harga_jual",
                    className: "",
                    width: "10%",
                    orderable: true,
                    searchable: false,
                    render: function(data, type, row){
                    return new Intl.NumberFormat('id-ID').format(data);
                }
                },{
                    data: "kategori.kategori_nama",
                    className: "",
                    width: "14%",
                    orderable: true,
                    searchable: false
                },{
                    data: "aksi",
                    className: "text-center",
                    width: "14%",
                    orderable: false,
                    searchable: false
            }]
        });

        $('#kategori_id').change(function(){
            tableBarang.ajax.reload();
        });
    });
</script>
@endpush
