<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class KategoriModel extends Model {
    use HasFactory;
    protected $primaryKey = 'kategori_id';
    protected $table = 'm_kategori';
    protected $fillable = ['kategori_nama', 'kategori_kode' ];
}