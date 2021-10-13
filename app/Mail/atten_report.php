<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class atten_report extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // dd($this->data);
        // if ($this->data == 1) {
        //     $
        // }
        // elseif ($this->data == 2) {
        //     # code...
        // }
        // elseif ($this->data == 3) {
        //     # code...
        // }
        // return $this->view('mail.late_atten');
        return $this->from('test.01synergy@gmail.com')
            ->view('mail.late_atten')->with('data', $this->data['interval'])
            ->attach(public_path('late_atten/' . $this->data['csv_name'] . '.csv'));
    }
}
