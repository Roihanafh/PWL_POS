@if(empty($detail_penjualan))
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-triangle mr-2"></i>Kesalahan</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <h5><i class="icon fas fa-ban"></i> Kesalahan!!!</h5>
                    Data yang anda cari tidak ditemukan
                </div>
                <a href="{{ url('/stok') }}" class="btn btn-warning"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
            </div>
        </div>
    </div>
@else
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-receipt mr-2"></i>Penjualan</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm mb-3">
                    <tr><th width="30%">ID Penjualan</th><td>{{ $penjualan->penjualan_id }}</td></tr>
                    <tr><th>Kode Penjualan</th><td>{{ $penjualan->penjualan_kode }}</td></tr>
                    <tr><th>Tanggal</th><td>{{ \Carbon\Carbon::parse($penjualan->penjualan_tanggal)->format('d-m-Y H:i') }}</td></tr>
                    <tr><th>Pembeli</th><td>{{ $penjualan->pembeli }}</td></tr>
                    <tr><th>Kasir</th><td>{{ $penjualan->user->username }}</td></tr>
                </table>

                <h5 class="mt-4 mb-2">Detail Penjualan</h5>
                <table class="table table-bordered table-striped table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detail_penjualan as $detail)
                            <tr>
                                <td>{{ $detail->barang->barang_nama ?? '-' }}</td>
                                <td>{{ $detail->jumlah }}</td>
                                <td>Rp{{ number_format($detail->harga, 0, ',', '.') }}</td>
                                <td>Rp{{ number_format($detail->jumlah * $detail->harga, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-secondary"><i class="fas fa-times mr-1"></i> Batal</button>
            </div>
        </div>
    </div>
@endif
