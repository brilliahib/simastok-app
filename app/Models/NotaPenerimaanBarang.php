<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaPenerimaanBarang extends Model
{
    protected $fillable = ['penerima', 'tanggal', 'file_path'];

    public function pergerakanBarangs()
    {
        return $this->hasMany(PergerakanBarang::class);
    }
}
