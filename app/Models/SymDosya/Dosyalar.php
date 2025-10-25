<?php

namespace App\Models\SymDosya;

use Illuminate\Database\Eloquent\Model;

class Dosyalar extends Model
{
    protected $table = 'dosyalar';
    protected $connection = 'sc_mysql';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = [
        'id', 'dosyano', 'adisoyadi', 'tc', 'dogumtarihi', 'telefon', 'il', 'ilce', 'adres', 
        'bolgesi', 'dosyatemsilcisi', 'dosyasorumlusu', 'bagimsiz', 'bolgekoordinatoru', 
        'durum', 'gelistarihi', 'avukat', 'ip', 'kazaozeti', 'kazaozetitarih', 'sozlesmetarihi', 
        'hasartarihi', 'odemetarihi', 'muracaattarihi', 'uyaritarihi', 'raportarihi', 
        'hatirlatmatarihi', 'sigortadosyano', 'sigortasirketi', 'policeno', 'sigortabilgisi', 
        'surucubilgisi', 'aracplakasi', 'kusurdurumu', 'sorumluadsoyad', 'aciklama', 
        'anlasmatutari', 'ilkislem', 'sondurum', 'ilmudurlugu', 'anlasmaorani', 'tazminatturu', 
        'vekaletname', 'temlikname', 'sozlesme', 'trafikkazasitespittutanagi', 'police', 
        'ruhsat', 'ehliyet', 'alkolraporu', 'ifadeler', 'bilirkisiraporu', 'adlitipkusurraporu', 
        'iddianame', 'kimlikfotokopisi', 'mahkemekarari', 'ibannumarasi', 'sakatlikraporu', 
        'geneladlimuayeneraporu', 'epikriz', 'gelirbelgesi', 'vukuatlinufuskayitornegi', 
        'kazafotografi', 'eksperraporu', 'fatura', 'olumuayeneotopsi', 'olumbelgesi', 
        'verasetilani', 'created_at', 'arsiv', 'table_color'
    ];

    /**
     * Get the dosya sorumlusu (file responsible person) relationship
     */
    public function dosyaSorumlusu()
    {
        return $this->belongsTo(Kullanicilar::class, 'dosyasorumlusu', 'id')->select('id', 'adi');
    }

    /**
     * Get the dosya temsilcisi (file representative) relationship
     */
    public function dosyaTemsilcisi()
    {
        return $this->belongsTo(Kullanicilar::class, 'dosyatemsilcisi', 'id')->select('id', 'adi');
    }

    /**
     * Get the avukat (lawyer) relationship
     */
    public function avukat()
    {
        return $this->belongsTo(Kullanicilar::class, 'avukat', 'id')->select('id', 'adi');
    }

    /**
     * Get the bolge koordinatoru (regional coordinator) relationship
     */
    public function bolgeKoordinatoru()
    {
        return $this->belongsTo(Kullanicilar::class, 'bolgekoordinatoru', 'id')->select('id', 'adi');
    }

    /**
     * Get the bagimsiz (independent) relationship
     */
    public function bagimsiz()
    {
        return $this->belongsTo(Kullanicilar::class, 'bagimsiz', 'id')->select('id', 'adi');
    }

    /**
     * Get the ilk islem (first action) relationship
     */
    public function ilkIslem()
    {
        return $this->belongsTo(DosyalarSabit::class, 'ilkislem', 'id')->select('id', 'sabiticerik');
    }

    /**
     * Get the son durum (last status) relationship
     */
    public function sonDurum()
    {
        return $this->belongsTo(DosyalarSabit::class, 'sondurum', 'id')->select('id', 'sabiticerik');
    }

    /**
     * Get the tazminat turu (compensation type) relationship
     */
    public function tazminatTuru()
    {
        return $this->belongsTo(DosyalarSabit::class, 'tazminatturu', 'id')->select('id', 'sabiticerik');
    }


}
