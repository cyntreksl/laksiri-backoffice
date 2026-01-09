<?php

namespace App\Console\Commands;

use App\Enum\TokenStatus;
use App\Models\Token;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarkDueTokensCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:mark-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark tokens as DUE if not completed within 16 hours (by same day midnight)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to mark due tokens...');

        try {
            // Get tokens that should be marked as DUE:
            // - Created today or earlier
            // - Status is ONGOING (not completed, not cancelled, not already due)
            // - Not completed (departed_at is null)
            // - Not cancelled (is_cancelled is false)
            // - Created before today's midnight (meaning they're from previous days or earlier today)
            
            $today = now()->startOfDay();
            
            $tokensToMarkDue = Token::where('status', TokenStatus::ONGOING->value)
                ->whereNull('departed_at')
                ->where('is_cancelled', false)
                ->where('created_at', '<', $today)
                ->get();

            $count = $tokensToMarkDue->count();

            if ($count === 0) {
                $this->info('No tokens to mark as DUE.');
                return self::SUCCESS;
            }

            $this->info("Found {$count} token(s) to mark as DUE.");

            foreach ($tokensToMarkDue as $token) {
                $token->status = TokenStatus::DUE->value;
                $token->save();

                Log::info('Token marked as DUE', [
                    'token_id' => $token->id,
                    'token_number' => $token->token,
                    'created_at' => $token->created_at->toIso8601String(),
                ]);
            }

            $this->info("Successfully marked {$count} token(s) as DUE.");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to mark due tokens: ' . $e->getMessage());
            Log::error('Failed to mark due tokens', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
