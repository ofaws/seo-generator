<?php

namespace App\Console\Commands;

use App\Exceptions\ErrorResponseException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OpenAI;
use Spatie\SimpleExcel\SimpleExcelReader;

class GenerateSeo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:generate {filename : name of source csv file} {--free}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate SEO items like titles, descriptions and keywords from raw data given in csv file';

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle()
    {
        $file = base_path('/sources/' . $this->argument('filename') . '.csv');

        throw_unless(File::exists($file), new ErrorResponseException('File not found'));

        $client = OpenAI::client(config('services.openai.key'));

        $rows = SimpleExcelReader::create($file)->getRows();

        File::ensureDirectoryExists(base_path('/results'));

        $resultFile = base_path('/results/' . $this->argument('filename') . '.csv');

        file_put_contents($resultFile, '"Source topic","Source overview",Title,Description,Keywords' . PHP_EOL);

        $rows->each(function (array $line, $position) use ($client, $resultFile) {

            if (isset($line['topic'])) {
                $seoTitle = $this->send($client,
                    "You are an expert SEO specialist and need to generate a SEO title. Your responses must be less than 60 characters",
                    sprintf('Generate SEO title for this topic: %s', $line['topic'])
                );

                $task = $line['overview']
                    ? sprintf('Generate SEO description based on this overview: %s', $line['overview'])
                    : sprintf('Generate SEO description for this topic: %s', $line['topic']);

                $seoDescription = $this->send($client,
                    "You are an expert SEO specialist and need to generate a SEO description. Your responses must be less than 156 characters",
                    $task
                );

                $kTask = $line['overview']
                    ? sprintf('Generate from 5 to 10 keywords based on this overview: %s', $line['overview'])
                    : sprintf('Generate from 5 to 10 keywords for this topic: %s', $line['topic']);

                $keywords = $this->send($client,
                    "You are an expert SEO specialist and need to generate SEO keywords. Generate keywords out of user's prompt and organize them in one-line list surrounded by square brackets",
                    $kTask
                );

                $content = implode(',', [
                        sprintf('"%s"', $line['topic']),
                        sprintf('"%s"', $line['overview']),
                        str_replace('""', '"', sprintf('"%s"', $seoTitle)),
                        str_replace('""', '"', sprintf('"%s"', $seoDescription)),
                        sprintf('"%s"', $keywords),
                    ]) . PHP_EOL;

                file_put_contents($resultFile, $content, FILE_APPEND | LOCK_EX);

                $this->components->task('Finished for ' . $position + 1 . ' row');

                if ($this->option('free')) {
                    sleep(55);
                }
            }
        });

        $this->components->info('Success!');
    }

    private function send($client, string $instruction, string $prompt)
    {
        return $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $instruction],
                ['role' => 'user', 'content' => $prompt],
            ],
        ])['choices'][0]['message']['content'];
    }
}
