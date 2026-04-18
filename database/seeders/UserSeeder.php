<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin (already exists - update if needed)
        $admin = User::updateOrCreate(
            ['email' => 'admin@campfix.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'mis',
                'is_admin' => true,
                'department' => 'IT Department',
            ]
        );

        // School Administrator (formerly Principal)
        $schoolAdmin = User::updateOrCreate(
            ['email' => 'principal@campfix.com'],
            [
                'name' => 'Dr. Maria Santos',
                'password' => Hash::make('principal123'),
                'role' => 'school_admin',
                'department' => 'Office of the School Administrator',
                'phone' => '09123456789',
            ]
        );

        // Academic Head
        User::updateOrCreate(
            ['email' => 'academic.head@campfix.com'],
            [
                'name' => 'Dr. Juan Rodriguez',
                'password' => Hash::make('academichead123'),
                'role' => 'academic_head',
                'department' => 'Academic Affairs',
                'phone' => '09123456790',
            ]
        );

        // Program Heads (4 roles - one for each department)
        $departments = ['GE', 'ICT', 'Business Management', 'THM'];
        $programHeadNames = ['Mr. Jose Garcia', 'Ms. Maria Lee', 'Mr. Pedro Santos', 'Mrs. Ana Torres'];
        $programHeadEmails = ['program.head.ge@campfix.com', 'program.head.ict@campfix.com', 'program.head.bm@campfix.com', 'program.head.thm@campfix.com'];

        for ($i = 0; $i < 4; $i++) {
            User::updateOrCreate(
                ['email' => $programHeadEmails[$i]],
                [
                    'name' => $programHeadNames[$i],
                    'password' => Hash::make('programhead123'),
                    'role' => 'program_head',
                    'department' => $departments[$i],
                    'phone' => '0912345679'.$i,
                ]
            );
        }

        // Faculty / Teacher
        $teacher = User::updateOrCreate(
            ['email' => 'teacher@campfix.com'],
            [
                'name' => 'Mr. Juan dela Cruz',
                'password' => Hash::make('teacher123'),
                'role' => 'faculty',
                'department' => 'Computer Science',
                'phone' => '09123456788',
            ]
        );

        // Maintenance Staff
        $maintenance = User::updateOrCreate(
            ['email' => 'maintenance@campfix.com'],
            [
                'name' => 'Mr. Pedro Custodio',
                'password' => Hash::make('maintenance123'),
                'role' => 'maintenance',
                'department' => 'Maintenance Department',
                'phone' => '09123456787',
            ]
        );

        // Additional Faculty Members
        User::updateOrCreate(
            ['email' => 'prof.perez@campfix.com'],
            [
                'name' => 'Mrs. Ana Perez',
                'password' => Hash::make('teacher123'),
                'role' => 'faculty',
                'department' => 'Engineering',
                'phone' => '09123456786',
            ]
        );

        User::updateOrCreate(
            ['email' => 'prof.gonzales@campfix.com'],
            [
                'name' => 'Mr. Roberto Gonzales',
                'password' => Hash::make('teacher123'),
                'role' => 'faculty',
                'department' => 'Business Administration',
                'phone' => '09123456785',
            ]
        );

        // Additional Maintenance Staff
        User::updateOrCreate(
            ['email' => 'tech.reyes@campfix.com'],
            [
                'name' => 'Mr. Mario Reyes',
                'password' => Hash::make('maintenance123'),
                'role' => 'maintenance',
                'department' => 'Maintenance Department',
                'phone' => '09123456784',
            ]
        );

        // Student (sample)
        User::updateOrCreate(
            ['email' => 'student@campfix.com'],
            [
                'name' => 'Juan Student',
                'password' => Hash::make('student123'),
                'role' => 'student',
                'department' => 'BS Computer Science',
                'phone' => '09123456783',
            ]
        );

        echo "Users seeded successfully!\n";
        echo "Login credentials:\n";
        echo "Admin: admin@campfix.com / admin123\n";
        echo "School Administrator: principal@campfix.com / principal123\n";
        echo "Academic Head: academic.head@campfix.com / academichead123\n";
        echo "Program Head GE: program.head.ge@campfix.com / programhead123\n";
        echo "Program Head ICT: program.head.ict@campfix.com / programhead123\n";
        echo "Program Head Business Management: program.head.bm@campfix.com / programhead123\n";
        echo "Program Head THM: program.head.thm@campfix.com / programhead123\n";
        echo "Teacher: teacher@campfix.com / teacher123\n";
        echo "Maintenance: maintenance@campfix.com / maintenance123\n";
        echo "Student: student@campfix.com / student123\n";
    }
}
