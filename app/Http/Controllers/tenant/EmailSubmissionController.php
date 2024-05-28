<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

use App\Models\tenant\User;
use App\Models\tenant\EmailSubmission;
use App\Models\tenant\EmailSubmissionField;
use App\Models\tenant\FieldType;
use App\Models\tenant\EmailSubmissionLog;

use App\Mail\EmailSubmission as EmailSubmissionMailer;
use App\Models\tenant\Setting;

class EmailSubmissionController extends Controller
{
  public function index(Request $request)
  {
    try {

      $query = EmailSubmission::query();
      $query->with(['recipients', 'fields', 'fields.type']);
      $emailSubmissions = $query->get();

      return response([
        'message' => 'All email submissions.',
        'email_submissions' => $emailSubmissions,
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
    ]);

    try {

      $newEmailSubmission = EmailSubmission::create([
        'name' => Str::of($request->name)->slug('_'),
        'origin' => $request->origin
      ]);

      $newEmailSubmission->load(['fields', 'fields.type']);

      return response([
        'message' => 'New email submission.',
        'email_submission' => $newEmailSubmission
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, EmailSubmission $emailSubmission)
  {

    $request->validate([
      'name' => ["string", Rule::unique('email_submissions')],
    ]);

    try {

      if ($request->has('name')) {
        $emailSubmission->name = Str::of($request->name)->slug('_');
      }

      if ($request->has('origin')) {
        $emailSubmission->origin = $request->origin;
      }

      $emailSubmission->save();

      return response([
        'message' => 'Updated email submission.',
        'email_submission' => $emailSubmission
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function destroy(Request $request, EmailSubmission $emailSubmission)
  {
    try {

      $emailSubmission->delete();

      return response([
        'message' => 'Email Submission removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function addField(Request $request, EmailSubmission $emailSubmission)
  {
    $request->validate([
      'name' => ['required'],
      'type_id' => ['required'],
    ]);

    try {

      $emailSubmission = $emailSubmission->load(['fields', 'fields.type']);

      $fieldType = FieldType::where('id', $request->type_id)->first();

      $newEmailSubmissionField = EmailSubmissionField::create([
        'name' => $request->name,
        'email_submission_id' => $emailSubmission->id,
        'type_id' => $fieldType->id
      ]);

      $newEmailSubmissionField->load(['type']);

      return response([
        'message' => 'Email submission field added.',
        'field' => $newEmailSubmissionField,
        'email_submission' => $emailSubmission->load(['fields', 'fields.type'])
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function removeField(Request $request, EmailSubmission $emailSubmission, EmailSubmissionField $field)
  {
    try {

      $emailSubmission = $emailSubmission->load(['fields', 'fields.type']);
      $field->delete();

      return response([
        'message' => 'Email submission field removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function addRecipient(Request $request, EmailSubmission $emailSubmission)
  {
    $request->validate([
      'user_id' => ['required'],
    ]);

    try {

      $emailSubmission = $emailSubmission->load(['fields', 'fields.type', 'recipients']);
      $emailSubmission->recipients()->syncWithoutDetaching([$request->user_id]);

      return response([
        'message' => 'Email submission recipient added.',
        'email_submission' => $emailSubmission
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function removeRecipient(Request $request, EmailSubmission $emailSubmission, User $user)
  {
    try {

      $emailSubmission = $emailSubmission->load(['fields', 'fields.type', 'recipients']);
      $emailSubmission->recipients()->detach($user->id);

      return response([
        'message' => 'Email submission recipient removed.',
        'email_submission' => $emailSubmission
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function syncRecipient(Request $request, EmailSubmission $emailSubmission)
  {
    // $request->validate([
    // 	'user_ids' => ['required', 'nullable'],
    // ]);

    try {

      $emailSubmission = $emailSubmission->load(['fields', 'fields.type', 'recipients']);

      if ($request->has('user_ids')) {
        $emailSubmission->recipients()->sync($request->user_ids);
      }

      return response([
        'message' => 'Email submission recipient synced.',
        'email_submission' => $emailSubmission,
        'test' => $request->user_ids
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function submit(Request $request, $emailSubmissionName)
  {
    try {

      $emailSubmission = EmailSubmission::with(['fields', 'fields.type', 'recipients'])->where('name', $emailSubmissionName)->first();

      if(!$emailSubmission){
				return Response([
					'message' => 'No matching form submission.'
				], 404);
			}

			$originHost = parse_url($request->header('origin'), PHP_URL_HOST);

			if($originHost != $emailSubmission->origin){
				return Response([
					'message' => 'Form submission cannot occur from this origin.',
					'requesting_origin' => $request->header('origin'),
          'allowed_origin' => $emailSubmission->origin
				], 401);
			}

      $formSubmissionObj = [];

      foreach ($emailSubmission->fields as $field) {
        $formSubmissionObj[$field->name] = $request[$field->name];
      }

      $emailLogSetting = Setting::where('key', 'email_submission_logs')->first();

      if($emailLogSetting->value){
        $emailSubmissionLog = EmailSubmissionLog::create([
          'email_submission_id' => $emailSubmission->id,
          'submission_data' => $formSubmissionObj
        ]);
        $emailSubmissionLog->save();
      }
      
      foreach ($emailSubmission->recipients as $recipient) {
        if (!$recipient->blocked && $recipient->email_verified_at && !$recipient->public) { // Verify user is admin, verified and not blocked
          Mail::to($recipient)->send(new EmailSubmissionMailer($emailSubmission, $formSubmissionObj));
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
