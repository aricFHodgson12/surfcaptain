<?php


namespace App\Services;
use App\ProviderAccount;
use App\User;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as ProviderUser;


class ProviderAccountService
{
    public function createOrGetUser(ProviderUser $providerUser)
    {
        $account = ProviderAccount::whereProvider('facebook')
            ->whereProviderId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {

            $account = new ProviderAccount([
                'provider_id' => $providerUser->getId(),
                'provider' => 'facebook'
            ]);

            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {

                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'name' => $providerUser->getName(),
                    'password' => md5(rand(1,10000)),
                    'is_subscribed' => 1
                ]);

                //setup default settings
                $user->addUserSettings();
            }

            $account->user()->associate($user);
            $account->save();

            return $user;
        }
    }
}
