<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'first_name' => 'Rahul',
                'last_name' => 'Sharma',
                'gender' => 'Male',
                'designation' => 'Supervisor',
                'trade' => 'Skilled',
                'skill_level' => 'Welding',
                'academics' => 'ITI Welder',
            ],
            [
                'first_name' => 'Amit',
                'last_name' => 'Patel',
                'gender' => 'Male',
                'designation' => 'Fitter',
                'trade' => 'Semi-Skilled',
                'skill_level' => 'Pipe Fitting',
                'academics' => 'ITI Fitter',
            ],
            [
                'first_name' => 'Suresh',
                'last_name' => 'Yadav',
                'gender' => 'Male',
                'designation' => 'Helper',
                'trade' => 'Un Skilled',
                'skill_level' => 'General Labour',
                'academics' => '10th Pass',
            ],
            [
                'first_name' => 'Priya',
                'last_name' => 'Verma',
                'gender' => 'Female',
                'designation' => 'Safety Supervisor',
                'trade' => 'Highly Skilled',
                'skill_level' => 'Industrial Safety',
                'academics' => 'BE Mechanical',
            ],
            [
                'first_name' => 'Neha',
                'last_name' => 'Joshi',
                'gender' => 'Female',
                'designation' => 'Engineer',
                'trade' => 'Highly Skilled',
                'skill_level' => 'Site Engineering',
                'academics' => 'BE Civil',
            ],
        ];

        foreach ($employees as $index => $employee) {

            Employee::create([

                // Employment
                'aadhaar_number' => fake()->unique()->numerify('############'),
                'joined_at' => now()->subMonths(rand(1, 24)),
                'is_permanent' => rand(0, 1),
                'emp_status' => 'active',

                // Personal
                'first_name' => $employee['first_name'],
                'last_name' => $employee['last_name'],
                'date_of_birth' => now()->subYears(rand(22, 40)),
                'gender' => $employee['gender'],
                'guardian_name' => fake()->name(),
                'mobile_number' => fake()->numerify('9#########'),
                'marital_status' => rand(0, 1) ? 'Married' : 'UnMarried',
                'blood_group' => fake()->randomElement([
                    'A+',
                    'B+',
                    'O+',
                    'AB+',
                    'A-',
                    'B-'
                ]),
                'nationality' => 'Indian',
                'identification_mark' => 'Mole on right cheek',

                // Address
                'permanent_address' => fake()->address(),
                'present_address' => fake()->address(),
                'country' => 'India',
                'state' => 'Gujarat',
                'district' => 'Vadodara',
                'taluka' => 'Vadodara',
                'village_city' => 'Vadodara',
                'pin_code' => fake()->numerify('39####'),
                'location_manual_entry' => true,

                // Professional
                'trade' => $employee['trade'],
                'skill_level' => $employee['skill_level'],
                'designation' => $employee['designation'],
                'highest_education' => $employee['academics'],
                'academics' => $employee['academics'],

                // Statutory
                'pan_number' => strtoupper(fake()->bothify('?????####?')),
                'uan_number' => fake()->numerify('############'),
                'esic_number' => fake()->numerify('#################'),

                // Bank
                'bank_name' => fake()->randomElement([
                    'State Bank of India',
                    'HDFC Bank',
                    'ICICI Bank',
                    'Axis Bank'
                ]),
                'bank_account_number' => fake()->bankAccountNumber(),
                'bank_ifsc_code' => 'SBIN0' . fake()->numerify('######'),

                // Nominee
                'nominee_name' => fake()->name(),
                'nominee_relationship' => fake()->randomElement([
                    'Father',
                    'Mother',
                    'Wife',
                    'Brother'
                ]),
                'nominee_date_of_birth' => now()->subYears(rand(25, 60)),
                'nominee_mobile_number' => fake()->numerify('9#########'),

                // Documents
                'doc_aadhaar_mode' => 'text',
                'doc_aadhaar_text' => 'Sample Aadhaar Document',
                'doc_pan_mode' => 'text',
                'doc_pan_text' => 'Sample PAN Document',
                'doc_bank_passbook_mode' => 'text',
                'doc_bank_passbook_text' => 'Sample Bank Passbook',
                'doc_education_certificate_mode' => 'text',
                'doc_education_certificate_text' => 'Sample Education Certificate',

                // Engagement
                'engagement_status' => 'Available',

                // Audit
                'created_by' => 1,
            ]);
        }
    }
}