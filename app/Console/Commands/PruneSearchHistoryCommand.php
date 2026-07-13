<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SearchHistory;


class PruneSearchHistoryCommand extends Command
{

    protected $signature = 'app:prune-search-history {--limit=10}'; 
    protected $description = "Keep only the latest search history records per user"; 
    /**
     * Keep only the latest search history records per user
     */
    public function handle()
    {
       $limit = (int) $this->option('limit'); 

       $userIds = SearchHistory::query()->distinct()->pluck('user_id');

       $deletedTotal = 0; 

       // TODO: add the source to the query, so we get the latest search history records per user and source
       foreach ($userIds as $userId) {
        $idsToKeep = SearchHistory::query()
            ->where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->pluck('id'); 

        $deleted = SearchHistory::query()
            ->where('user_id', $userId)
            ->whereNotIn('id', $idsToKeep->all(), 'and')
            ->delete();
            
        $deletedTotal += $deleted; 

       }
       
       $this->info("Deleted {$deletedTotal} old search history records"); 

       return self::SUCCESS; 
    }
}
