<?php

namespace Database\Seeders\Tk;

use App\Enums\Baikal\BaikalUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalBaikal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TerminalBaikalSeeder extends Seeder
{
    private array $candidatsToUpdate = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $username = config('companies.baikal.username');
        $url = config('companies.baikal.url') . BaikalUrlType::Affiliate->value;

        $response = Http::withBasicAuth($username, '')->get($url);

        // особенность данного списка в том, что здесь нет явного указания страны в буквенно-цифровом виде
        // поэтому, сеять данный список необходимо в последнюю очередь, когда уже есть набор основных локаций
        // в связи с этим за основу посева будет взята таблица локаций, а не стран

        foreach ($response->object() as $fillial) {

            $place = $fillial->name;
            $identifier = $fillial->guid;
            $terminals = $fillial->terminals;

            foreach ($terminals as $terminal) {

                $dirty = $terminal->address;

                $location = Location::where('name', $place)->first();

                if (!$location) {
                    $this->candidatsToUpdate[] = $place . ': ' . $dirty;
                    continue;
                }

                TerminalBaikal::create([
                    'location_id' => $location->id,
                    'identifier' => $identifier,
                    'name' => $location->name,
                    'dirty' => $dirty,
                ]);
            }
        }

        dump('Следующие локации остались не добавленными: ', $this->candidatsToUpdate);
    }
}
