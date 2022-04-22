<?php

namespace App\Jobs\Auth;

use App\Mail\Signup\UserSignup;
use App\Models\PersonalUserInfo;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessUserRegister implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        //
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;

        $personalUserInfo = new PersonalUserInfo([
            'user_id' => $user->id,
            'father_name' => null,
            'gender' => null,
            'date_of_birth' => null,
            'shenasname_no' => null,
            'mellicode' => null,
            'country_id' => 101,
            'home_address' => null,
            'phone_number' => null,
            'image_mellicard' => null,
            'verified' => false,
            'status' => 'user-registered',
            'user_verified_at' => null,
        ]);


        // Save User Personal Info
        $user->personalInfos()->save($personalUserInfo);


        // Start Send Welcome SMS to user


        $apikey = env('SMS_API_KEY');
        $client = new \GuzzleHttp\Client([
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => "AccessKey {$apikey}"]
        ]);

        // Values to send
        $patternValues = [
            "name" => $user->first_name,
            "mobile" => $user->mobile_number,
        ];


        // Begin Post sms
        $client->post(
            'https://rest.ippanel.com/v1/messages/patterns/send',
            ['body' => json_encode(
                [
                    'pattern_code' => "pupiq1iq22",
                    'originator' => "+983000505",
                    'recipient' => $user->mobile_number,
                    'values' => $patternValues,
                ]
            )]
        );


        // Try Send Mail to User
        // Mail::to($user->email)->send(new UserSignup($user));
    }
}
