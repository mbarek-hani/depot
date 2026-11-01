<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class ChangeAdminPasswordCommand extends Command
{
    protected $signature = 'admin:password
                            {name? : the name of the admin account}
                            {--password= : the new password}';

    protected $description = 'Change the password of an admin account';

    public function handle(): int
    {
        $name = trim((string) ($this->argument('name') ?: text(
            label: 'Admin name',
            placeholder: 'ex: admin',
            required: true,
        )));

        $admin = User::query()->where('name', $name)->first();

        if (! $admin) {
            error('No account was found with that name.');

            return self::FAILURE;
        }

        if (! $admin->isAdmin()) {
            error('This account is not an admin account.');

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

        $admin->update([
            'password' => $newPassword,
            'remember_token' => null,
        ]);

        DB::table('sessions')->where('user_id', $admin->id)->delete();

        info('Password updated for admin "'.$admin->name.'".');

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
