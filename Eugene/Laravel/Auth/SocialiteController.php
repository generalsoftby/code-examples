<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatuses;
use App\Http\Controllers\Controller;
use App\Manager\Registration\SocialiteManager;
use App\Support\Socialite\AbstractSocialiteUser;
use App\Support\Socialite\FacebookUser;
use App\Support\Socialite\GoogleUser;
use App\Support\Socialite\TwitterUser;
use App\Support\Socialite\VkontakteUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Objects that take data from different social networks
     *
     * @var AbstractSocialiteUser[]
     */
    protected $userBuilders = [];


    /**
     * Social network registration logic
     *
     * @var SocialiteManager
     */
    private $socialiteManager;

    /**
     * @param $socialiteManager SocialiteManager
     */
    function __construct(SocialiteManager $socialiteManager)
    {
        $this->socialiteManager = $socialiteManager;
        $this->userBuilders = [
            'facebook' => new FacebookUser(),
            'vkontakte' => new VkontakteUser(),
            'twitter' => new TwitterUser(),
            'google' => new GoogleUser(),
        ];
    }


    /**
     * @param string $provider
     * @return mixed
     */
    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }


    /**
     * Process after callback from social network for registration or authorization
     *
     * @param $request Request
     * @param $provider string
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(Request $request, string $provider)
    {
        $builder = Arr::get($this->userBuilders, $provider, null);

        if (is_null($builder)) {
            App::abort(404);
        }

        $driver = Socialite::driver($provider);

        try {
            $data = $builder->collectData($driver);
        } catch(\Exception $e) {
            return redirect(route('index'));
        }

        $data['provider'] = $provider;

        $token_user = $this->socialiteManager->findPersonByToken($data);

        // Authorization because user with given token already present in the system
        if ($token_user) {
            if ( $token_user->status != UserStatuses::ACTIVE ) {
                $tokenErrors = [
                    UserStatuses::BANNED => trans('registration.banned'),
                    UserStatuses::PENDING_FOR_CONFIRMATION => trans('registration.pending')
                ];
                $request->session()->flash('socialite_error', Arr::get($tokenErrors, $token_user->status));

                return redirect(route('auth.register'));

            }
            Auth::guard()->login($token_user);

            return redirect(route('profile.index'));
        }

        $email_user = $this->socialiteManager->findPersonByEmail($data);

        // User not found by token but user with such email present in the system
        if ($email_user) {
            $request->session()->flash('socialite_error', trans('registration.socialite_email_exists'));

            return redirect(route('auth.register'));
        }

        // User not found by email or token
        // Check required field
        $missedParams = $this->socialiteManager->checkRequiredParams($data);

        // Create new user if all required fields are filled
        if (empty($missedParams)) {
            $user = $this->socialiteManager->createUser($provider, $data);
            $request->session()->flash('status', trans('registration.registered'));
            Auth::guard()->login($user);
            return redirect(route('profile.index'));
        }

        // Ask required field from user
        $request->session()->flash('missedParams', $missedParams);
        $request->session()->flash('filledParams', $data);
        return redirect(route('auth.socialite.missed_params_form'));
    }


    /**
     * Situation when not all required fields were returned by social network
     *
     * @param $request Request
     * @return \Illuminate\Http\Response
     */
    public function registerMissedParamsForm(Request $request)
    {
        $request->session()->keep(['filledParams', 'missedParams']);
        $missedParams = $request->session()->get('missedParams', []);
        $filledParams = $request->session()->get('filledParams', []);

        if (empty($missedParams)) {
            return redirect(route('index'));
        }

        return view('auth.registerMissedParams', [
            'missedParams' => $missedParams,
            'filledParams' => $filledParams
        ]);
    }


    /**
     * Create a user from missing info registration page
     *
     * @param $request Request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function registerMissedParams(MissedParamsRequest $request)
    {
        $request->session()->keep(['filledParams', 'missedParams']);
        $data = $request->all();

        $route = '';
        if ($data['is_email_missed']) {
            $data['status'] = UserStatuses::PENDING_FOR_CONFIRMATION;
            $route = 'auth.registerEnd';
        } else {
            $route = 'index';
        }

        $user = $this->socialiteManager->createUser($data['provider'], $data);

        if ($data['is_email_missed']) {
            // Social network didn't give email. Need email confirmation.
            $user->confirmation_code = $confirmationCode = str_random(30);
            $user->save();
            event(new Registered($user));
        } else {
            Auth::guard()->login($user);
        }

        return redirect(route($route));
    }
}
