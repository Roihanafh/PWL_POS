@empty($penjualan)
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kesalahan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Kesalahan!!!</h5>
                    Data penjualan tidak ditemukan.
                </div>
                <a href="{{ url('/penjualan') }}" class="btn btn-warning">Kembali</a>
            </div>
        </div>
    </div>
@else
<form action="{{ url('transaksi/' . $penjualan->penjualan_id . '/update_ajax') }}" method="POST" id="form-edit-transaksi">
    @csrf
    @method('PUT')
    <input type="hidden" name="penjualan_tanggal" value="{{ $penjualan->penjualan_tanggal }}">
    <input type="text" name="user_id" value="{{ Auth::user()->user_id }}" >

    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>User</label>
                    <input type="text" class="form-control" value="{{ $penjualan->user->username }}" readonly>
                    <small class="form-text text-muted">User dari data transaksi.</small>
                </div>

                <div class="form-group">
                    <label>Nama Pembeli</label>
                    <input type="text" name="pembeli" class="form-control" value="{{ $penjualan->pembeli }}" required>
                </div>

                <div id="barang-wrapper">
                    @foreach($penjualanDetail as $detail)
                    <div class="row barang-item mb-3">
                        <div class="col-md-5">
                            <label>Barang</label>
                            <select name="barang_id[]" class="form-control barang-select" required>
                                <option value="">- Pilih Barang -</option>
                                @foreach($barang as $b)
                                    <option value="{{ $b->barang_id }}" data-harga="{{ $b->harga_jual }}"
                                        {{ $b->barang_id == $detail->barang_id ? 'selected' : '' }}>
                                        {{ $b->barang_nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Harga</label>
                            <input type="number" name="harga[]" class="form-control harga-input"
                                   value="{{ $detail->harga }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Jumlah</label>
                            <input type="number" name="jumlah[]" class="form-control" value="{{ $detail->jumlah }}" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-remove-barang">×</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-secondary mb-2" id="tambah-barang">+ Tambah Barang</button>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function () {
    // Update harga otomatis saat barang dipilih
    $(document).on('change', '.barang-select', function () {
        var harga = $(this).find(':selected').data('harga');
        $(this).closest('.barang-item').find('.harga-input').val(harga);
    });

    // Tambah barang baru
    $('#tambah-barang').click(function () {
        var clone = $('.barang-item:first').clone();
        clone.find('select, input').val('');
        $('#barang-wrapper').append(clone);
    });

    // Hapus barang
    $(document).on('click', '.btn-remove-barang', function () {
        if ($('.barang-item').length > 1) {
            $(this).closest('.barang-item').remove();
        }
    });

    // Submit form via AJAX
    $('#form-edit-transaksi').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: this.action,
            method: this.method,
            data: $(this).serialize(),
            success: function (res) {
                if (res.status) {
                    $('#myModal').modal('hide');
                    Swal.fire('Berhasil', res.message, 'success');
                    dataTransaksi.ajax.reload();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });
});
</script>
@endempty
