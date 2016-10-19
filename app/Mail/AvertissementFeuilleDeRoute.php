<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AvertissementFeuilleDeRoute extends Mailable
{
    use Queueable, SerializesModels;

    public $emission;
    public $fdr;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emission, $fdr)
    {
        $this->emission = $emission;
        $this->fdr = $fdr;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('programmation@chyz.ca')
            ->subject("Feuille de route - {$this->emission['post_title']} du {$this->fdr['creation_date']}")
            ->view('emails.avertissementFdR');
    }
}
