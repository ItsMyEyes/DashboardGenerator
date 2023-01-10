<?php

namespace KiyoraDashboard\Commands;

use KiyoraDashboard\Models\Role;
use KiyoraDashboard\Models\User;
use Illuminate\Console\Command;

class AddAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->ask("Your Name? ");
        $username = $this->ask("Your Username? ");
        $email = $this->ask("Your Email? ");
        $password = $this->ask("Password?", "babydontcry");

        $checkRole = Role::where('name', 'SuperAdmin')->first();;
        if (!$checkRole) {
            Role::create([
                'name' => 'SuperAdmin',
                'guard_name' => 'web'
            ]);
        }

        if ($name == '' || $username == "" || $email == "") {
            $this->error('Field is required');
            return true;
        }

        $checkMail = User::where('email', $email)->first();
        if ($checkMail) {
            $this->error('Email must unique');
            return true;
        }

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => bcrypt($password),
        ]);
        $user->assignRole('SuperAdmin');
        $this->info("Success You Can Login Now");
        return Command::SUCCESS;
    }
}
