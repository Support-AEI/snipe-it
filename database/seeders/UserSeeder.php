<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        if (! Company::count()) {
            $this->call(CompanySeeder::class);
        }

        $companyIds = Company::all()->pluck('id');

        User::factory()->count(1)->firstAdmin()->create(['company_id' => $companyIds->random()]);
        User::factory()->count(1)->snipeAdmin()->create(['company_id' => $companyIds->random()]);
        User::factory()->count(3)->superuser()->create(['company_id' => $companyIds->random()]);
        User::factory()->count(3)->admin()->create(['company_id' => $companyIds->random()]);
        User::factory()->count(50)->viewAssets()->create(['company_id' => $companyIds->random()]);

        $src = public_path('/img/demo/avatars/');
        $dst = 'avatars'.'/';
        $del_files = Storage::files($dst);

        foreach ($del_files as $del_file) { // iterate files
            $file_to_delete = str_replace($src, '', $del_file);
            Log::debug('Deleting: '.$file_to_delete);
            try {
                Storage::disk('public')->delete($dst.$del_file);
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $add_files = glob($src.'/*.*');
        foreach ($add_files as $add_file) {
            $file_to_copy = str_replace($src, '', $add_file);
            Log::debug('Copying: '.$file_to_copy);
            try {
                Storage::disk('public')->put($dst.$file_to_copy, file_get_contents($src.$file_to_copy));
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $users = User::orderBy('id', 'asc')->take(20)->get();
        $file_number = 1;

        foreach ($users as $user) {

            $user->avatar = $file_number.'.jpg';
            $user->save();
            $file_number++;
        }
        


    }
}
