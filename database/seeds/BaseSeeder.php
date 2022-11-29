<?php

use Illuminate\Database\Seeder;

class BaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $this->exec();
        } catch (Exception $e) {
            $this->command->warn(get_class($this) . ' did not work correctly.');
            $this->command->info($e->getMessage());
            $this->command->warn('Please try again.');
        }
    }
}
