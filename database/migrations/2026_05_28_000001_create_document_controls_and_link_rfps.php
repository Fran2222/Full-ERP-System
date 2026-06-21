<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('document_controls')) {
            Schema::create('document_controls', function (Blueprint $table) {
                $table->id();
                $table->string('module_name', 80)->nullable();
                $table->string('form_name')->nullable();
                $table->string('type', 80)->nullable();
                $table->string('document_no')->nullable();
                $table->string('revision_no', 10)->default('00');
                $table->date('effective_date')->nullable();
                $table->string('status', 30)->default('active');
                $table->string('route_name')->nullable();
                $table->string('template_path')->nullable();
                $table->string('code_prefix', 80)->nullable();
                $table->foreignId('rfp_type_id')->nullable()->constrained('rfp_types')->nullOnDelete();
                $table->text('revision_notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        } else {
            Schema::table('document_controls', function (Blueprint $table) {
                if (! Schema::hasColumn('document_controls', 'module_name')) {
                    $table->string('module_name', 80)->nullable();
                }
                if (! Schema::hasColumn('document_controls', 'form_name')) {
                    $table->string('form_name')->nullable();
                }
                if (! Schema::hasColumn('document_controls', 'type')) {
                    $table->string('type', 80)->nullable();
                }
                if (! Schema::hasColumn('document_controls', 'document_no')) {
                    $table->string('document_no')->nullable();
                }
                if (! Schema::hasColumn('document_controls', 'revision_no')) {
                    $table->string('revision_no', 10)->default('00');
                }
                if (! Schema::hasColumn('document_controls', 'effective_date')) {
                    $table->date('effective_date')->nullable();
                }
                if (! Schema::hasColumn('document_controls', 'status')) {
                    $table->string('status', 30)->default('active');
                }
                if (! Schema::hasColumn('document_controls', 'route_name')) {
                    $table->string('route_name')->nullable();
                }
                if (! Schema::hasColumn('document_controls', 'template_path')) {
                    $table->string('template_path')->nullable();
                }
                if (! Schema::hasColumn('document_controls', 'code_prefix')) {
                    $table->string('code_prefix', 80)->nullable();
                }
                if (! Schema::hasColumn('document_controls', 'rfp_type_id')) {
                    $table->foreignId('rfp_type_id')->nullable()->constrained('rfp_types')->nullOnDelete();
                }
                if (! Schema::hasColumn('document_controls', 'revision_notes')) {
                    $table->text('revision_notes')->nullable();
                }
                if (! Schema::hasColumn('document_controls', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('document_controls', 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('project_rfps')) {
            Schema::table('project_rfps', function (Blueprint $table) {
                if (! Schema::hasColumn('project_rfps', 'document_control_id')) {
                    $table->foreignId('document_control_id')->nullable()->after('sequence_no')->constrained('document_controls')->nullOnDelete();
                }
                if (! Schema::hasColumn('project_rfps', 'document_no')) {
                    $table->string('document_no')->nullable()->after('document_control_id');
                }
                if (! Schema::hasColumn('project_rfps', 'document_revision_no')) {
                    $table->string('document_revision_no', 10)->nullable()->after('document_no');
                }
                if (! Schema::hasColumn('project_rfps', 'document_effective_date')) {
                    $table->date('document_effective_date')->nullable()->after('document_revision_no');
                }
                if (! Schema::hasColumn('project_rfps', 'document_sequence_no')) {
                    $table->unsignedInteger('document_sequence_no')->nullable()->after('document_effective_date');
                }
            });
        }

        if (Schema::hasTable('rfp_types')) {
            $types = DB::table('rfp_types')->orderBy('id')->get();
            foreach ($types as $index => $type) {
                $code = $type->code ?? strtoupper(substr((string) $type->name, 0, 3));
                $documentNo = 'WMC-' . $code . '-' . str_pad((string)($index + 1), 3, '0', STR_PAD_LEFT);

                $exists = DB::table('document_controls')
                    ->where('document_no', $documentNo)
                    ->where(function ($query) use ($type) {
                        $query->where('rfp_type_id', $type->id)
                              ->orWhereNull('rfp_type_id');
                    })
                    ->exists();

                if (! $exists) {
                    DB::table('document_controls')->insert([
                        'module_name' => 'RFP',
                        'form_name' => 'Request for Payment - ' . $type->name,
                        'type' => $code,
                        'document_no' => $documentNo,
                        'revision_no' => '00',
                        'effective_date' => '2026-05-26',
                        'status' => 'active',
                        'route_name' => 'project-rfps.create',
                        'template_path' => 'modules.project-mgmt.rfps.create',
                        'code_prefix' => 'RFP-' . $code,
                        'rfp_type_id' => $type->id,
                        'revision_notes' => 'Initial controlled document revision.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Safe rollback: do not drop document_controls automatically because it may
        // already contain production document records.
    }
};
