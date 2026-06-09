<?php

namespace App\Policies;

use App\Models\QrCode;
use App\Models\User;

class QrCodePolicy
{
    public function view(User $user, QrCode $qrCode): bool
    {
        return $qrCode->user_id === $user->id;
    }

    public function update(User $user, QrCode $qrCode): bool
    {
        return $this->view($user, $qrCode);
    }

    public function delete(User $user, QrCode $qrCode): bool
    {
        return $this->view($user, $qrCode);
    }
}
