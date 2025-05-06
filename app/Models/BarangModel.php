<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BarangModel extends Model {
    use HasFactory;
    protected $primaryKey = 'barang_id';
    protected $table = 'm_barang';
    protected $fillable = ['barang_nama', 'created_at', 'updated_at','barang_kode', 'harga_beli', 'harga_jual', 'harga_jual', 'kategori_id', 'image' ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriModel::class, 'kategori_id', 'kategori_id');
    }
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($image) => asset('storage/posts/'.$image),
        );
    }
}