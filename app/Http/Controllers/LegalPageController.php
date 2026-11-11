<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use App\Support\SafeHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function privacy(): View
    {
        return $this->render(LegalPage::PRIVACY);
    }

    public function terms(): View
    {
        return $this->render(LegalPage::TERMS);
    }

    /**
     * /legal/{slug}: las páginas con dirección propia redirigen a ella para
     * no tener dos URLs con el mismo contenido.
     */
    public function show(string $slug): View|RedirectResponse
    {
        if (array_key_exists($slug, LegalPage::FIXED)) {
            return redirect()->route(LegalPage::FIXED[$slug], status: 301);
        }

        return $this->render($slug);
    }

    private function render(string $slug): View
    {
        $page = LegalPage::query()->published()->where('slug', $slug)->firstOrFail();

        return view('legal.show', [
            'page' => $page,
            // Lo escribe el personal en el editor del panel: se limpia antes de imprimirlo.
            'content' => SafeHtml::clean($page->content),
        ]);
    }
}
