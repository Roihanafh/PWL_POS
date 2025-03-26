<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;//implementasi class authenticatable

class UserModel extends Authenticatable
{
    use HasFactory;

    protected $table = 'm_user';    //mendefinisikan nama table yang digunakan model ini
    protected $primaryKey = 'user_id';//mendefinisikan PK dari tabel yang digunakan
    protected $fillable = ['level_id', 'username', 'nama','password', 'created_at','updated_at'];

    protected $hidden = ['password']; //jangan di tampilkan saat select
    protected $casts = ['password' => 'hashed'];//castiing password agar langsung dihash

    public function level(): BelongsTo
    {
        return $this->belongsTo(LevelModel::class,'level_id','level_id');
    }

    public function getRoleName():string
    {
        return $this->level->level_nama;
    }

    public function hasRole($role):bool
    {
        return $this->level->level_kode == $role;
    }
    public function getRole()
    {
        return $this->level->level_kode;
    }
}
