<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // relatie naar user
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // many-to-many met categories
use Illuminate\Database\Eloquent\Relations\MorphMany; // meerdere media per post
use Illuminate\Database\Eloquent\Relations\MorphOne; // 1 featured image
use Illuminate\Database\Eloquent\SoftDeletes; // soft deletes voor posts
use Illuminate\Support\Str; // slug genereren
class Post extends Model
{
    use SoftDeletes; // post kan soft deleted en gerestored worden
    protected $fillable = [
        'user_id', // auteur van de post
        'title', // titel van de post
        'slug', // unieke slug
        'excerpt', // korte samenvatting
        'body', // volledige inhoud
        'is_published', // publicatiestatus
        'published_at', // publicatiedatum
    ];
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean', // boolean gedrag voor status
            'published_at' => 'datetime', // publicatiedatum als datetime
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            if (blank($post->slug) && filled($post->title)) {
                $post->slug = Str::slug($post->title); // extra veiligheid op modelniveau
}
});
        static::deleting(function (Post $post): void {
            if ($post->isForceDeleting()) {
                $post->media()
                    ->withTrashed()
                    ->get()
                    ->each(fn (Media $media) => $media->forceDelete());
                return;
            }
            $post->media()
                ->get()
                ->each(fn (Media $media) => $media->delete());
        });
        static::restored(function (Post $post): void {
            $post->media()
                ->withTrashed()
                ->get()
                ->each(function (Media $media): void {
                    if ($media->trashed()) {
                        $media->restore();
                    }
                });
        });
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // elke post hoort bij 1 user
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class); // een post heeft meerdere categorieën
}
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable'); // alle media van deze post
}
    public function featuredImage(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')
            ->where('is_featured', true); // één featured image
    }
}
