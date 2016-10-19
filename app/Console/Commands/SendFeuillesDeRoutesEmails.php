<?php

namespace App\Console\Commands;

use App\Fdr;
use App\Mail\AvertissementFeuilleDeRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendFeuillesDeRoutesEmails extends Command
{
    protected $emission;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jdb:send-emails {emId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Envoie des courriels de notification pour les feuilles de routes d'une émission.";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->emission = collect(Cache::get("journal:ems:{$this->argument('emId')}"));

        $fdrNonTermines = Fdr::where('em_id', $this->emission['ID'])
            ->where('approuve', 0)->get();

        $fdrNonTermines->each(function ($item, $key){
            Mail::to($this->emission['courriel'])->queue(new AvertissementFeuilleDeRoute($this->emission, $item));
            Log::notice("Courriel d'avertissement envoyé pour {$this->emission['post_title']}: {$item['creation_date']}");
        });
    }
}
