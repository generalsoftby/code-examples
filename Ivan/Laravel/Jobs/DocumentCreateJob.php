<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use App\Models\Document;
use App\Repositories\FilesRepository;
use PDF;

class DocumentCreateJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var User */
    protected $user;

    /** @var string */
    protected $template;

    /** @var string */
    protected $title;

    /** @var array */
    protected $contentData;

    /** @var string */
    protected $locale;

    /**
     * DocumentCreateJob constructor.
     * @param User $user
     * @param $template
     * @param $titleData
     * @param array $contentData
     */
    public function __construct(User $user, $template, $titleData, $contentData)
    {
        $this->user = $user;
        $this->template = $template;
        $this->title = trans('documents.title.' . $this->template, $titleData);
        $this->contentData = $contentData;
        $this->locale = \App::getLocale();
    }

    /**
     * @param FilesRepository $filesRepository
     */
    public function handle(FilesRepository $filesRepository)
    {
        \URL::forceRootUrl(env('WEB_PROTOCOL') . '://' .env('WEB_DOMAIN'));
        \App::setLocale($this->locale);

        $document = new Document;
        $document->user()->associate($this->user);
        $document->title = $this->title;
        $document->template = $this->template;
        $document->content = view('documents.' . $this->template, array_merge($this->contentData))->render();

        $pdf = PDF::loadView('documents.print.index', compact('document'));
        $pdfContent = $pdf->output();

        $file = $filesRepository->createByContent($pdfContent, $document->title . '.pdf');

        $document->file()->associate($file);
        $document->save();
    }
}
