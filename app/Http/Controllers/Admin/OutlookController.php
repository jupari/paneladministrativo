<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\admin\ConfigAccountController;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Cuenta;
use App\Models\CuentaMadre;
use App\Models\Datopersistente;
use App\Models\Email;
use Beta\Microsoft\Graph\Model\MailFolder;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Microsoft\Graph\Model\Message;
use Yajra\DataTables\Facades\DataTables;

class OutlookController extends Controller
{
    //
    private $oauthClient;

    public function __construct()
    {
        // $this->oauthClient = new GenericProvider([
        //     'clientId'                => env('MICROSOFT_CLIENT_ID'),
        //     'clientSecret'            => env('MICROSOFT_CLIENT_SECRET'),
        //     'redirectUri'             => env('MICROSOFT_REDIRECT_URI'),
        //     'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
        //     'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
        //     'urlResourceOwnerDetails' => '',
        //     'scopes'                  => 'https://graph.microsoft.com/.default'
        // ]);


    }

    public function configProvider($email){
        try {
            //code...
            $cuenta =  CuentaMadre::with('account')->where('email', $email)->first();

            // dd($cuenta);
            if($cuenta){
                $this->oauthClient = new GenericProvider([
                    'clientId'                => $cuenta->account->clientId,
                    'clientSecret'            => $cuenta->account->clientSecret,
                    'redirectUri'             => $cuenta->account->redirectUri,
                    'urlAuthorize'            => $cuenta->account->urlAuthorize,
                    'urlAccessToken'          => $cuenta->account->urlAccessToken,
                    'urlResourceOwnerDetails' => $cuenta->account->urlResourceOwnerDetails,
                    'scopes'                  => 'openid profile email offline_access Mail.Read '//.$cuenta->account->scopes //offline_access se necesita para traer el referesh_token
                ]);
            }
        } catch (\Exception $e) {
            //throw $th;
            throw new Exception('errorfn_configprovider'. $e->getMessage());
        }
    }

    public function redirectToProvider($email)
    {
        $this->configProvider($email);
        //Obtenemos el id de la cuenta

        $accountId= Account::where('email', $email)->pluck('id');
        $authorizationUrl = $this->oauthClient->getAuthorizationUrl();

        //datopersistente
        Datopersistente::truncate();

        $resCreated =  Datopersistente::create(['dato'=> $email,'oauthState'=>$this->oauthClient->getState()]);

        //dd($this->oauthClient->getState());
        Session::put('oauthState', $this->oauthClient->getState());
        Session::put('accountId', $accountId[0]);
        Session::put('email',$email);
        return response()->json(['redirectUrl'=>$authorizationUrl,'email_consulta'=>$email]);
    }

    public function handleProviderCallback(Request $request)
    {

        try {
        $accountId = Datopersistente::first();
        $account = Account::where('email',$accountId->dato)->first();
        $this->configProvider($account->email);

        $state = $request->query('state');
        $code = $request->query('code');

        if (empty($state) || $state !== $accountId->oauthState) {
            return view('errors',['error'=> 'Estado invalido']);
        }

            $accessToken = $this->oauthClient->getAccessToken('authorization_code', [
                'code' => $code
            ]);

            //dd($accessToken);

            $account->oauth_token = $accessToken->getToken();
            $account->refresh_token = $accessToken->getRefreshToken();
            $account->token_expires_at = new Carbon($accessToken->getExpires());
            $account->save();


            // dd($emails);
            //return view('admin.emailreader.email', ['emails' => $emails]);
           return redirect('admin/admin.configemail.index');
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return view('errors',['error'=> $e->getMessage()]);
        }
    }

    private function extractEmailAddresses($recipients)
    {
        $addresses = [];
        foreach ($recipients as $recipient) {
            // dd($recipient['emailAddress']['address']);
            $addresses[] = $recipient['emailAddress'];
        }
        return $addresses;
    }




    public function getEmails($accountId,$tipo)
    {

        try {
            $account = Account::where('email',$accountId)->first();
            //$this->configProvider($account->email);
            $cuenta =  CuentaMadre::with('account')->where('email', $account->email)->first();

            // dd($cuenta);
            if($cuenta){
                $this->oauthClient = new GenericProvider([
                    'clientId'                => $cuenta->account->clientId,
                    'clientSecret'            => $cuenta->account->clientSecret,
                    'redirectUri'             => $cuenta->account->redirectUri,
                    'urlAuthorize'            => $cuenta->account->urlAuthorize,
                    'urlAccessToken'          => $cuenta->account->urlAccessToken,
                    'urlResourceOwnerDetails' => $cuenta->account->urlResourceOwnerDetails,
                    'scopes'                  => 'openid profile email offline_access Mail.Read '//.$cuenta->account->scopes //offline_access se necesita para traer el referesh_token
                ]);
            }


            if (Carbon::now()->greaterThan($account->token_expires_at)) {

                $newAccessToken = $this->oauthClient->getAccessToken('refresh_token', [
                    'refresh_token' => $account->refresh_token
                ]);
                // dd($newAccessToken);
                $account->oauth_token = $newAccessToken->getToken();
                $account->refresh_token = $newAccessToken->getRefreshToken();
                $account->token_expires_at =new Carbon($newAccessToken->getExpires());
                $account->save();
            }

            $graph = new Graph();
            $graph->setAccessToken($account->oauth_token);

            $top=config('app.topMessages');
            $folderCorreo='inbox';

            //dd($graph);
            // $folders = $graph->createRequest('GET', '/me/mailFolders?$top=20')
            // ->setReturnType(MailFolder::class)
            // ->execute();

            // dd($folders);


            $messages = $graph->createRequest('GET', 'https://graph.microsoft.com/v1.0/me/mailFolders/'.$folderCorreo.'/messages?$top='.$top)
                // ->addHeaders(["ConsistencyLevel" => "eventual"])
                // ->addHeaders(["Prefer" => "outlook.body-content-type='text'"])
                ->setReturnType(Model\Message::class)
                ->execute();

            $query = config('app.filtro_email');

            list($keyword1,$keyword2,$keyword3) = explode('|',$query);

            //dd($messages);
            //$keywords = explode(' ', $query);
            // $keywords = $query;
            // Filtrar correos que contienen la palabra, frase o cumplen la fecha
            // $filteredMessages = array_filter($messages, function($message) use ($keywords) {
            //     foreach ($keywords as $keyword) {
            //         if (stripos($message->getSubject(), $keyword) !== false || stripos($message->getBodyPreview(), $keyword) !== false) {
            //             return true;
            //         }
            //     }
            //     return false;
            // });

            if($tipo=='codigo'){
                $filteredMessages = array_filter($messages, function($message) use ($keyword1) {

                    if (stripos($message->getSubject(), $keyword1) !== false || stripos($message->getBodyPreview(), $keyword1) !== false ) {
                        return true;
                    }

                    return false;
                });
            }else{
                $filteredMessages = array_filter($messages, function($message) use ($keyword2) {

                    if (stripos($message->getSubject(), $keyword2) !== false || stripos($message->getBodyPreview(), $keyword2) !== false) {
                        return true;
                    }

                    return false;
                });
            }

            $emails = [];
            foreach ($filteredMessages as $message) {
                $emails[] = new Email([
                    'id' => $message->getId(),
                    'subject' => $message->getSubject(),
                    'body' => $message->getBody()->getContent(),
                    'bodyPreview' => $message->getBodyPreview(),
                    'from' => $message->getFrom()->getEmailAddress()->getAddress(),
                    'sender' => $message->getSender()->getEmailAddress()->getAddress(),
                    'toRecipients' => $this->extractEmailAddresses($message->getToRecipients()),
                    'ccRecipients' => $this->extractEmailAddresses($message->getCcRecipients()),
                    'bccRecipients' => $this->extractEmailAddresses($message->getBccRecipients()),
                    'isRead' => $message->getIsRead(),
                    'receivedDateTime' => Carbon::instance($message->getReceivedDateTime())->format('Y-m-d H:i:s') ,
                    'sentDateTime' => Carbon::instance($message->getSentDateTime())->format('Y-m-d H:i:s'),
                    'internetMessageId' => $message->getInternetMessageId(),
                    'conversationId' => $message->getConversationId(),
                    'hasAttachments' => $message->getHasAttachments(),
                ]);
            }


            return response()->json(['messages' => $emails],200);
        } catch (\Exception $e) {
            //$log=Log::error('Error fetching emails for ' . $account->email . ': ' . $e->getMessage());
            // $this->refreshToken();
            return response()->json(['2.error'=>$e->getMessage()],500);
        }
    }

    //no se usa por ahora
    public function getEmailsCorreo($email)
    {

        try {
            $account = Account::where('email', $email)->first();
            $this->configProvider($account->email);
            if (Carbon::now()->greaterThan($account->token_expires_at)) {

                $newAccessToken = $this->oauthClient->getAccessToken('refresh_token', [
                    'refresh_token' => $account->refresh_token
                ]);
                // dd($newAccessToken);
                $account->oauth_token = $newAccessToken->getToken();
                $account->refresh_token = $newAccessToken->getRefreshToken();
                $account->token_expires_at =new Carbon($newAccessToken->getExpires());
                $account->save();
            }

            $graph = new Graph();
            $graph->setAccessToken($account->oauth_token);

            $top=100;
            $folderCorreo='inbox';

            //dd($graph);
            // $folders = $graph->createRequest('GET', '/me/mailFolders?$top=20')
            // ->setReturnType(MailFolder::class)
            // ->execute();

            // dd($folders);


            $messages = $graph->createRequest('GET', '/me/mailFolders/'.$folderCorreo.'/messages?$top='.$top)
                // ->addHeaders(["ConsistencyLevel" => "eventual"])
                // ->addHeaders(["Prefer" => "outlook.body-content-type='text'"])
                ->setReturnType(Model\Message::class)
                ->execute();

            $query = config('app.filtro_email');

            //dd($messages);

            //$keywords = explode(' ', $query);
            $keywords = $query;
            // Filtrar correos que contienen la palabra, frase o cumplen la fecha
            // $filteredMessages = array_filter($messages, function($message) use ($keywords) {
            //     foreach ($keywords as $keyword) {
            //         if (stripos($message->getSubject(), $keyword) !== false || stripos($message->getBodyPreview(), $keyword) !== false) {
            //             return true;
            //         }
            //     }
            //     return false;
            // });

            $filteredMessages = array_filter($messages, function($message) use ($keywords) {

                if (stripos($message->getSubject(), $keywords) !== false || stripos($message->getBodyPreview(), $keywords) !== false) {
                    return true;
                }

                return false;
            });

            $emails = [];
            foreach ($filteredMessages as $message) {
                $emails[] = new Email([
                    'id' => $message->getId(),
                    'subject' => $message->getSubject(),
                    'body' => $message->getBody()->getContent(),
                    'bodyPreview' => $message->getBodyPreview(),
                    'from' => $message->getFrom()->getEmailAddress()->getAddress(),
                    'sender' => $message->getSender()->getEmailAddress()->getAddress(),
                    'toRecipients' => $this->extractEmailAddresses($message->getToRecipients()),
                    'ccRecipients' => $this->extractEmailAddresses($message->getCcRecipients()),
                    'bccRecipients' => $this->extractEmailAddresses($message->getBccRecipients()),
                    'isRead' => $message->getIsRead(),
                    'receivedDateTime' => Carbon::instance($message->getReceivedDateTime())->format('Y-m-d H:i:s') ,
                    'sentDateTime' => Carbon::instance($message->getSentDateTime())->format('Y-m-d H:i:s'),
                    'internetMessageId' => $message->getInternetMessageId(),
                    'conversationId' => $message->getConversationId(),
                    'hasAttachments' => $message->getHasAttachments(),
                ]);
            }


            return response()->json(['messages' => $emails],200);
        } catch (\Exception $e) {
            $log=Log::error('Error fetching emails for ' . $account->email . ': ' . $e->getMessage());
            // $this->refreshToken();
            return response()->json(['2.error'=>$e->getMessage()],500);
        }
    }

    private function refreshToken($refreshToken, $clientId, $clientSecret,$tenant_id, $scope)
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post('https://login.microsoftonline.com/' . $tenant_id . '/oauth2/v2.0/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' =>$scope,
                'refresh_token' => $refreshToken,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        $credential = Account::findOrFail($clientId);
        $credential->oauth_token = $data['access_token'];
        $credential->refresh_token = $data['refresh_token'];
        $credential->token_expires_at = new  Carbon($data['expires_in']);
        $credential->save();

        if (isset($data['error'])) {
            throw new \Exception($data['error_description']);
        }

        return $data;
    }

    // public function fetchEmails($email, $password)
    // {
    //     // Configurar el cliente Guzzle
    //     //dd($password);
    //     $client = new Client();
    //     // dd($client);
    //     try {
    //         // Autenticar usando las credenciales del usuario
    //         $response = $client->post('https://login.microsoftonline.com/'. env('TENANT_ID') .'/oauth2/token', [
    //             'form_params' => [
    //                 'grant_type' => 'password',
    //                 'client_id' => env('MICROSOFT_CLIENT_ID'),
    //                 'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
    //                 'scope' => 'https://graph.microsoft.com/.default',
    //                 'name' => 'jupari3hotmail.com',
    //                 'password' => 'E5p4rtac05',
    //             ],
    //         ]);

    //         //dd($response);
    //         $data = json_decode($response->getBody(), true);
    //         $accessToken = $data['access_token'];

    //         // Configurar el cliente de Microsoft Graph
    //         $graph = new Graph();
    //         $graph->setAccessToken($accessToken);

    //         // Obtener los correos electrónicos
    //         $messages = $graph->createRequest('GET', '/me/mailFolders/inbox/messages')
    //             ->setReturnType(Message::class)
    //             ->execute();

    //         return $messages;
    //     } catch (\Exception $e) {
    //         return response()->json($e->getMessage(),500);
    //     }
    // }

    public function index(Request $request){


        try {

            $query =  Account::all();

             if($request->ajax()) {
                return DataTables::of($query)
                                ->addIndexColumn()
                                ->addColumn('correo', function ($td) {

                                    $href = $td->email;

                                    return $href;

                                })
                                ->addColumn('token', function ($td) {

                                $href = $td->oauth_token;

                                return $href;

                                })
                                ->addColumn('expiracion', function ($td) {

                                    $href =  date('Y-m-d h:i:s A', strtotime($td->token_expires_at));

                                    return $href;

                                })
                                ->addColumn('acciones', function ($td) {
                                    // if(Auth::user()->can('cuentappal.edit')){
                                    $email = $td->email;
                                    $password =$td->password;
                                        $href = '<button type="button" onclick="upVerCorreos('.$td->id.')" class="btn btn-info btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Correos"><i class="fas fa-eye"></i></button>&nbsp';
                                    // }else{
                                        // $href='';
                                    // }
                                    // $href .= '<button type="button" class="btn btn-danger btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Quitar Usuario"><i class="fas fa-trash"></i></button>';

                                return $href;

                                })
                                ->rawColumns(['correo','token','expiracion','acciones'])
                                ->make(true);

            }
            return view('admin.emailreader.index');

         }
         catch (Exception $e) {

             return response()->json(['error' => 'Error al obtener los estados ' . $e->getMessage()], 500);
         }
    }
}
