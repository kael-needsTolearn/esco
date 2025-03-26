<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class sendNotif extends Notification
{
    use Queueable;
    private $ForgetPassData;
    private $ctr;

    /**
     * Create a new notification instance.
     */
    public function __construct($ForgetPassData,$ctr)
    {
        $this->ForgetPassData =$ForgetPassData;   
        $this->ctr =$ctr;   

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
       // dd($this->ForgetPassData);
        if( $this->ctr ===1   ){

            return (new MailMessage)
            ->line($this->ForgetPassData['body'])
            ->action('Login Here', 
            $this->ForgetPassData['url'])
            ->line($this->ForgetPassData['ThankyouMessage']);
            
        }else if($this->ctr ===2 ){
            return (new MailMessage)
            ->line($this->ForgetPassData['body'])
            ->action('Reset Password', 
            $this->ForgetPassData['url'])
            ->line($this->ForgetPassData['ThankyouMessage']);
        }else{
            return (new MailMessage)
            ->line($this->ForgetPassData['body'])
            ->action('Reset Password', 
            $this->ForgetPassData['url'])
            ->line($this->ForgetPassData['ThankyouMessage']);
        }
       
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
