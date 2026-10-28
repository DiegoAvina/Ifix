<?php

namespace App\Http\Controllers;

use App\Services\Storefront\Search\SearchSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchSuggestionController extends Controller
{
    public function __invoke(
        Request $request,
        SearchSuggestionService $suggestions,
    ): JsonResponse {

        $search = $request->query('search');

        return response()->json([
            'products' => $suggestions->search(
                is_string($search) ? $search : ''
            ),
        ]);

    }
}
