<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestData extends Command
{
    protected $signature = 'test:data';
    protected $description = 'Test data';

    public function handle()
    {
        $questions = DB::table('questions')->get();
        foreach($questions as $q) {
            $this->info("Q_ID: {$q->id} | QuizID: '{$q->quiz_id}' | Type: {$q->type} | Q: {$q->question}");
        }
    }
}
