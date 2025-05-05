@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools">
                {{-- <button onclick="modalAction('{{ url('stok/import') }}')" class="btn btn-info">Import Stok</button> --}} 
                {{-- <a class="btn btn-sm btn-primary mt-1" href="{{ url('stok/create') }}">Tambah</a> --}}
                <a href="{{ url('stok/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Stok</a>
                <a href="{{ url('stok/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Stok</a>
                <button onclick="modalAction('{{ url('stok/create_ajax') }}')" class="btn btn-success" >Tambah ajax</button>
            </div>
        </div>
        <div class="card-body">
            <!-- untuk Filter data -->
            <div class="row">
                <!-- Filter 1 -->
                <div class="col-md-3">
                    <div class="form-group row">
                        <label class="col-2 control-label col-form-label">Filter :</label>
                        <div class="col-10">
                            <select name="supplier_id" id="supplier_id" class="form-control" required>
                                <option value="">- Semua -</option>
                                @foreach($supplier as $s)
                                    <option value="{{ $s->supplier_id }}">{{ $s->supplier_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Supplier</small>
                        </div>
                    </div>
                </div>
                <!-- Filter 2 -->
                <div class="col-md-3">
                    <div class="form-group row">
                        <label class="col-1 control-label col-form-label"></label>
                        <div class="col-10">
                            <select name="barang_id" id="barang_id" class="form-control" required>
                                <option value="">- Semua -</option>
                                @foreach($barang as $b)
                                    <option value="{{ $b->barang_id }}">{{ $b->barang_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Barang</small>
                        </div>
                    </div>
                </div>
                <!-- Filter 3 -->
                <div class="col-md-3">
                    <div class="form-group row">
                        <div class="col-10">
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">- Semua -</option>
                                @foreach($user as $u)
                                    <option value="{{ $u->user_id }}">{{ $u->username }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Username</small>
                        </div>
                    </div>
                </div>
            </div>
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <table class="table table-bordered table-striped table-hover table-sm" id="table_stok"> 
                <thead> 
                    <tr>
                        <th>ID</th>
                        <th>Supplier</th>
                        <th>Barang</th>
                        <th>User</th>
                        <th>Tanggal</th>
                        <th>Jumlah Stok</th>
                        <th>Aksi</th>
                    </tr> 
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
    
    function modalAction(url = '') {
        $('#myModal').load(url, function () {
            $('#myModal').modal('show');
        });
    }
    var dataStok;   
    var isAdding = false; // flag untuk mendeteksi apakah sedang menambah data

    $(document).ready(function () {
        dataStok = $('#table_stok').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('stok/list') }}",
                dataType: "json",
                type: "POST",
                data: function (d) {
                    // jika sedang menambah data, kosongkan filter
                    d.user_id = isAdding ? null : $('#user_id').val();
                    d.barang_id = isAdding ? null : $('#barang_id').val();
                    d.supplier_id = isAdding ? null : $('#supplier_id').val();
                }
            },
            search: {
                smart: false,
                regex: true
            },
            columns: [
                { data: "DT_RowIndex", className: "text-center", orderable: false, searchable: false },
                {
                    data: "supplier.supplier_nama",
                    orderable: true,
                    searchable: true,
                    render: function (data, type, row) {
                        return row.supplier?.supplier_nama ?? '-';
                    }
                },
                {
                    data: "barang.barang_nama",
                    orderable: true,
                    searchable: true,
                    render: function (data, type, row) {
                        return row.barang?.barang_nama ?? '-';
                    }
                },
                {
                    data: "user.username",
                    orderable: true,
                    searchable: true,
                    render: function (data, type, row) {
                        return row.user?.username ?? '-';
                    }
                },
                { data: "stok_tanggal", orderable: true, searchable: false },
                { data: "stok_jumlah", orderable: true, searchable: false },
                { data: "aksi", className: "text-center", orderable: false, searchable: false, width: "10%" }
            ]
        });

        // Event untuk filter tetap
        $('#user_id, #barang_id, #supplier_id').change(function () {
            isAdding = false;
            dataStok.ajax.reload();
        });
    });

    // Fungsi ini dipanggil setelah data berhasil ditambahkan
    function reloadAfterAdd() {
        isAdding = true;
        dataStok.ajax.reload(function () {
            isAdding = false;
        });
    }
</script>
@endpush
