<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiIntentService;

class BuildMovieEmbeddings extends Command
{
    protected $signature = 'ai:build-embeddings';
    protected $description = 'Build embeddings for all movies and store locally';

    protected $ai;

    public function __construct(AiIntentService $ai)
    {
        parent::__construct();
        $this->ai = $ai;
    }

    public function handle()
    {
        $this->info('Starting to build embeddings for movies...');
        $out = $this->ai->buildMovieEmbeddings();
        $this->info('Built ' . count($out) . ' embeddings. Saved to storage/app/movie_embeddings.json');
        return 0;
    }
}
