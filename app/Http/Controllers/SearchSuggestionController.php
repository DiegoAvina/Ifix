<?php

namespace App\Http\Controllers;

use App\Services\Storefront\Search\SearchSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchSuggestionController extends Controller
{
    public function __invoke(
        Request $request,
        SearchSuggestionService $search,
    ): JsonResponse {

        return response()->json([
            'products' => $search->search(
                $request->string('search')->toString()
            ),
        ]);

    }
}