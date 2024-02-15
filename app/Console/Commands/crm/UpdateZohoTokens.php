<?php

namespace App\Console\Commands\crm;

use App\Models\ZohoToken;
use Illuminate\Console\Command;

class UpdateZohoTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:update-tokens';

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

        $grant_type = 'refresh_token';
        $redirect_uri = 'https://qa-api.mygisselle.com';
        $scope = 'ZohoMail.messages.ALL ZohoMail.accounts.ALL';


        // Guzzle
        $refreshToken = env('ZOHO_TEST_REFRESH_TOKEN');
        $client_id = env('ZOHO_TEST_CLIENT_ID');
        $client_secret = env('ZOHO_TEST_CLIENT_SECRET');

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', "https://accounts.zoho.com/oauth/v2/token?refresh_token=$refreshToken&client_id=$client_id&client_secret=$client_secret&grant_type=$grant_type&redirect_uri=$redirect_uri&scope=$scope", [
            'body' => json_encode([
                'fromAddress' => 'areacomercial@globaltecnologiasacademy.com',
                'toAddress' => 'andresjosehr@gmail.com',
                'subject' => 'Custom datetime at!',
                'content' => 'Hola mundo!'
            ])
        ]);

        $data = json_decode($res->getBody());

        ZohoToken::where('type', 'qa')->update([
            'token' => $data->access_token,
            'updated_at' => date('Y-m-d H:i:s')
        ]);




        $refreshToken = env('ZOHO_REFRESH_TOKEN');
        $client_id = env('ZOHO_CLIENT_ID');
        $client_secret = env('ZOHO_CLIENT_SECRET');
        $res = $client->request('POST', "https://accounts.zoho.com/oauth/v2/token?refresh_token=$refreshToken&client_id=$client_id&client_secret=$client_secret&grant_type=$grant_type&redirect_uri=$redirect_uri&scope=$scope", [
            'body' => json_encode([
                'fromAddress' => 'areacomercial@globaltecnologiasacademy.com',
                'toAddress' => 'andresjosehr@gmail.com',
                'subject' => 'Custom datetime at!',
                'content' => 'Hola mundo!'
            ])
        ]);

        $data = json_decode($res->getBody());

        ZohoToken::where('type', 'production')->update([
            'token' => $data->access_token,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return Command::SUCCESS;
    }
}
