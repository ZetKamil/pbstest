<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; // basis Eloquent model
use Illuminate\Database\Eloquent\Relations\MorphTo; // inverse polymorfe relatie
use Illuminate\Database\Eloquent\SoftDeletes; // soft deletes trait
use Illuminate\Support\Facades\Storage; // bestand verwijderen van disk
class Media extends Model
{
    use SoftDeletes; // mediarecords kunnen soft deleted worden
    protected $fillable = [
        'disk', // storage disk waarop bestand staat
        'file_name', // bestandsnaam
'file_path', // pad op de disk
'mime_type', // mime type van het bestand
'file_size', // grootte in bytes
'alt_text', // alt-tekst
'caption', // optioneel onderschrift
'sort_order', // sorteervolgorde
'is_featured', // featured image of niet
];
protected static function booted(): void
{
    static::forceDeleted(function (Media $media): void {
// Alleen bij een echte definitieve verwijdering ruimen we ook het bestand op schijf op.
if (blank($media->file_path)) {
return; // niets te verwijderen
}
$disk = $media->disk ?: 'public'; // fallback naar public als
if (Storage::disk($disk)->exists($media->file_path)) {
    Storage::disk($disk)->delete($media->file_path); // fysiekbestand verwijderen
}
});
}
public function mediable(): MorphTo
{
    return $this->morphTo(); // koppeling terug naar Post, User, Category, ...
}
}
