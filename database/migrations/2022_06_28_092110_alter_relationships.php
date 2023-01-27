<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->foreign('name_id')->references('id')->on('names')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('phones', function (Blueprint $table) {
            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('notification_users', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('blog_id')->references('id')->on('extra_services')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('like_posts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('seen_posts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('malfunctions', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('malfunction_statue_id')->references('id')->on('malfunction_statues')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('working_hour_employees', function (Blueprint $table) {
            $table->foreign('working_hour_id')->references('id')->on('working_hours')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('day_id')->references('id')->on('days')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('balance_payments', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('payment_type_id')->references('id')->on('payment_types')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreign('certificate_name_id')->references('id')->on('certificate_names')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('certificate_source_id')->references('id')->on('certificate_sources')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('certificate_rating_id')->references('id')->on('certificate_ratings')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('certificate_sources', function (Blueprint $table) {
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('previous_works', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('receptions', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('doctors', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('session_duration_id')->references('id')->on('session_durations')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('doctor_ratings', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('rating_doctor_value_id')->references('id')->on('rating_doctor_values')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('vacation_requests', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('reception_id')->references('id')->on('receptions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('appointment_statue_id')->references('id')->on('appointment_statues')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('nurses', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('clinics', function (Blueprint $table) {
            $table->foreign('clinic_name_id')->references('id')->on('clinic_names')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('clinic_names', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('clinic_departments')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('patients', function (Blueprint $table) {
            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('blood_type_id')->references('id')->on('blood_types')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('disease_patients', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('disease_id')->references('id')->on('diseases')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('allergy_patients', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('allergy_id')->references('id')->on('allergies')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('previous_session_id')->references('id')->on('sessions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('waiting_id')->references('id')->on('waitings')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('session_calculations', function (Blueprint $table) {
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('reception_id')->references('id')->on('receptions')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('medicines', function (Blueprint $table) {
            $table->foreign('prescription_id')->references('id')->on('prescriptions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('medical_name_id')->references('id')->on('medical_names')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('medical_analyses', function (Blueprint $table) {
            $table->foreign('prescription_id')->references('id')->on('prescriptions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('medical_analysis_name_id')->references('id')->on('medical_analysis_names')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('extra_treatments', function (Blueprint $table) {
            $table->foreign('session_calculation_id')->references('id')->on('session_calculations')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('waitings', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
