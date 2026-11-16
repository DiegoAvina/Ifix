<?php

namespace App\Http\Controllers;

use App\Models\AboutPage;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        $page = AboutPage::current();

        abort_unless($page->published, 404);

        return view('about.show', [
            'page' => $page,
            'values' => $page->valueItems(),
            'stats' => $page->statItems(),
            'milestones' => $page->milestoneItems(),
        ]);
    }
}
