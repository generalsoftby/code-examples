<?php namespace App\Commands\Region;

use App\Commands\Command;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Filesystem\Factory;
use \Carbon\Carbon;
use App\Models;


class GeoRegionList extends Command implements SelfHandling {

    protected $name = 'geo_regions:list';

	const LOCALE_CODE_KEY = 4;

	const REGION_ISO_KEY = 6;

	const REGION_NAME_KEY = 7;

	const CITY_NAME_KEY = 10;

     /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        \DB::table('geo_regions')->truncate();
        $regions = \DB::table('regions')->get();
        foreach(\Storage::files('/files/region') as $file){
            $content = \File::get(\Config::get('app.storage_path') . $file);
            $csv = str_getcsv($content, "\n");
            array_shift($csv);

            foreach($csv as $key => $row){
                $row_values = str_getcsv($row, ",");
                if($row_values[self::LOCALE_CODE_KEY] != 'RU' || ($row_values[self::CITY_NAME_KEY] == '' && $row_values[self::REGION_NAME_KEY] == '')) continue;

                $city_name = str_replace('"', '', $row_values[self::CITY_NAME_KEY]);
                $region_name = str_replace('"', '', $row_values[self::REGION_NAME_KEY] == "МО" ? "Московская область" : $row_values[self::REGION_NAME_KEY]);
                $parent_iso = $row_values[self::LOCALE_CODE_KEY] . '-' . $row_values[self::REGION_ISO_KEY];

                $parent_region = $regions->where('name', '=', $city_name)->first();
                if(!$parent_region) $parent_region = $regions->where('iso', '=', $parent_iso)->first();
                if( Models\GeoRegion::where('name', $city_name)->where('parent_region_name', $region_name)->where('parent_iso', $parent_iso)->exists()) continue;

                $geo_region = $this->add([
                    'name'                  => $city_name,
                    'parent_region_name'    => $region_name,
                    'parent_iso'            => $parent_iso,
                    'region_id'             => $parent_region ? $parent_region->id : NULL,
                    
                ]);

                if( Models\GeoRegion::where('name', '')->where('parent_region_name', $region_name)->where('parent_iso', $parent_iso)->exists()) continue;

                $geo_region = $this->add([
                    'parent_region_name'    => $region_name,
                    'parent_iso'            => $parent_iso,
                    'region_id'             => $parent_region ? $parent_region->id : NULL,
                    
                ]);
            }
            \Storage::delete($file);
        }
    }

    private function add($data)
    {
        $geo_region = new Models\GeoRegion();
        foreach ($data as $key => $value) {
            $geo_region->setAttribute($key, $value);
        }
        $geo_region->save();

        return $geo_region;
    }

}
