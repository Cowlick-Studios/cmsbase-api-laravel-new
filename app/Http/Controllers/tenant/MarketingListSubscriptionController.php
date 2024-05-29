<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

use App\Models\tenant\MarketingMailingList;
use App\Models\tenant\MarketingMailingListSubscribers;

class MarketingListSubscriptionController extends Controller
{
    public function index(Request $request, MarketingMailingList $mailingList)
    {
  
      try {
  
        $query = MarketingMailingListSubscribers::query();
				$query->where('list_id', $mailingList->id);
        $subscribers = $query->get();
  
        return response([
          'message' => 'List of all mailing list subscribers.',
          'subscribers' => $subscribers
        ], 200);
      } catch (Exception $e) {
        return response([
          'message' => $e->getMessage()
        ], 500);
      }
    }
  
    public function store(Request $request, MarketingMailingList $mailingList)
    {
  
      $request->validate([
        'email' => ['string'],
      ]);
  
      try {

				$existingSubscription = MarketingMailingListSubscribers::where('list_id', $mailingList->id)->where('email', $request->email)->first();

				if($existingSubscription){
					return response([
						'message' => 'Subscription already exists.'
					], 400);
				} 

				$newSubscription = MarketingMailingListSubscribers::create([
					'email' => $request->email,
					'list_id' => $mailingList->id
				]);
        
        return response([
          'message' => 'Created new marketing mailing list subscriber.',
          'subscription' => $newSubscription
        ], 200);
      } catch (Exception $e) {
        return response([
          'message' => $e->getMessage()
        ], 500);
      }
    }
  
    public function destroy(Request $request, MarketingMailingList $mailingList, MarketingMailingListSubscribers $subscription)
    {
      try {
  
        $subscription->delete();
  
        return response([
          'message' => 'Marketing mailing list subscription destroyed.'
        ], 200);
      } catch (Exception $e) {
        return response([
          'message' => $e->getMessage()
        ], 500);
      }
    }

		public function subscribe(Request $request, MarketingMailingList $mailingList)
    {
  
      $request->validate([
        'email' => ['string', 'required'],
      ]);
  
      try {

				// recaptcha verification
				if($mailingList->recaptcha){
					if($request->has('g-recaptcha-response')){
	
						$recaptchaRes = Http::post('https://www.google.com/recaptcha/api/siteverify', [
							'secret' => $mailingList->recaptcha_secret,
							'response' => $request->input('g-recaptcha-response'),
							'remoteip' => $request->ip()
						]);
	
						// View the response
						if (!$recaptchaRes->successful() || !$recaptchaRes->json('success')) {
							return Response([
								'message' => 'recaptcha could not be verified.',
							], 400);
						}
					} else {
						return Response([
							'message' => 'No g-recaptcha-response in request.'
						], 400);
					}
				}
	
				// turnstile verification
				if($mailingList->turnstile){
					if($request->has('cf-turnstile-response')){
	
						$turnstileRes = Http::post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
							'secret' => $mailingList->turnstile_secret,
							'response' => $request->input('cf-turnstile-response'),
							'ip' => $request->ip()
						]);
	
						// View the response
						if (!$turnstileRes->successful() || !$turnstileRes->json('success')) {
							return Response([
								'message' => 'turnstile could not be verified.',
							], 400);
						}
					} else {
						return Response([
							'message' => 'No cf-turnstile-response in request.'
						], 400);
					}
				}
  
				$existingSubscription = MarketingMailingListSubscribers::where('list_id', $mailingList->id)->where('email', $request->email)->first();

				if($existingSubscription){
					return response([
						'message' => 'Subscription already exists.'
					], 400);
				} 

				MarketingMailingListSubscribers::create([
					'email' => $request->email,
					'list_id' => $mailingList->id
				]);
  
        return response([
          'message' => 'Successful subscription.'
        ], 200);
      } catch (Exception $e) {
        return response([
          'message' => $e->getMessage()
        ], 500);
      }
    }

		public function unsubscribe(Request $request, MarketingMailingList $mailingList, $email)
    {  
      try {
  
        MarketingMailingListSubscribers::where('email', $email)->where('list_id', $mailingList->id)->delete();
  
        return response([
          'message' => 'You have been removed from the mailing list.'
        ], 200);
      } catch (Exception $e) {
        return response([
          'message' => $e->getMessage()
        ], 500);
      }
    }
}
