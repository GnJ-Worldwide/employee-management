<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id')
                  ->nullable();

            $table->unsignedBigInteger('contract_id')
                  ->nullable();

            $table->unsignedBigInteger('vendor_id')
                  ->nullable();

            // Attendance data
            $table->date('date');

            $table->string('status');
            // Possible values (see AttendanceStatus enum):
            //   Present | Absent | Present with Overtime
            //   National Holiday | Head Office | Travelling
            //   Privilege Leave | Casual Leave | Sick Leave
            //   Site Transfer | Out of Duty

            $table->decimal('overtime_hours', 5, 2)->default(0.00);
            // Only relevant when status = 'Present with Overtime'

            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('marked_by')
                  ->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Query optimisation indexes
            $table->index(['contract_id', 'date']);
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};