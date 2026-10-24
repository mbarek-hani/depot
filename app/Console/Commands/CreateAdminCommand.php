<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create
                            {--name= : the name of the admin}
                            {--email= : the email of the admin}
                            {--password= : the password of the admin}';

    protected $description = 'Create a new admin account';

    public function handle(): int
    {
        $name = $this->option('name') ?: text(
            label: 'Name',
            placeholder: 'ex: admin',
            required: true,
        );

        $email = $this->option('email') ?: text(
            label: 'Email',
            placeholder: 'admin@example.com',
            required: true,
            validate: fn (string $value) => filter_var($value, FILTER_VALIDATE_EMAIL)
                ? null
                : 'Please enter a valid email address',
        );

        $email = strtolower(trim($email));

        if (User::where('email', $email)->exists()) {
            error('Email already taken');

            return self::FAILURE;
        }

        $password = $this->option('password') ?: password(
            label: 'Password',
            required: true,
            validate: fn (string $value) => $this->passwordError($value),
        );

        $validation = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:190', 'unique:users,email'],
                'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            ],
        );

        if ($validation->fails()) {
            foreach ($validation->errors()->all() as $message) {
                error($message);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::Admin,
            'is_active' => true,
            'receive_expiry_alerts' => true,
            'receive_low_stock_alerts' => true,
        ]);

        $user->sendEmailVerificationNotification();

        info('Admin account was created successfully!'.$user->email);
        warning('The account has not been verified yet. You should confirm the email address before logging in.');

        if (config('mail.default') === 'log') {
            $path = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->getEmailForVerification()),
                ],
                absolute: false,
            );

            $url = url($path);

            $this->newLine();
            $this->line($url);
        }

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
