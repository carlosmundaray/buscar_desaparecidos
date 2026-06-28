<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {name? : Nombre del administrador} {email? : Correo electronico} {password? : Contrasena}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Crea un usuario administrador para el panel de control';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name') ?? $this->ask('Nombre del administrador');
        $email = $this->argument('email') ?? $this->ask('Correo electrónico');
        $password = $this->argument('password') ?? $this->secret('Contraseña (mínimo 8 caracteres)');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->error('Error de validación al crear el administrador:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("- $error");
            }
            return 1;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Administrador '{$user->name}' creado con éxito (ID: {$user->id}).");
        return 0;
    }
}
