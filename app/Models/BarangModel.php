<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class BarangModel extends Model {
    use HasFactory;
    protected $primaryKey = 'barang_id';
    protected $table = 'm_barang';
    protected $fillable = ['barang-nama', 'barang_kode', 'harga_beli', 'harga_jual', 'harga_jual', 'kategori_id' ];
}