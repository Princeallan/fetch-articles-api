<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Helper\Table;

class FetchArticles extends Command
{
    protected $signature = 'fetch_articles
                            {--limit=5 : Number of articles to retrieve}
                            {--has_comments_only : Fetch articles with comments only}';

    protected $description = 'Fetches articles from dev.to api and displays in tabular form';

    public function handle()
    {
        $withCommentsOnly = $this->option('has_comments_only');

        $response = Http::get('https://dev.to/api/articles', [
            'per_page' => $this->option('limit'),
            'has_comments' => $withCommentsOnly ? 'true' : 'false',
        ]);

        if ($response->failed()) {
            $this->error('Failed to fetch articles.');
            return;
        }

        $articles = $response->json();

        if (empty($articles)) {
            $this->info('No articles found.');
            return;
        }

        $table = new Table($this->output);
        $table->setHeaders(['Title', 'Publish Date', 'Comments Count', 'Author']);

        foreach ($articles as $article) {
            $table->addRow([
                $article['title'],
                $article['readable_publish_date'],
                $article['comments_count'],
                $article['user']['username'],
            ]);
        }

        $table->render();
    }

}
