@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
        </div>
        <div class="card-body">
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
@endsection

@push('css')
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            var dataTransaksi = $('#table_transaksi').DataTable({
                serverSide: true, 
                ajax: {
                    "url": "{{ url('transaksi/list') }}",
                    "dataType": "json",
                    "type": "POST"
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
                        searchable: true 
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
                        className: "",
                        orderable: false,
                        searchable: false 
                    }
                ]
            });
        });
    </script>
@endpush
