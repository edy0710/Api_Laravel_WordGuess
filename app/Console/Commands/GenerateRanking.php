<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

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
        $users = User::orderByDesc('points')
                ->take(10)
                ->get(['name', 'points']);
        
        $this->table(
            ['Posición', 'Nombre', 'Puntos'],
            $users->map(function ($user, $index) {
                return [
                    $index + 1,
                    $user->name,
                    $user->points
                ];
            })
        );
    }
}
