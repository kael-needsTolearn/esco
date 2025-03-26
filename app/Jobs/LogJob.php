<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $Name;
    private $Activity;

    public function __construct($Name, $Activity)
    {
        $this->Name = $Name;
        $this->Activity = $Activity;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $Name = $this->Name;
        $Activity = $this->Activity;

        $this->storeLogFile($Name, $Activity);
    }

    private function storeLogFile($Name, $Activity)
    {
        $dateTime = new \DateTime();

        // Find the most recent Monday (or today if it's Monday)
        if ($dateTime->format('N') != 1) {
            $dateTime->modify('last Monday');
        }

        // Format the date to 'mm-dd-yyyy'
        $formattedDate = $dateTime->format('m-d-Y');

        // Construct the log file path
        $path = 'logs/Activity-' . $formattedDate . '.txt';

        // Prepare log content
        $First_Name = isset($Name->First_Name) ? $Name->First_Name : '';
        $Last_Name = isset($Name->Last_Name) ? $Name->Last_Name : 'System';
        $Name = trim($First_Name . ' ' . $Last_Name);

        $datenow = now()->format('F d, Y H:i') ;
        $additionalContent =  '    ' . $Name . '          ' . $Activity;
        Log::info('Logging this: ' . $Name . ' - ' . $Activity);

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->append($path, $datenow.$additionalContent);
        } else {
            // Create a new log file and add an initial entry
            Storage::disk('local')->put($path, 'Log Starts at ' . now()->format('F d, Y H:i') . PHP_EOL .$datenow. $additionalContent);
        }
        // $shouldAppend = true;
        // if (Storage::disk('local')->exists($path)) {
        //     $lines = collect(explode(PHP_EOL, Storage::disk('local')->get($path)))->filter();
        //     $lastLine = $lines->last(); // Get the last line of the file

        //     $lastLineContent = trim(preg_replace('/^\S+ \d{1,2}, \d{4} \d{2}:\d{2}    /', '', $lastLine));
        //     $newContent = trim($additionalContent);

        //     if ($lastLineContent === $newContent) {
        //         $shouldAppend = false;
        //     }
        // }

        // if ($shouldAppend) {
        //     if (Storage::disk('local')->exists($path)) {
        //         Storage::disk('local')->append($path, $datenow . $additionalContent);
        //     } else {
        //         // Create a new log file and add an initial entry
        //         Storage::disk('local')->put($path, 'Log Starts at ' . now()->format('F d, Y H:i') . PHP_EOL . $datenow . $additionalContent);
        //     }
        // }

        // Store or append the log content to the file

    }
}

