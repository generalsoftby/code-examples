<?php namespace App\Commands\Deferred;

use App\Commands\Command;

use App\Models;

class DeferredEmptyClear extends Command {

    protected $name = 'deferred:empty:clear';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $now = \Carbon::now();
        $date = $now->subDays(1);

        $not_empty = Models\DeferredModel::groupBy('deferred_id')->pluck('deferred_id')->all();

        $deferred_ids = Models\Deferred::where('updated_at', '<', $date)
            ->whereNotIn('id', $not_empty)
            ->pluck('id')
            ->all();
        $this->info(count($deferred_ids));

        foreach(array_chunk($deferred_ids, 100) as $chunk){
            Models\Deferred::whereIn('id', $chunk)->delete();
        }
    }

}
