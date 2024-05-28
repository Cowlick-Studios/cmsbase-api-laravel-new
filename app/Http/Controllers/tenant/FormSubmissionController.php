<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

use App\Models\tenant\User;
use App\Models\tenant\FormSubmission;
use App\Models\tenant\FormSubmissionField;
use App\Models\tenant\FieldType;
use App\Models\tenant\FormSubmissionLog;

use App\Mail\EmailSubmission as EmailSubmissionMailer;

class FormSubmissionController extends Controller
{
  public function index(Request $request)
  {
    try {

      $query = FormSubmission::query();
      $query->with(['recipients', 'fields', 'fields.type']);
      $emailSubmissions = $query->get();

      return response([
        'message' => 'All form submissions.',
        'form_submissions' => $emailSubmissions,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function show(Request $request, FormSubmission $formSubmission)
  {
    try {
      $formSubmission->load(['recipients', 'fields', 'fields.type', 'logs']);

      return response([
        'message' => 'Single form submissions.',
        'form_submission' => $formSubmission,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function store(Request $request)
  {
    $request->validate([
      'name' => ['required', Rule::unique('email_submissions')],
      'origin' => ['string'],
    ]);

    try {

      $newFormSubmission = FormSubmission::create([
        'name' => Str::of($request->name)->slug('_'),
        'origin' => $request->origin
      ]);

      $newFormSubmission->load(['fields', 'fields.type']);

      return response([
        'message' => 'New form submission.',
        'form_submission' => $newFormSubmission
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, FormSubmission $formSubmission)
  {

    $request->validate([
      'name' => ["string", Rule::unique('email_submissions')],
      'origin' => ['string'],
      'send_mail' => ['boolean'],
      'recaptcha' => ['boolean'],
      'turnstile' => ['boolean'],
      'recaptcha_secret' => ['string'],
      'turnstile_secret' => ['string'],
    ]);

    try {

      if ($request->has('name')) {
        $formSubmission->name = Str::of($request->name)->slug('_');
      }

      if ($request->has('origin')) {
        $formSubmission->origin = $request->origin;
      }

      if ($request->has('send_mail')) {
        $formSubmission->send_mail = $request->send_mail;
      }

      if ($request->has('recaptcha')) {
        $formSubmission->recaptcha = $request->recaptcha;
      }

      if ($request->has('turnstile')) {
        $formSubmission->turnstile = $request->turnstile;
      }

      if ($request->has('recaptcha_secret')) {
        $formSubmission->recaptcha_secret = $request->recaptcha_secret;
      }

      if ($request->has('turnstile_secret')) {
        $formSubmission->turnstile_secret = $request->turnstile_secret;
      }

      $formSubmission->save();

      return response([
        'message' => 'Updated form submission.',
        'form_submission' => $formSubmission
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function destroy(Request $request, FormSubmission $formSubmission)
  {
    try {
      $formSubmission->delete();

      return response([
        'message' => 'Form Submission removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function addField(Request $request, FormSubmission $formSubmission)
  {
    $request->validate([
      'name' => ['required'],
      'type_id' => ['required'],
    ]);

    try {

      $formSubmission = $formSubmission->load(['fields', 'fields.type']);

      $fieldType = FieldType::where('id', $request->type_id)->first();

      $newFormSubmissionField = FormSubmissionField::create([
        'name' => $request->name,
        'form_submission_id' => $formSubmission->id,
        'type_id' => $fieldType->id
      ]);

      $newFormSubmissionField->load(['type']);

      return response([
        'message' => 'Form submission field added.',
        'field' => $newFormSubmissionField,
        'form_submission' => $formSubmission->load(['fields', 'fields.type'])
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function removeField(Request $request, FormSubmission $formSubmission, FormSubmissionField $field)
  {
    try {

      $formSubmission = $formSubmission->load(['fields', 'fields.type']);
      $field->delete();

      return response([
        'message' => 'Form submission field removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function addRecipient(Request $request, FormSubmission $formSubmission)
  {
    $request->validate([
      'user_id' => ['required'],
    ]);

    try {

      $formSubmission = $formSubmission->load(['fields', 'fields.type', 'recipients']);
      $formSubmission->recipients()->syncWithoutDetaching([$request->user_id]);

      return response([
        'message' => 'Form submission recipient added.',
        'form_submission' => $formSubmission
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function removeRecipient(Request $request, FormSubmission $formSubmission, User $user)
  {
    try {

      $formSubmission = $formSubmission->load(['fields', 'fields.type', 'recipients']);
      $formSubmission->recipients()->detach($user->id);

      return response([
        'message' => 'Form submission recipient removed.',
        'form_submission' => $formSubmission
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function syncRecipient(Request $request, FormSubmission $formSubmission)
  {
    $request->validate([
    	'user_ids' => ['nullable'],
    ]);

    try {

      $formSubmission = $formSubmission->load(['fields', 'fields.type', 'recipients']);

      if ($request->has('user_ids')) {
        $formSubmission->recipients()->sync($request->user_ids);
      }

      return response([
        'message' => 'Form submission recipient synced.',
        'form_submission' => $formSubmission
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function submit(Request $request, $formSubmissionName)
  {
    try {

      $formSubmission = FormSubmission::with(['fields', 'fields.type', 'recipients'])->where('name', $formSubmissionName)->first();

      if(!$formSubmission){
				return Response([
					'message' => 'No matching form submission.'
				], 404);
			}

      // recaptcha verification
      if($formSubmission->recaptcha){
        if($request->has('g-recaptcha-response')){

					$recaptchaRes = Http::post('https://www.google.com/recaptcha/api/siteverify', [
						'secret' => $formSubmission->recaptcha_secret,
						'response' => $request->input('g-recaptcha-response'),
						'remoteip' => $request->ip()
					]);

					// View the response
					if (!$recaptchaRes->successful() || !$recaptchaRes->json('success')) {
						return Response([
							'message' => 'recaptcha could not be verified.',
						], 404);
					}
				} else {
					return Response([
						'message' => 'No g-recaptcha-response in request.'
					], 404);
				}
      }

      // turnstile verification
      if($formSubmission->turnstile){
        if($request->has('cf-turnstile-response')){

					$turnstileRes = Http::post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
						'secret' => $formSubmission->turnstile_secret,
						'response' => $request->input('cf-turnstile-response'),
						'ip' => $request->ip()
					]);

					// View the response
					if (!$turnstileRes->successful() || !$turnstileRes->json('success')) {
						return Response([
							'message' => 'turnstile could not be verified.',
						], 404);
					}
				} else {
					return Response([
						'message' => 'No cf-turnstile-response in request.'
					], 404);
				}
      }

      // Verify origin
			$originHost = parse_url($request->header('origin'), PHP_URL_HOST);
			if($originHost != $formSubmission->origin){
				return Response([
					'message' => 'Form submission cannot occur from this origin.',
					'requesting_origin' => $request->header('origin'),
          'allowed_origin' => $formSubmission->origin
				], 401);
			}

      // Create key value store
      $formSubmissionObj = [];
      foreach ($formSubmission->fields as $field) {
        $formSubmissionObj[$field->name] = $request[$field->name];
      }

      // Log result if setting
      if($formSubmission->log){
        $formSubmissionLog = FormSubmissionLog::create([
          'form_submission_id' => $formSubmission->id,
          'submission_data' => $formSubmissionObj
        ]);
        $formSubmissionLog->save();
      }
      
      // send to recipiant if setting
      if($formSubmission->send_mail){
        foreach ($formSubmission->recipients as $recipient) {
          if (!$recipient->blocked && $recipient->email_verified_at && !$recipient->public) { // Verify user is admin, verified and not blocked
            Mail::to($recipient)->send(new EmailSubmissionMailer($formSubmission, $formSubmissionObj));
          }
        }
      }
      
      return response([
        'message' => 'Email submission sent.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
