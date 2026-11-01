<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create
                            {--name= : the name of the admin account}
                            {--password= : the password of the admin account}';

    protected $description = 'Create an admin account';

    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?: text(
            label: 'Name',
            placeholder: 'ex: admin',
            required: true,
        )));

        if (User::query()->where('name', $name)->exists()) {
            error('This name is already taken.');

            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?: password(
            label: 'Password',
            required: true,
            validate: fn (string $value) => $this->passwordError($value),
        ));

        $validation = Validator::make(
            ['name' => $name, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:120', 'unique:users,name'],
                'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            ],
        );

        if ($validation->fails()) {
            foreach ($validation->errors()->all() as $message) {
                error($message);
            }

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'password' => $password,
            'role' => UserRole::Admin,
            'is_active' => true,
            'theme' => 'system',
        ]);

        info('Admin account "'.$name.'" was created successfully.');

        return self::SUCCESS;
    }

    protected function passwordError(string $value): ?string
    {
        $validator = Validator::make(
            ['password' => $value],
            ['password' => Password::min(8)->letters()->numbers()],
        );

        return $validator->fails() ? $validator->errors()->first('password') : null;
    }
}
