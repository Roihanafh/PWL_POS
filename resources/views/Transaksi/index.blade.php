@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar Transaksi</h3>
            <div class="card-tools">
                {{-- <button onclick="modalAction('{{ url('/transaksi/import') }}')" class="btn btn-info">Import Transaksi</button> --}}
                {{-- <a href="{{ url('/transaksi/create') }}" class="btn btn-primary">Tambah Data</a> --}}
                <a href="{{ url('/transaksi/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Transaksi</a>
                <a href="{{ url('/transaksi/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Transaksi</a>
                <button onclick="modalAction('{{ url('/transaksi/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
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
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">- Semua -</option>
                                @foreach($user as $u)
                                    <option value="{{ $u->user_id }}">{{ $u->username }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">User</small>
                        </div>
                    </div>
                </div>
                <!-- Filter 2 -->
                <div class="col-md-3">
                    <div class="form-group row">
                        <label class="col-1 control-label col-form-label"></label>
                        <div class="col-10">
                            <select name="penjualan_id" id="penjualan_id" class="form-control" required>
                                <option value="">- Semua -</option>
                                @foreach($pembeli as $p)
                                    <option value="{{ $p->penjualan_id }}">{{ $p->pembeli }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Pembeli</small>
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
            <table class="table table-bordered table-striped table-hover table-sm" id="table_transaksi"> 
                <thead> 
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Pembeli</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
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
        function modalAction(url = '') {
            $('#myModal').load(url, function () {
                $('#myModal').modal('show');
            });
        }
        var dataTransaksi;
        var isAdding = false; // flag untuk mendeteksi apakah sedang menambah data
        $(document).ready(function() {
            dataTransaksi = $('#table_transaksi').DataTable({
                processing: true,
                serverSide: true, 
                ajax: {
                    "url": "{{ url('transaksi/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    data: function (d) {
                        // jika sedang menambah data, kosongkan filter
                        d.user_id = isAdding ? null : $('#user_id').val();
                        d.penjualan_id = isAdding ? null : $('#penjualan_id').val();
                    }
                },
                columns: [
                    {
                        data: "DT_RowIndex", 
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    }, {
                        data: "user",
                        className: "",
                        orderable: true,
                        searchable: true,
                        render(data, type, row) {
                            return row.user?.username ?? '-';
                        }
                    }, {
                        data: "pembeli",
                        className: "",
                        orderable: true,
                        searchable: true 
                    }, {
                        data: "penjualan_kode",
                        className: "",
                        orderable: true,
                        searchable: true 
                    }, {
                        data: "penjualan_tanggal",
                        className: "",
                        orderable: true,
                        searchable: true 
                    }, {
                        data: "aksi",
                        className: "text-center",
                        orderable: false,
                        searchable: false,
                        width: "15%"
                    }
                ]
            });
                $('#user_id, #penjualan_id').change(function () {
                    isAdding = false;
                    dataTransaksi.ajax.reload();
                })
        });
        // Fungsi ini dipanggil setelah data berhasil ditambahkan
        function reloadAfterAdd() {
            isAdding = true;
            dataTransaksi.ajax.reload(function () {
                isAdding = false;
            });
        }
    </script>
@endpush
