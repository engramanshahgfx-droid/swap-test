<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Airline;

class AirlineSeeder extends Seeder
{
    public function run(): void
    {
        $airlines = [
            // Middle East
            ['name' => 'Emirates', 'code' => 'EK', 'country' => 'United Arab Emirates', 'is_active' => true],
            ['name' => 'flydubai', 'code' => 'FZ', 'country' => 'United Arab Emirates', 'is_active' => true],
            ['name' => 'Air Arabia', 'code' => 'G9', 'country' => 'United Arab Emirates', 'is_active' => true],
            ['name' => 'Etihad Airways', 'code' => 'EY', 'country' => 'United Arab Emirates', 'is_active' => true],
            ['name' => 'Qatar Airways', 'code' => 'QR', 'country' => 'Qatar', 'is_active' => true],
            ['name' => 'Saudia', 'code' => 'SV', 'country' => 'Saudi Arabia', 'is_active' => true],
            ['name' => 'Flynas', 'code' => 'XY', 'country' => 'Saudi Arabia', 'is_active' => true],
            ['name' => 'Flyadeal', 'code' => 'F3', 'country' => 'Saudi Arabia', 'is_active' => true],
            ['name' => 'Gulf Air', 'code' => 'GF', 'country' => 'Bahrain', 'is_active' => true],
            ['name' => 'Oman Air', 'code' => 'WY', 'country' => 'Oman', 'is_active' => true],
            ['name' => 'Kuwait Airways', 'code' => 'KU', 'country' => 'Kuwait', 'is_active' => true],
            ['name' => 'Jazeera Airways', 'code' => 'J9', 'country' => 'Kuwait', 'is_active' => true],
            ['name' => 'Royal Jordanian', 'code' => 'RJ', 'country' => 'Jordan', 'is_active' => true],
            ['name' => 'Middle East Airlines', 'code' => 'ME', 'country' => 'Lebanon', 'is_active' => true],

            // Africa
            ['name' => 'Air Algérie', 'code' => 'AH', 'country' => 'Algeria', 'is_active' => true],
            ['name' => 'EgyptAir', 'code' => 'MS', 'country' => 'Egypt', 'is_active' => true],
            ['name' => 'Ethiopian Airlines', 'code' => 'ET', 'country' => 'Ethiopia', 'is_active' => true],
            ['name' => 'Kenya Airways', 'code' => 'KQ', 'country' => 'Kenya', 'is_active' => true],
            ['name' => 'Royal Air Maroc', 'code' => 'AT', 'country' => 'Morocco', 'is_active' => true],
            ['name' => 'South African Airways', 'code' => 'SA', 'country' => 'South Africa', 'is_active' => true],
            ['name' => 'RwandAir', 'code' => 'WB', 'country' => 'Rwanda', 'is_active' => true],
            ['name' => 'Tunisair', 'code' => 'TU', 'country' => 'Tunisia', 'is_active' => true],
            ['name' => 'Air Mauritius', 'code' => 'MK', 'country' => 'Mauritius', 'is_active' => true],
            ['name' => 'TAAG Angola Airlines', 'code' => 'DT', 'country' => 'Angola', 'is_active' => true],

            // Europe
            ['name' => 'Lufthansa', 'code' => 'LH', 'country' => 'Germany', 'is_active' => true],
            ['name' => 'Swiss International Air Lines', 'code' => 'LX', 'country' => 'Switzerland', 'is_active' => true],
            ['name' => 'Austrian Airlines', 'code' => 'OS', 'country' => 'Austria', 'is_active' => true],
            ['name' => 'Air France', 'code' => 'AF', 'country' => 'France', 'is_active' => true],
            ['name' => 'KLM', 'code' => 'KL', 'country' => 'Netherlands', 'is_active' => true],
            ['name' => 'British Airways', 'code' => 'BA', 'country' => 'United Kingdom', 'is_active' => true],
            ['name' => 'Iberia', 'code' => 'IB', 'country' => 'Spain', 'is_active' => true],
            ['name' => 'Vueling', 'code' => 'VY', 'country' => 'Spain', 'is_active' => true],
            ['name' => 'Finnair', 'code' => 'AY', 'country' => 'Finland', 'is_active' => true],
            ['name' => 'SAS Scandinavian Airlines', 'code' => 'SK', 'country' => 'Denmark', 'is_active' => true],
            ['name' => 'ITA Airways', 'code' => 'AZ', 'country' => 'Italy', 'is_active' => true],
            ['name' => 'Aegean Airlines', 'code' => 'A3', 'country' => 'Greece', 'is_active' => true],
            ['name' => 'TAP Air Portugal', 'code' => 'TP', 'country' => 'Portugal', 'is_active' => true],
            ['name' => 'Ryanair', 'code' => 'FR', 'country' => 'Ireland', 'is_active' => true],
            ['name' => 'easyJet', 'code' => 'U2', 'country' => 'United Kingdom', 'is_active' => true],
            ['name' => 'Wizz Air', 'code' => 'W6', 'country' => 'Hungary', 'is_active' => true],
            ['name' => 'Air Serbia', 'code' => 'JU', 'country' => 'Serbia', 'is_active' => true],
            ['name' => 'Brussels Airlines', 'code' => 'SN', 'country' => 'Belgium', 'is_active' => true],
            ['name' => 'LOT Polish Airlines', 'code' => 'LO', 'country' => 'Poland', 'is_active' => true],
            ['name' => 'Croatia Airlines', 'code' => 'OU', 'country' => 'Croatia', 'is_active' => true],
            ['name' => 'Icelandair', 'code' => 'FI', 'country' => 'Iceland', 'is_active' => true],
            ['name' => 'Norwegian Air Shuttle', 'code' => 'DY', 'country' => 'Norway', 'is_active' => true],

            // North America
            ['name' => 'American Airlines', 'code' => 'AA', 'country' => 'United States', 'is_active' => true],
            ['name' => 'Delta Air Lines', 'code' => 'DL', 'country' => 'United States', 'is_active' => true],
            ['name' => 'United Airlines', 'code' => 'UA', 'country' => 'United States', 'is_active' => true],
            ['name' => 'Southwest Airlines', 'code' => 'WN', 'country' => 'United States', 'is_active' => true],
            ['name' => 'JetBlue Airways', 'code' => 'B6', 'country' => 'United States', 'is_active' => true],
            ['name' => 'Alaska Airlines', 'code' => 'AS', 'country' => 'United States', 'is_active' => true],
            ['name' => 'Spirit Airlines', 'code' => 'NK', 'country' => 'United States', 'is_active' => true],
            ['name' => 'Frontier Airlines', 'code' => 'F9', 'country' => 'United States', 'is_active' => true],
            ['name' => 'Air Canada', 'code' => 'AC', 'country' => 'Canada', 'is_active' => true],
            ['name' => 'WestJet', 'code' => 'WS', 'country' => 'Canada', 'is_active' => true],
            ['name' => 'Aeromexico', 'code' => 'AM', 'country' => 'Mexico', 'is_active' => true],
            ['name' => 'Volaris', 'code' => 'Y4', 'country' => 'Mexico', 'is_active' => true],

            // South America
            ['name' => 'LATAM Airlines', 'code' => 'LA', 'country' => 'Chile', 'is_active' => true],
            ['name' => 'JetSMART', 'code' => 'JA', 'country' => 'Chile', 'is_active' => true],
            ['name' => 'Avianca', 'code' => 'AV', 'country' => 'Colombia', 'is_active' => true],
            ['name' => 'Copa Airlines', 'code' => 'CM', 'country' => 'Panama', 'is_active' => true],
            ['name' => 'Gol Linhas Aereas', 'code' => 'G3', 'country' => 'Brazil', 'is_active' => true],
            ['name' => 'Azul Brazilian Airlines', 'code' => 'AD', 'country' => 'Brazil', 'is_active' => true],
            ['name' => 'Aerolineas Argentinas', 'code' => 'AR', 'country' => 'Argentina', 'is_active' => true],

            // Asia
            ['name' => 'ANA All Nippon Airways', 'code' => 'NH', 'country' => 'Japan', 'is_active' => true],
            ['name' => 'Japan Airlines', 'code' => 'JL', 'country' => 'Japan', 'is_active' => true],
            ['name' => 'Korean Air', 'code' => 'KE', 'country' => 'South Korea', 'is_active' => true],
            ['name' => 'Asiana Airlines', 'code' => 'OZ', 'country' => 'South Korea', 'is_active' => true],
            ['name' => 'Singapore Airlines', 'code' => 'SQ', 'country' => 'Singapore', 'is_active' => true],
            ['name' => 'Scoot', 'code' => 'TR', 'country' => 'Singapore', 'is_active' => true],
            ['name' => 'Thai Airways', 'code' => 'TG', 'country' => 'Thailand', 'is_active' => true],
            ['name' => 'AirAsia', 'code' => 'AK', 'country' => 'Malaysia', 'is_active' => true],
            ['name' => 'Malaysia Airlines', 'code' => 'MH', 'country' => 'Malaysia', 'is_active' => true],
            ['name' => 'Garuda Indonesia', 'code' => 'GA', 'country' => 'Indonesia', 'is_active' => true],
            ['name' => 'Batik Air', 'code' => 'ID', 'country' => 'Indonesia', 'is_active' => true],
            ['name' => 'Philippine Airlines', 'code' => 'PR', 'country' => 'Philippines', 'is_active' => true],
            ['name' => 'Cebu Pacific', 'code' => '5J', 'country' => 'Philippines', 'is_active' => true],
            ['name' => 'Vietnam Airlines', 'code' => 'VN', 'country' => 'Vietnam', 'is_active' => true],
            ['name' => 'Cathay Pacific', 'code' => 'CX', 'country' => 'Hong Kong', 'is_active' => true],
            ['name' => 'Air China', 'code' => 'CA', 'country' => 'China', 'is_active' => true],
            ['name' => 'China Eastern Airlines', 'code' => 'MU', 'country' => 'China', 'is_active' => true],
            ['name' => 'China Southern Airlines', 'code' => 'CZ', 'country' => 'China', 'is_active' => true],
            ['name' => 'Hainan Airlines', 'code' => 'HU', 'country' => 'China', 'is_active' => true],
            ['name' => 'Air India', 'code' => 'AI', 'country' => 'India', 'is_active' => true],
            ['name' => 'IndiGo', 'code' => '6E', 'country' => 'India', 'is_active' => true],
            ['name' => 'SriLankan Airlines', 'code' => 'UL', 'country' => 'Sri Lanka', 'is_active' => true],
        ];

        foreach ($airlines as $airline) {
            Airline::firstOrCreate(['code' => $airline['code']], $airline);
        }
    }
}
