<?php

namespace App\Policies;

use App\Models\Peticione;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PeticionePolicy
{
    use HandlesAuthorization;

    public function update(User $user, Peticione $peticione): bool
    {
        if($user->role_id == 1){
            return true;
        }
        else if ($user->role_id == 2 && $user->id == $peticione->user_id) {
            return true;
        }
        return false;
    }

    public function cambiarEstado(User $user, Peticione $peticione): bool
    {
        if($user->role_id == 1){
            return true;
        }
        return false;
    }

    public function delete(User $user, Peticione $peticione): bool
    {
        if($user->role_id == 1){
            return true;
        }
        else if (($user->role_id === 2 && $user->id === $peticione->user_id)) {
            return true;
        }
        return false;
    }

    public function firmar(User $user, Peticione $peticione): bool
    {
        if($user->role_id == 1){
            return true;
        }
        else if ($user->role_id === 2 && !$peticione->firmas()->where('user_id', $user->id)->exists()) {
            return true;
        }
        return false;
    }
}
