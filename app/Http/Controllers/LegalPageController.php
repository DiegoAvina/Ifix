<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use App\Support\SafeHtml;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function privacy(): View
    {
        return $this->show(LegalPage::PRIVACY);
    }

    public function show(string $slug): View
    {
        $page = LegalPage::query()->published()->where('slug', $slug)->firstOrFail();

        return view('legal.show', [
            'page' => $page,
            // Lo escribe el personal en el editor del panel: se limpia antes de imprimirlo.
            'content' => SafeHtml::clean($page->content),
        ]);
    }
}
