<?php

namespace App\Console\Commands;

use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendMedicalRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated push notifications for upcoming vaccination and deworming due dates (30 days, 7 days, 3 days, and today)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking medical logs for scheduled reminders...');

        $today = Carbon::today();
        $targetDays = [
            30 => 'in 1 month',
            7  => 'in 7 days',
            3  => 'in 3 days',
            0  => 'today',
        ];

        $sentCount = 0;

        foreach ($targetDays as $daysAhead => $timeLabel) {
            $targetDate = $today->copy()->addDays($daysAhead)->format('Y-m-d');

            $logs = MedicalLog::with('pet')
                ->whereDate('next_due_date', $targetDate)
                ->get();

            foreach ($logs as $log) {
                $pet = $log->pet;
                $petName = ($pet && !empty($pet->name)) ? $pet->name : ('Pet no. ' . $log->pet_id);
                $category = ucfirst(str_replace('_', ' ', $log->category ?? 'healthcare'));
                $itemLabel = !empty($log->vaccine_name) ? $log->vaccine_name : $category;
                $dueDateFormatted = $log->next_due_date->format('M d, Y');

                $isDeworming = ($log->category === 'deworming');
                $actionNoun = $isDeworming ? 'deworming dose' : 'booster';
                $advisoryType = $isDeworming ? 'Deworming Advisory' : 'Vaccination Advisory';

                // Build advisory notification content
                if ($daysAhead === 0) {
                    $title = $isDeworming
                        ? "Deworming Due Today: {$petName}"
                        : "Vaccine Booster Due Today: {$petName}";
                    $body = "{$petName}'s {$itemLabel} {$actionNoun} is scheduled for today ({$dueDateFormatted}). Please consult your private veterinarian or visit CAWS.";
                } elseif ($daysAhead === 3) {
                    $title = $isDeworming
                        ? "Deworming Due in 3 Days: {$petName}"
                        : "Vaccine Booster Due in 3 Days: {$petName}";
                    $body = "Reminder: {$petName}'s {$itemLabel} {$actionNoun} is due on {$dueDateFormatted} (in 3 days). Remember to present your CAWS Pet Card during checkup.";
                } elseif ($daysAhead === 7) {
                    $title = $isDeworming
                        ? "Upcoming Deworming: {$petName}"
                        : "Upcoming Vaccine Booster: {$petName}";
                    $body = "Advisory: {$petName}'s {$itemLabel} {$actionNoun} is scheduled for {$dueDateFormatted} (in 1 week). Check your Pet Health Card for details.";
                } else {
                    $title = "{$advisoryType}: {$petName}";
                    $body = "Advisory: {$petName}'s {$itemLabel} {$actionNoun} will be due in 1 month ({$dueDateFormatted}). Plan ahead with your veterinarian.";
                }

                // Find adopter email
                $adopterEmails = AdoptionApplication::where('pet_id', $log->pet_id)
                    ->whereIn('status', ['approved', 'under_review', 'pending'])
                    ->pluck('applicant_email')
                    ->unique();

                foreach ($adopterEmails as $email) {
                    $this->line("Dispatching {$category} notification to {$email} for {$petName} (Due: {$dueDateFormatted}, {$timeLabel})");

                    FirebaseNotificationService::sendToUser(
                        $email,
                        $title,
                        $body,
                        [
                            'type'         => 'vaccine_reminder',
                            'pet_id'       => (string) $log->pet_id,
                            'category'     => (string) $log->category,
                            'vaccine_name' => (string) ($log->vaccine_name ?? ''),
                            'due_date'     => $log->next_due_date->format('Y-m-d'),
                            'days_left'    => (string) $daysAhead,
                        ]
                    );

                    $sentCount++;
                }
            }
        }

        $this->info("Completed. Dispatched {$sentCount} healthcare push reminder(s).");

        return Command::SUCCESS;
    }
}
