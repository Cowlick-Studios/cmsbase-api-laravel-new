<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\Rule;

use App\Models\tenant\MarketingMailingList;

class MarketingListController extends Controller
{

  public function index(Request $request)
  {

    // $request->validate([
    //   'public' => ['boolean'],
    //   'blocked' => ['boolean'],
    // ]);

    try {

      $query = MarketingMailingList::query();
      $query->withCount(['subscribers']);
			$lists = $query->get();

      return response([
        'message' => 'List of all mailing lists.',
				'lists' => $lists
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

	public function show(Request $request, MarketingMailingList $mailingList)
  {
    try {

      $mailingList->load(['subscribers'])->loadCount(['subscribers']);

      return response([
        'message' => 'Single marketing mailing list.',
				'list' => $mailingList
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
      'name' => ['required', 'string', Rule::unique('marketing_mailing_lists')],
      'description' => ['string'],
    ]);

    try {

      $newMailingList = new MarketingMailingList($request->all());
      $newMailingList->save();

      return response([
        'message' => 'Created new marketing mailing list.',
				'list' => $newMailingList
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

	public function update(Request $request, MarketingMailingList $mailingList)
  {

    $request->validate([
      'name' => ['string'],
      'description' => ['string', 'nullable'],
			'recaptcha' => ['boolean'],
			'recaptcha_secret' => ['string', 'nullable'],
			'turnstile' => ['boolean'],
			'turnstile_secret' => ['string', 'nullable'],
    ]);

    try {

			$mailingList->update($request->all());

      return response([
        'message' => 'Updated marketing mailing list.',
				'list' => $mailingList
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

	public function destroy(Request $request, MarketingMailingList $mailingList)
  {

    $request->validate([
      'public' => ['boolean'],
      'blocked' => ['boolean'],
    ]);

    try {

      $mailingList->delete();

      return response([
        'message' => 'Marketing mailing list destroyed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
