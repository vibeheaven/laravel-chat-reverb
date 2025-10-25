<?php

namespace App\Models\SymDosya;

use Illuminate\Database\Eloquent\Model;

class Kullanicilar extends Model
{
    protected $table = 'kullanicilar';
    protected $connection = 'sc_mysql';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id', 'adi', 'eposta', 'auth_token', 'sifre', 'telefon', 'start_date', 'salary', 'rol', 'yasakla', 'bolge', 'bolge', 'adres', 'il', 'ilce', 'ulke', 'konusmadili', 'kurum', 'acente', 'sifregonder', 'secret_key', 'gmail', 'sc_code', 'created_at', 'table_color', 'last_login'];

    public function getRolAttribute()
    {
        switch ($this->attributes['rol']) {
            case '1':
                return 'Yönetici';
            case '2':
                return 'Genel Merkez';
            case '0':
                return 'Personel';
            case '3':
                return 'Avukat';
            case '4':
                return 'Bölge Koordinatörü';
            case '5':
                return 'Sigorta Acentesi';
            case '6':
                return 'Acente Temsilcisi';
            case '7':
                return 'Bağımsız';
            default:
        }
    }

    /**
     * Dosya sorumlusu olarak atandığı dosyalar
     */
    public function dosyalarSorumlu()
    {
        return $this->hasMany(Dosyalar::class, 'dosyasorumlusu', 'id');
    }

    /**
     * Dosya temsilcisi olarak atandığı dosyalar
     */
    public function dosyalarTemsilci()
    {
        return $this->hasMany(Dosyalar::class, 'dosyatemsilcisi', 'id');
    }

    /**
     * Avukat olarak atandığı dosyalar
     */
    public function dosyalarAvukat()
    {
        return $this->hasMany(Dosyalar::class, 'avukat', 'id');
    }

    /**
     * Bölge koordinatörü olarak atandığı dosyalar
     */
    public function dosyalarBolgeKoordinatoru()
    {
        return $this->hasMany(Dosyalar::class, 'bolgekoordinatoru', 'id');
    }

    /**
     * Bağımsız olarak atandığı dosyalar
     */
    public function dosyalarBagimsiz()
    {
        return $this->hasMany(Dosyalar::class, 'bagimsiz', 'id');
    }

}
