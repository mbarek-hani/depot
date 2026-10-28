<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class ChangeUserPasswordCommand extends Command
{
    protected $signature = 'user:password
                            {name? : the name of the account}
                            {--password= : the new password}';

    protected $description = 'Change the password of a local account';

    public function handle(): int
    {
        $name = trim((string) ($this->argument('name') ?: text(
            label: 'Account name',
            placeholder: 'ex: سارة',
            required: true,
        )));

        $user = User::query()->where('name', $name)->first();

        if (! $user) {
            error('No account was found with that name.');

            return self::FAILURE;
        }

        $newPassword = (string) ($this->option('password') ?: password(
            label: 'New password',
            required: true,
            validate: fn (string $value) => $this->passwordError($value),
        ));

        $validation = Validator::make(
            ['password' => $newPassword],
            ['password' => ['required', 'string', Password::min(8)->letters()->numbers()]],
        );

        if ($validation->fails()) {
            foreach ($validation->errors()->all() as $message) {
                error($message);
            }

            return self::FAILURE;
        }

        $user->update(['password' => $newPassword]);

        info('Password updated for "'.$user->name.'".');

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
