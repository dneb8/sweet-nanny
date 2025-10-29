<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $firstRole = null;
        $roles = [];

        if ($this->relationLoaded('roles')) {
            $roles = $this->roles->pluck('name')->values()->all();
            $firstRole = $this->roles->first()->name ?? null;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'surnames' => $this->surnames,
            'email' => $this->email,
            'number' => $this->number,
            'role' => $firstRole,
            'roles' => $roles,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
