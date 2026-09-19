<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Creates the first admin on a fresh install, or promotes an existing user.
 *
 * Production is never seeded, so there is no admin account until this runs.
 */
class MakeAdminUser extends Command
{
    protected $signature = 'user:admin
                            {email : The account email}
                            {--name= : Display name (new accounts only)}
                            {--role=admin : admin or staff}';

    protected $description = 'Create an admin/staff user, or promote an existing one';

    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->option('role');

        if (! in_array($role, ['admin', 'staff'], true)) {
            $this->error('Role must be admin or staff.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update(['role' => $role]);
            $this->info("Promoted {$user->name} <{$email}> to {$role}.");

            return self::SUCCESS;
        }

        $name = $this->option('name') ?: $this->ask('Display name');
        $password = $this->secret('Password (min 8 characters)');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            [
                'email' => ['required', 'email', 'unique:users,email'],
                'name' => ['required', 'string', 'max:191'],
                'password' => ['required', Password::min(8)],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        $this->info("Created {$role} account for {$email}.");

        return self::SUCCESS;
    }
}
