<?php

namespace App\Models\SymDosya;

use Illuminate\Database\Eloquent\Model;

class DosyalarSabit extends Model
{
    protected $table = 'dosyalar_sabit';
    protected $connection = 'sc_mysql';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id', 'sabitadi', 'sabiticerik', 'user_id', 'dosya_ust', 'eklenmetarihi'];
}
