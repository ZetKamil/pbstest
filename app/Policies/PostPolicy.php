<?php
namespace App\Policies;
use App\Models\Post; // het model waarop de policy werkt
use App\Models\User; // de ingelogde gebruiker
use Illuminate\Auth\Access\Response; // optioneel voor duidelijke responses
class PostPolicy
{
    /**
     * Mag de gebruiker de lijst van posts zien?
     */
    public function viewAny(User $user): bool
    {
        return true; // iedereen die ingelogd is mag de postlijst zien
    }
    /**
     * Mag de gebruiker één specifieke post bekijken?
     */
    public function view(User $user, Post $post): bool
    {
        return true; // voorlopig mag iedereen die in de admin zit een post bekijken
}
    /**
     * Mag de gebruiker een nieuwe post aanmaken?
     */
    public function create(User $user): bool
    {
        return true; // voorlopig mag iedere admingebruiker een post aanmaken
    }
    /**
     * Mag de gebruiker deze post bewerken?
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // alleen de auteur mag zijn eigen post bewerken
}
    /**
     * Mag de gebruiker deze post soft deleten?
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // alleen de auteur mag zijn eigen post verwijderen
}
    /**
     * Mag de gebruiker deze post restoren?
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // alleen de auteur mag zijn eigen post herstellen
}
    /**
     * Mag de gebruiker deze post definitief verwijderen?
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // alleen de auteur mag zijn eigen post permanent verwijderen
}
    /**
     * Mag de gebruiker deze post publiceren?
     */
    public function publish(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // alleen de auteur mag publiceren
}
    /**
     * Mag de gebruiker deze post depubliceren?
     */
    public function unpublish(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // alleen de auteur mag depubliceren
}
}
