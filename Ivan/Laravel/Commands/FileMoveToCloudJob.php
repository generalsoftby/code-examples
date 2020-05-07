<?php

namespace App\Jobs;

use App\Models\File;
use App\Repositories\FilesRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FileMoveToCloudJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var int */
    protected $fileId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(File $file)
    {
        $this->fileId = $file->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FilesRepository $filesRepository)
    {
        \DB::transaction(function () use ($filesRepository) {
            /** @var File $file */
            $file = File::query()
                ->lockForUpdate()
                ->findOrFail($this->fileId);

            if ($file->is_cloud) {
                logger()->error('[' . __CLASS__ . '] File#' . $file->id . ' already in a cloud.');
                return;
            }

            $storagePath = $filesRepository->getStoragePath($file);
            $filePath = storage_path('app/' . $storagePath);

            $file->is_cloud = true;

            try {
                $filesRepository->storeFile(new \Illuminate\Http\File($filePath), $file);
            } catch (\Exception $exception) {
                logger()->error('[' . __CLASS__ . ']' . $exception->getMessage());
                return;
            }

            $file->save();

            try {
                unlink($filePath);
            } catch (\Exception $exception) {
                logger()->error('[' . __CLASS__ . '] ' . $exception->getMessage());
            }
        });
    }
}
