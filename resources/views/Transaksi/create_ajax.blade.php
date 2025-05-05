<!-- Modal Create Transaksi -->
<div class="modal-dialog modal-lg" role="document">
    <form action="{{ url('/transaksi/ajax') }}" method="POST" id="form-transaksi">
        @csrf
        <input type="hidden" name="penjualan_tanggal" value="{{ now() }}">
        <input type="hidden" name="user_id" value="{{ Auth::user()->user_id }}">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTransaksiLabel">Tambah Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>User</label>
                    <input type="text" class="form-control" value="{{ Auth::user()->username }}" readonly>
                    <small class="form-text text-muted">User diambil otomatis dari akun yang sedang login.</small>
                </div>

                <div class="form-group">
                    <label>Nama Pembeli</label>
                    <input type="text" name="pembeli" class="form-control" required>
                </div>

                <div id="barang-wrapper">
                    <div class="row barang-item mb-3">
                        <div class="col-md-5">
                            <label>Barang</label>
                            <select name="barang_id[]" class="form-control barang-select" required>
                                <option value="">- Pilih Barang -</option>
                                @foreach($barang as $b)
                                    <option value="{{ $b->barang_id }}" data-harga="{{ $b->harga_jual }}">
                                        {{ $b->barang_nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Harga</label>
                            <input type="number" name="harga[]" class="form-control harga-input" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Jumlah</label>
                            <input type="number" name="jumlah[]" class="form-control" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-remove-barang">×</button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-secondary mb-2" id="tambah-barang">+ Tambah Barang</button>
            </div>

            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-warning">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>


<script>
$(document).ready(function () {
    // Set harga otomatis saat barang dipilih
    $(document).on('change', '.barang-select', function () {
        var harga = $(this).find(':selected').data('harga');
        $(this).closest('.barang-item').find('.harga-input').val(harga);
    });

    // Tambah baris barang baru
    $('#tambah-barang').on('click', function () {
        var clone = $('.barang-item:first').clone();
        clone.find('select, input').val('');
        $('#barang-wrapper').append(clone);
    });

    // Hapus baris barang
    $(document).on('click', '.btn-remove-barang', function () {
        if ($('.barang-item').length > 1) {
            $(this).closest('.barang-item').remove();
        }
    });

    // Submit AJAX
    $('#form-transaksi').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr("action"),
            method: "POST",
            data: $(this).serialize(),
            success: function (res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    $('#modal-transaksi').modal('hide');
                    location.reload();
                    dataTransaksi.ajax.reload();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });
});
</script>
